<?php
// test_mysql.php

echo "Testando conexão MySQL...\n\n";

$socket = '/opt/lampp/var/mysql/mysql.sock';

try {
    // Teste com socket
    $pdo = new PDO(
        "mysql:unix_socket={$socket};dbname=mysql;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conexão com socket funcionou!\n";
    
    // Teste query
    $stmt = $pdo->query("SELECT 'OK' as status");
    $result = $stmt->fetch();
    echo "✅ Query executada com sucesso: " . $result['status'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}