<?php
// test_matricula.php - Arquivo de diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'teste' => 'Conexão OK',
    'timestamp' => date('Y-m-d H:i:s')
]);