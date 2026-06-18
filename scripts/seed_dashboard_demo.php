<?php
/**
 * Seed completo para dashboards (suporte + coordenação).
 * Uso: php scripts/seed_dashboard_demo.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Execute apenas via CLI: php scripts/seed_dashboard_demo.php\n");
    exit(1);
}

require dirname(__DIR__) . '/conexao.php';

$driver = app_db_driver();
$isPgsql = $driver === 'pgsql';

function seed_date_expr(string $offset, bool $isPgsql): string
{
    if ($offset === 'today') {
        return $isPgsql ? 'CURRENT_DATE' : 'CURDATE()';
    }
    if (preg_match('/^([+-])(\d+)$/', $offset, $m)) {
        $sign = $m[1] === '+' ? '+' : '-';
        $days = (int) $m[2];
        return $isPgsql
            ? "CURRENT_DATE {$sign} INTERVAL '{$days} days'"
            : "DATE_{$sign}(CURDATE(), INTERVAL {$days} DAY)";
    }
    throw new InvalidArgumentException("Offset inválido: {$offset}");
}

function seed_now_expr(string $offset, bool $isPgsql): string
{
    if (preg_match('/^([+-])(\d+)\s*(minute|hour|day)s?$/i', trim($offset), $m)) {
        $sign = $m[1];
        $n = (int) $m[2];
        $unit = strtoupper(rtrim(strtolower($m[3]), 's'));
        if ($isPgsql) {
            return "NOW() {$sign} INTERVAL '{$n} {$unit}'";
        }
        $mysqlUnit = $unit === 'MINUTE' ? 'MINUTE' : ($unit === 'HOUR' ? 'HOUR' : 'DAY');
        return $sign === '-'
            ? "DATE_SUB(NOW(), INTERVAL {$n} {$mysqlUnit})"
            : "DATE_ADD(NOW(), INTERVAL {$n} {$mysqlUnit})";
    }
    return 'NOW()';
}

function seed_user_id(PDO $pdo, string $email): int
{
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $id = $stmt->fetchColumn();
    if (!$id) {
        throw new RuntimeException("Usuário não encontrado: {$email}");
    }
    return (int) $id;
}

echo "LabHub — seed dashboard demo ({$driver})\n";

try {
    $pdo->beginTransaction();

    $idAna = seed_user_id($pdo, 'professor@uniceplac.edu.br');
    $idJoao = seed_user_id($pdo, 'joao@uniceplac.edu.br');

    // Cadastros base (blocos, andares, salas)
    foreach (['A', 'B', 'C'] as $bloco) {
        $pdo->prepare(
            'INSERT INTO blocos (nome) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM blocos WHERE nome = ?)'
        )->execute([$bloco, $bloco]);
    }
    foreach (['Térreo', '1º Andar', '2º Andar'] as $andar) {
        $pdo->prepare(
            'INSERT INTO andares (nome) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM andares WHERE nome = ?)'
        )->execute([$andar, $andar]);
    }
    foreach (['101', '102', '103', '201', '202', '203', '302', 'Lab', 'Lab 02', 'Multimídia', 'Redes', 'EAD'] as $sala) {
        $pdo->prepare(
            'INSERT INTO salas (nome) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM salas WHERE nome = ?)'
        )->execute([$sala, $sala]);
    }

    // Limpar dados operacionais
    $pdo->exec('DELETE FROM controle_chaves');
    $pdo->exec('DELETE FROM chamados_suporte');
    $pdo->exec('DELETE FROM agendamentos');
    $pdo->exec('DELETE FROM ensalamento');
    $pdo->exec('DELETE FROM quadro_aulas');

    // Grade ativa
    $quadroId = (int) $pdo->query('SELECT id FROM quadros_horarios ORDER BY id DESC LIMIT 1')->fetchColumn();
    if ($quadroId <= 0) {
        $pdo->exec("INSERT INTO quadros_horarios (nome, periodo_letivo) VALUES ('Grade Oficial 2026.1', '2026.1')");
        $quadroId = (int) $pdo->lastInsertId();
    }

    $quadroRows = [
        ['Matutino', 'Segunda', 'Ciência da Computação', '2026.1', 4, 38, $idAna, 1, '1º Horário', 'A', '1º Andar', '101', 4, 2],
        ['Matutino', 'Segunda', 'Sistemas de Informação', '2026.1', 2, 32, $idJoao, 2, '2º Horário', 'A', '1º Andar', '102', 4, 2],
        ['Vespertino', 'Terça', 'Ciência da Computação', '2026.1', 6, 30, $idAna, 3, '1º Horário', 'B', '2º Andar', '201', 4, 3],
        ['Vespertino', 'Terça', 'Engenharia de Software', '2026.1', 3, 28, $idJoao, 4, '2º Horário', 'C', 'Térreo', '103', 4, 2],
        ['Noturno', 'Quarta', 'Análise e Desenvolvimento', '2026.1', 1, 40, $idAna, 1, '1º Horário', 'A', '1º Andar', '101', 4, 2],
        ['Noturno', 'Quarta', 'Redes de Computadores', '2026.1', 5, 35, $idJoao, 3, '2º Horário', 'B', '2º Andar', '202', 4, 3],
        ['Matutino', 'Quinta', 'Ciência da Computação', '2026.1', 7, 36, $idAna, 2, '1º e 2º Horários', 'A', '1º Andar', 'Lab', 6, 4],
        ['Vespertino', 'Quinta', 'Sistemas de Informação', '2026.1', 4, 33, $idJoao, 1, '1º Horário', 'A', '1º Andar', '101', 4, 2],
        ['Noturno', 'Quinta', 'Programação Web', '2026.1', 4, 38, $idAna, 2, '2º Horário', 'A', '1º Andar', 'Lab 02', 4, 2],
        ['Matutino', 'Sexta', 'Banco de Dados', '2026.1', 2, 30, $idJoao, 4, '1º Horário', 'C', 'Térreo', 'Multimídia', 4, 3],
        ['Vespertino', 'Sexta', 'Programação Web', '2026.1', 4, 0, null, null, '1º Horário', null, null, 'EAD', 4, 0],
        ['Noturno', 'Sábado', 'Inteligência Artificial', '2026.1', 6, 25, $idAna, 3, '1º e 2º Horários', 'B', '2º Andar', 'Redes', 6, 4],
    ];

    $stmtQa = $pdo->prepare(
        'INSERT INTO quadro_aulas (id_quadro, turno, dia_semana, curso, semestre, id_disciplina, modalidade, numero_alunos, id_professor, id_laboratorio, horario, bloco, andar, sala, carga_horaria_total, horas_laboratorio)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($quadroRows as $r) {
        $stmtQa->execute([
            $quadroId, $r[0], $r[1], $r[2], $r[3], $r[4], 'Presencial', $r[5],
            $r[6], $r[7], $r[8], $r[9], $r[10], $r[11], $r[12], $r[13],
        ]);
    }

    $agendamentos = [
        [1, $idAna, 4, 'today', 'Matutino', '1º Horário', 'aprovado'],
        [2, $idJoao, 2, 'today', 'Matutino', '2º Horário', 'aprovado'],
        [1, $idAna, 4, 'today', 'Vespertino', '1º Horário', 'aprovado'],
        [3, $idJoao, 5, 'today', 'Vespertino', '2º Horário', 'aprovado'],
        [2, $idAna, 6, 'today', 'Noturno', '1º Horário', 'aprovado'],
        [1, $idAna, 4, '+1', 'Noturno', '1º Horário', 'aprovado'],
        [3, $idJoao, 5, '+1', 'Noturno', '2º Horário', 'pendente'],
        [4, $idAna, 6, '+2', 'Vespertino', '1º Horário', 'aprovado'],
        [2, $idJoao, 2, '+2', 'Noturno', '2º Horário', 'pendente'],
        [1, $idAna, 4, '+3', 'Matutino', '1º e 2º Horários', 'aprovado'],
        [3, $idJoao, 5, '+4', 'Matutino', '1º Horário', 'pendente'],
        [2, $idAna, 7, '+5', 'Vespertino', '2º Horário', 'aprovado'],
        [4, $idJoao, 3, '+6', 'Noturno', '1º Horário', 'rejeitado'],
        [1, $idAna, 4, '+7', 'Noturno', '2º Horário', 'aprovado'],
        [3, $idJoao, 5, '+8', 'Vespertino', '1º Horário', 'pendente'],
        [2, $idAna, 2, '+10', 'Matutino', '2º Horário', 'aprovado'],
        [1, $idAna, 4, '-1', 'Vespertino', '1º Horário', 'aprovado'],
        [2, $idJoao, 2, '-2', 'Matutino', '1º Horário', 'aprovado'],
        [3, $idJoao, 5, '-3', 'Noturno', '2º Horário', 'aprovado'],
        [4, $idAna, 6, '-5', 'Vespertino', '2º Horário', 'rejeitado'],
    ];

    foreach ($agendamentos as $a) {
        $dateExpr = seed_date_expr($a[3], $isPgsql);
        $sql = 'INSERT INTO agendamentos (id_laboratorio, id_professor, id_disciplina, data_reserva, turno, periodo, status)
                VALUES (?, ?, ?, ' . $dateExpr . ', ?, ?, ?)';
        $pdo->prepare($sql)->execute([$a[0], $a[1], $a[2], $a[4], $a[5], $a[6]]);
    }

    // Chaves
    $today = seed_date_expr('today', $isPgsql);
    $pdo->exec("INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, funcionario_entrega, status)
        SELECT a.id, 'Prof. Ana Silva', 'Laboratório de Informática 01', {$today}, '(62) 99999-8888', '14:25:00', '18:00:00', 'Carlos Suporte', 'em_uso'
        FROM agendamentos a WHERE a.id_professor = {$idAna} AND a.data_reserva = {$today} AND a.turno = 'Vespertino' LIMIT 1");

    $pdo->exec("INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, funcionario_entrega, status)
        SELECT a.id, 'Prof. João Mendes', 'Laboratório de Redes', {$today}, '(62) 98888-7777', '14:40:00', '18:00:00', 'Carlos Suporte', 'em_uso'
        FROM agendamentos a WHERE a.id_professor = {$idJoao} AND a.data_reserva = {$today} AND a.turno = 'Vespertino' LIMIT 1");

    $yesterday = seed_date_expr('-1', $isPgsql);
    $pdo->exec("INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, hora_devolucao_real, funcionario_entrega, funcionario_recebimento, status)
        SELECT a.id, 'Prof. Ana Silva', 'Laboratório de Informática 01', {$yesterday}, '(62) 99999-8888', '14:20:00', '18:00:00', '17:50:00', 'Carlos Suporte', 'Carlos Suporte', 'devolvido'
        FROM agendamentos a WHERE a.id_professor = {$idAna} AND a.data_reserva = {$yesterday} LIMIT 1");

    $d2 = seed_date_expr('-2', $isPgsql);
    $pdo->exec("INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, hora_devolucao_real, funcionario_entrega, funcionario_recebimento, status)
        SELECT a.id, 'Prof. João Mendes', 'Laboratório de Informática 02', {$d2}, '(62) 98888-7777', '08:30:00', '12:00:00', '11:55:00', 'Carlos Suporte', 'Técnico Carlos Suporte', 'devolvido'
        FROM agendamentos a WHERE a.id_professor = {$idJoao} AND a.data_reserva = {$d2} LIMIT 1");

    $d3 = seed_date_expr('-3', $isPgsql);
    $pdo->exec("INSERT INTO controle_chaves (id_agendamento, professor_nome, laboratorio, data_uso, celular, hora_retirada, hora_devolucao_prevista, hora_devolucao_real, funcionario_entrega, funcionario_recebimento, status)
        SELECT a.id, 'Prof. João Mendes', 'Laboratório de Redes', {$d3}, '(62) 98888-7777', '21:00:00', '22:30:00', '22:15:00', 'Carlos Suporte', 'Carlos Suporte', 'devolvido'
        FROM agendamentos a WHERE a.id_professor = {$idJoao} AND a.data_reserva = {$d3} LIMIT 1");

    // SOS
    $sosRows = [
        [$idAna, 'Prof. Ana Silva', 'Laboratório de Informática 01', 'Computador posição 12 não inicia. Turma de Programação Web aguardando.', 'pendente', '-12 minute'],
        [$idJoao, 'Prof. João Mendes', 'Laboratório de Redes', 'Projetor sem imagem — cabo HDMI testado, problema persiste.', 'pendente', '-35 minute'],
        [$idAna, 'Prof. Ana Silva', 'Laboratório Multimídia', 'Som do sistema não funciona na sala. Aula de multimídia hoje.', 'pendente', '-2 hour'],
        [$idJoao, 'Prof. João Mendes', 'Laboratório de Informática 02', 'Mouse sem funcionar em 3 bancadas do fundo.', 'pendente', '-4 hour'],
        [$idAna, 'Prof. Ana Silva', 'Laboratório de Informática 02', 'Wi-Fi instável — resolvido após reinício do roteador.', 'resolvido', '-1 day'],
        [$idJoao, 'Prof. João Mendes', 'Laboratório Multimídia', 'Teclado quebrado bancada 5 — substituído.', 'resolvido', '-2 day'],
        [$idAna, 'Prof. Ana Silva', 'Laboratório de Redes', 'Switch da rack desligado — energia restabelecida.', 'resolvido', '-4 day'],
        [$idJoao, 'Prof. João Mendes', 'Laboratório de Informática 01', 'Licença do software expirada — reativada.', 'resolvido', '-6 day'],
        [$idAna, 'Prof. Ana Silva', 'Laboratório de Informática 01', 'Ar-condicionado com vazamento — manutenção predial acionada.', 'resolvido', '-10 day'],
    ];
    foreach ($sosRows as $s) {
        $when = seed_now_expr($s[5], $isPgsql);
        $pdo->prepare(
            "INSERT INTO chamados_suporte (id_professor, professor_nome, laboratorio, mensagem, status, data_hora)
             VALUES (?, ?, ?, ?, ?, {$when})"
        )->execute([$s[0], $s[1], $s[2], $s[3], $s[4]]);
    }

    // Ensalamento
    $ensRows = [
        [$idAna, 4, 'Ciência da Computação', 'A', '1º Andar', '201', 'Presencial', 'Matutino'],
        [$idJoao, 5, 'Sistemas de Informação', 'B', '2º Andar', '102', 'Presencial', 'Matutino'],
        [$idAna, 2, 'Ciência da Computação', 'A', '1º Andar', '203', 'Presencial', 'Vespertino'],
        [$idJoao, 3, 'Engenharia de Software', 'C', 'Térreo', '105', 'Presencial', 'Vespertino'],
        [$idAna, 4, 'Ciência da Computação', 'A', '1º Andar', '201', 'Presencial', 'Noturno'],
        [$idJoao, 5, 'Sistemas de Informação', 'B', '2º Andar', '302', 'Presencial', 'Noturno'],
        [$idAna, 6, 'Análise e Desenvolvimento', 'A', '1º Andar', '104', 'EAD Polo Goiânia', 'Noturno'],
    ];
    $stmtEns = $pdo->prepare(
        'INSERT INTO ensalamento (id_professor, id_disciplina, curso, bloco, andar, sala, categoria, turno) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($ensRows as $e) {
        $stmtEns->execute($e);
    }

    $pdo->commit();

    $resumo = [
        'agendamentos' => (int) $pdo->query('SELECT COUNT(*) FROM agendamentos')->fetchColumn(),
        'quadro_aulas' => (int) $pdo->query('SELECT COUNT(*) FROM quadro_aulas')->fetchColumn(),
        'sos_pendentes' => (int) $pdo->query("SELECT COUNT(*) FROM chamados_suporte WHERE status = 'pendente'")->fetchColumn(),
        'chaves_em_uso' => (int) $pdo->query("SELECT COUNT(*) FROM controle_chaves WHERE status = 'em_uso'")->fetchColumn(),
        'ensalamento' => (int) $pdo->query('SELECT COUNT(*) FROM ensalamento')->fetchColumn(),
        'hoje_aprovadas' => (int) $pdo->query(
            'SELECT COUNT(*) FROM agendamentos WHERE data_reserva = ' . seed_date_expr('today', $isPgsql) . " AND status = 'aprovado'"
        )->fetchColumn(),
    ];

    echo "Seed concluído com sucesso.\n";
    foreach ($resumo as $k => $v) {
        echo "  {$k}: {$v}\n";
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
