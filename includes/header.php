<?php
// includes/header.php - LOCALIZAÇÃO: escola/includes/

// ✅ SESSÃO PRIMEIRO
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// ✅ VALIDAÇÃO DE SESSÃO (DEVE VIR ANTES DE QUALQUER OUTPUT)
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/index.php');
    exit;
}

// ✅ CONEXÃO COM BANCO
if (!class_exists('Conexao')) {
    require_once __DIR__ . '/../config/conexao.php';
}

// ✅ FUNÇÕES AUXILIARES (COM CAMINHO CORRETO)
if (file_exists(__DIR__ . '/../functions/funcoes_chamadas.php')) {
    require_once __DIR__ . '/../functions/funcoes_chamadas.php';
}

// ✅ DEFINIÇÃO DE CONSTANTES
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/../');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e40af">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle).' | ' : '' ?>Sistema E.B.D</title>
    
    <!-- Favicon -->
    <link rel="icon" href="../assets/images/biblia.png" type="image/x-icon">
    
    <!-- CSS Externos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Locais -->
    <?php if (file_exists(__DIR__ . '/../assets/css/dashboard.css')): ?>
        <link rel="stylesheet" href="../assets/css/dashboard.css">
    <?php endif; ?>
    <?php if (file_exists(__DIR__ . '/../assets/css/aluno.css')): ?>
        <link rel="stylesheet" href="../assets/css/aluno.css">
    <?php endif; ?>
    
    <style>
        :root {
            --color-primary: #3b82f6; --color-primary-dark: #2563eb; --color-success: #10b981;
            --color-warning: #f59e0b; --color-danger: #ef4444; --color-gray-50: #f8fafc;
            --color-gray-100: #f1f5f9; --color-gray-200: #e2e8f0; --color-gray-300: #cbd5e1;
            --color-gray-600: #475569; --color-gray-800: #1e293b; --color-white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05); --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --radius: 8px; --radius-lg: 12px; --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body { 
            font-family: 'Inter', system-ui, sans-serif; 
            background: var(--color-gray-50); 
            color: var(--color-gray-800); 
            line-height: 1.5; 
            padding-top: 56px; 
            overflow-x: hidden; 
        }
        .navbar { box-shadow: var(--shadow-sm); background-color: var(--color-white) !important; }
        .navbar-brand { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; }
        .navbar-brand img { height: 30px; width: auto; }
        .navbar-nav .nav-link { font-weight: 500; color: var(--color-gray-600); transition: var(--transition); }
        .navbar-nav .nav-link:hover { color: var(--color-primary-dark); }
        .navbar-nav .nav-link.active { color: var(--color-primary-dark); font-weight: 600; }
    </style>
</head>
<body>