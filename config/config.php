<?php
// config/config.php - Configurações Globais do Sistema

// Evitar redefinição se incluído múltiplas vezes
if (defined('BASE_URL')) {
    return;
}

// ── URL base dinâmica ────────────────────────────────────────────────────────
// Calcula o caminho relativo da pasta raiz do projeto (escola/)
// a partir do DOCUMENT_ROOT físico do servidor.
//
// Resultado esperado: https://adtc2maranguapecombr.com/sistemas/escola
// (sem barra final — todos os links devem usar BASE_URL . '/caminho')

$protocol    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');   // .../escola
$docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$relativePath = ltrim(str_replace($docRoot, '', $projectRoot), '/');

// BASE_URL sem barra final
define('BASE_URL',   $protocol . '://' . $host . '/' . $relativePath);
// Caminho físico absoluto da raiz do projeto (escola/)
define('BASE_PATH',  $projectRoot);
// URL para a pasta de assets
define('ASSETS_URL', BASE_URL . '/assets');

// ── Sessão ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}