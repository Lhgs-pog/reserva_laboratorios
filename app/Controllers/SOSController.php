<?php
namespace App\Controllers;

use App\Models\SOS as SOSModel;

class SOSController extends BaseController {
    private $sosModel;

    public function __construct() {
        $this->sosModel = new SOSModel();
        $this->requireAuth();
    }

    /**
     * Conta chamados pendentes — endpoint check_sos.php
     * Retorna: {"qtd": N}
     */
    public function contarPendentes() {
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
                $this->redirectWithError('painel_professor.php', 'Erro ao criar chamado: ' . $e->getMessage());
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
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
?>
