<?php
// views/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once dirname(__DIR__) . '/config/conexao.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/functions/funcoes_dashboard.php';

if (!isset($pdo)) {
    die('Erro de conexão com o banco de dados.');
}

$titulo_pagina  = 'Dashboard';
$pageTitle      = 'Dashboard';
$usuario_nome   = $_SESSION['usuario_nome']   ?? $_SESSION['nome']   ?? 'Usuário';
$usuario_perfil = $_SESSION['usuario_perfil'] ?? $_SESSION['perfil'] ?? 'user';

// Uma única função traz tudo — sem queries espalhadas
$d = obterDadosDashboard($pdo);

$totalAlunos     = $d['total_alunos'];
$totalClasses    = $d['total_classes'];
$chamadasHoje    = $d['chamadas_hoje'];
$chamadasMes     = $d['chamadas_mes'];
$frequenciaMedia = $d['frequencia_media'];
$ultimasChamadas = $d['ultimas_chamadas'];
$aniversariantes = $d['aniversariantes'];
$ofertas         = $d['ofertas'];

require_once dirname(__DIR__) . '/includes/header.php';

$hora = (int)date('H');
if      ($hora >= 5  && $hora < 12) { $saudacao = 'Bom dia';   $icone = 'sun';  }
elseif  ($hora >= 12 && $hora < 18) { $saudacao = 'Boa tarde'; $icone = 'sun';  }
else                                 { $saudacao = 'Boa noite'; $icone = 'moon'; }
?>

<!-- Meta tag para viewport responsiva -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

<style>
/* Reset e melhorias de responsividade */
:root {
    --mobile-breakpoint: 768px;
    --tablet-breakpoint: 1024px;
}

* {
    box-sizing: border-box;
}

/* Melhorias de touch para mobile */
@media (max-width: 768px) {
    button, a, .clickable {
        min-height: 44px;
        min-width: 44px;
    }
    
    /* Ajustes de scroll horizontal */
    .container-fluid {
        overflow-x: hidden;
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
    
    /* Fontes responsivas */
    .display-5 {
        font-size: 1.5rem !important;
    }
    
    h1 {
        font-size: 1.4rem !important;
    }
    
    h5 {
        font-size: 1rem !important;
    }
}

/* Layout flexível para cards */
.stat-card, .modern-card, .quick-action-card {
    width: 100%;
    box-sizing: border-box;
}

/* Grid responsivo melhorado */
.row.g-4 {
    --bs-gutter-x: 1rem;
    margin-right: calc(-.5 * var(--bs-gutter-x));
    margin-left: calc(-.5 * var(--bs-gutter-x));
}

.row.g-4 > * {
    padding-right: calc(var(--bs-gutter-x) * .5);
    padding-left: calc(var(--bs-gutter-x) * .5);
}

/* Responsividade das tabelas */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.custom-table {
    min-width: 600px;
}

@media (max-width: 768px) {
    .custom-table {
        min-width: 500px;
    }
    
    .custom-table th, 
    .custom-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.85rem;
    }
}

/* Ajustes de cards no mobile */
@media (max-width: 768px) {
    .stat-card {
        padding: 1rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .quick-action-card {
        padding: 0.75rem 0.5rem;
    }
    
    .quick-action-icon {
        width: 40px;
        height: 40px;
    }
    
    .quick-action-icon i {
        font-size: 1.2rem !important;
    }
    
    .quick-action-card h6 {
        font-size: 0.8rem;
    }
    
    .quick-action-card small {
        font-size: 0.7rem;
    }
    
    /* Ajuste do badge de trimestre e presença */
    .badge-trimestre, .badge-presenca {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
    }
}

/* Tablet adjustments */
@media (min-width: 769px) and (max-width: 1024px) {
    .container-fluid {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
    
    .quick-action-card {
        padding: 1rem;
    }
}

/* Desktop */
@media (min-width: 1025px) {
    .container-fluid {
        padding-left: 24px !important;
        padding-right: 24px !important;
        max-width: 1400px;
        margin: 0 auto;
    }
}

/* Ajustes do layout de saudação */
@media (max-width: 768px) {
    .welcome-icon {
        width: 48px;
        height: 48px;
    }
    
    .welcome-icon i {
        font-size: 1.2rem;
    }
    
    .welcome-card {
        padding: 0.75rem 1rem;
    }
    
    .welcome-avatar {
        width: 40px;
        height: 40px;
    }
    
    .welcome-avatar i {
        font-size: 1.5rem;
    }
    
    .text-lg-end {
        text-align: left !important;
        margin-top: 0.75rem !important;
    }
}

/* Cards de aniversariantes responsivos */
@media (max-width: 768px) {
    .birthday-item {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
    
    .birthday-day-box {
        width: 36px;
        height: 36px;
        font-size: 0.9rem;
    }
    
    .birthday-item .d-flex {
        width: 100%;
    }
    
    .birthday-item .badge-ebd {
        align-self: flex-start;
        margin-left: 52px;
    }
}

/* Cards de ofertas responsivos */
@media (max-width: 768px) {
    .offer-icon {
        width: 50px;
        height: 50px;
    }
    
    .offer-icon i {
        font-size: 1.5rem;
    }
    
    .offer-stats h3 {
        font-size: 1.2rem;
    }
    
    .offer-average {
        padding: 0.75rem;
    }
    
    .offer-average h4 {
        font-size: 1.1rem;
    }
}

/* Progress bars responsivas */
.progress {
    max-width: 100%;
}

/* Ajuste do texto de frequência */
.stat-trend {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .stat-trend {
        font-size: 0.65rem;
    }
}

/* Evitar overflow de texto */
.text-truncate-mobile {
    white-space: normal;
    word-break: break-word;
}

@media (max-width: 768px) {
    .custom-table td,
    .custom-table th {
        white-space: normal;
        word-break: break-word;
    }
}

/* Melhorias de espaçamento mobile */
@media (max-width: 768px) {
    .mb-4 {
        margin-bottom: 1rem !important;
    }
    
    .g-4 {
        --bs-gutter-y: 0.75rem;
    }
    
    .card-body.p-4 {
        padding: 1rem !important;
    }
    
    .p-3 {
        padding: 0.75rem !important;
    }
}

/* Scroll suave para elementos com overflow */
.table-responsive::-webkit-scrollbar {
    height: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

/* Suporte para telas muito pequenas */
@media (max-width: 480px) {
    .col-6 {
        flex: 0 0 auto;
        width: 100% !important;
    }
    
    .row.g-4 .col-6 {
        margin-bottom: 0.75rem;
    }
    
    .quick-action-card {
        text-align: center;
    }
    
    /* Empilhar cards de ação rápida em telas muito pequenas */
    .row.g-3 .col-6 {
        width: 100%;
    }
}

/* Landscape mode no mobile */
@media (max-width: 768px) and (orientation: landscape) {
    .row.g-4 .col-6 {
        flex: 0 0 auto;
        width: 50% !important;
    }
    
    .container-fluid {
        padding-bottom: 20px;
    }
}

/* Ajustes do footer para mobile */
@media (max-width: 768px) {
    .system-info {
        font-size: 0.7rem;
        padding: 0.5rem;
    }
}

/* Melhorias de acessibilidade e toque */
.btn, 
.quick-action-card,
.stat-card,
.birthday-item {
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-action-card:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }
}

/* Container principal com padding responsivo */
.main-content {
    padding-bottom: 70px;
}

@media (max-width: 768px) {
    .main-content {
        padding-bottom: 50px;
    }
}
</style>

<!-- Conteúdo principal -->
<div class="container-fluid px-3 px-md-4 main-content">

    <!-- Saudação Moderna -->
    <div class="row align-items-center mb-3 mb-md-4" data-aos="fade-down">
        <div class="col-12 col-lg-8">
            <div class="d-flex align-items-center gap-2 gap-md-3 mb-2">
                <div class="welcome-icon flex-shrink-0">
                    <i class="fas fa-<?= $icone ?> fa-lg fa-md-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h1 class="display-5 fw-bold mb-0" style="color: var(--gray-800); font-size: clamp(1.2rem, 5vw, 2rem);">
                        <?= $saudacao ?>, <?= htmlspecialchars(explode(' ', $usuario_nome)[0]) ?>!
                    </h1>
                    <p class="text-muted mb-0 mt-1 small small-md-normal">
                        <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y') ?>
                        <span class="mx-1 mx-md-2 d-none d-sm-inline">&bull;</span>
                        <br class="d-sm-none">
                        <i class="fas fa-clock me-1"></i><?= date('H:i') ?>
                        <span class="mx-1 mx-md-2 d-none d-sm-inline">&bull;</span>
                        <br class="d-sm-none">
                        <i class="fas fa-church me-1"></i> <span class="d-none d-sm-inline">Escola Bíblica Dominical</span>
                        <span class="d-inline d-sm-none">EBD</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 text-lg-end mt-2 mt-lg-0">
            <div class="welcome-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <small class="text-white-50 d-none d-sm-block">Bem-vindo,</small>
                        <div class="fw-bold text-white fs-6 fs-md-5"><?= htmlspecialchars($usuario_nome) ?></div>
                        <small class="text-white-50 small">
                            <i class="fas fa-user-tag me-1"></i><?= ucfirst($usuario_perfil) ?>
                        </small>
                    </div>
                    <div class="welcome-avatar flex-shrink-0 ms-2">
                        <i class="fas fa-user-circle fa-xl fa-md-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Modernos - Responsivo 2x2 no mobile -->
    <div class="row g-3 g-md-4 mb-3 mb-md-4">
        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card h-100">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mb-2">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value fs-2 fs-md-1 fw-bold"><?= number_format($totalAlunos, 0, ',', '.') ?></div>
                <div class="stat-label text-muted small">Total de Alunos</div>
                <div class="stat-trend text-success small mt-1">
                    <i class="fas fa-arrow-up me-1"></i> Ativos
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="150">
            <div class="stat-card h-100">
                <div class="stat-icon bg-success bg-opacity-10 text-success mb-2">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div class="stat-value fs-2 fs-md-1 fw-bold"><?= number_format($totalClasses, 0, ',', '.') ?></div>
                <div class="stat-label text-muted small">Classes Ativas</div>
                <div class="stat-trend text-muted small mt-1">
                    <i class="fas fa-church me-1"></i> Congregações
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card h-100">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning mb-2">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value fs-2 fs-md-1 fw-bold"><?= number_format($chamadasHoje, 0, ',', '.') ?></div>
                <div class="stat-label text-muted small">Chamadas Hoje</div>
                <div class="stat-trend text-info small mt-1">
                    <i class="fas fa-calendar-alt me-1"></i> Mês: <?= $chamadasMes ?>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="250">
            <div class="stat-card h-100">
                <div class="stat-icon bg-info bg-opacity-10 text-info mb-2">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value fs-2 fs-md-1 fw-bold <?= $frequenciaMedia < 50 ? 'text-danger' : ($frequenciaMedia < 75 ? 'text-warning' : 'text-success') ?>">
                    <?= number_format($frequenciaMedia, 1, ',', '.') ?>%
                </div>
                <div class="stat-label text-muted small">Frequência Média</div>
                <?php if ($frequenciaMedia > 0): ?>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-<?= $frequenciaMedia < 50 ? 'danger' : ($frequenciaMedia < 75 ? 'warning' : 'success') ?>"
                         style="width: <?= min($frequenciaMedia, 100) ?>%"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas - Grid responsivo -->
    <div class="modern-card mb-3 mb-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary p-3 p-md-4">
            <h5 class="mb-0 text-white fs-6 fs-md-5">
                <i class="fas fa-bolt me-2"></i> Ações Rápidas
            </h5>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-2 g-md-3">
                <?php
                $acoes = [
                    ['href' => BASE_URL . '/views/chamadas/index.php',   'icon' => 'book-open',  'color' => 'primary', 'label' => 'Nova Chamada', 'desc' => 'Registrar presenças'],
                    ['href' => BASE_URL . '/views/matriculas/index.php', 'icon' => 'user-plus',  'color' => 'success', 'label' => 'Nova Matrícula', 'desc' => 'Matricular aluno'],
                    ['href' => BASE_URL . '/views/alunos/index.php',     'icon' => 'users',      'color' => 'info',    'label' => 'Alunos', 'desc' => 'Gerenciar alunos'],
                    ['href' => BASE_URL . '/views/relatorios/index.php', 'icon' => 'chart-bar',  'color' => 'warning', 'label' => 'Relatórios', 'desc' => 'Analisar dados'],
                    ['href' => BASE_URL . '/views/classes/index.php',    'icon' => 'chalkboard', 'color' => 'danger',  'label' => 'Classes', 'desc' => 'Gerenciar classes'],
                ];
                foreach ($acoes as $acao): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $acao['href'] ?>" class="text-decoration-none d-block">
                        <div class="quick-action-card text-center h-100">
                            <div class="quick-action-icon bg-<?= $acao['color'] ?> bg-opacity-10 mx-auto mb-2">
                                <i class="fas fa-<?= $acao['icon'] ?> fa-lg fa-md-2x text-<?= $acao['color'] ?>"></i>
                            </div>
                            <h6 class="fw-semibold mb-0 mt-2 small small-md-normal"><?= $acao['label'] ?></h6>
                            <small class="text-muted d-none d-sm-block"><?= $acao['desc'] ?></small>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Últimas Chamadas - com scroll horizontal em mobile -->
    <div class="modern-card mb-3 mb-md-4" data-aos="fade-up" data-aos-delay="350">
        <div class="card-header-modern bg-success p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white fs-6 fs-md-5">
                <i class="fas fa-clock me-2"></i> Últimas Chamadas
            </h5>
            <a href="<?= BASE_URL ?>/views/chamadas/listar.php" class="btn btn-sm btn-light">
                Ver todas <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ultimasChamadas)): ?>
                <div class="text-center py-4 py-md-5">
                    <i class="fas fa-clipboard-list fa-2x fa-md-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0 small">Nenhuma chamada registrada ainda.</p>
                    <small class="text-muted small">Inicie uma chamada clicando em "Nova Chamada"</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="custom-table mb-0">
                        <thead>
                            <tr>
                                <th class="small">Data</th>
                                <th class="small">Classe</th>
                                <th class="small">Presença</th>
                                <th class="text-center small">Visit.</th>
                                <th class="text-end small">Oferta</th>
                                <th class="text-center small">Trim.</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimasChamadas as $ch):
                            $tot  = (int)($ch['total_alunos'] ?? 0);
                            $pres = (int)($ch['presentes']    ?? 0);
                            $pct  = $tot > 0 ? round($pres / $tot * 100, 1) : 0;
                            
                            $pctCor = '';
                            if ($pct >= 75) {
                                $pctCor = 'text-success';
                            } elseif ($pct >= 50) {
                                $pctCor = 'text-warning';
                            } else {
                                $pctCor = 'text-danger';
                            }
                        ?>
                        <tr>
                            <td class="align-middle small">
                                <i class="fas fa-calendar-alt me-1 me-md-2 text-primary"></i>
                                <?= $ch['data_formatada'] ?>
                            </td>
                            <td class="align-middle small">
                                <i class="fas fa-chalkboard-user me-1 me-md-2 text-success"></i>
                                <?= htmlspecialchars($ch['classe_nome']) ?>
                            </td>
                            <td class="align-middle">
                                <span class="fw-semibold small"><?= $pres ?>/<?= $tot ?></span>
                                <span class="ms-1 ms-md-2 small <?= $pctCor ?>">(<?= $pct ?>%)</span>
                                <div class="progress mt-1 d-none d-md-flex" style="height: 3px; width: 100px;">
                                    <div class="progress-bar bg-<?= $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') ?>"
                                         style="width: <?= $pct ?>%"></div>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge-presenca"><?= (int)($ch['total_visitantes'] ?? 0) ?></span>
                            </td>
                            <td class="text-end align-middle fw-bold text-success small">
                                R$ <?= number_format($ch['oferta_classe'] ?? 0, 2, ',', '.') ?>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge-trimestre"><?= htmlspecialchars($ch['trimestre']) ?></span>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Aniversariantes + Ofertas - Layout responsivo empilhável -->
    <div class="row g-3 g-md-4 mb-3 mb-md-4">
        <!-- Aniversariantes do Mês -->
        <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="modern-card h-100 d-flex flex-column">
                <div class="card-header-modern p-3 p-md-4" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                    <h5 class="mb-0 text-white fs-6 fs-md-5">
                        <i class="fas fa-birthday-cake me-2"></i> Aniversariantes do Mês
                    </h5>
                </div>
                <div class="card-body p-0 flex-grow-1">
                    <?php if (empty($aniversariantes)): ?>
                        <div class="text-center py-4 py-md-5">
                            <i class="fas fa-cake-candles fa-2x fa-md-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                            <p class="text-muted mb-0 small">Nenhum aniversariante este mês</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($aniversariantes as $ani): ?>
                        <div class="birthday-item d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div class="d-flex align-items-center gap-2 gap-md-3 flex-grow-1">
                                <div class="birthday-day-box flex-shrink-0">
                                    <?= (int)$ani['dia'] ?>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="d-block small small-md-normal"><?= htmlspecialchars($ani['nome']) ?></strong>
                                    <small class="text-muted d-none d-sm-block">
                                        <i class="fas fa-chalkboard-user me-1"></i><?= htmlspecialchars($ani['classe_nome']) ?>
                                        &bull; <i class="fas fa-cake me-1"></i><?= (int)$ani['idade'] ?> anos
                                    </small>
                                    <small class="text-muted d-block d-sm-none">
                                        <i class="fas fa-chalkboard-user me-1"></i><?= htmlspecialchars($ani['classe_nome']) ?>
                                    </small>
                                </div>
                            </div>
                            <span class="badge-ebd badge-primary rounded-pill flex-shrink-0 ms-2 small">
                                <i class="fas fa-gift me-1"></i> Dia <?= (int)$ani['dia'] ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ofertas -->
        <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="450">
            <div class="modern-card h-100 d-flex flex-column">
                <div class="card-header-modern p-3 p-md-4" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                    <h5 class="mb-0 text-white fs-6 fs-md-5">
                        <i class="fas fa-money-bill-wave me-2"></i> Ofertas (Últimos 30 dias)
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4 text-center flex-grow-1">
                    <div class="row g-3 g-md-4">
                        <div class="col-12 col-sm-6">
                            <div class="offer-stats">
                                <div class="offer-icon bg-success bg-opacity-10 mx-auto">
                                    <i class="fas fa-dollar-sign fa-xl fa-md-2x text-success"></i>
                                </div>
                                <h3 class="text-success mt-2 mb-0 fs-4 fs-md-3">
                                    R$ <?= number_format($ofertas['total_ofertas'], 2, ',', '.') ?>
                                </h3>
                                <p class="text-muted small mb-0">Total arrecadado</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="offer-stats">
                                <div class="offer-icon bg-primary bg-opacity-10 mx-auto">
                                    <i class="fas fa-calendar-week fa-xl fa-md-2x text-primary"></i>
                                </div>
                                <h3 class="text-primary mt-2 mb-0 fs-4 fs-md-3"><?= (int)$ofertas['domingos'] ?></h3>
                                <p class="text-muted small mb-0">Domingos com oferta</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3 my-md-4">
                    <div class="offer-average">
                        <h6 class="text-muted mb-2 small">Média por Domingo</h6>
                        <h4 class="text-primary fw-bold fs-5 fs-md-4">
                            R$ <?= number_format($ofertas['media_oferta'], 2, ',', '.') ?>
                        </h4>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 75%;"></div>
                        </div>
                        <small class="text-muted mt-2 d-block small">Meta: 75% da capacidade</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Versão do Sistema -->
    <div class="row" data-aos="fade-up" data-aos-delay="500">
        <div class="col-12 mb-3 mb-md-4">
            <div class="system-info text-center small">
                <i class="fas fa-database me-1"></i> Versão 3.0
                <span class="mx-1 mx-md-2 d-none d-sm-inline">&bull;</span>
                <br class="d-sm-none">
                <i class="fas fa-calendar-week me-1"></i> Sistema EBD
                <span class="mx-1 mx-md-2 d-none d-sm-inline">&bull;</span>
                <br class="d-sm-none">
                <i class="fas fa-heart text-danger me-1"></i> Escola Bíblica Dominical
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos do Dashboard */
.welcome-icon {
    width: clamp(48px, 8vw, 64px);
    height: clamp(48px, 8vw, 64px);
    background: linear-gradient(135deg, var(--primary-100) 0%, var(--primary-50) 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-600);
}

.welcome-card {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    border-radius: 20px;
    padding: clamp(0.75rem, 2vw, 1rem) clamp(1rem, 3vw, 1.5rem);
    box-shadow: var(--shadow-md);
}

.welcome-avatar {
    width: clamp(40px, 7vw, 48px);
    height: clamp(40px, 7vw, 48px);
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* Cards de ação rápida */
.quick-action-card {
    background: white;
    border-radius: 16px;
    padding: clamp(0.75rem, 2vw, 1.25rem);
    transition: all 0.3s ease;
    border: 1px solid var(--gray-200);
}

.quick-action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.quick-action-icon {
    width: clamp(45px, 8vw, 56px);
    height: clamp(45px, 8vw, 56px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Badge de trimestre */
.badge-trimestre {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
    padding: clamp(0.2rem, 0.5vw, 0.25rem) clamp(0.5rem, 1.5vw, 0.75rem);
    border-radius: 20px;
    font-size: clamp(0.65rem, 2vw, 0.75rem);
    font-weight: 600;
    display: inline-block;
}

.badge-presenca {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    padding: clamp(0.2rem, 0.5vw, 0.25rem) clamp(0.4rem, 1vw, 0.6rem);
    border-radius: 20px;
    font-size: clamp(0.65rem, 2vw, 0.75rem);
    font-weight: 600;
    display: inline-block;
}

/* Aniversariante item */
.birthday-item {
    transition: background-color 0.2s ease;
}

.birthday-item:hover {
    background-color: var(--gray-50);
}

.birthday-day-box {
    width: clamp(36px, 7vw, 48px);
    height: clamp(36px, 7vw, 48px);
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: clamp(0.9rem, 3vw, 1.2rem);
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
}

/* Estatísticas de ofertas */
.offer-stats {
    text-align: center;
}

.offer-icon {
    width: clamp(50px, 10vw, 60px);
    height: clamp(50px, 10vw, 60px);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.offer-average {
    background: var(--gray-50);
    border-radius: 16px;
    padding: clamp(0.75rem, 2vw, 1rem);
}

/* Info do sistema */
.system-info {
    background: var(--gray-100);
    border-radius: 12px;
    padding: clamp(0.5rem, 1.5vw, 0.75rem);
    color: var(--gray-600);
}

/* Classes utilitárias responsivas */
@media (max-width: 768px) {
    .fs-md-1 { font-size: 1.5rem !important; }
    .fs-md-2x { font-size: 1.5rem !important; }
    .fs-md-5 { font-size: 1rem !important; }
    .fa-md-2x { font-size: 1.25rem !important; }
    .small-md-normal { font-size: 0.875rem !important; }
}

@media (min-width: 769px) {
    .fs-md-1 { font-size: 2.5rem !important; }
    .fs-md-2x { font-size: 2rem !important; }
    .fs-md-5 { font-size: 1.25rem !important; }
    .fa-md-2x { font-size: 2rem !important; }
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: clamp(1rem, 2vw, 1.5rem);
    transition: all 0.3s ease;
    border: 1px solid var(--gray-200);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon {
    width: clamp(40px, 7vw, 48px);
    height: clamp(40px, 7vw, 48px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-value {
    font-size: clamp(1.3rem, 5vw, 2rem);
    font-weight: 700;
    margin: 0.5rem 0 0.25rem;
}
</style>

<script>
// Inicializar AOS
if (typeof AOS !== 'undefined') {
    AOS.init({
        duration: 600,
        once: true,
        offset: 50,
        disable: window.innerWidth < 768 ? 'phone' : false
    });
}

// Ajuste para landscape mode
window.addEventListener('resize', function() {
    if (typeof AOS !== 'undefined') {
        AOS.refresh();
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>