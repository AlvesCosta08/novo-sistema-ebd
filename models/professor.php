<?php
require_once '../config/conexao.php';

class Professor {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    // Listar todos os professores
    public function listar() {
        $sql = "SELECT p.id, u.nome AS usuario_nome
                FROM professores p
                JOIN usuarios u ON p.usuario_id = u.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salvar um novo professor
    public function salvar($usuario_id) {
        $query = "INSERT INTO professores (usuario_id) VALUES (:usuario_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }

    // Editar um professor existente
    public function editar($id, $usuario_id) {
        $query = "UPDATE professores SET usuario_id = :usuario_id WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }

    // Excluir um professor
    public function excluir($id) {
        $query = "DELETE FROM professores WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}