<?php
require_once '../config/conexao.php';

class ProfessorClasse {
    private $db;

    public function __construct() {
        global $pdo;  // Assumindo que você tenha um objeto de conexão global
        $this->db = $pdo;
    }
    public function listar() {
        $stmt = $this->db->query("
            SELECT pc.id, p.nome AS professor, c.nome AS classe
            FROM professor_classes pc
            JOIN professores p ON pc.professor_id = p.id
            JOIN classes c ON pc.classe_id = c.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adicionar($professor_id, $classe_id) {
        $stmt = $this->db->prepare("
            INSERT INTO professor_classes (professor_id, classe_id) 
            VALUES (:professor_id, :classe_id)
        ");
        return $stmt->execute(['professor_id' => $professor_id, 'classe_id' => $classe_id]);
    }

    public function remover($id) {
        $stmt = $this->db->prepare("DELETE FROM professor_classes WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}