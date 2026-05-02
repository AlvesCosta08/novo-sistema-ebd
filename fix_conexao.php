<?php
// SCRIPT DE CORREÇÃO DEFINITIVA DO CONEXAO.PHP
// Execute este script UMA vez e depois DELETE

$arquivo = __DIR__ . '/conexao.php';
$backup = __DIR__ . '/conexao_backup_' . date('Ymd_His') . '.php';

// Fazer backup
if (file_exists($arquivo)) {
    copy($arquivo, $backup);
    echo "<p>✅ Backup criado: " . basename($backup) . "</p>";
}

// NOVO CONTEÚDO CORRIGIDO (SEM DEPENDÊNCIAS)
$novo_conteudo = '<?php
/**
 * CONEXÃO COM BANCO DE DADOS - VERSÃO DEFINITIVA CORRIGIDA
 * SEM DEPENDÊNCIA DE COMPOSER, DOTENV OU ARQUIVOS EXTERNOS
 */

// ============================================
// ⚠️ CONFIGURE AQUI COM OS DADOS DO SEU BANCO ⚠️
// ============================================

$db_host = "localhost";
$db_name = "adtc2m99_ebd";      // Nome do banco de dados
$db_user = "adtc2m99_user";      // Usuário do banco
$db_pass = "";                    // ⚠️ COLOQUE A SENHA CORRETA AQUI ⚠️
$db_port = 3306;
$db_charset = "utf8mb4";

// ============================================
// NÃO ALTERAR DAQUI PARA BAIXO
// ============================================

// Definir constantes
define("DB_HOST", $db_host);
define("DB_NAME", $db_name);
define("DB_USER", $db_user);
define("DB_PASS", $db_pass);
define("DB_PORT", $db_port);
define("DB_CHARSET", $db_charset);
define("APP_ENV", "production");
define("APP_DEBUG", false);

// Configurar exibição de erros
ini_set("display_errors", 0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// Conectar ao banco de dados
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log("ERRO CONEXAO DB: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados. Contate o administrador.");
}

// Função auxiliar para compatibilidade
function env($key, $default = null) {
    return $default;
}
?>';

// Salvar o novo arquivo
if (file_put_contents($arquivo, $novo_conteudo)) {
    echo "<p style=\"color:green; font-weight:bold;\">✅ conexao.php CORRIGIDO com sucesso!</p>";
    echo "<hr>";
    echo "<h3>⚠️ PRÓXIMO PASSO IMPORTANTE:</h3>";
    echo "<p>1. Abra o arquivo <strong>conexao.php</strong> e coloque a senha do banco de dados na linha:</p>";
    echo "<code>\$db_pass = \"\";</code> → <code>\$db_pass = \"SUA_SENHA_AQUI\";</code>";
    echo "<p>2. Depois de colocar a senha, acesse:</p>";
    echo "<p><a href=\"../auth/login.php\" style=\"font-size:18px; font-weight:bold;\">👉 TESTAR O SISTEMA 👈</a></p>";
    echo "<hr>";
    echo "<p><strong style=\"color:red;\">🔴 IMPORTANTE: DELETE ESTE ARQUIVO APÓS USAR!</strong></p>";
    echo "<p><a href=\"?delete=1\">Clique aqui para deletar este arquivo</a></p>";
    
    // Opção para deletar o arquivo
    if (isset($_GET['delete'])) {
        unlink(__FILE__);
        echo "<script>alert('Arquivo removido com sucesso!'); window.location.href = '../auth/login.php';</script>";
    }
} else {
    echo "<p style=\"color:red;\">❌ ERRO: Não foi possível escrever no arquivo. Verifique as permissões da pasta.</p>";
    echo "<p>Tente ajustar as permissões da pasta config para 755 ou 777 temporariamente.</p>";
}
?>