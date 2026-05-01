<?php
/**
 * Configuração do Banco de Dados usando .env
 * 
 * @package Escola\Config
 */

// Definir ROOT_PATH se não estiver definida
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/..');
}

// Carregar autoload do Composer (se existir)
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
    
    use Dotenv\Dotenv;
    
    try {
        // Carregar variáveis de ambiente do arquivo .env
        $dotenv = Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
    } catch (Exception $e) {
        // Arquivo .env não encontrado - apenas em desenvolvimento local
        if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
            // Em desenvolvimento, pode usar valores padrão, mas com aviso
            error_log("⚠️ Arquivo .env não encontrado. Usando configurações padrão para desenvolvimento local.");
        } else {
            // Em produção, erro fatal - não pode continuar sem .env
            die("Erro: Arquivo .env não encontrado. Contate o administrador do sistema.");
        }
    }
}

// Definir constantes do banco de dados (APENAS do .env)
define('DB_HOST', $_ENV['DB_HOST'] ?? null);
define('DB_NAME', $_ENV['DB_NAME'] ?? null);
define('DB_USER', $_ENV['DB_USER'] ?? null);
define('DB_PASS', $_ENV['DB_PASS'] ?? null);
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Verificar se todas as configurações obrigatórias existem
$missingConfigs = [];
if (!DB_HOST) $missingConfigs[] = 'DB_HOST';
if (!DB_NAME) $missingConfigs[] = 'DB_NAME';
if (!DB_USER) $missingConfigs[] = 'DB_USER';
if (!DB_PASS && DB_PASS === null) $missingConfigs[] = 'DB_PASS';

if (!empty($missingConfigs)) {
    $errorMsg = "❌ Configurações de banco de dados ausentes no arquivo .env: " . implode(', ', $missingConfigs);
    error_log($errorMsg);
    
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
        die("<pre>$errorMsg\n\nVerifique seu arquivo .env na raiz do projeto.</pre>");
    } else {
        die("Erro de configuração do sistema. Contate o administrador.");
    }
}

// Configurar ambiente
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Configurar exibição de erros
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Conexão com o banco de dados
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ]);
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    } else {
        error_log("Erro de conexão com banco: " . $e->getMessage());
        die("Erro ao conectar ao banco de dados. Contate o administrador.");
    }
}

// Função auxiliar para obter variáveis de ambiente
function env($key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}
?>
