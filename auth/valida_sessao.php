<?php
// auth/valida_sessao.php
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Carregar config para ter BASE_URL disponível
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/config.php';
}
 
// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}
 
// Verificar inatividade (30 minutos)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/auth/login.php?msg=sessao_expirada');
    exit();
}
$_SESSION['last_activity'] = time();