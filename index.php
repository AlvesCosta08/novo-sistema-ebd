<?php
session_start();

if (isset($_SESSION["usuario_id"])) {
    // Se o usuário estiver logado, redireciona para o painel
    header("Location: views/dashboard.php");
} else {
    // Caso contrário, leva para a tela de login
    header("Location: auth/login.php");
}
exit;
?>
