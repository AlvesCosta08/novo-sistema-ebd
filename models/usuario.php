<?php
require_once '../config/conexao.php';

class Usuario {
    private $conexao;

    public function __construct() {
        global $pdo;  // Assumindo que você tenha um objeto de conexão global
        $this->conexao = $pdo;
    }

    public function listar() {
        $sql = "SELECT u.id, u.nome, u.email, u.perfil, c.nome AS congregacao_nome FROM usuarios u LEFT JOIN congregacoes c ON u.congregacao_id = c.id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar($nome, $email, $senha, $perfil, $congregacao_id) {
        $sql = "INSERT INTO usuarios (nome, email, senha, perfil, congregacao_id) VALUES (:nome, :email, :senha, :perfil, :congregacao_id)";
        $stmt = $this->conexao->prepare($sql);
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);  // Criptografando a senha
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_hash);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':congregacao_id', $congregacao_id, PDO::PARAM_INT);  // Garantir que o tipo de dado seja INT
        return $stmt->execute();
    }

    public function editar($id, $nome, $email, $perfil, $congregacao_id, $senha = null) {
        if ($senha) {
            // Se a senha for fornecida, criptografa a nova senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, perfil = :perfil, congregacao_id = :congregacao_id WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindParam(':senha', $senha_hash);
        } else {
            // Caso a senha não seja fornecida, não altera o campo de senha
            $sql = "UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, congregacao_id = :congregacao_id WHERE id = :id";
            $stmt = $this->conexao->prepare($sql);
        }
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':perfil', $perfil);
        $stmt->bindParam(':congregacao_id', $congregacao_id, PDO::PARAM_INT);  // Garantir que o tipo de dado seja INT
        
        return $stmt->execute();
    }

    public function excluir($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function buscar($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}