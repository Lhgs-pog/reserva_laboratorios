<?php
namespace App\Controllers;

use App\Models\SOS as SOSModel;

class SOSController extends BaseController {
    private $sosModel;

    public function __construct() {
        $this->sosModel = new SOSModel();
        $this->requireAuth();
    }

    private function requireSuporteOuCoordenador(): void {
        $perfil = $_SESSION['perfil'] ?? '';
        if (!in_array($perfil, ['suporte', 'coordenador'], true)) {
            $this->json([
                'qtd'          => 0,
                'qtd_suporte'  => 0,
                'html_suporte' => '',
                'error'        => 'acesso_negado',
            ], 403);
        }
    }

    /**
     * Conta chamados pendentes — endpoint check_sos.php
     * Retorna: {"qtd": N}
     */
    public function contarPendentes() {
        $this->requireSuporteOuCoordenador();
        try {
            $total = $this->sosModel->contarPendentes();
            $this->json(['qtd' => $total]);
        } catch (\Exception $e) {
            $this->json(['qtd' => 0]);
        }
    }

    /**
     * Retorna qtd + HTML dos chamados pendentes — endpoint check_sos_status.php
     * Retorna: {"qtd_suporte": N, "html_suporte": "..."}
     */
    public function listarStatus() {
        $this->requireSuporteOuCoordenador();
        try {
            $dados = $this->sosModel->listarStatus();
            $this->json($dados);
        } catch (\Exception $e) {
            $this->json(['qtd_suporte' => 0, 'html_suporte' => '']);
        }
    }

    /**
     * Lista todos os chamados
     */
    public function listar() {
        $this->requirePerfil('suporte');
        $chamados = $this->sosModel->listarTodos();
        return compact('chamados');
    }

    /**
     * Cria novo chamado SOS
     */
    public function criar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $laboratorio = $this->getPost('laboratorio_sos');
            $mensagem    = $this->getPost('mensagem_sos');

            try {
                $this->sosModel->criar(
                    $_SESSION['usuario_id'],
                    $laboratorio,
                    $mensagem,
                    $_SESSION['nome'] ?? ''
                );
                $this->redirectWithSuccess('painel_professor.php', 'Chamado criado com sucesso!');
            } catch (\Exception $e) {
                error_log('[SOSController] criar: ' . $e->getMessage());
                $this->redirectWithError('painel_professor.php', 'Erro ao criar chamado. Tente novamente.');
            }
        }
    }

    /**
     * Atualiza status do chamado
     */
    public function atualizarStatus() {
        $this->requirePerfil('suporte');

        $id     = $this->getPost('id_chamado');
        $status = $this->getPost('status');

        try {
            $this->sosModel->atualizarStatus($id, $status);
            $this->json(['success' => true, 'message' => 'Status atualizado']);
        } catch (\Exception $e) {
            error_log('[SOSController] atualizarStatus: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Não foi possível atualizar o status.'], 400);
        }
    }
}
?>
