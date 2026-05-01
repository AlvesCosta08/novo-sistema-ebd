<?php
require_once '../models/usuario.php';
require_once '../config/conexao.php';

$acao = isset($_POST['acao']) ? $_POST['acao'] : '';

$usuario = new Usuario();

switch ($acao) {
    case 'listar':
        listarUsuarios($usuario);
        break;

    case 'salvar':
        salvarUsuario($usuario);
        break;

    case 'editar':
        editarUsuario($usuario);
        break;

    case 'excluir':
        excluirUsuario($usuario);
        break;

    case 'buscar':
        buscarUsuario($usuario);
        break;

    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não definida']);
        break;
}

function listarUsuarios($usuario) {
    $usuarios = $usuario->listar();
    if ($usuarios) {
        echo json_encode(['sucesso' => true, 'data' => $usuarios]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum usuário encontrado']);
    }
}

function salvarUsuario($usuario) {
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $perfil = isset($_POST['perfil']) ? $_POST['perfil'] : '';
    // Verificando se o valor de congregacao_id está presente ou é nulo
    $congregacao_id = isset($_POST['congregacao_id']) && !empty($_POST['congregacao_id']) ? $_POST['congregacao_id'] : null;

    if (empty($nome) || empty($email) || empty($senha) || empty($perfil)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    $resultado = $usuario->salvar($nome, $email, $senha, $perfil, $congregacao_id);
    if ($resultado) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário cadastrado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar usuário']);
    }
}

function editarUsuario($usuario) {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $perfil = isset($_POST['perfil']) ? $_POST['perfil'] : '';
    // Verificando se o valor de congregacao_id está presente ou é nulo
    $congregacao_id = isset($_POST['congregacao_id']) && !empty($_POST['congregacao_id']) ? $_POST['congregacao_id'] : null;

    if (empty($id) || empty($nome) || empty($email) || empty($perfil)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
        return;
    }

    $resultado = $usuario->editar($id, $nome, $email, $perfil, $congregacao_id, $senha);
    if ($resultado) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário atualizado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao editar usuário']);
    }
}

function excluirUsuario($usuario) {
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if (empty($id)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID não fornecido']);
        return;
    }

    $resultado = $usuario->excluir($id);
    if ($resultado) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário excluído com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir usuário']);
    }
}

function buscarUsuario($usuario) {
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if (empty($id)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID não fornecido']);
        return;
    }

    $dadosUsuario = $usuario->buscar($id);
    if ($dadosUsuario) {
        echo json_encode(['sucesso' => true, 'data' => $dadosUsuario]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado']);
    }
}