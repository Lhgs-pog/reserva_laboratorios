<?php
namespace App\Models;

use PDO;

require_once __DIR__ . '/../Config/sos_helpers.php';

class SOS extends BaseModel {
    protected $table = 'chamados_suporte';

    public function contarAbertos() {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE " . \sos_sql_in_ativos();
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    public function contarPendentes() {
        return $this->contarAbertos();
    }

    public function listarAtivos() {
        $sql = "SELECT * FROM {$this->table} WHERE " . \sos_sql_in_ativos() . " ORDER BY data_hora DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEncerrados(int $limit = 200) {
        $sql = "SELECT * FROM {$this->table} WHERE " . \sos_sql_in_encerrados()
            . " ORDER BY COALESCE(resolvido_em, atualizado_em, data_hora) DESC LIMIT " . (int) $limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorProfessor(int $idProfessor, int $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE id_professor = :id ORDER BY data_hora DESC LIMIT " . (int) $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idProfessor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodos($status = null) {
        $sql = "SELECT * FROM {$this->table}";
        if ($status) {
            $sql .= " WHERE status = :status";
        }
        $sql .= " ORDER BY data_hora DESC";
        $stmt = $this->pdo->prepare($sql);
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        return $this->fetchAll($stmt);
    }

    public function listarStatus() {
        $chamados    = $this->listarAtivos();
        $qtd_suporte = count($chamados);

        $html_suporte = '';
        if ($qtd_suporte > 0) {
            $html_suporte .= '<div class="alert-sos-banner alert-sos-notificacao" role="button" title="Clique para ver os chamados">';
            $html_suporte .= '<div class="alert-sos-banner-header">';
            $html_suporte .= '<i class="bi bi-headset"></i>';
            $html_suporte .= '<strong>' . $qtd_suporte . ' chamado(s) em aberto</strong>';
            $html_suporte .= '<small>(clique para abrir)</small></div>';
            $html_suporte .= '<ul class="alert-sos-banner-list">';
            foreach ($chamados as $c) {
                $st = \sos_status_label($c['status'] ?? 'pendente');
                $html_suporte .= '<li><span class="badge ' . \sos_status_badge_class($c['status'] ?? 'pendente') . ' me-1">' . htmlspecialchars($st) . '</span> ';
                $html_suporte .= '<strong>' . htmlspecialchars($c['professor_nome'] ?? '') . '</strong> — ';
                $html_suporte .= htmlspecialchars($c['laboratorio'] ?? '') . ': ';
                $html_suporte .= htmlspecialchars($c['mensagem'] ?? '') . '</li>';
            }
            $html_suporte .= '</ul></div>';
        }

        return ['qtd_suporte' => $qtd_suporte, 'html_suporte' => $html_suporte];
    }

    public function buscarPorId($id) {
        return $this->findById($id);
    }

    public function criar($id_professor, $laboratorio, $mensagem, $professor_nome = '') {
        $sql  = "INSERT INTO {$this->table} (id_professor, professor_nome, laboratorio, mensagem, status)
                 VALUES (:id_professor, :professor_nome, :laboratorio, :mensagem, 'pendente')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_professor', $id_professor);
        $stmt->bindParam(':professor_nome', $professor_nome);
        $stmt->bindParam(':laboratorio', $laboratorio);
        $stmt->bindParam(':mensagem', $mensagem);
        return $stmt->execute();
    }

    public function atualizarChamado(int $id, array $dados): bool {
        $permitidos = ['status', 'observacao_interna', 'resposta_professor', 'id_atendente', 'nome_atendente', 'resolvido_em', 'ultimo_email_em', 'historico_log'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $sets[] = "{$campo} = :{$campo}";
                $params[":{$campo}"] = $dados[$campo];
            }
        }

        if (!$sets) {
            return false;
        }

        $sets[] = 'atualizado_em = ' . (app_db_driver() === 'pgsql' ? 'NOW()' : 'NOW()');
        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function atualizarStatus($id, $status) {
        return $this->atualizarChamado((int) $id, ['status' => $status]);
    }
}
?>
