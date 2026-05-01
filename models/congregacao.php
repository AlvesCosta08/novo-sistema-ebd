<?php
require_once '../config/conexao.php';
class Congregacao {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM congregacoes";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao buscar congregações: ' . $e->getMessage()];
        }
    }

    public function salvar($nome) {
        try {
            $sql = "INSERT INTO congregacoes (nome) VALUES (?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nome]);
            return ['sucesso' => true, 'mensagem' => 'Congregação cadastrada com sucesso'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao salvar congregação: ' . $e->getMessage()];
        }
    }

    public function excluir($id) {
        try {
            $sql = "DELETE FROM congregacoes WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return ['sucesso' => true, 'mensagem' => 'Congregação excluída com sucesso'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao excluir congregação: ' . $e->getMessage()];
        }
    }

    public function buscar($id) {
        try {
            $sql = "SELECT * FROM congregacoes WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao buscar congregação: ' . $e->getMessage()];
        }
    }

    public function editar($id, $nome) {
        try {
            $sql = "UPDATE congregacoes SET nome = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nome, $id]);
            return ['sucesso' => true, 'mensagem' => 'Congregação atualizada com sucesso!'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro ao editar congregação: ' . $e->getMessage()];
        }
    }
}