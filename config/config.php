<?php
// config.php - Configurações Globais do Sistema

// Definir URL Base ABSOLUTA (ajuste conforme seu domínio)
define('BASE_URL', 'https://dtc2maranguapecombr.com/sistemas/escola/views/');

// Ou, para detectar automaticamente (descomente se preferir):
/*
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . '://' . $host . rtrim($script, '/') . '/');
*/

// Definir caminho físico absoluto
define('BASE_PATH', __DIR__ . '/../views/');

// Iniciar sessão (se ainda não iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>