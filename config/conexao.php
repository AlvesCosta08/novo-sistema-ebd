<?php
// conexao.php - Configuração do Banco de Dados

// Prevenir acesso direto
if (!defined('CONEXAO_LOADED')) {
    define('CONEXAO_LOADED', true);
}

// Carregar variáveis do arquivo .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Configurações do banco de dados
$host = getenv('DB_HOST') ?: '50.116.87.140';
$dbname = getenv('DB_NAME') ?: 'adtc2m99_ebd';
$username = getenv('DB_USERNAME') ?: 'adtc2m99_ebd';
$password = getenv('DB_PASSWORD') ?: 'Lav8@471';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>