<?php
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/escola');

$host = "localhost";
$dbname = "escola";
$username = "root";
$password = "";



try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>
