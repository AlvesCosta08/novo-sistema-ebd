<?php
require_once '../models/professor.php';
require_once '../config/conexao.php';

$acao = $_POST['acao'] ?? '';  // Usando o operador null coalescing para evitar erros

$professor = new Professor();

switch ($acao) {
    case 'listar':
        listarProfessores($professor);
        break;

    case 'salvar':
        salvarProfessor($professor);
        break;

    case 'editar':
        editarProfessor($professor);
        break;

    case 'excluir':
        excluirProfessor($professor);
        break;

    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não definida']);
        break;
}

function listarProfessores($professor) {
    $professores = $professor->listar();
    if ($professores) {
        echo json_encode(['sucesso' => true, 'data' => $professores]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum professor encontrado']);
    }
}

function salvarProfessor($professor) {
    $usuario_id = $_POST['usuario_id'] ?? '';

    if (empty($usuario_id)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não selecionado']);
        return;
    }

    if ($professor->salvar($usuario_id)) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Professor cadastrado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar professor']);
    }
}

function editarProfessor($professor) {
    $id = $_POST['id'] ?? '';
    $usuario_id = $_POST['usuario_id'] ?? '';

    if (empty($id) || empty($usuario_id)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    if ($professor->editar($id, $usuario_id)) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Professor atualizado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar professor']);
    }
}

function excluirProfessor($professor) {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID não fornecido']);
        return;
    }

    if ($professor->excluir($id)) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Professor excluído com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir professor']);
    }
}
?>





