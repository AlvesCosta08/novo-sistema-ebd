<?php
require_once '../models/professorclasse.php';
require_once '../config/conexao.php';

$acao = $_POST['acao'];

switch ($acao) {
    case 'listar':
        // Listar professores
        $query = "SELECT p.id, u.nome AS usuario_nome
                  FROM professores p
                  JOIN usuarios u ON p.usuario_id = u.id";
        $result = $pdo->query($query);
        $professores = $result->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['sucesso' => true, 'data' => $professores]);
        break;

    case 'salvar':
        // Salvar novo professor
        $usuario_id = $_POST['usuario_id'];
        
        $query = "INSERT INTO professores (usuario_id) VALUES (:usuario_id)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Professor cadastrado com sucesso.']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar professor.']);
        }
        break;

    case 'editar':
        // Editar professor
        $id = $_POST['id'];
        $usuario_id = $_POST['usuario_id'];
        
        $query = "UPDATE professores SET usuario_id = :usuario_id WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Professor editado com sucesso.']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao editar professor.']);
        }
        break;

    case 'excluir':
        // Excluir professor
        $id = $_POST['id'];
        
        $query = "DELETE FROM professores WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Professor excluído com sucesso.']);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir professor.']);
        }
        break;

    case 'buscar':
        // Buscar professor para editar
        $id = $_POST['id'];
        
        $query = "SELECT * FROM professores WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $professor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($professor) {
            echo json_encode(['sucesso' => true, 'data' => $professor]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Professor não encontrado.']);
        }
        break;
}
?>
