<?php
echo "=== TESTE DE CONFIGURAÇÃO ===<br><br>";

echo "1. Carregando config.php...<br>";
require_once dirname(__DIR__) . '/config/config.php';
echo "✓ BASE_URL: " . BASE_URL . "<br>";
echo "✓ BASE_PATH: " . BASE_PATH . "<br><br>";

echo "2. Carregando conexao.php...<br>";
require_once dirname(__DIR__) . '/config/conexao.php';
echo "✓ Conexão estabelecida com sucesso!<br><br>";

echo "3. Testando query simples...<br>";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM alunos WHERE status = 'ativo'");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "✓ Total de alunos ativos: " . $total . "<br><br>";

echo "4. Verificando sessão...<br>";
session_start();
if (isset($_SESSION['usuario_id'])) {
    echo "✓ Usuário logado: " . ($_SESSION['usuario_nome'] ?? 'N/A') . "<br>";
} else {
    echo "⚠️ Nenhum usuário logado<br>";
}

echo "<br>=== FIM DO TESTE ===";
?>