<?php
namespace App\Models;

use PDO;

class Agendamento extends BaseModel {
    protected $table = 'agendamentos';

    /**
     * Busca laboratórios
     */
    public function buscarLaboratorios() {
        $sql = "SELECT id, nome, capacidade FROM laboratorios ORDER BY nome";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca disciplinas
     */
    public function buscarDisciplinas() {
        $sql = "SELECT id, nome FROM disciplinas ORDER BY nome";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria nova reserva (aprovada automaticamente para coordenador)
     */
    public function criarReserva($id_lab, $id_prof, $id_disciplina, $turno, $periodo, $data) {
        $sql = "INSERT INTO {$this->table} (id_laboratorio, id_professor, id_disciplina, turno, periodo, data_reserva, status) 
                VALUES (:lab, :prof, :disc, :turno, :periodo, :data, 'aprovado')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':lab', $id_lab);
        $stmt->bindParam(':prof', $id_prof);
        $stmt->bindParam(':disc', $id_disciplina);
        $stmt->bindParam(':turno', $turno);
        $stmt->bindParam(':periodo', $periodo);
        $stmt->bindParam(':data', $data);
        
        return $stmt->execute();
    }

    /**
     * Solicita nova reserva (pendente de aprovação)
     */
    public function solicitarReserva($id_lab, $id_prof, $id_disciplina, $turno, $periodo, $data) {
        $sql = "INSERT INTO {$this->table} (id_laboratorio, id_professor, id_disciplina, turno, periodo, data_reserva, status) 
                VALUES (:lab, :prof, :disc, :turno, :periodo, :data, 'pendente')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':lab', $id_lab);
        $stmt->bindParam(':prof', $id_prof);
        $stmt->bindParam(':disc', $id_disciplina);
        $stmt->bindParam(':turno', $turno);
        $stmt->bindParam(':periodo', $periodo);
        $stmt->bindParam(':data', $data);
        
        return $stmt->execute();
    }

    /**
     * Lista alocações do dia
     */
    public function listarAlocacoesDoDia($data) {
        $sql = "SELECT l.nome as laboratorio, u.nome as professor, d.nome as disciplina, a.turno, a.periodo 
                FROM {$this->table} a
                INNER JOIN laboratorios l ON a.id_laboratorio = l.id
                INNER JOIN usuarios u ON a.id_professor = u.id
                INNER JOIN disciplinas d ON a.id_disciplina = d.id
                WHERE a.data_reserva = :data AND a.status = 'aprovado'
                ORDER BY " . app_sql_order_turno('a.turno') . ", l.nome ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':data', $data);
        $stmt->execute();
        
        return $this->fetchAll($stmt);
    }

    /**
     * Lista alocações do professor
     */
    public function listarAlocacoesProfessor($id_professor) {
        $sql = "SELECT a.id, l.nome as laboratorio, d.nome as disciplina, a.turno, a.periodo, a.data_reserva, a.status 
                FROM {$this->table} a
                INNER JOIN laboratorios l ON a.id_laboratorio = l.id
                INNER JOIN disciplinas d ON a.id_disciplina = d.id
                WHERE a.id_professor = :id_professor 
                ORDER BY a.data_reserva DESC, " . app_sql_order_turno('a.turno');
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_professor', $id_professor);
        $stmt->execute();
        
        return $this->fetchAll($stmt);
    }

    /**
     * Lista solicitações pendentes
     */
    public function listarSolicitacoesPendentes() {
        $sql = "SELECT a.id, l.nome as laboratorio, u.nome as professor, d.nome as disciplina, a.turno, a.periodo, a.data_reserva 
                FROM {$this->table} a
                INNER JOIN laboratorios l ON a.id_laboratorio = l.id
                INNER JOIN usuarios u ON a.id_professor = u.id
                INNER JOIN disciplinas d ON a.id_disciplina = d.id
                WHERE a.status = 'pendente'
                ORDER BY a.data_reserva ASC";
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista reservas confirmadas
     */
    public function listarReservasConfirmadas() {
        $sql = "SELECT a.id, l.nome as laboratorio, u.nome as professor, d.nome as disciplina, a.turno, a.periodo, a.data_reserva 
                FROM {$this->table} a
                INNER JOIN laboratorios l ON a.id_laboratorio = l.id
                INNER JOIN usuarios u ON a.id_professor = u.id
                INNER JOIN disciplinas d ON a.id_disciplina = d.id
                WHERE a.status = 'aprovado'
                ORDER BY a.data_reserva DESC";
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca reserva por ID
     */
    public function buscarReservaPorId($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $this->fetchAssoc($stmt);
    }

    /**
     * Atualiza status da reserva
     */
    public function atualizarStatusReserva($id_agendamento, $novo_status) {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':status', $novo_status);
        $stmt->bindParam(':id', $id_agendamento);
        return $stmt->execute();
    }

    /**
     * Atualiza reserva
     */
    public function atualizarReserva($id, $id_lab, $id_prof, $id_disc, $turno, $periodo, $data) {
        $sql = "UPDATE {$this->table} 
                SET id_laboratorio = :lab, id_professor = :prof, id_disciplina = :disc, turno = :turno, periodo = :periodo, data_reserva = :data 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':lab', $id_lab);
        $stmt->bindParam(':prof', $id_prof);
        $stmt->bindParam(':disc', $id_disc); 
        $stmt->bindParam(':turno', $turno);
        $stmt->bindParam(':periodo', $periodo);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Exclui reserva
     */
    public function excluirReserva($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Verifica conflito de horário
     */
    public function verificaChoqueHorario($id_lab, $data_reserva, $turno, $periodo) {
        // Verifica se há reservas de outros professores
        $stmt_ag = $this->pdo->prepare("SELECT periodo FROM {$this->table} 
                                        WHERE id_laboratorio = ? AND data_reserva = ? AND turno = ? AND status = 'aprovado'");
        $stmt_ag->execute([$id_lab, $data_reserva, $turno]);
        $avulsos = $stmt_ag->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($avulsos as $p_banco) {
            if ($periodo === '1º e 2º Horários' || $p_banco === '1º e 2º Horários' || $periodo === $p_banco) {
                return "Já existe uma reserva aprovada para outro professor neste laboratório nesse dia e horário.";
            }
        }
        
        return null; // Sem conflito
    }
}
?>
