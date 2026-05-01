<?php
require_once '../config/conexao.php';
require_once '../models/classe.php';

header('Content-Type: application/json');

$classeModel = new Classe($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    switch ($acao) {
        case 'listar':
            echo json_encode(['sucesso' => true, 'data' => $classeModel->listar()]);
            break;

        case 'salvar':
            $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
            if (empty($nome)) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Nome é obrigatório.']);
                break;
            }
            
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Editar classe
                $id = (int)$_POST['id'];
                echo json_encode($classeModel->editar($id, $nome));
            } else {
                // Salvar nova classe
                echo json_encode($classeModel->salvar($nome));
            }
            break;

        case 'excluir':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if (!$id) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID não fornecido.']);
                break;
            }
            echo json_encode($classeModel->excluir($id));
            break;

        case 'buscar':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if (!$id) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID não fornecido.']);
                break;
            }
            $classe = $classeModel->buscarPorId($id);
            if ($classe) {
                echo json_encode(['sucesso' => true, 'data' => $classe]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Classe não encontrada.']);
            }
            break;

        default:
            echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
            break;
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não especificada.']);
}