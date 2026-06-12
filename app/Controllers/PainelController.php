<?php
namespace App\Controllers;

use App\Models\Agendamento as AgendamentoModel;

class PainelController extends BaseController {
    private $agendamentoModel;

    public function __construct() {
        $this->agendamentoModel = new AgendamentoModel();
        $this->requireAuth();
    }

    /**
     * Painel do professor
     */
    public function professor() {
        $this->requirePerfil('professor');

        $alocacoes = $this->agendamentoModel->listarAlocacoesProfessor($_SESSION['usuario_id']);
        $laboratorios = $this->agendamentoModel->buscarLaboratorios();
        $disciplinas = $this->agendamentoModel->buscarDisciplinas();

        return compact('alocacoes', 'laboratorios', 'disciplinas');
    }

    /**
     * Painel do coordenador
     */
    public function coordenador() {
        $this->requirePerfil('coordenador');

        $solicitacoes_pendentes = $this->agendamentoModel->listarSolicitacoesPendentes();
        $reservas_confirmadas = $this->agendamentoModel->listarReservasConfirmadas();

        return compact('solicitacoes_pendentes', 'reservas_confirmadas');
    }

    /**
     * Painel de suporte
     */
    public function suporte() {
        $this->requirePerfil('suporte');

        // Implementar lógica do painel de suporte
        return [];
    }
}
?>
