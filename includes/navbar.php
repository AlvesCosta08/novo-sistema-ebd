<?php
// includes/navbar.php

// ✅ Garantir sessão ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Determinar o nome do usuário de forma segura
$nomeUsuario = 'Usuário';
if (isset($_SESSION['nome']) && !empty($_SESSION['nome'])) {
    $nomeUsuario = $_SESSION['nome'];
} elseif (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) {
    $nomeUsuario = $_SESSION['usuario_nome'];
} elseif (isset($_SESSION['usuario_id'])) {
    // Se tem ID mas não tem nome, tenta buscar
    $nomeUsuario = "ID: " . $_SESSION['usuario_id'];
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../assets/images/biblia.png" alt="EBD" height="30" class="d-inline-block align-text-top">
            <span class="d-none d-sm-inline">Escola Bíblica</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="alunos/index.php">Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="classes/index.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="professores/index.php">Professores</a></li>
                <li class="nav-item"><a class="nav-link" href="relatorios/index.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted me-2 d-none d-md-inline">
                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($nomeUsuario) ?>
                </span>
                <a class="btn btn-outline-danger btn-sm" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline ms-1">Sair</span>
                </a>
            </div>
        </div>
    </div>
</nav>