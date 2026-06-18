<?php
require_once __DIR__ . '/app/Config/session_bootstrap.php';
labhub_session_start();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['perfil'] !== 'suporte' && $_SESSION['perfil'] !== 'coordenador')) {
    labhub_redirect_login('expired');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/app/Config/csrf_helpers.php';
    labhub_csrf_require_post();
}

require 'conexao.php';
require_once __DIR__ . '/bootstrap.php';
labhub_register_autoload();

use App\Models\Agendamento;
use App\Services\SosChamadoService;

$agendamento = new Agendamento();
$mensagem = '';
if (!empty($_SESSION['suporte_flash'])) {
    $mensagem = $_SESSION['suporte_flash'];
    unset($_SESSION['suporte_flash']);
}
$id_usuario_logado = $_SESSION['usuario_id'];
date_default_timezone_set('America/Sao_Paulo'); 

// --- LÓGICA: DAR BAIXA NA CHAVE (COM HORA REAL) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dar_baixa_chave'])) {
    try {
        $stmt = $pdo->prepare("UPDATE controle_chaves SET status = 'devolvido', funcionario_recebimento = :func, hora_devolucao_real = :hora_real WHERE id = :id");
        $stmt->execute([
            ':func' => $_POST['func_recebe'],
            ':hora_real' => $_POST['hora_devolucao_real'],
            ':id' => $_POST['id_chave']
        ]);
        $mensagem = '<div class="alert alert-primary alert-autohide rounded-0 border-0 border-start border-4 border-primary shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i>Chave recebida e baixada com sucesso!</div>';
    } catch (PDOException $e) {
        $mensagem = '<div class="alert alert-danger alert-autohide rounded-0 mb-4">Erro ao dar baixa. Tente novamente.</div>';
        error_log('[painel_suporte] dar_baixa_chave: ' . $e->getMessage());
    }
}

// --- LÓGICA DE UPLOAD DE FOTO ---
if (isset($_FILES['nova_foto']) && $_FILES['nova_foto']['error'] === UPLOAD_ERR_OK) {
    $extensao = strtolower(pathinfo($_FILES['nova_foto']['name'], PATHINFO_EXTENSION));
    $formatos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($extensao, $formatos_permitidos)) {
        $diretorio = 'uploads/';
        if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);
        $novo_nome = 'user_' . $id_usuario_logado . '_' . time() . '.' . $extensao;
        $destino = $diretorio . $novo_nome;
        if (move_uploaded_file($_FILES['nova_foto']['tmp_name'], $destino)) {
            try {
                if (!empty($_SESSION['foto_perfil']) && file_exists($_SESSION['foto_perfil'])) unlink($_SESSION['foto_perfil']);
                $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
                $stmt->execute([':foto' => $destino, ':id' => $id_usuario_logado]);
                $_SESSION['foto_perfil'] = $destino;
                header('Location: painel_suporte.php?aba=sessao-perfil&foto=ok');
                exit;
            } catch (PDOException $e) { $mensagem = '<div class="alert alert-danger alert-autohide mb-4">Erro ao salvar foto.</div>'; }
        }
    }
}

// --- ATUALIZAR CHAMADO SOS (fluxo completo) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_chamado_sos'])) {
    $svc = new SosChamadoService($pdo);
    $resultado = $svc->atualizar(
        (int) ($_POST['id_chamado'] ?? 0),
        $_POST,
        (int) $id_usuario_logado,
        (string) ($_SESSION['nome'] ?? 'Suporte TI')
    );
    $tipo = $resultado['ok'] ? 'success' : 'danger';
    $_SESSION['suporte_flash'] = '<div class="alert alert-' . $tipo . ' alert-autohide rounded-0 border-0 border-start border-4 border-' . $tipo . ' shadow-sm mb-4">'
        . htmlspecialchars($resultado['msg']) . '</div>';
    header('Location: painel_suporte.php?aba=sessao-sos-ativos');
    exit;
}

// Legado: encerrar rápido redireciona para fluxo completo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resolver_chamado'])) {
    $_POST['atualizar_chamado_sos'] = 1;
    $_POST['status'] = 'resolvido';
    $_POST['resposta_professor'] = $_POST['resposta_professor'] ?? 'Chamado encerrado pelo suporte.';
    $_POST['enviar_email'] = $_POST['enviar_email'] ?? 0;
    $svc = new SosChamadoService($pdo);
    $resultado = $svc->atualizar(
        (int) ($_POST['id_chamado'] ?? 0),
        $_POST,
        (int) $id_usuario_logado,
        (string) ($_SESSION['nome'] ?? 'Suporte TI')
    );
    $tipo = $resultado['ok'] ? 'success' : 'danger';
    $_SESSION['suporte_flash'] = '<div class="alert alert-' . $tipo . ' alert-autohide rounded-0 border-0 border-start border-4 border-' . $tipo . ' shadow-sm mb-4">'
        . htmlspecialchars($resultado['msg']) . '</div>';
    header('Location: painel_suporte.php?aba=sessao-sos-ativos');
    exit;
}

$foto_atual = app_foto_perfil_usuario($pdo, (int) $id_usuario_logado);

if (isset($_GET['foto']) && $_GET['foto'] === 'ok') {
    $mensagem = '<div class="alert alert-success alert-autohide rounded-0 border-0 border-start border-4 border-success shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i>Foto atualizada com sucesso!</div>';
}

// --- BUSCAS DE DADOS ---
$alertas_suporte = [];
try {
    $stmt_alertas = $pdo->query("SELECT * FROM chamados_suporte WHERE " . sos_sql_in_ativos() . " ORDER BY data_hora DESC");
    $alertas_suporte = $stmt_alertas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
$qtd_alertas = count($alertas_suporte);

$historico_chamados = [];
try {
    $stmt_hist_cham = $pdo->query(
        "SELECT * FROM chamados_suporte WHERE " . sos_sql_in_encerrados()
        . " ORDER BY COALESCE(resolvido_em, atualizado_em, data_hora) DESC LIMIT 200"
    );
    $historico_chamados = $stmt_hist_cham->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$chaves_em_uso_assoc = [];
try {
    $stmt_chaves = $pdo->query("SELECT * FROM controle_chaves WHERE status = 'em_uso'");
    $chaves_em_uso = $stmt_chaves->fetchAll(PDO::FETCH_ASSOC);
    foreach ($chaves_em_uso as $chave) {
        $chaves_em_uso_assoc[$chave['id_agendamento']] = $chave; 
    }
} catch (PDOException $e) {}

$historico_chaves = [];
try {
    $stmt_hist_chaves = $pdo->query("SELECT * FROM controle_chaves ORDER BY data_uso DESC, hora_retirada DESC");
    $historico_chaves = $stmt_hist_chaves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$lista_laboratorios = $pdo->query("SELECT * FROM laboratorios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

$data_filtro = isset($_GET['data_busca']) ? $_GET['data_busca'] : date('Y-m-d');

// --- 1. BUSCA DE AGENDAMENTOS AVULSOS ---
$stmt_aloc = $pdo->prepare("SELECT a.id, l.nome as laboratorio, u.nome as professor, d.nome as disciplina, a.turno, a.periodo 
        FROM agendamentos a
        INNER JOIN laboratorios l ON a.id_laboratorio = l.id
        INNER JOIN usuarios u ON a.id_professor = u.id
        INNER JOIN disciplinas d ON a.id_disciplina = d.id
        WHERE a.data_reserva = :data AND a.status = 'aprovado'
        ORDER BY " . app_sql_order_turno('a.turno') . ", l.nome ASC");
$stmt_aloc->execute([':data' => $data_filtro]);
$alocacoes = $stmt_aloc->fetchAll(PDO::FETCH_ASSOC);

// --- 2. INTEGRAÇÃO COM QUADRO DE HORÁRIOS (NOVO) ---
$id_quadro_ativo = $pdo->query("SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1")->fetchColumn();
$dias_map = [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'];
$dia_semana_filtro = $dias_map[date('w', strtotime($data_filtro))];

if ($id_quadro_ativo) {
    $stmt_qa = $pdo->prepare("SELECT qa.id, l.nome as laboratorio, u.nome as professor, d.nome as disciplina, qa.turno, qa.horario as periodo 
        FROM quadro_aulas qa 
        INNER JOIN laboratorios l ON qa.id_laboratorio = l.id 
        INNER JOIN usuarios u ON qa.id_professor = u.id 
        INNER JOIN disciplinas d ON qa.id_disciplina = d.id
        WHERE qa.id_quadro = ? AND qa.dia_semana = ? AND qa.id_laboratorio IS NOT NULL");
    $stmt_qa->execute([$id_quadro_ativo, $dia_semana_filtro]);
    $aulas_fixas = $stmt_qa->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($aulas_fixas as $af) {
        $af['id'] = $af['id'] + 1000000; // Soma 1 milhão no ID para evitar conflito de chave com os avulsos
        $alocacoes[] = $af;
    }
}

// --- LÓGICA DE AGRUPAMENTO E CONTADORES ---
$total_reservas = count($alocacoes);
$alocacoes_matutino = [];
$alocacoes_vespertino = [];
$alocacoes_noturno = [];

foreach ($alocacoes as $linha) {
    $turno_limpo = trim($linha['turno']);
    if ($turno_limpo === 'Matutino' || $turno_limpo === 'Manhã') {
        $alocacoes_matutino[] = $linha;
    } elseif ($turno_limpo === 'Vespertino' || $turno_limpo === 'Tarde') {
        $alocacoes_vespertino[] = $linha;
    } elseif ($turno_limpo === 'Noturno' || $turno_limpo === 'Noite') {
        $alocacoes_noturno[] = $linha;
    }
}

$qtd_matutino = count($alocacoes_matutino);
$qtd_vespertino = count($alocacoes_vespertino);
$qtd_noturno = count($alocacoes_noturno);

// --- FUNÇÃO QUE RENDERIZA O CARD ---
function renderizarCardSuporte($l, $chaves_em_uso_assoc, $borda) {
    $nome_lab = $l['laboratorio'];
    $id_agendamento = $l['id']; 
    
    $chave_ativa = isset($chaves_em_uso_assoc[$id_agendamento]) ? $chaves_em_uso_assoc[$id_agendamento] : null;
    $modal_id = $id_agendamento . '_' . uniqid();
    
    $esta_atrasado = false;
    $link_wpp = "#";
    
    if($chave_ativa) {
        $hora_atual = date('H:i:s');
        $esta_atrasado = ($hora_atual > $chave_ativa['hora_devolucao_prevista']);
        
        $numero_limpo = preg_replace('/[^0-9]/', '', $chave_ativa['celular']);
        if(strlen($numero_limpo) == 10 || strlen($numero_limpo) == 11) { $numero_limpo = "55" . $numero_limpo; }
        $msg_wpp = urlencode("Olá, Prof. {$chave_ativa['professor_nome']}. Aqui é do Suporte TI da UNICEPLAC. Notamos que a chave do {$nome_lab} ainda consta com você. Já finalizou a aula?");
        $link_wpp = "https://wa.me/{$numero_limpo}?text={$msg_wpp}";
    }
    ?>
    <div class="col">
        <div class="apple-ticket card h-100 <?= $borda ?> <?= $esta_atrasado ? 'border border-danger border-2' : '' ?> p-3 position-relative">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="fw-bold <?= $esta_atrasado ? 'text-danger' : 'text-dark' ?> mb-2 text-truncate"><?= htmlspecialchars($l['laboratorio']) ?></h6>
                <?php if($esta_atrasado): ?>
                    <i class="bi bi-exclamation-triangle-fill text-danger heartbeat fs-5" title="Atrasado!"></i>
                <?php endif; ?>
            </div>
            
            <div class="small mb-1 text-primary fw-bold"><i class="bi bi-clock-history me-2"></i><?= htmlspecialchars($l['periodo']) ?></div>
            <div class="small mb-1"><i class="bi bi-person me-2 text-muted"></i><?= htmlspecialchars($l['professor']) ?></div>
            <div class="small text-secondary <?= $chave_ativa ? 'mb-3' : '' ?>"><i class="bi bi-book me-2 text-muted"></i><?= htmlspecialchars($l['disciplina']) ?></div>
            
            <?php if ($chave_ativa): ?>
                <hr class="my-2 opacity-25">
                <?php if($esta_atrasado): ?>
                    <div class="apple-tag late"><div class="apple-dot late"></div> ATRASADO</div>
                    <button class="apple-btn apple-btn-danger heartbeat mt-2" data-bs-toggle="modal" data-bs-target="#modalDetalheChave<?= $modal_id ?>"><i class="bi bi-key-fill me-2"></i> Cobrar Chave</button>
                <?php else: ?>
                    <div class="apple-tag in-use"><div class="apple-dot in-use"></div> EM USO</div>
                    <button class="apple-btn apple-btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalDetalheChave<?= $modal_id ?>"><i class="bi bi-key-fill me-2"></i> Receber Chave</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($chave_ativa): ?>
        <div class="modal fade" id="modalDetalheChave<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content <?= $esta_atrasado ? 'border-danger' : 'border-primary' ?>" style="border-width: 3px; border-radius: 20px;">
                    <div class="modal-header <?= $esta_atrasado ? 'bg-danger' : 'bg-primary' ?> text-white border-0" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <h5 class="modal-title fw-bold"><i class="bi bi-key me-2"></i> Detalhes da Retirada</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start p-4">
                        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><?= htmlspecialchars($nome_lab) ?></h5>
                        <div class="mb-3">
                            <p class="mb-1 text-secondary small"><strong>Professor:</strong> <span class="text-dark"><?= htmlspecialchars($chave_ativa['professor_nome']) ?></span></p>
                            <p class="mb-1 text-secondary small"><strong>Entregue por:</strong> <span class="text-dark"><?= htmlspecialchars($chave_ativa['funcionario_entrega']) ?></span></p>
                            <p class="mb-1 text-secondary small"><strong>Hora da Retirada:</strong> <span class="text-dark"><?= date('H:i', strtotime($chave_ativa['hora_retirada'])) ?></span></p>
                        </div>
                        <div class="alert <?= $esta_atrasado ? 'alert-danger shadow-sm' : 'alert-warning' ?> py-3 mb-3 d-flex justify-content-between align-items-center" style="border-radius: 15px;">
                            <div><strong class="small opacity-75">PREVISÃO DE VOLTA</strong> <br><span class="fs-4 fw-bold"><?= date('H:i', strtotime($chave_ativa['hora_devolucao_prevista'])) ?></span></div>
                            <?php if($esta_atrasado): ?><span class="badge bg-danger fs-6 heartbeat py-2 px-3 rounded-pill"><i class="bi bi-exclamation-triangle-fill me-1"></i> ATRASADO</span><?php endif; ?>
                        </div>
                        
                        <a href="<?= $link_wpp ?>" target="_blank" class="apple-btn" style="background: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.2); color: #198754; margin-bottom: 1.5rem;"><i class="bi bi-whatsapp fs-5 me-2"></i> Mandar WhatsApp</a>
                        
                        <form method="POST" action="painel_suporte.php" class="bg-light p-3 border rounded-4">
                            <input type="hidden" name="dar_baixa_chave" value="1">
                            <input type="hidden" name="id_chave" value="<?= $chave_ativa['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">Hora da Devolução (Real):</label>
                                <input type="time" class="form-control rounded-pill px-3" name="hora_devolucao_real" value="<?= date('H:i') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-secondary">Recebido por (Seu Nome):</label>
                                <input type="text" class="form-control rounded-pill px-3" name="func_recebe" placeholder="Ex: Técnico João" required>
                            </div>
                            <button type="submit" class="btn <?= $esta_atrasado ? 'btn-danger' : 'btn-primary' ?> w-100 fw-bold py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Confirmar Devolução</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Suporte TI - UNICEPLAC</title>
    <?php require __DIR__ . '/app/Views/partials/favicon.php'; ?>
    <?php require __DIR__ . '/app/Views/partials/csrf-meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/labhub-theme.css?v=20260619">
    <link rel="stylesheet" href="css/labhub-layout.css?v=20260619">
    <link rel="stylesheet" href="css/notificacoes-nav.css">
    <link rel="stylesheet" href="css/labhub-alerts.css?v=20260619">
    <script>
        const savedTheme = localStorage.getItem('tema-uniceplac') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>

    <style>
        :root { 
            --verde-uniceplac: #00734F; --roxo-uniceplac: #421B71; --laranja-uniceplac: #F0733C; 
            --manha-cor: #ffc107; --tarde-cor: #fd7e14; --noite-cor: #421B71; 
        }
        body { background-color: #f4f6f8; transition: background-color 0.3s ease; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .card, .card-header, .form-control, .form-select, .btn, .badge, .alert, .offcanvas { border-radius: 0 !important; }
        .bg-uniceplac { background-color: var(--lh-verde) !important; color: #fff !important; }
        .navbar { border-bottom: 1px solid rgba(0,0,0,0.05) !important; background: rgba(255, 255, 255, 0.9) !important; backdrop-filter: blur(10px); }
        
        .content-section { display: none; animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .offcanvas-menu-link { padding: 12px 20px; color: #495057; text-decoration: none; display: block; border-bottom: 1px solid #f1f1f1; font-weight: 500; transition: background-color 0.2s; }
        .offcanvas-menu-link:hover, .offcanvas-menu-link.active-link { background-color: rgba(0, 115, 79, 0.05); color: var(--verde-uniceplac); border-right: 4px solid var(--verde-uniceplac); }
        
        .avatar-img-small { width: 40px; height: 40px; object-fit: cover; border-radius: 50% !important; border: 2px solid #dee2e6; cursor: pointer; }
        .top-icon-btn { color: #495057; font-size: 1.3rem; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; transition: 0.2s;}
        .top-icon-btn:hover { color: var(--verde-uniceplac); }
        
        @keyframes sos-pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }

        .sos-historico-timeline { max-height: 220px; overflow-y: auto; }
        .sos-historico-item { display: flex; gap: 0.75rem; padding: 0.65rem 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.06); }
        .sos-historico-item:last-child { border-bottom: none; }
        .sos-historico-icon { flex-shrink: 0; width: 2rem; height: 2rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; background: rgba(0, 115, 79, 0.12); color: var(--lh-verde); }
        .sos-historico-item[data-tipo="observacao_interna"] .sos-historico-icon { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
        .sos-historico-item[data-tipo="resposta_professor"] .sos-historico-icon { background: rgba(25, 135, 84, 0.12); color: #198754; }
        .sos-historico-item[data-tipo="status"] .sos-historico-icon { background: rgba(0, 115, 79, 0.12); color: var(--lh-verde); }
        .sos-historico-item[data-tipo="email"] .sos-historico-icon { background: rgba(255, 193, 7, 0.15); color: #997404; }
        .sos-historico-meta { font-size: 0.72rem; color: #6c757d; }
        .sos-historico-texto { font-size: 0.85rem; white-space: pre-wrap; word-break: break-word; }
        [data-bs-theme="dark"] .sos-historico-timeline { background: #252525 !important; border-color: #333 !important; }
        [data-bs-theme="dark"] .sos-historico-item { border-bottom-color: #333; }
        
        @keyframes heartbeat { 0% { transform: scale(1); } 20% { transform: scale(1.05); } 40% { transform: scale(1); } 60% { transform: scale(1.05); } 80% { transform: scale(1); } 100% { transform: scale(1); } }
        .heartbeat { animation: heartbeat 1.5s infinite; }

        /* =========================================
           ESTILOS APPLE GLASSMORPHISM
           ========================================= */
        .apple-kpi { border-radius: 24px !important; padding: 24px 20px; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4) !important; box-shadow: 0 8px 32px rgba(0,0,0,0.06); position: relative; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .apple-kpi:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }
        
        .kpi-total { background: linear-gradient(135deg, rgba(0, 115, 79, 0.05), rgba(0, 115, 79, 0.15)); border-color: rgba(0, 115, 79, 0.2) !important; }
        .kpi-matutino { background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), rgba(255, 193, 7, 0.15)); border-color: rgba(255, 193, 7, 0.2) !important; }
        .kpi-vespertino { background: linear-gradient(135deg, rgba(253, 126, 20, 0.05), rgba(253, 126, 20, 0.15)); border-color: rgba(253, 126, 20, 0.2) !important; }
        .kpi-noturno { background: linear-gradient(135deg, rgba(66, 27, 113, 0.05), rgba(66, 27, 113, 0.15)); border-color: rgba(66, 27, 113, 0.2) !important; }

        .apple-ticket { border-radius: 20px !important; border: 1px solid rgba(0,0,0,0.08) !important; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: all 0.3s ease; position: relative; overflow: hidden; }
        .apple-ticket:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-3px); }
        
        .border-matutino { border-left: 6px solid var(--manha-cor) !important; }
        .border-vespertino { border-left: 6px solid var(--tarde-cor) !important; }
        .border-noturno { border-left: 6px solid var(--noite-cor) !important; }

        .apple-tag { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 30px; font-size: 0.70rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; backdrop-filter: blur(8px); width: 100%; margin-bottom: 8px; }
        .apple-tag.in-use { background: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.2); color: #198754; }
        .apple-tag.late { background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #dc3545; }
        
        .apple-dot { width: 8px; height: 8px; border-radius: 50%; margin-right: 8px; }
        .apple-dot.in-use { background-color: #198754; box-shadow: 0 0 6px #198754; animation: pulse-dot-green 1.5s infinite; }
        .apple-dot.late { background-color: #dc3545; box-shadow: 0 0 6px #dc3545; animation: pulse-dot-red 1.5s infinite; }
        
        @keyframes pulse-dot-green { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); } }
        @keyframes pulse-dot-red { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }

        .apple-btn { display: flex; align-items: center; justify-content: center; width: 100%; padding: 8px 16px; border-radius: 30px; font-size: 0.85rem; font-weight: 600; backdrop-filter: blur(8px); transition: all 0.2s ease; cursor: pointer; text-decoration: none; border: none; }
        .apple-btn-primary { background: rgba(0, 115, 79, 0.1); border: 1px solid rgba(0, 115, 79, 0.2); color: var(--lh-verde); }
        .apple-btn-primary:hover { background: rgba(0, 115, 79, 0.2); transform: translateY(-1px); }
        .apple-btn-danger { background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #dc3545; }
        .apple-btn-danger:hover { background: rgba(220, 53, 69, 0.2); transform: translateY(-1px); }

        /* Divisórias de Turno */
        .turn-divider { display: flex; align-items: center; margin: 2rem 0 1rem; color: #6c757d; }
        .turn-divider::before, .turn-divider::after { content: ""; flex: 1; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .turn-divider:not(:empty)::before { margin-right: 1em; }
        .turn-divider:not(:empty)::after { margin-left: 1em; }
        .turn-badge { display: inline-flex; align-items: center; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; backdrop-filter: blur(10px); }
        .badge-matutino { background: rgba(255, 193, 7, 0.15); color: #d39e00; border: 1px solid rgba(255, 193, 7, 0.3); }
        .badge-vespertino { background: rgba(253, 126, 20, 0.15); color: #e85e00; border: 1px solid rgba(253, 126, 20, 0.3); }
        .badge-noturno { background: rgba(66, 27, 113, 0.15); color: #421B71; border: 1px solid rgba(66, 27, 113, 0.3); }

        #nova_foto_input { display: none; }

        /* =========================================
           MODO ESCURO (DARK MODE APPLE)
           ========================================= */
        [data-bs-theme="dark"] body { background-color: #121212; color: #e0e0e0; }
        [data-bs-theme="dark"] .bg-white, [data-bs-theme="dark"] .bg-light { background-color: #1e1e1e !important; color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .navbar { background: rgba(30, 30, 30, 0.9) !important; border-bottom-color: #333 !important; }
        [data-bs-theme="dark"] .card { background-color: #1e1e1e; border-color: #333 !important; }
        
        [data-bs-theme="dark"] .apple-kpi { background: rgba(40,40,40,0.6); border-color: rgba(255,255,255,0.1) !important; }
        [data-bs-theme="dark"] .kpi-total { background: linear-gradient(135deg, rgba(0, 115, 79, 0.15), rgba(0, 115, 79, 0.3)); color: #28c76f; border-color: rgba(40,199,111,0.3) !important; }
        [data-bs-theme="dark"] .kpi-matutino { background: linear-gradient(135deg, rgba(255, 193, 7, 0.15), rgba(255, 193, 7, 0.3)); color: #ffc107; border-color: rgba(255,193,7,0.3) !important; }
        [data-bs-theme="dark"] .kpi-vespertino { background: linear-gradient(135deg, rgba(253, 126, 20, 0.15), rgba(253, 126, 20, 0.3)); color: #fd7e14; border-color: rgba(253,126,20,0.3) !important; }
        [data-bs-theme="dark"] .kpi-noturno { background: linear-gradient(135deg, rgba(162, 106, 226, 0.15), rgba(162, 106, 226, 0.3)); color: #a26ae2; border-color: rgba(162,106,226,0.3) !important; }

        [data-bs-theme="dark"] .apple-ticket { background: rgba(35,35,35,0.7); border-color: rgba(255,255,255,0.08) !important; }
        [data-bs-theme="dark"] .text-dark { color: #f8f9fa !important; }
        [data-bs-theme="dark"] .text-secondary, [data-bs-theme="dark"] .text-muted { color: #adb5bd !important; }
        [data-bs-theme="dark"] .table { color: #e0e0e0; border-color: #444; }
        [data-bs-theme="dark"] .table-light th { background-color: #2a2a2a !important; color: #e0e0e0; border-color: #444; }
        [data-bs-theme="dark"] .table-hover tbody tr:hover { background-color: #2a2a2a !important; color: #fff; }
        [data-bs-theme="dark"] .offcanvas { background-color: #1e1e1e !important; }
        [data-bs-theme="dark"] .offcanvas-menu-link { color: #e0e0e0; border-bottom-color: #333; }
        [data-bs-theme="dark"] .offcanvas-menu-link:hover { background-color: rgba(255,255,255,0.05); }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #2a2a2a; color: #fff; border-color: #444; }
        [data-bs-theme="dark"] .form-control:focus, [data-bs-theme="dark"] .form-select:focus { background-color: #333; color: #fff; border-color: var(--verde-uniceplac); }
        [data-bs-theme="dark"] .modal-content { background-color: #1e1e1e; border-color: #444; border-radius: 20px !important; }
        [data-bs-theme="dark"] .top-icon-btn { color: #e0e0e0; }
        [data-bs-theme="dark"] .top-icon-btn:hover { color: var(--laranja-uniceplac); }
        
        [data-bs-theme="dark"] .apple-tag.in-use { background: rgba(40, 199, 111, 0.15); border-color: rgba(40, 199, 111, 0.3); color: #28c76f; }
        [data-bs-theme="dark"] .apple-dot.in-use { background-color: #28c76f; box-shadow: 0 0 6px #28c76f; }
        [data-bs-theme="dark"] .apple-tag.late { background: rgba(234, 84, 85, 0.15); border-color: rgba(234, 84, 85, 0.3); color: #ea5455; }
        [data-bs-theme="dark"] .apple-dot.late { background-color: #ea5455; box-shadow: 0 0 6px #ea5455; }
        
        [data-bs-theme="dark"] .apple-btn-primary { background: rgba(0, 115, 79, 0.15); border-color: rgba(0, 115, 79, 0.3); color: #86efac; }
        [data-bs-theme="dark"] .apple-btn-danger { background: rgba(234, 84, 85, 0.12); border-color: rgba(234, 84, 85, 0.3); color: #ea5455; }
        
        [data-bs-theme="dark"] .turn-divider::before, [data-bs-theme="dark"] .turn-divider::after { border-bottom-color: rgba(255,255,255,0.1); }
        [data-bs-theme="dark"] .badge-matutino { background: rgba(255, 193, 7, 0.15); color: #ffc107; border-color: rgba(255, 193, 7, 0.3); }
        [data-bs-theme="dark"] .badge-vespertino { background: rgba(253, 126, 20, 0.15); color: #fd7e14; border-color: rgba(253, 126, 20, 0.3); }
        [data-bs-theme="dark"] .badge-noturno { background: rgba(162, 106, 226, 0.15); color: #a26ae2; border-color: rgba(162, 106, 226, 0.3); }
    </style>
</head>
<body>

    <form id="formFotoPerfil" action="painel_suporte.php" method="POST" enctype="multipart/form-data" class="d-none">
        <input type="file" name="nova_foto" id="nova_foto_input" accept="image/png, image/jpeg, image/webp">
    </form>

    <nav class="navbar navbar-light bg-white mb-4 shadow-sm sticky-top">
        <div class="container-fluid px-3 px-md-4">
            <a href="#sessao-sos-ativos" class="navbar-brand d-flex align-items-center text-decoration-none lh-navbar-home"
                title="Início — Chamados ativos"
                onclick="event.preventDefault(); if(typeof fecharNotificacoesPopup==='function') fecharNotificacoesPopup(); bootstrap.Offcanvas.getInstance(document.getElementById('sidebarMenu'))?.hide(); showSection('sessao-sos-ativos', false); window.scrollTo({top:0,behavior:'smooth'});">
                <img src="uniceplac.png" id="navbarLogo" alt="Logo UNICEPLAC — início" style="height: 70px; margin-right: 12px; transition: 0.3s;">
            </a>
            <div class="ms-auto d-flex align-items-center">
                <div class="me-4 top-icon-btn" id="themeToggleBtn" title="Alternar Tema"><i class="bi bi-moon-stars" id="themeIcon"></i></div>
                <?php
                $notif_qtd = $qtd_alertas;
                $notif_extra_badges = ['badge-sos-menu'];
                require __DIR__ . '/app/Views/partials/notificacoes-nav.php';
                ?>
                <div class="me-3 top-icon-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><i class="bi bi-grid-3x3-gap fs-5"></i></div>
                <img src="<?= htmlspecialchars($foto_atual) ?>" alt="Foto" class="avatar-img-small ms-1" id="btnAlterarFotoNav"
                    title="Meu perfil" style="cursor:pointer;" onclick="showSection('sessao-perfil')">
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header bg-uniceplac text-white py-3 border-0">
            <h6 class="offcanvas-title fw-bold">Menu do Suporte</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column bg-white">
            <div class="p-4 text-center border-bottom bg-light">
                <img src="<?= htmlspecialchars($foto_atual) ?>" alt="Foto" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50% !important; border: 3px solid var(--laranja-uniceplac);" class="shadow-sm mb-2">
                <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($_SESSION['nome']) ?></h5>
                <span class="badge lh-badge lh-badge-suporte text-uppercase mt-2 px-3 py-1">Suporte TI</span>
            </div>
            <div class="flex-grow-1 overflow-auto">
                <div class="p-3 text-muted small fw-bold text-uppercase opacity-50">Operacional</div>
                <a href="#sessao-mapa-diario" class="offcanvas-menu-link active-link"><i class="bi bi-speedometer2 text-primary me-2"></i> Mapa Diário</a>
                <a href="#sessao-sos-ativos" class="offcanvas-menu-link">
                    <i class="bi bi-headset text-sos-attention me-2"></i> Chamados Ativos
                    <span id="badge-sos-menu" class="badge badge-sos-count ms-2 <?= $qtd_alertas > 0 ? '' : 'd-none' ?>"><?= $qtd_alertas ?></span>
                </a>
                <a href="#sessao-labs" class="offcanvas-menu-link"><i class="bi bi-pc-display text-secondary me-2"></i> Laboratórios</a>
                
                <div class="p-3 text-muted small fw-bold text-uppercase opacity-50 border-top mt-2">Relatórios Gerenciais</div>
                <a href="#sessao-historico-chaves" class="offcanvas-menu-link"><i class="bi bi-key text-info me-2"></i> Histórico de Chaves</a>
                <a href="#sessao-historico-chamados" class="offcanvas-menu-link"><i class="bi bi-headset text-success me-2"></i> Chamados Atendidos</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-perfil'); bootstrap.Offcanvas.getInstance(document.getElementById('sidebarMenu'))?.hide();" class="offcanvas-menu-link"><i class="bi bi-person-circle text-secondary me-2"></i> Meu Perfil</a>
                
                <?php if($_SESSION['perfil'] === 'coordenador'): ?>
                    <hr class="my-2 mx-3 border-secondary opacity-25">
                    <div class="p-3 text-muted small fw-bold text-uppercase opacity-50">Administração</div>
                    <a href="painel_coordenador.php" class="offcanvas-menu-link"><i class="bi bi-arrow-left-circle text-warning me-2"></i> Voltar Coordenação</a>
                <?php endif; ?>
            </div>
            <div class="p-3 border-top mt-auto"><a href="logout.php" class="btn btn-outline-danger w-100 fw-bold">Sair</a></div>
        </div>
    </div>

    <div class="container-fluid px-4 pb-5">
        
        <div id="container-mensagens">
            <?= $mensagem ?>
        </div>

        <div id="area-chamados-dinamica"></div>
        
        <div id="sessao-sos-ativos" class="content-section">
            <div class="card shadow-sm border-0 mb-4 card-sos-attention">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="bi bi-headset me-3 fs-4"></i> Chamados em aberto
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Atualize status, registre observações internas e responda ao professor (com e-mail opcional).</p>
                    </div>
                    <span class="badge badge-sos-count rounded-pill px-3 py-2"><?= $qtd_alertas ?> em aberto</span>
                </div>
                <div class="card-body p-0">
                    <?php if ($qtd_alertas > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 py-3">Data/Hora</th>
                                        <th>Professor</th>
                                        <th>Laboratório</th>
                                        <th style="min-width: 200px;">Problema</th>
                                        <th>Status</th>
                                        <th class="pe-4">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alertas_suporte as $ch): ?>
                                        <tr class="tr-sos-attention" data-sos-id="<?= (int) $ch['id'] ?>"
                                            data-sos-json="<?= htmlspecialchars(json_encode([
                                                'id' => (int) $ch['id'],
                                                'professor_nome' => $ch['professor_nome'] ?? '',
                                                'laboratorio' => $ch['laboratorio'] ?? '',
                                                'mensagem' => $ch['mensagem'] ?? '',
                                                'status' => $ch['status'] ?? 'pendente',
                                                'historico' => sos_historico_lista($ch),
                                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>">
                                            <td class="ps-4"><strong><?= date('d/m H:i', strtotime($ch['data_hora'])) ?></strong></td>
                                            <td><?= htmlspecialchars($ch['professor_nome']) ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($ch['laboratorio']) ?></td>
                                            <td class="sos-problema-cell text-muted small">
                                                <?php
                                                $msg = trim($ch['mensagem'] ?? '');
                                                $msgLonga = mb_strlen($msg) > 120;
                                                ?>
                                                <div class="sos-problema-text<?= $msgLonga ? ' sos-problema-collapsed' : '' ?>"><?= nl2br(htmlspecialchars($msg)) ?></div>
                                                <?php if ($msgLonga): ?>
                                                    <button type="button" class="sos-problema-toggle">Ver descrição completa</button>
                                                <?php endif; ?>
                                                <?php
                                                $ultimaObs = sos_historico_ultima_obs_interna($ch);
                                                if ($ultimaObs !== ''):
                                                ?>
                                                    <div class="mt-2 p-2 bg-light rounded small border-start border-3 border-secondary">
                                                        <span class="fw-bold text-secondary">Última obs. TI:</span> <?= nl2br(htmlspecialchars($ultimaObs)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= sos_render_badge($ch['status'] ?? 'pendente') ?></td>
                                            <td class="pe-4">
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold btn-atender-chamado">
                                                    <i class="bi bi-pencil-square me-1"></i> Atender
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle fs-1 text-success opacity-50 d-block mb-2"></i>
                            <p class="text-muted mb-0">Nenhum chamado em aberto. Tudo tranquilo!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="sessao-mapa-diario" class="content-section">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="apple-kpi kpi-total">
                        <span class="d-block small fw-bold text-uppercase mb-1 opacity-75">Total Hoje</span>
                        <h2 class="fw-bold m-0"><?= $total_reservas ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="apple-kpi kpi-matutino">
                        <span class="d-block small fw-bold text-uppercase mb-1 opacity-75">Matutino</span>
                        <h2 class="fw-bold m-0"><?= $qtd_matutino ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="apple-kpi kpi-vespertino">
                        <span class="d-block small fw-bold text-uppercase mb-1 opacity-75">Vespertino</span>
                        <h2 class="fw-bold m-0"><?= $qtd_vespertino ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="apple-kpi kpi-noturno">
                        <span class="d-block small fw-bold text-uppercase mb-1 opacity-75">Noturno</span>
                        <h2 class="fw-bold m-0"><?= $qtd_noturno ?></h2>
                    </div>
                </div>
            </div>

            <div class="row align-items-end mb-4 g-3">
                <div class="col-md-6"><h4 class="fw-bold text-uniceplac mb-1">Mapa de Ocupação</h4><p class="text-muted small mb-0">Grade do dia: <?= date('d/m/Y', strtotime($data_filtro)) ?></p></div>
                <div class="col-md-6 text-md-end">
                    <form action="painel_suporte.php" method="GET" class="d-inline-block shadow-sm" style="border-radius: 20px; overflow: hidden;">
                        <div class="input-group input-group-sm"><span class="input-group-text bg-white border-end-0 text-muted">Data</span><input type="date" class="form-control border-start-0 text-secondary" name="data_busca" value="<?= htmlspecialchars($data_filtro) ?>" onchange="this.form.submit()"></div>
                    </form>
                </div>
            </div>

            <div id="grid-mapa-diario">
                <?php if ($total_reservas > 0): ?>

                    <?php if ($qtd_matutino > 0): ?>
                        <div class="turn-divider"><span class="turn-badge badge-matutino"><i class="bi bi-sunrise-fill me-2"></i>Turno Matutino</span></div>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                            <?php foreach ($alocacoes_matutino as $l): renderizarCardSuporte($l, $chaves_em_uso_assoc, 'border-matutino'); endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($qtd_vespertino > 0): ?>
                        <div class="turn-divider"><span class="turn-badge badge-vespertino"><i class="bi bi-sun-fill me-2"></i>Turno Vespertino</span></div>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                            <?php foreach ($alocacoes_vespertino as $l): renderizarCardSuporte($l, $chaves_em_uso_assoc, 'border-vespertino'); endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($qtd_noturno > 0): ?>
                        <div class="turn-divider"><span class="turn-badge badge-noturno"><i class="bi bi-moon-stars-fill me-2"></i>Turno Noturno</span></div>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
                            <?php foreach ($alocacoes_noturno as $l): renderizarCardSuporte($l, $chaves_em_uso_assoc, 'border-noturno'); endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="apple-ticket text-center p-5 shadow-sm text-muted">
                        <i class="bi bi-calendar-x fs-1 opacity-50 mb-3 d-block"></i> Nenhuma reserva para a data selecionada.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="sessao-labs" class="content-section">
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--roxo-uniceplac);">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold d-flex align-items-center text-dark"><i class="bi bi-pc-display text-secondary me-3 fs-4"></i> Relação de Laboratórios</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive lh-table-scroll">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th class="ps-4 py-3">ID</th><th>Nome / Identificação</th><th>Capacidade</th></tr></thead>
                            <tbody>
                                <?php if (count($lista_laboratorios) > 0): ?>
                                    <?php foreach ($lista_laboratorios as $lab): ?>
                                        <tr>
                                            <td class="ps-4 text-muted">#<?= $lab['id'] ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($lab['nome']) ?></td>
                                            <td class="text-secondary"><i class="bi bi-people-fill me-2"></i><?= $lab['capacidade'] ?> lugares</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-5 text-muted">Nenhum laboratório cadastrado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="sessao-historico-chaves" class="content-section">
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #0dcaf0;">
                <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <h5 class="mb-3 mb-md-0 fw-bold d-flex align-items-center text-dark"><i class="bi bi-key text-info me-3 fs-4"></i> Histórico / Log de Chaves</h5>
                    <button class="btn btn-outline-success fw-bold shadow-sm rounded-pill px-4" onclick="exportarTabelaParaCSV('tabela-historico-chaves', 'Relatorio_Chaves_Uniceplac.csv')">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Baixar Planilha (CSV)
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive lh-table-scroll">
                        <table id="tabela-historico-chaves" class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Data</th>
                                    <th>Laboratório</th>
                                    <th>Professor</th>
                                    <th>Hr. Retirada</th>
                                    <th>Hr. Prevista</th>
                                    <th>Hr. Real (Devolução)</th>
                                    <th>Téc. Entrega</th>
                                    <th>Téc. Recebeu</th>
                                    <th class="pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($historico_chaves) > 0): ?>
                                    <?php foreach ($historico_chaves as $log): ?>
                                        <tr>
                                            <td class="ps-4"><strong><?= date('d/m/Y', strtotime($log['data_uso'])) ?></strong></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($log['laboratorio']) ?></td>
                                            <td><?= htmlspecialchars($log['professor_nome']) ?> <br><small class="text-muted"><?= htmlspecialchars($log['celular']) ?></small></td>
                                            <td class="text-primary fw-bold"><?= date('H:i', strtotime($log['hora_retirada'])) ?></td>
                                            <td class="text-secondary"><?= date('H:i', strtotime($log['hora_devolucao_prevista'])) ?></td>
                                            <td class="text-primary fw-bold">
                                                <?= ($log['status'] == 'devolvido' && !empty($log['hora_devolucao_real'])) ? date('H:i', strtotime($log['hora_devolucao_real'])) : '-' ?>
                                            </td>
                                            <td><small><?= htmlspecialchars($log['funcionario_entrega']) ?></small></td>
                                            <td><small><?= $log['funcionario_recebimento'] ? htmlspecialchars($log['funcionario_recebimento']) : '-' ?></small></td>
                                            <td class="pe-4">
                                                <?php if($log['status'] == 'devolvido'): ?>
                                                    <span class="badge bg-success rounded-pill px-3">Devolvido</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark rounded-pill px-3">Em Uso</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center py-5 text-muted">Nenhum registro de chaves encontrado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="sessao-historico-chamados" class="content-section">
            <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid #198754;">
                <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h5 class="mb-1 fw-bold d-flex align-items-center text-dark">
                            <i class="bi bi-headset text-success me-3 fs-4"></i> Histórico de chamados
                        </h5>
                        <p class="text-muted small mb-0">Resolvidos e não resolvidos — com resposta enviada ao professor.</p>
                    </div>
                    <button class="btn btn-outline-success fw-bold shadow-sm rounded-pill px-4 mt-2 mt-md-0" onclick="exportarTabelaParaCSV('tabela-historico-chamados', 'Relatorio_Chamados_SOS_Uniceplac.csv')">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Baixar Planilha (CSV)
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive lh-table-scroll">
                        <table id="tabela-historico-chamados" class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Encerrado em</th>
                                    <th>Laboratório</th>
                                    <th>Professor</th>
                                    <th>Problema</th>
                                    <th>Resposta TI</th>
                                    <th>Atendente</th>
                                    <th class="pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($historico_chamados) > 0): ?>
                                    <?php foreach ($historico_chamados as $chamado): ?>
                                        <tr data-sos-json="<?= htmlspecialchars(json_encode([
                                            'id' => (int) $chamado['id'],
                                            'professor_nome' => $chamado['professor_nome'] ?? '',
                                            'laboratorio' => $chamado['laboratorio'] ?? '',
                                            'mensagem' => $chamado['mensagem'] ?? '',
                                            'status' => $chamado['status'] ?? 'resolvido',
                                            'historico' => sos_historico_lista($chamado),
                                        ], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>">
                                            <td class="ps-4"><strong><?= !empty($chamado['resolvido_em']) ? date('d/m/Y H:i', strtotime($chamado['resolvido_em'])) : date('d/m/Y H:i', strtotime($chamado['data_hora'])) ?></strong></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($chamado['laboratorio']) ?></td>
                                            <td><?= htmlspecialchars($chamado['professor_nome']) ?></td>
                                            <td class="text-muted small"><?= nl2br(htmlspecialchars($chamado['mensagem'])) ?></td>
                                            <td class="small"><?= !empty($chamado['resposta_professor']) ? nl2br(htmlspecialchars($chamado['resposta_professor'])) : '<span class="text-muted">—</span>' ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($chamado['nome_atendente'] ?? '—') ?></td>
                                            <td class="pe-4">
                                                <?= sos_render_badge($chamado['status'] ?? 'resolvido') ?>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 btn-atender-chamado">Editar</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">Nenhum chamado encerrado encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/app/Views/partials/sessao-perfil.php'; ?>

    </div>

    <?php require __DIR__ . '/app/Views/partials/modal-atender-chamado.php'; sos_render_modal_atendimento(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/notificacoes-nav.js?v=20260619b"></script>
    
    <script>
        function exportarTabelaParaCSV(tableId, filename) {
            let csv = [];
            let rows = document.querySelectorAll("#" + tableId + " tr");
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " "); 
                    row.push('"' + text.replace(/"/g, '""') + '"');
                }
                csv.push(row.join(","));
            }
            let csvFile = new Blob(["\uFEFF" + csv.join("\n")], {type: "text/csv;charset=utf-8;"});
            let downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        function updateThemeElements(theme) {
            const icon = document.getElementById('themeIcon');
            const logo = document.getElementById('navbarLogo');
            if(theme === 'dark') {
                icon.classList.remove('bi-moon-stars'); icon.classList.add('bi-sun', 'text-warning');
                if (logo) logo.src = 'uniceplac.png'; 
            } else {
                icon.classList.remove('bi-sun', 'text-warning'); icon.classList.add('bi-moon-stars');
                if (logo) logo.src = 'uniceplac2.png'; 
            }
        }

        function abrirSosAtivos(item) {
            showSection('sessao-sos-ativos', false);
            setTimeout(function () {
                let row = null;
                if (item && item.id) {
                    row = document.querySelector('#sessao-sos-ativos tr[data-sos-id="' + item.id + '"]');
                }
                if (!row) {
                    row = document.querySelector('#sessao-sos-ativos tbody tr');
                }
                if (row && typeof window.destacarLinhaNotificacao === 'function') {
                    window.destacarLinhaNotificacao(row);
                } else {
                    const secao = document.getElementById('sessao-sos-ativos');
                    if (secao) secao.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 120);
        }
        window.abrirSosAtivos = abrirSosAtivos;

        function showSection(sectionId, scrollTop) {
            if (scrollTop === undefined) scrollTop = true;
            document.querySelectorAll('.content-section').forEach(sec => sec.style.display = 'none');
            document.querySelectorAll('.offcanvas-menu-link').forEach(link => link.classList.remove('active-link'));
            const target = document.getElementById(sectionId);
            if (target) {
                target.style.display = 'block';
                const activeLink = document.querySelector(`.offcanvas-menu-link[href="#${sectionId}"]`);
                if (activeLink) activeLink.classList.add('active-link');
                window.history.replaceState(null, null, '#' + sectionId);
                if (scrollTop) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }
        window.showSection = showSection;

        function monitorarTempoReal() {
            fetch('check_sos_status.php', { credentials: 'same-origin' })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    const area = document.getElementById('area-chamados-dinamica');
                    if (area) area.innerHTML = data.html_suporte || '';
                    const badgeSecao = document.querySelector('#sessao-sos-ativos .badge-sos-count');
                    if (badgeSecao) {
                        const qtd = parseInt(data.qtd_suporte, 10) || 0;
                        badgeSecao.textContent = qtd + ' em aberto';
                        badgeSecao.classList.toggle('d-none', qtd <= 0);
                    }
                    const sessaoSos = document.getElementById('sessao-sos-ativos');
                    if (sessaoSos && sessaoSos.style.display !== 'none') {
                        fetch('painel_suporte.php?aba=sessao-sos-ativos&_t=' + Date.now(), { cache: 'no-store', credentials: 'same-origin' })
                            .then(function (res) { return res.text(); })
                            .then(function (html) {
                                const doc = new DOMParser().parseFromString(html, 'text/html');
                                const novoBody = doc.querySelector('#sessao-sos-ativos tbody');
                                const bodyAtual = document.querySelector('#sessao-sos-ativos tbody');
                                if (novoBody && bodyAtual && novoBody.innerHTML.trim() !== bodyAtual.innerHTML.trim()) {
                                    bodyAtual.innerHTML = novoBody.innerHTML;
                                }
                                const novoCardBody = doc.querySelector('#sessao-sos-ativos .card-body');
                                const cardBodyAtual = document.querySelector('#sessao-sos-ativos .card-body');
                                if (novoCardBody && cardBodyAtual && !novoBody && novoCardBody.innerHTML.trim() !== cardBodyAtual.innerHTML.trim()) {
                                    cardBodyAtual.innerHTML = novoCardBody.innerHTML;
                                }
                            })
                            .catch(function () {});
                    }
                    if (typeof window.recarregarNotificacoes === 'function') {
                        window.recarregarNotificacoes(true);
                    }
                })
                .catch(function (err) {
                    console.error('Erro ao atualizar chamados SOS:', err);
                });

            const sessaoMapa = document.getElementById('sessao-mapa-diario');
            const modalAberto = document.querySelector('.modal.show'); 
            
            if (sessaoMapa && sessaoMapa.style.display !== 'none' && !modalAberto) {
                const dataFiltro = document.querySelector('input[name="data_busca"]').value;
                fetch('painel_suporte.php?data_busca=' + dataFiltro + '&_t=' + new Date().getTime(), { cache: "no-store" })
                    .then(res => res.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        
                        document.querySelector('.kpi-total h2').innerText = doc.querySelector('.kpi-total h2').innerText;
                        document.querySelector('.kpi-matutino h2').innerText = doc.querySelector('.kpi-matutino h2').innerText;
                        document.querySelector('.kpi-vespertino h2').innerText = doc.querySelector('.kpi-vespertino h2').innerText;
                        document.querySelector('.kpi-noturno h2').innerText = doc.querySelector('.kpi-noturno h2').innerText;
                        
                        const novaGrid = doc.getElementById('grid-mapa-diario').innerHTML;
                        const gridAtual = document.getElementById('grid-mapa-diario');
                        if (gridAtual.innerHTML.trim() !== novaGrid.trim()) {
                            gridAtual.innerHTML = novaGrid;
                        }
                    })
                    .catch(err => console.error("Erro ao atualizar a grade em tempo real:", err));
            }
        }

        function limparFeedbacks() {
            document.querySelectorAll('.alert-autohide').forEach(alerta => {
                setTimeout(() => { alerta.style.transition = "opacity 0.6s ease"; alerta.style.opacity = "0"; setTimeout(() => alerta.remove(), 600); }, 4000);
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            limparFeedbacks();
            updateThemeElements(savedTheme);

            const areaChamados = document.getElementById('area-chamados-dinamica');
            if (areaChamados) {
                areaChamados.addEventListener('click', function (e) {
                    const alerta = e.target.closest('.alert-sos-notificacao');
                    if (alerta) abrirSosAtivos();
                });
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.sos-problema-toggle');
                if (!btn) return;
                e.preventDefault();
                const text = btn.closest('.sos-problema-cell')?.querySelector('.sos-problema-text');
                if (!text) return;
                const collapsed = text.classList.toggle('sos-problema-collapsed');
                btn.textContent = collapsed ? 'Ver descrição completa' : 'Ver menos';
            });

            const modalAtenderEl = document.getElementById('modalAtenderChamado');
            const modalAtender = modalAtenderEl ? new bootstrap.Modal(modalAtenderEl) : null;

            const sosHistoricoTipos = {
                status: { label: 'Alteração de status', icon: 'bi-arrow-repeat' },
                observacao_interna: { label: 'Observação interna', icon: 'bi-journal-text' },
                resposta_professor: { label: 'Resposta ao professor', icon: 'bi-chat-left-text' },
                email: { label: 'E-mail enviado', icon: 'bi-envelope-check' }
            };

            function formatarDataHistorico(iso) {
                if (!iso) return '—';
                const d = new Date(String(iso).replace(' ', 'T'));
                if (Number.isNaN(d.getTime())) return iso;
                return d.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            }

            function renderSosHistorico(historico) {
                const el = document.getElementById('sosModalHistorico');
                if (!el) return;
                if (!historico || !historico.length) {
                    el.innerHTML = '<p class="text-muted small mb-0 fst-italic">Nenhuma atualização registrada ainda.</p>';
                    return;
                }
                el.innerHTML = historico.map(function (entry) {
                    const tipo = entry.tipo || 'outro';
                    const meta = sosHistoricoTipos[tipo] || { label: tipo, icon: 'bi-clock-history' };
                    const texto = (entry.texto || '').replace(/</g, '&lt;').replace(/\n/g, '<br>');
                    const autor = (entry.autor || 'Suporte TI').replace(/</g, '&lt;');
                    const statusBadge = entry.status
                        ? '<span class="badge bg-secondary-subtle text-secondary ms-1">' + entry.status.replace(/_/g, ' ') + '</span>'
                        : '';
                    return '<div class="sos-historico-item" data-tipo="' + tipo + '">' +
                        '<div class="sos-historico-icon"><i class="bi ' + meta.icon + '"></i></div>' +
                        '<div class="flex-grow-1 min-w-0">' +
                        '<div class="sos-historico-meta">' +
                        '<strong>' + meta.label + '</strong> · ' + autor + ' · ' + formatarDataHistorico(entry.em) +
                        statusBadge +
                        '</div>' +
                        '<div class="sos-historico-texto mt-1">' + texto + '</div>' +
                        '</div></div>';
                }).join('');
            }

            function abrirModalChamado(dados) {
                if (!modalAtender || !dados) return;
                document.getElementById('sosModalId').value = dados.id;
                document.getElementById('sosModalStatus').value = dados.status || 'pendente';
                document.getElementById('sosModalProfessor').value = dados.professor_nome || '';
                document.getElementById('sosModalObsInterna').value = '';
                document.getElementById('sosModalResposta').value = '';
                document.getElementById('sosModalResumo').textContent =
                    (dados.laboratorio || '') + ' — ' + (dados.professor_nome || '');
                document.getElementById('sosModalProblema').innerHTML =
                    '<strong>Problema relatado:</strong><br>' + (dados.mensagem || '').replace(/</g, '&lt;').replace(/\n/g, '<br>');
                renderSosHistorico(dados.historico || []);
                document.getElementById('sosModalEnviarEmail').checked = true;
                modalAtender.show();
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-atender-chamado');
                if (!btn) return;
                const row = btn.closest('[data-sos-json]');
                if (!row) return;
                try {
                    abrirModalChamado(JSON.parse(row.getAttribute('data-sos-json')));
                } catch (err) {
                    console.error(err);
                }
            });

            initNotificacoesNav({
                contexto: 'sos',
                verTodasFn: 'abrirSosAtivos',
                badgeIds: ['badge-sos-menu'],
                playSound: true,
                somVolume: 0.05,
                initialIds: <?= json_encode(array_map(static fn($a) => 'sos:' . $a['id'], $alertas_suporte), JSON_UNESCAPED_UNICODE) ?>,
                sosStyle: true,
                pollInterval: 30000,
                onItemClick: function (item) {
                    if (!item) {
                        abrirSosAtivos(null);
                        return;
                    }
                    abrirSosAtivos(item);
                }
            });

            let hashURL = new URLSearchParams(window.location.search).get('aba') || window.location.hash.replace('#', '') || (<?= (int) $qtd_alertas ?> > 0 ? 'sessao-sos-ativos' : 'sessao-mapa-diario');
            showSection(hashURL);
            if (new URLSearchParams(window.location.search).get('aba')) {
                window.history.replaceState(null, null, '#' + hashURL);
            }

            document.querySelectorAll('.offcanvas-menu-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href.startsWith('#')) {
                        e.preventDefault();
                        showSection(href.replace('#', ''));
                        bootstrap.Offcanvas.getInstance(document.getElementById('sidebarMenu')).hide();
                    }
                });
            });

            document.getElementById('themeToggleBtn').addEventListener('click', function() {
                let newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('tema-uniceplac', newTheme);
                updateThemeElements(newTheme); 
            });
            
            setInterval(monitorarTempoReal, 120000);
            monitorarTempoReal();
            
            const inputFoto = document.getElementById('nova_foto_input');
            if (inputFoto) {
                inputFoto.addEventListener('change', function() {
                    if (this.value) document.getElementById('formFotoPerfil').submit();
                });
            }
        });
    </script>
</body>
</html>