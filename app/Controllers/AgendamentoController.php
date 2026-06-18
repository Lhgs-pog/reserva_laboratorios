<?php
namespace App\Controllers;

use App\Models\Agendamento as AgendamentoModel;
use App\Models\User;

class AgendamentoController extends BaseController {
    private $agendamentoModel;
    private $userModel;

    public function __construct() {
        $this->agendamentoModel = new AgendamentoModel();
        $this->userModel = new User();
        $this->requireAuth();
    }

    /**
     * Página de edição de agendamento
     */
    public function editar() {
        $id = $this->getGet('id');
        
        if (!$id) {
            $this->redirect('painel_professor.php');
        }

        $agendamento = $this->agendamentoModel->buscarReservaPorId($id);
        
        if (!$agendamento || ($agendamento['id_professor'] != $_SESSION['usuario_id'] && $_SESSION['perfil'] !== 'coordenador')) {
            $this->redirect('painel_professor.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarEdicao($id);
        }

        $laboratorios = $this->agendamentoModel->buscarLaboratorios();
        $disciplinas = $this->agendamentoModel->buscarDisciplinas();
        $pdo = \App\Config\Database::getInstance()->getPDO();
        $professores = $pdo->query("SELECT id, nome FROM usuarios WHERE perfil = 'professor' ORDER BY nome ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $reserva_atual = $agendamento;

        return compact('reserva_atual', 'laboratorios', 'disciplinas', 'professores');
    }

    /**
     * Processa edição de agendamento
     */
    private function processarEdicao($id) {
        $id_lab = $this->getPost('id_laboratorio');
        $id_disciplina = $this->getPost('id_disciplina');
        $turno = $this->getPost('turno');
        $periodo = $this->getPost('periodo');
        $data = $this->getPost('data_reserva');

        try {
            $this->agendamentoModel->atualizarReserva(
                $id,
                $id_lab,
                $_SESSION['usuario_id'],
                $id_disciplina,
                $turno,
                $periodo,
                $data
            );

            $this->redirectWithSuccess('painel_professor.php', 'Agendamento atualizado com sucesso!');
        } catch (\Exception $e) {
            error_log('[AgendamentoController] editar: ' . $e->getMessage());
            $this->redirectWithError('editor_agendamento.php?id=' . $id, 'Erro ao atualizar agendamento. Tente novamente.');
        }
    }

    /**
     * Cria nova reserva (coordenador)
     */
    public function criar() {
        $this->requirePerfil('coordenador');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarCriacaoCoordenador();
        }

        $laboratorios = $this->agendamentoModel->buscarLaboratorios();
        $professores = $this->userModel->buscarProfessores();
        $disciplinas = $this->agendamentoModel->buscarDisciplinas();

        return compact('laboratorios', 'professores', 'disciplinas');
    }

    /**
     * Processa criação de reserva pelo coordenador
     */
    private function processarCriacaoCoordenador() {
        $id_lab = $this->getPost('id_laboratorio');
        $id_prof = $this->getPost('id_professor');
        $id_disciplina = $this->getPost('id_disciplina');
        $turno = $this->getPost('turno');
        $periodo = $this->getPost('periodo');
        $data = $this->getPost('data_reserva');

        try {
            $this->agendamentoModel->criarReserva($id_lab, $id_prof, $id_disciplina, $turno, $periodo, $data);
            $this->redirectWithSuccess('painel_coordenador.php#sessao-agendar-lab', 'Agendamento criado com sucesso!');
        } catch (\Exception $e) {
            error_log('[AgendamentoController] criar: ' . $e->getMessage());
            $this->redirectWithError('painel_coordenador.php#sessao-agendar-lab', 'Erro ao criar agendamento. Tente novamente.');
        }
    }

    /**
     * Aprova solicitação de agendamento
     */
    public function aprovar() {
        $this->requirePerfil('coordenador');

        $id = $this->getPost('id_agendamento');

        try {
            $this->agendamentoModel->atualizarStatusReserva($id, 'aprovado');
            $this->json(['success' => true, 'message' => 'Agendamento aprovado']);
        } catch (\Exception $e) {
            error_log('[AgendamentoController] aprovar: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Não foi possível aprovar o agendamento.'], 400);
        }
        $this->requirePerfil('coordenador');

        $id = $this->getPost('id_agendamento');

        try {
            $this->agendamentoModel->atualizarStatusReserva($id, 'rejeitado');
            $this->json(['success' => true, 'message' => 'Agendamento rejeitado']);
        } catch (\Exception $e) {
            error_log('[AgendamentoController] rejeitar: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Não foi possível rejeitar o agendamento.'], 400);
        }
        $this->requireAuth();

        $id = $this->getPost('id_agendamento');
        $agendamento = $this->agendamentoModel->buscarReservaPorId($id);

        if ($agendamento['id_professor'] != $_SESSION['usuario_id'] && $_SESSION['perfil'] !== 'coordenador') {
            $this->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        try {
            $this->agendamentoModel->excluirReserva($id);
            $this->json(['success' => true, 'message' => 'Agendamento excluído']);
        } catch (\Exception $e) {
            error_log('[AgendamentoController] excluir: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Não foi possível excluir o agendamento.'], 400);
        }
    }
}
?>
