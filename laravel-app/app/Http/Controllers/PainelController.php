<?php
/**
 * Painel Controller - Laravel Style
 */

namespace LaravelApp\Http\Controllers;

use LaravelApp\Models\Agendamento;
use LaravelApp\Models\SOS;

class PainelController extends Controller {
    /**
     * Professor panel
     */
    public function professor() {
        $this->requireRole('professor');

        $alocacoes = Agendamento::byProfessor($_SESSION['usuario_id']);

        return $this->view('painel.professor', [
            'alocacoes' => $alocacoes,
            'usuario' => $this->getUser(),
        ]);
    }

    /**
     * Coordinator panel
     */
    public function coordenador() {
        $this->requireRole('coordenador');

        $solicitacoes = Agendamento::getPendentes();
        $confirmados = Agendamento::getAprovados();

        return $this->view('painel.coordenador', [
            'solicitacoes' => $solicitacoes,
            'confirmados' => $confirmados,
            'usuario' => $this->getUser(),
        ]);
    }

    /**
     * Support panel
     */
    public function suporte() {
        $this->requireRole('suporte');

        $chamados = SOS::getPendentes();

        return $this->view('painel.suporte', [
            'chamados' => $chamados,
            'usuario' => $this->getUser(),
        ]);
    }
}
?>
