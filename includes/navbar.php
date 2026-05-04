<?php
// includes/navbar.php - Navbar compatível com Bootstrap 5 + jQuery
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

$usuario_nome   = $_SESSION['usuario_nome']   ?? $_SESSION['nome']   ?? 'Usuário';
$usuario_perfil = $_SESSION['usuario_perfil'] ?? $_SESSION['perfil'] ?? 'user';
$primeiroNome   = explode(' ', $usuario_nome)[0];
$scriptAtual = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="../views/dashboard.php">
            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 40px; height: 40px; flex-shrink: 0;">
               <img src="<?= BASE_URL ?>/assets/images/biblia.png" alt="Logo EBD System" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="d-none d-sm-block">
                <span class="fw-bold fs-5 text-white">EBD System</span>
                <small class="d-block text-white-50" style="font-size: 10px; line-height: 1;">Escola Bíblica Dominical</small>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= str_contains($scriptAtual, 'dashboard') ? 'active' : '' ?>" href="<?= BASE_URL ?>/views/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link <?= str_contains($scriptAtual, 'chamadas') ? 'active' : '' ?>" href="<?= BASE_URL ?>/views/chamadas/index.php"><i class="fas fa-book-open me-2"></i> Chamadas</a></li>
                <li class="nav-item"><a class="nav-link <?= str_contains($scriptAtual, 'alunos') ? 'active' : '' ?>" href="<?= BASE_URL ?>/views/alunos/index.php"><i class="fas fa-users me-2"></i> Alunos</a></li>
                <li class="nav-item"><a class="nav-link <?= str_contains($scriptAtual, 'matriculas') ? 'active' : '' ?>" href="<?= BASE_URL ?>/views/matriculas/index.php"><i class="fas fa-user-plus me-2"></i> Matrículas</a></li>
                <li class="nav-item"><a class="nav-link <?= str_contains($scriptAtual, 'classes') ? 'active' : '' ?>" href="<?= BASE_URL ?>/views/classes/index.php"><i class="fas fa-chalkboard-user me-2"></i> Classes</a></li>
                <li class="nav-item"><a class="nav-link <?= str_contains($scriptAtual, 'relatorios') ? 'active' : '' ?>" href="<?= BASE_URL ?>/views/relatorios/index.php"><i class="fas fa-chart-line me-2"></i> Relatórios</a></li>

                <?php if ($usuario_perfil === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-2"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><a class="dropdown-item" href="../views/usuario/index.php"><i class="fas fa-user-shield me-2 text-primary"></i> Usuários</a></li>
                        <li><a class="dropdown-item" href="../views/congregacao/index.php"><i class="fas fa-church me-2 text-primary"></i> Congregações</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <form class="d-flex mb-2 mb-lg-0 me-3" role="search" action="<?= BASE_URL ?>/views/busca.php" method="GET">
                <input class="form-control me-2" type="search" placeholder="Pesquisar..." aria-label="Search" name="q">
                <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
            </form>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-user text-white fa-lg"></i>
                        </div>
                        <span class="fw-semibold d-none d-md-inline"><?= htmlspecialchars($primeiroNome) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><div class="dropdown-item-text"><div class="fw-bold"><?= htmlspecialchars($usuario_nome) ?></div><small class="text-muted"><span class="badge bg-primary mt-1"><?= ucfirst($usuario_perfil) ?></span></small></div></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>