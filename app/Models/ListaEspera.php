<?php

namespace App\Models;

use PDO;

class ListaEspera extends BaseModel
{
    protected $table = 'lista_espera_laboratorio';

    public function ensureSchema(): void
    {
        require_once __DIR__ . '/../Config/lista_espera_schema.php';
        app_ensure_lista_espera_schema($this->pdo);
    }

    public function buscarPorProfessorSlot(int $idProfessor, string $data, string $turno, string $periodo): ?array
    {
        $this->ensureSchema();
        $stmt = $this->pdo->prepare(
            "SELECT le.*, d.nome AS disciplina
             FROM {$this->table} le
             JOIN disciplinas d ON le.id_disciplina = d.id
             WHERE le.id_professor = ? AND le.data_reserva = ? AND le.turno = ? AND le.periodo = ?"
        );
        $stmt->execute([$idProfessor, $data, $turno, $periodo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function posicaoNaFila(int $id): int
    {
        $this->ensureSchema();
        $stmt = $this->pdo->prepare(
            "SELECT le.data_reserva, le.turno, le.periodo, le.criado_em
             FROM {$this->table} le WHERE le.id = ? AND le.status = 'aguardando'"
        );
        $stmt->execute([$id]);
        $atual = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$atual) {
            return 0;
        }

        $stmtPos = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE data_reserva = ? AND turno = ? AND periodo = ? AND status = 'aguardando'
             AND criado_em <= ?"
        );
        $stmtPos->execute([$atual['data_reserva'], $atual['turno'], $atual['periodo'], $atual['criado_em']]);
        return (int) $stmtPos->fetchColumn();
    }

    public function listarAguardandoProfessor(int $idProfessor): array
    {
        $this->ensureSchema();
        $stmt = $this->pdo->prepare(
            "SELECT le.*, d.nome AS disciplina
             FROM {$this->table} le
             JOIN disciplinas d ON le.id_disciplina = d.id
             WHERE le.id_professor = ? AND le.status = 'aguardando'
             ORDER BY le.data_reserva ASC, le.criado_em ASC"
        );
        $stmt->execute([$idProfessor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array<string, mixed>> */
    public function listarAguardandoTodos(): array
    {
        $this->ensureSchema();
        $stmt = $this->pdo->query(
            "SELECT le.*, d.nome AS disciplina, u.nome AS professor, u.email AS professor_email
             FROM {$this->table} le
             JOIN disciplinas d ON le.id_disciplina = d.id
             JOIN usuarios u ON le.id_professor = u.id
             WHERE le.status = 'aguardando'
             ORDER BY le.data_reserva ASC, le.turno ASC, le.periodo ASC, le.criado_em ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarAlocado(int $id): void
    {
        $this->ensureSchema();
        $this->pdo->prepare("UPDATE {$this->table} SET status = 'alocado' WHERE id = ?")->execute([$id]);
    }

    public function inscrever(int $idProfessor, int $idDisciplina, string $data, string $turno, string $periodo): int
    {
        $this->ensureSchema();
        $existente = $this->buscarPorProfessorSlot($idProfessor, $data, $turno, $periodo);

        if ($existente) {
            if (($existente['status'] ?? '') === 'aguardando') {
                throw new \RuntimeException('Você já está na lista de espera para este horário.');
            }
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table}
                 SET id_disciplina = ?, status = 'aguardando', criado_em = NOW(), email_enviado_em = NULL
                 WHERE id = ?"
            );
            $stmt->execute([$idDisciplina, $existente['id']]);
            return (int) $existente['id'];
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (id_professor, id_disciplina, data_reserva, turno, periodo, status)
             VALUES (?, ?, ?, ?, ?, 'aguardando')"
        );
        $stmt->execute([$idProfessor, $idDisciplina, $data, $turno, $periodo]);
        return (int) $this->pdo->lastInsertId();
    }

    public function marcarEmailEnviado(int $id): void
    {
        $sql = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql'
            ? "UPDATE {$this->table} SET email_enviado_em = NOW() WHERE id = ?"
            : "UPDATE {$this->table} SET email_enviado_em = NOW() WHERE id = ?";
        $this->pdo->prepare($sql)->execute([$id]);
    }
}
