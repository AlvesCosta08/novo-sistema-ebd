<?php
// valida_sessao.php - CORRIGIDO

// ✅ Verificar se a sessão já está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Salvar a página que estava tentando acessar
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirecionar para login
    header('Location: /escola/login.php');
    exit();
}

// Verificar tempo de inatividade (opcional - 30 minutos)
$timeout = 1800; // 30 minutos em segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: /escola/login.php?msg=Sessão expirada');
    exit();
}
$_SESSION['last_activity'] = time();
?>