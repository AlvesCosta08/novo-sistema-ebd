<?php 
require_once '../config/conexao.php';

class Classe {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listar() {
        $stmt = $this->pdo->prepare("SELECT * FROM classes");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM classes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar($nome) {
        $query = "INSERT INTO classes (nome) VALUES (:nome)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();
        return ['sucesso' => true, 'mensagem' => 'Classe cadastrada com sucesso!'];
    }

    public function editar($id, $nome) {
        if (!$this->buscarPorId($id)) {
            return ['sucesso' => false, 'mensagem' => 'Classe não encontrada'];
        }
        $query = "UPDATE classes SET nome = :nome WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();
        return ['sucesso' => true, 'mensagem' => 'Classe atualizada com sucesso!'];
    }

    public function excluir($id) {
        $stmt = $this->pdo->prepare("DELETE FROM classes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return ['sucesso' => true, 'mensagem' => 'Classe excluída com sucesso!'];
    }

    public function listarClassesPorCongregacao($congregacao_id) {
        // Consulta SQL para buscar as classes ativas associadas à congregação
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT c.id, c.nome
            FROM classes c
            JOIN matriculas m ON m.classe_id = c.id
            WHERE m.congregacao_id = :congregacao_id
            AND m.status = 'ativo'
        ");
        $stmt->bindParam(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
        $stmt->execute();
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if ($classes) {
            return ['sucesso' => true, 'classes' => $classes];
        } else {
            return ['sucesso' => false, 'mensagem' => 'Nenhuma classe encontrada para a congregação'];
        }
    }
    
    
}