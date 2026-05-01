<?php
// tests/bootstrap.php

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

// Carrega autoload do Composer
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

date_default_timezone_set('America/Sao_Paulo');

// Configuração do banco de testes com socket do LAMPP
function getTestConnection() {
    try {
        // Usando o socket correto do LAMPP (igual funcionou no test_mysql.php)
        $socket = '/opt/lampp/var/mysql/mysql.sock';
        
        $pdo = new PDO(
            "mysql:unix_socket={$socket};dbname=escola_testes;charset=utf8mb4",
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        
        // Recria o esquema do banco para testes
        recreateTestSchema($pdo);
        
        return $pdo;
    } catch (PDOException $e) {
        die("Erro ao conectar ao banco de testes: " . $e->getMessage() . "\n");
    }
}

function recreateTestSchema($pdo) {
    // Desabilita verificação de chaves estrangeiras
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Remove tabelas se existirem
    $tables = ['presencas', 'chamadas', 'matriculas', 'alunos', 'professores', 
               'usuarios', 'classes', 'congregacoes', 'logs_acesso'];
    
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Recria tabelas
    $pdo->exec("
        CREATE TABLE `congregacoes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `classes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(100) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `usuarios` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `senha` varchar(255) NOT NULL,
            `perfil` enum('admin','user','professor') NOT NULL,
            `congregacao_id` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `alunos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(100) NOT NULL,
            `data_nascimento` date DEFAULT NULL,
            `telefone` varchar(15) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `classe_id` int(11) NOT NULL,
            `congregacao_id` int(11) DEFAULT NULL,
            `status` enum('ativo','inativo','suspenso','transferido') DEFAULT 'ativo',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `matriculas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `aluno_id` int(11) NOT NULL,
            `classe_id` int(11) NOT NULL,
            `congregacao_id` int(11) NOT NULL,
            `usuario_id` int(11) NOT NULL,
            `data_matricula` date NOT NULL,
            `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
            `trimestre` varchar(10) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `chamadas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `data` date NOT NULL,
            `classe_id` int(11) NOT NULL,
            `congregacao_id` int(11) NOT NULL,
            `professor_id` int(11) NOT NULL,
            `trimestre` varchar(7) NOT NULL,
            `oferta_classe` decimal(10,2) DEFAULT 0.00,
            `total_visitantes` int(11) DEFAULT 0,
            `total_biblias` int(11) DEFAULT 0,
            `total_revistas` int(11) DEFAULT 0,
            `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `presencas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `chamada_id` int(11) NOT NULL,
            `aluno_id` int(11) NOT NULL,
            `presente` enum('presente','ausente','justificado') NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `professores` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario_id` int(11) NOT NULL,
            `congregacao_id` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE `logs_acesso` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario_id` int(11) NOT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `data_acesso` datetime NOT NULL,
            `data_saida` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

function clearTestData($pdo) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE presencas");
    $pdo->exec("TRUNCATE TABLE chamadas");
    $pdo->exec("TRUNCATE TABLE matriculas");
    $pdo->exec("TRUNCATE TABLE alunos");
    $pdo->exec("TRUNCATE TABLE professores");
    $pdo->exec("TRUNCATE TABLE usuarios");
    $pdo->exec("TRUNCATE TABLE classes");
    $pdo->exec("TRUNCATE TABLE congregacoes");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}