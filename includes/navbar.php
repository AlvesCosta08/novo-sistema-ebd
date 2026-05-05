<?php
// includes/navbar.php - Navbar moderna com bg-body-tertiary e cores modernas
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

$usuario_nome   = $_SESSION['usuario_nome']   ?? $_SESSION['nome']   ?? 'Usuário';
$usuario_perfil = $_SESSION['usuario_perfil'] ?? $_SESSION['perfil'] ?? 'user';
$usuario_email  = $_SESSION['usuario_email']  ?? '';
$primeiroNome   = explode(' ', $usuario_nome)[0];
$scriptAtual    = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);

// Menu items
$menuItems = [
    ['url' => 'dashboard.php', 'icon' => 'tachometer-alt', 'label' => 'Dashboard', 'section' => 'dashboard'],
    ['url' => 'chamadas/index.php', 'icon' => 'book-open', 'label' => 'Chamadas', 'section' => 'chamadas'],
    ['url' => 'alunos/index.php', 'icon' => 'users', 'label' => 'Alunos', 'section' => 'alunos'],
    ['url' => 'matriculas/index.php', 'icon' => 'user-plus', 'label' => 'Matrículas', 'section' => 'matriculas'],
    ['url' => 'classes/index.php', 'icon' => 'chalkboard-user', 'label' => 'Classes', 'section' => 'classes'],
    ['url' => 'relatorios/index.php', 'icon' => 'chart-line', 'label' => 'Relatórios', 'section' => 'relatorios'],
    ['url' => 'sorteios.php', 'icon' => 'gift', 'label' => 'Sorteios', 'section' => 'sorteios'],
];

function isActive($scriptAtual, $section) {
    return str_contains($scriptAtual, $section) ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-bottom: 1px solid rgba(0,0,0,0.05);">
    <div class="container-fluid px-3 px-md-4">
        <!-- Brand/Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 py-1" href="<?= BASE_URL ?>/views/dashboard.php">
            <div class="brand-icon-wrapper rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
                <img src="<?= BASE_URL ?>/assets/images/biblia.png" alt="Logo EBD System" class="img-fluid" style="width: 26px; height: 26px; object-fit: contain; filter: brightness(0) invert(1);">
            </div>
            <div class="brand-text">
                <span class="fw-bold fs-5" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); background-clip: text; -webkit-background-clip: text; color: transparent;">EBD System</span>
                <small class="d-none d-md-block text-muted" style="font-size: 10px; line-height: 1.2;">Escola Bíblica Dominical</small>
            </div>
        </a>

        <!-- Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menuItems as $item): ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= isActive($scriptAtual, $item['section']) ?>" 
                       href="<?= BASE_URL ?>/views/<?= $item['url'] ?>">
                        <i class="fas fa-<?= $item['icon'] ?> fa-fw"></i>
                        <span><?= $item['label'] ?></span>
                        <?php if ($item['label'] === 'Sorteios'): ?>
                        <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">Novo</span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>

                <!-- Admin Dropdown com Sair no final -->
                <?php if ($usuario_perfil === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog fa-fw"></i>
                        <span>Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/views/usuario/index.php"><i class="fas fa-user-shield me-2 text-primary"></i> Usuários</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/views/congregacao/index.php"><i class="fas fa-church me-2 text-primary"></i> Congregações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/views/backup/index.php"><i class="fas fa-database me-2 text-info"></i> Backup</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger fw-semibold py-2" href="<?= BASE_URL ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2 text-danger"></i> Sair
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Search Bar -->
            <form class="d-flex my-2 my-lg-0 me-lg-3" role="search" action="<?= BASE_URL ?>/views/busca.php" method="GET">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-sm" type="search" placeholder="Pesquisar..." aria-label="Search" name="q" style="border-radius: 30px 0 0 30px; border: 1px solid #e2e8f0;">
                    <button class="btn btn-primary btn-sm" type="submit" style="border-radius: 0 30px 30px 0;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- User Dropdown (para usuários comuns) -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                            <i class="fas fa-user text-white fa-sm"></i>
                        </div>
                        <span class="fw-semibold d-none d-lg-inline text-dark"><?= htmlspecialchars($primeiroNome) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="min-width: 260px;">
                        <li>
                            <div class="dropdown-item-text">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($usuario_nome) ?></div>
                                <small class="text-muted d-block"><?= htmlspecialchars($usuario_email) ?></small>
                                <span class="badge bg-primary mt-2"><?= ucfirst($usuario_perfil) ?></span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/perfil.php"><i class="fas fa-user-circle me-2 text-primary"></i> Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/configuracoes.php"><i class="fas fa-sliders-h me-2 text-secondary"></i> Configurações</a></li>
                        <?php if ($usuario_perfil !== 'admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger fw-semibold" href="<?= BASE_URL ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2 text-danger"></i> Sair
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* ============================================
   NAVBAR MODERNA - ESTILOS PREMIUM
   ============================================ */

/* Reset para desktop - garantir que todos os menus apareçam */
@media (min-width: 992px) {
    .navbar-expand-lg .navbar-nav {
        flex-direction: row !important;
        max-height: none !important;
        overflow: visible !important;
    }
    
    .navbar-expand-lg .navbar-collapse {
        display: flex !important;
        flex-basis: auto !important;
    }
    
    .navbar-nav {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
}

/* Navbar Links */
.navbar .nav-link {
    padding: 0.5rem 1rem;
    margin: 0 2px;
    border-radius: 12px;
    color: #1e293b;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.navbar .nav-link:hover {
    background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
    color: #4f46e5;
    transform: translateY(-1px);
}

.navbar .nav-link.active {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
}

.navbar .nav-link.active i {
    color: white;
}

/* Dropdown Menu */
.navbar .dropdown-menu {
    border-radius: 16px;
    overflow: hidden;
    animation: fadeInDown 0.2s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.navbar .dropdown-item {
    padding: 10px 20px;
    transition: all 0.2s ease;
    color: #334155;
}

.navbar .dropdown-item:hover {
    background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
    padding-left: 28px;
    color: #4f46e5;
}

.navbar .dropdown-item.text-danger:hover {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626 !important;
}

.navbar .form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
}

.brand-icon-wrapper {
    transition: all 0.3s ease;
}

.brand-icon-wrapper:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
}

.user-avatar {
    transition: all 0.2s ease;
}

.user-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
}

.navbar-toggler {
    border: none;
    padding: 8px 12px;
    background: transparent;
}

.navbar-toggler:focus {
    box-shadow: none;
    outline: none;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%234f46e5' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.navbar .badge {
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 20px;
}

/* Estilos para mobile */
@media (max-width: 991.98px) {
    .navbar .nav-link {
        padding: 0.5rem 0.75rem;
        white-space: normal;
    }
    
    .navbar .dropdown-menu {
        border: none;
        background: transparent;
        box-shadow: none;
        padding-left: 20px;
    }
    
    .navbar .dropdown-item {
        padding-left: 20px;
    }
    
    .navbar .dropdown-item:hover {
        padding-left: 28px;
        background: rgba(79, 70, 229, 0.05);
    }
    
    /* No mobile, permite scroll se necessário */
    .navbar-nav {
        max-height: calc(100vh - 80px);
        overflow-y: auto;
    }
}

@media (max-width: 768px) {
    .navbar-brand .brand-text {
        display: none;
    }
    
    .navbar .nav-link {
        font-size: 14px;
    }
}

/* Desktop: sem scroll, menus visíveis */
@media (min-width: 992px) {
    .navbar-nav {
        max-height: none !important;
        overflow-y: visible !important;
    }
}
</style>