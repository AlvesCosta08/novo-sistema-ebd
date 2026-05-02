<?php
// auth/validar_login.php

session_start();
require_once dirname(__DIR__) . '/config/conexao.php';
require_once dirname(__DIR__) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

if (empty($_POST['email']) || empty($_POST['senha'])) {
    $_SESSION['mensagem'] = 'Preencha todos os campos.';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

try {
    if (!isset($pdo)) {
        die('Erro na conexão com o banco de dados!');
    }

    $sql  = 'SELECT id, nome, email, senha, perfil, congregacao_id FROM usuarios WHERE email = :email';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        if (in_array($usuario['perfil'], ['admin', 'professor'])) {

            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['nome']           = $usuario['nome'] ?? 'Usuário';
            $_SESSION['usuario_nome']   = $usuario['nome'] ?? 'Usuário';
            $_SESSION['usuario_email']  = $usuario['email'];
            $_SESSION['perfil']         = $usuario['perfil'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];
            $_SESSION['congregacao_id'] = $usuario['congregacao_id'] ?? null;
            $_SESSION['last_activity']  = time();

            session_regenerate_id(true);

            // Redirecionar para onde tentava ir, ou dashboard
            $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/views/dashboard.php');
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit();

        } else {
            $_SESSION['mensagem'] = 'Acesso restrito. Apenas administradores e professores podem acessar.';
        }
    } elseif ($usuario) {
        $_SESSION['mensagem'] = 'Senha incorreta.';
    } else {
        $_SESSION['mensagem'] = 'Usuário não encontrado.';
    }

} catch (PDOException $e) {
    $_SESSION['mensagem'] = 'Erro no banco de dados: ' . $e->getMessage();
}

header('Location: ' . BASE_URL . '/auth/login.php');
exit();