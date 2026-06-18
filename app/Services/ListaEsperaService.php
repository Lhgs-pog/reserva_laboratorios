<?php

namespace App\Services;

use App\Models\ListaEspera;
use PDO;

class ListaEsperaService
{
    private PDO $pdo;
    private ListaEspera $model;
    private MailService $mail;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->model = new ListaEspera();
        $this->mail = new MailService();
    }

    public function slotLotado(string $data, string $turno, string $periodo): bool
    {
        require_once __DIR__ . '/../Config/disponibilidade_helpers.php';
        $livres = array_filter(
            labhub_labs_para_slot($this->pdo, $data, $turno, $periodo, null, true),
            static fn($l) => ($l['status'] ?? '') === 'livre'
        );
        return count($livres) === 0;
    }

    /**
     * @return array{ok:bool,msg:string,id?:int,posicao?:int,email_enviado?:bool}
     */
    public function inscrever(int $idProfessor, int $idDisciplina, string $data, string $turno, string $periodo): array
    {
        require_once __DIR__ . '/../Config/horario_helpers.php';

        if (!$this->slotLotado($data, $turno, $periodo)) {
            return [
                'ok' => false,
                'msg' => 'Há laboratório(s) livre(s) neste horário. Selecione um laboratório e envie a solicitação normalmente.',
            ];
        }

        try {
            $id = $this->model->inscrever($idProfessor, $idDisciplina, $data, $turno, $periodo);
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => 'Não foi possível registrar na lista de espera.'];
        }

        $posicao = $this->model->posicaoNaFila($id);
        $professor = $this->buscarUsuario($idProfessor);
        $disciplina = $this->buscarDisciplina($idDisciplina);

        $emailOk = false;
        if ($professor && !empty($professor['email'])) {
            $emailOk = $this->mail->enviarListaEsperaLaboratorio(
                (string) $professor['email'],
                (string) $professor['nome'],
                [
                    'data_reserva' => $data,
                    'turno' => $turno,
                    'periodo' => $periodo,
                    'disciplina' => $disciplina,
                    'posicao' => $posicao,
                ]
            );
            if ($emailOk) {
                $this->model->marcarEmailEnviado($id);
            }
        }

        $dataFmt = date('d/m/Y', strtotime($data));
        $horario = labhub_horario_label($turno, $periodo);

        return [
            'ok' => true,
            'id' => $id,
            'posicao' => $posicao,
            'email_enviado' => $emailOk,
            'msg' => "Você entrou na lista de espera para {$dataFmt} ({$horario}). Posição: {$posicao}º na fila."
                . ($emailOk ? ' Enviamos a confirmação por e-mail.' : ''),
        ];
    }

    public function resumoFila(int $idProfessor, string $data, string $turno, string $periodo): ?array
    {
        $row = $this->model->buscarPorProfessorSlot($idProfessor, $data, $turno, $periodo);
        if (!$row || ($row['status'] ?? '') !== 'aguardando') {
            return null;
        }
        return [
            'id' => (int) $row['id'],
            'posicao' => $this->model->posicaoNaFila((int) $row['id']),
            'disciplina' => $row['disciplina'] ?? '',
        ];
    }

    private function buscarUsuario(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, email FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function buscarDisciplina(int $id): string
    {
        $stmt = $this->pdo->prepare('SELECT nome FROM disciplinas WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return (string) ($stmt->fetchColumn() ?: 'Disciplina');
    }
}
