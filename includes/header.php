<?php
// includes/header.php - Cabeçalho do Sistema com tema padronizado

// Garantir que as constantes estão definidas
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Obter informações do usuário logado
$usuario_nome    = $_SESSION['usuario_nome']   ?? $_SESSION['nome']   ?? 'Usuário';
$usuario_perfil  = $_SESSION['usuario_perfil'] ?? $_SESSION['perfil'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D - <?= htmlspecialchars($titulo_pagina ?? $pageTitle ?? 'Dashboard') ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables 2.2.2 -->
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet">
    
    <!-- Font Awesome 6.7.2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Tema Personalizado EBD -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/theme-ebd.css">
    
    <!-- CSS Personalizados -->
    <?php if (file_exists(BASE_PATH . '/assets/css/dashboard.css')): ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/dashboard.css">
    <?php endif; ?>
    <?php if (file_exists(BASE_PATH . '/assets/css/aluno.css')): ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/aluno.css">
    <?php endif; ?>

    <style>
        /* Ajustes adicionais de padding */
        body { padding-top: 76px; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-400);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-600);
        }
    </style>
</head>
<body>
    <!-- Navbar com classe personalizada -->
    <?php require_once __DIR__ . '/navbar.php'; ?>

    <!-- Início do conteúdo principal -->
    <main class="container-fluid py-4">
    
    <!-- Scripts necessários -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <script>
        // Inicializar AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
        
        // Função global para inicializar DataTables
        function initDataTable(tableId, options = {}) {
            const defaultOptions = {
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.2.2/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
            };
            
            const finalOptions = {...defaultOptions, ...options};
            return $(tableId).DataTable(finalOptions);
        }
        
        // Inicializar DataTables automático
        $(document).ready(function() {
            $('.datatable').each(function() {
                if ($(this).attr('id')) {
                    initDataTable('#' + $(this).attr('id'));
                }
            });
            
            // Tooltips automáticos
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
    
    <!-- Scripts personalizados -->
    <?php if (file_exists(BASE_PATH . '/assets/js/custom.js')): ?>
    <script src="<?= ASSETS_URL ?>/js/custom.js"></script>
    <?php endif; ?>
