<?php
require_once "../config/conexao.php";

header("Content-Type: application/json"); // Garantir que a resposta seja JSON

if (!isset($_POST['acao'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
    exit;
}

$acao = $_POST['acao'];

try {
    if ($acao == 'listar') {
        try {
            $sql = "SELECT id, nome FROM congregacoes";  // Pode incluir campos que você precisa
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Respondendo com sucesso e os dados
            echo json_encode(['sucesso' => true, 'data' => $dados]);
        } catch (PDOException $e) {
            // Caso haja algum erro, responder com erro
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar congregações: ' . $e->getMessage()]);
        }
    }

    elseif ($acao == 'salvar') {
        $nome = trim($_POST['nome']);
        if (empty($nome)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Nome não pode estar vazio']);
            exit;
        }

        $sql = "INSERT INTO congregacoes (nome) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Congregação cadastrada com sucesso']);
    }

    elseif ($acao == 'editar') {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);

        if (empty($id) || !is_numeric($id) || empty($nome)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido ou nome vazio']);
            exit;
        }

        $query = "UPDATE congregacoes SET nome = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nome, $id]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Congregação atualizada com sucesso!']);
    }

    elseif ($acao == 'excluir') {
        $id = $_POST['id'];
    
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido']);
            exit;
        }
    
        // Excluir a congregação diretamente, já que não há dependências
        $sql = "DELETE FROM congregacoes WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    
        echo json_encode(['sucesso' => true, 'mensagem' => 'Congregação excluída com sucesso']);
    }
    

    elseif ($acao == 'buscar') {
        $id = $_POST['id'];
        
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido']);
            exit;
        }

        $sql = "SELECT * FROM congregacoes WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $congregacao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($congregacao) {
            echo json_encode(['sucesso' => true, 'congregacao' => $congregacao]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Congregação não encontrada']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
}
?>