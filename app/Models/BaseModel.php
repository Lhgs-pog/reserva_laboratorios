<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class BaseModel {
    protected $pdo;
    protected $table;

    public function __construct() {
        $this->pdo = Database::getInstance()->getPDO();
    }

    /**
     * Busca um registro por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todos os registros
     */
    public function findAll() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executar consulta personalizada
     */
    protected function executeQuery($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch associativo
     */
    protected function fetchAssoc($stmt) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all associativo
     */
    protected function fetchAll($stmt) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
