<?php
/**
 * Configuração de URLs do Sistema
 */

// Detecta o protocolo (HTTP ou HTTPS)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Detecta o host (ex: localhost, dominio.com)
$host = $_SERVER['HTTP_HOST'];

// Detecta o caminho base do projeto
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = rtrim(dirname(dirname($scriptName)), '/');

// Define URL base completa
define('BASE_URL_FULL', $protocol . '://' . $host . $basePath);
define('BASE_PATH', $basePath);

// Se estiver em subpasta, ajusta
if (strpos($basePath, '/escola') !== false) {
    define('BASE_URL', $basePath);
} else {
    define('BASE_URL', '/escola');
}

// Define outras constantes de URL
define('ASSETS_URL', BASE_URL . '/assets');
define('VIEWS_URL', BASE_URL . '/views');
define('AUTH_URL', BASE_URL . '/auth');
define('API_URL', BASE_URL . '/api');

// Função helper para criar URLs
function url($path = '') {
    // Remove barra inicial se existir
    $path = ltrim($path, '/');
    
    // Se for URL completa, retorna como está
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }
    
    // Constrói URL relativa ao projeto
    return BASE_URL . '/' . $path;
}

// Função para URL absoluta
function url_absolute($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL_FULL . '/' . $path;
}

// Função para assets
function asset($path = '') {
    $path = ltrim($path, '/');
    return ASSETS_URL . '/' . $path;
}

// Função para views
function view($path = '') {
    $path = ltrim($path, '/');
    return VIEWS_URL . '/' . $path;
}

// Função para redirecionamento
function redirect($path = '') {
    $url = url($path);
    header("Location: $url");
    exit;
}