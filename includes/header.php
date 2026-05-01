<?php
/**
 * Header Principal do Sistema
 * 
 * Este arquivo deve ser incluído no início de TODAS as páginas do sistema
 * ANTES de qualquer saída HTML.
 * 
 * @package Escola\Includes
 * @version 3.0
 */

// ✅ CONFIGURAÇÕES INICIAIS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ VERIFICAÇÃO DE SESSÃO (protege páginas que não são de login)
$paginasPublicas = ['login.php', 'validar_login.php', 'recuperar_senha.php'];
$paginaAtual = basename($_SERVER['PHP_SELF']);

if (!in_array($paginaAtual, $paginasPublicas) && !isset($_SESSION['usuario_id'])) {
    header('Location: /escola/auth/login.php');
    exit;
}

// ✅ CARREGAR CONFIGURAÇÕES E FUNÇÕES
require_once __DIR__ . '/../config/conexao.php';

// Carregar funções gerais (se existir)
if (file_exists(__DIR__ . '/../functions/funcoes_gerais.php')) {
    require_once __DIR__ . '/../functions/funcoes_gerais.php';
}

// Carregar funções de chamadas (se existir)
if (file_exists(__DIR__ . '/../functions/funcoes_chamadas.php')) {
    require_once __DIR__ . '/../functions/funcoes_chamadas.php';
}

// Carregar funções do dashboard (se existir)
if (file_exists(__DIR__ . '/../functions/funcoes_dashboard.php')) {
    require_once __DIR__ . '/../functions/funcoes_dashboard.php';
}

// ✅ CONSTANTES GLOBAIS
define('BASE_URL', '/escola');
define('ASSETS_URL', BASE_URL . '/assets');
define('VIEWS_URL', BASE_URL . '/views');

// ✅ VARIÁVEIS DO USUÁRIO
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nome = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_perfil = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id = $_SESSION['congregacao_id'] ?? null;

// ✅ TÍTULO DA PÁGINA
$pageTitle = $pageTitle ?? 'Sistema EBD';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="theme-color" content="#1e40af">
    <meta name="description" content="Sistema de Escola Bíblica Dominical - Gestão de Chamadas e Matrículas">
    <meta name="author" content="EBD System">
    <title><?= htmlspecialchars($pageTitle) ?> | Sistema EBD</title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= ASSETS_URL ?>/images/biblia.png" type="image/x-icon">
    
    <!-- CSS Externos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Locais -->
    <?php if (file_exists(__DIR__ . '/../assets/css/dashboard.css')): ?>
        <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/dashboard.css">
    <?php endif; ?>
    
    <style>
        /* ===== VARIÁVEIS GLOBAIS ===== */
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --success: #198754;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #0dcaf0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray-600: #6c757d;
            --border-radius: 12px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: var(--light);
            color: var(--dark);
            line-height: 1.5;
            padding-top: 60px;
            overflow-x: hidden;
        }
        
        /* Navbar */
        .navbar {
            background: white !important;
            box-shadow: var(--shadow-sm);
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .navbar-brand img {
            height: 32px;
            width: auto;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--gray-600);
            transition: all 0.2s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary);
        }
        
        /* Cards */
        .card {
            border-radius: var(--border-radius);
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            font-weight: 600;
        }
        
        /* Botões */
        .btn {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        /* Tabelas */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: var(--light);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 500;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Container */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            body {
                padding-top: 56px;
            }
            
            .btn {
                padding: 6px 16px;
                font-size: 0.875rem;
            }
            
            .table thead th {
                font-size: 0.75rem;
            }
        }
        
        /* Utilitários */
        .cursor-pointer {
            cursor: pointer;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Scrollbar Personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>

<!-- Loading Overlay Global -->
<div id="globalLoading" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<!-- Navbar Principal -->
<nav class="navbar navbar-expand-lg fixed-top bg-white">
    <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand" href="<?= VIEWS_URL ?>/dashboard.php">
            <img src="<?= ASSETS_URL ?>/images/biblia.png" alt="Logo" class="d-inline-block align-text-top me-2">
            <span class="fw-bold">EBD System</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" 
                       href="<?= VIEWS_URL ?>/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($_SERVER['PHP_SELF'], 'chamada') !== false ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-clipboard-list me-1"></i> Chamadas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/chamada/index.php">Registrar Chamada</a></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/chamada/listar.php">Histórico de Chamadas</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($_SERVER['PHP_SELF'], 'matricula') !== false ? 'active' : '' ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-users me-1"></i> Matrículas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/matricula/index.php">Gerenciar Matrículas</a></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/matricula/listar.php">Listar Matrículas</a></li>
                    </ul>
                </li>
                <?php if ($usuario_perfil === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i> Administração
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/alunos/index.php">Alunos</a></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/classes/index.php">Classes</a></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/congregacoes/index.php">Congregações</a></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/usuarios/index.php">Usuários</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/relatorios/index.php">Relatórios</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars(explode(' ', $usuario_nome)[0]) ?>
                        <span class="badge bg-secondary ms-1"><?= ucfirst($usuario_perfil) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">
                            <small class="text-muted"><?= htmlspecialchars($usuario_nome) ?></small>
                        </span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= VIEWS_URL ?>/perfil.php">
                            <i class="fas fa-user me-2"></i> Meu Perfil
                        </a></li>
                        <li><a class="dropdown-item text-danger" href="/escola/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Scripts de inicialização -->
<script>
    // Variáveis Globais do Sistema
    const BASE_URL = '<?= BASE_URL ?>';
    const ASSETS_URL = '<?= ASSETS_URL ?>';
    const USER_ID = <?= (int)$usuario_id ?>;
    const USER_PERFIL = '<?= $usuario_perfil ?>';
    const USER_CONGR_ID = <?= json_encode($congregacao_id) ?>;
    
    // Função para exibir Toast
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;
        
        const icon = type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle';
        const bgColor = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';
        
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white ${bgColor} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.style.minWidth = '250px';
        toastEl.style.marginBottom = '10px';
        
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icon} me-2"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
    
    // Função para exibir SweetAlert (se disponível)
    function showAlert(title, message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: message,
                icon: type,
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            showToast(message, type);
        }
    }
    
    // Função para loading
    function showLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) overlay.style.display = 'flex';
    }
    
    function hideLoading() {
        const overlay = document.getElementById('globalLoading');
        if (overlay) overlay.style.display = 'none';
    }
    
    // Função para formatar data
    function formatarData(dataISO) {
        if (!dataISO) return '';
        const [ano, mes, dia] = dataISO.split('-');
        return `${dia}/${mes}/${ano}`;
    }
    
    // Função para formatar moeda
    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    }
</script>
<body>