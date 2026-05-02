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

<!-- Conteúdo principal -->
<div class="container-fluid px-4">

    <!-- Saudação Moderna -->
    <div class="row align-items-center mb-4" data-aos="fade-down">
        <div class="col-12 col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="welcome-icon">
                    <i class="fas fa-<?= $icone ?> fa-2x"></i>
                </div>
                <div>
                    <h1 class="display-5 fw-bold mb-0" style="color: var(--gray-800);">
                        <?= $saudacao ?>, <?= htmlspecialchars(explode(' ', $usuario_nome)[0]) ?>!
                    </h1>
                    <p class="text-muted mb-0 mt-1">
                        <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y') ?>
                        <span class="mx-2">&bull;</span>
                        <i class="fas fa-clock me-1"></i><?= date('H:i') ?>
                        <span class="mx-2">&bull;</span>
                        <i class="fas fa-church me-1"></i> Escola Bíblica Dominical
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="welcome-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-white-50">Bem-vindo,</small>
                        <div class="fw-bold text-white fs-5"><?= htmlspecialchars($usuario_nome) ?></div>
                        <small class="text-white-50">
                            <i class="fas fa-user-tag me-1"></i><?= ucfirst($usuario_perfil) ?>
                        </small>
                    </div>
                    <div class="welcome-avatar">
                        <i class="fas fa-user-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Modernos -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value"><?= number_format($totalAlunos, 0, ',', '.') ?></div>
                <div class="stat-label">Total de Alunos</div>
                <div class="stat-trend text-success">
                    <i class="fas fa-arrow-up me-1"></i> Ativos
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="150">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div class="stat-value"><?= number_format($totalClasses, 0, ',', '.') ?></div>
                <div class="stat-label">Classes Ativas</div>
                <div class="stat-trend text-muted">
                    <i class="fas fa-church me-1"></i> Congregações
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value"><?= number_format($chamadasHoje, 0, ',', '.') ?></div>
                <div class="stat-label">Chamadas Hoje</div>
                <div class="stat-trend text-info">
                    <i class="fas fa-calendar-alt me-1"></i> Total no mês: <?= $chamadasMes ?>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="250">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value <?= $frequenciaMedia < 50 ? 'text-danger' : ($frequenciaMedia < 75 ? 'text-warning' : 'text-success') ?>">
                    <?= number_format($frequenciaMedia, 1, ',', '.') ?>%
                </div>
                <div class="stat-label">Frequência Média</div>
                <?php if ($frequenciaMedia > 0): ?>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-<?= $frequenciaMedia < 50 ? 'danger' : ($frequenciaMedia < 75 ? 'warning' : 'success') ?>"
                         style="width: <?= min($frequenciaMedia, 100) ?>%"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-bolt me-2"></i> Ações Rápidas
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
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
                    <a href="<?= $acao['href'] ?>" class="text-decoration-none">
                        <div class="quick-action-card text-center">
                            <div class="quick-action-icon bg-<?= $acao['color'] ?> bg-opacity-10">
                                <i class="fas fa-<?= $acao['icon'] ?> fa-2x text-<?= $acao['color'] ?>"></i>
                            </div>
                            <h6 class="fw-semibold mb-0 mt-2"><?= $acao['label'] ?></h6>
                            <small class="text-muted"><?= $acao['desc'] ?></small>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Últimas Chamadas -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="350">
        <div class="card-header-modern bg-success d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white">
                <i class="fas fa-clock me-2"></i> Últimas Chamadas
            </h5>
            <a href="<?= BASE_URL ?>/views/chamadas/listar.php" class="btn btn-sm btn-light">
                Ver todas <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ultimasChamadas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0">Nenhuma chamada registrada ainda.</p>
                    <small class="text-muted">Inicie uma chamada clicando em "Nova Chamada" nas ações rápidas</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Classe</th>
                                <th>Presença</th>
                                <th class="text-center">Visitantes</th>
                                <th class="text-end">Oferta</th>
                                <th class="text-center">Trimestre</th>
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
                            <td>
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                <?= $ch['data_formatada'] ?>
                            </td>
                            <td>
                                <i class="fas fa-chalkboard-user me-2 text-success"></i>
                                <?= htmlspecialchars($ch['classe_nome']) ?>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= $pres ?>/<?= $tot ?></span>
                                <span class="ms-2 small <?= $pctCor ?>">(<?= $pct ?>%)</span>
                                <div class="progress mt-1" style="height: 3px; width: 100px;">
                                    <div class="progress-bar bg-<?= $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') ?>"
                                         style="width: <?= $pct ?>%"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-presenca"><?= (int)($ch['total_visitantes'] ?? 0) ?></span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                R$ <?= number_format($ch['oferta_classe'] ?? 0, 2, ',', '.') ?>
                            </td>
                            <td class="text-center">
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

    <!-- Aniversariantes + Ofertas - Layout em dois cards -->
    <div class="row g-4 mb-4">
        <!-- Aniversariantes do Mês -->
        <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="modern-card h-100">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-birthday-cake me-2"></i> Aniversariantes do Mês
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($aniversariantes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-cake-candles fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                            <p class="text-muted mb-0">Nenhum aniversariante este mês</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($aniversariantes as $ani): ?>
                        <div class="birthday-item d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="birthday-day-box">
                                    <?= (int)$ani['dia'] ?>
                                </div>
                                <div>
                                    <strong class="d-block"><?= htmlspecialchars($ani['nome']) ?></strong>
                                    <small class="text-muted">
                                        <i class="fas fa-chalkboard-user me-1"></i><?= htmlspecialchars($ani['classe_nome']) ?>
                                        &bull; <i class="fas fa-cake me-1"></i><?= (int)$ani['idade'] ?> anos
                                    </small>
                                </div>
                            </div>
                            <span class="badge-ebd badge-primary rounded-pill">
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
            <div class="modern-card h-100">
                <div class="card-header-modern" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-money-bill-wave me-2"></i> Ofertas (Últimos 30 dias)
                    </h5>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="offer-stats">
                                <div class="offer-icon bg-success bg-opacity-10">
                                    <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                </div>
                                <h3 class="text-success mt-2 mb-0">
                                    R$ <?= number_format($ofertas['total_ofertas'], 2, ',', '.') ?>
                                </h3>
                                <p class="text-muted small">Total arrecadado</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="offer-stats">
                                <div class="offer-icon bg-primary bg-opacity-10">
                                    <i class="fas fa-calendar-week fa-2x text-primary"></i>
                                </div>
                                <h3 class="text-primary mt-2 mb-0"><?= (int)$ofertas['domingos'] ?></h3>
                                <p class="text-muted small">Domingos com oferta</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="offer-average">
                        <h6 class="text-muted mb-2">Média por Domingo</h6>
                        <h4 class="text-primary fw-bold">
                            R$ <?= number_format($ofertas['media_oferta'], 2, ',', '.') ?>
                        </h4>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 75%;"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Meta: 75% da capacidade</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Versão do Sistema -->
    <div class="row" data-aos="fade-up" data-aos-delay="500">
        <div class="col-12 mb-4">
            <div class="system-info text-center">
                <i class="fas fa-database me-1"></i> Versão 3.0
                <span class="mx-2">&bull;</span>
                <i class="fas fa-calendar-week me-1"></i> Sistema EBD
                <span class="mx-2">&bull;</span>
                <i class="fas fa-heart text-danger me-1"></i> Escola Bíblica Dominical
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos do Dashboard */
.welcome-icon {
    width: 64px;
    height: 64px;
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
    padding: 1rem 1.5rem;
    box-shadow: var(--shadow-md);
}

.welcome-avatar {
    width: 48px;
    height: 48px;
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
    padding: 1.25rem;
    transition: all 0.3s ease;
    border: 1px solid var(--gray-200);
}

.quick-action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-200);
}

.quick-action-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Badge de trimestre */
.badge-trimestre {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-presenca {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
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
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
}

/* Estatísticas de ofertas */
.offer-stats {
    text-align: center;
}

.offer-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.offer-average {
    background: var(--gray-50);
    border-radius: 16px;
    padding: 1rem;
}

/* Info do sistema */
.system-info {
    background: var(--gray-100);
    border-radius: 12px;
    padding: 0.75rem;
    color: var(--gray-600);
    font-size: 0.8rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .welcome-icon {
        width: 50px;
        height: 50px;
    }
    
    .welcome-icon i {
        font-size: 1.5rem;
    }
    
    .quick-action-card {
        padding: 0.75rem;
    }
    
    .quick-action-icon {
        width: 45px;
        height: 45px;
    }
    
    .quick-action-icon i {
        font-size: 1.25rem;
    }
    
    .birthday-day-box {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

<script>
// Inicializar AOS
AOS.init({
    duration: 600,
    once: true,
    offset: 50
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>