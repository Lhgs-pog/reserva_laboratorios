<?php
namespace App\Controllers;

use App\Models\Agendamento;
use App\Models\SOS as SOSModel;

class NotificacaoController extends BaseController {
    public function __construct() {
        $this->requireAuth();
    }

    public function listar() {

        $perfil = $_SESSION['perfil'] ?? '';
        $contexto = trim($_GET['contexto'] ?? '');
        $items  = [];

        try {
            require_once __DIR__ . '/../Config/sos_helpers.php';

            if ($contexto === 'sos') {
                if (in_array($perfil, ['suporte', 'coordenador'], true)) {
                    $items = $this->itemsSosPendentes();
                }
            } elseif ($contexto === 'reservas') {
                if ($perfil === 'coordenador') {
                    $items = $this->itemsReservasPendentes();
                } elseif ($perfil === 'professor') {
                    $items = array_merge(
                        $this->itemsReservasProfessor((int) $_SESSION['usuario_id']),
                        $this->itemsListaEsperaProfessor((int) $_SESSION['usuario_id'])
                    );
                }
            } elseif ($perfil === 'suporte') {
                $items = $this->itemsSosPendentes();
            } elseif ($perfil === 'coordenador') {
                $items = $this->itemsReservasPendentes();
            } elseif ($perfil === 'professor') {
                $items = array_merge(
                    $this->itemsReservasProfessor((int) $_SESSION['usuario_id']),
                    $this->itemsListaEsperaProfessor((int) $_SESSION['usuario_id'])
                );
            }
        } catch (\Exception $e) {
            $this->json(['qtd' => 0, 'items' => []]);
        }

        $this->json([
            'qtd'   => count($items),
            'items' => $items,
        ]);
    }

    private function itemsReservasPendentes(): array {
        require_once __DIR__ . '/../Config/horario_helpers.php';
        $agendamento = new Agendamento();
        $pendentes   = $agendamento->listarSolicitacoesPendentes();
        $items       = [];

        foreach ($pendentes as $p) {
            $turno = (string) ($p['turno'] ?? '');
            $periodo = (string) ($p['periodo'] ?? '');
            $items[] = [
                'id'        => (int) $p['id'],
                'tipo'      => 'reserva',
                'titulo'    => ($p['professor'] ?? 'Professor') . ' — ' . ($p['laboratorio'] ?? 'Lab'),
                'subtitulo' => trim(($p['disciplina'] ?? '') . ' · ' . $turno . ' · ' . $periodo, ' ·'),
                'data'      => !empty($p['data_reserva']) ? date('d/m/Y', strtotime($p['data_reserva'])) : '',
                'horario'   => labhub_horario_label($turno, $periodo),
                'icon'      => 'bi-hourglass-split',
                'color'     => 'warning',
            ];
        }

        return $items;
    }

    private function itemsReservasProfessor(int $idProfessor): array {
        require_once __DIR__ . '/../Config/horario_helpers.php';
        $agendamento = new Agendamento();
        $pendentes   = $agendamento->listarPendentesProfessor($idProfessor);
        $items       = [];

        foreach ($pendentes as $p) {
            $turno = (string) ($p['turno'] ?? '');
            $periodo = (string) ($p['periodo'] ?? '');
            $items[] = [
                'id'        => (int) $p['id'],
                'tipo'      => 'reserva',
                'titulo'    => ($p['laboratorio'] ?? 'Laboratório') . ' — aguardando aprovação',
                'subtitulo' => trim(($p['disciplina'] ?? '') . ' · ' . $turno . ' · ' . $periodo, ' ·'),
                'data'      => !empty($p['data_reserva']) ? date('d/m/Y', strtotime($p['data_reserva'])) : '',
                'horario'   => labhub_horario_label($turno, $periodo),
                'icon'      => 'bi-hourglass-split',
                'color'     => 'warning',
            ];
        }

        return $items;
    }

    private function itemsListaEsperaProfessor(int $idProfessor): array {
        require_once __DIR__ . '/../Config/horario_helpers.php';
        require_once __DIR__ . '/../Config/lista_espera_schema.php';

        $model = new \App\Models\ListaEspera();
        $model->ensureSchema();
        $filas = $model->listarAguardandoProfessor($idProfessor);
        $items = [];

        foreach ($filas as $f) {
            $turno = (string) ($f['turno'] ?? '');
            $periodo = (string) ($f['periodo'] ?? '');
            $posicao = $model->posicaoNaFila((int) $f['id']);
            $items[] = [
                'id'        => (int) $f['id'],
                'tipo'      => 'lista_espera',
                'titulo'    => 'Lista de espera — laboratório lotado',
                'subtitulo' => trim(($f['disciplina'] ?? '') . ' · ' . $turno . ' · ' . $periodo . ' · ' . $posicao . 'º na fila', ' ·'),
                'data'      => !empty($f['data_reserva']) ? date('d/m/Y', strtotime($f['data_reserva'])) : '',
                'horario'   => labhub_horario_label($turno, $periodo),
                'icon'      => 'bi-hourglass-bottom',
                'color'     => 'info',
            ];
        }

        return $items;
    }

    private function itemsSosPendentes(): array {
        $sosModel = new SOSModel();
        $chamados = $sosModel->listarAtivos();
        $items    = [];

        foreach ($chamados as $c) {
            $hora = !empty($c['data_hora']) ? date('d/m H:i', strtotime($c['data_hora'])) : '';
            $items[] = [
                'id'        => (int) $c['id'],
                'tipo'      => 'sos',
                'titulo'    => ($c['professor_nome'] ?? 'Professor') . ' — ' . ($c['laboratorio'] ?? 'Lab'),
                'subtitulo' => trim((string) ($c['mensagem'] ?? '')),
                'data'      => $hora,
                'icon'      => 'bi-headset',
                'color'     => 'attention',
            ];
        }

        return $items;
    }
}
?>
