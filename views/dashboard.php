<?php
/**
 * Dashboard Principal do Sistema
 * 
 * @package Escola\Views
 * @version 2.0
 */

// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar título da página
$pageTitle = 'Dashboard';

// Incluir header (já contém validação de sessão e funções)
require_once __DIR__ . '/../includes/header.php';

// Recupera dados do usuário logado (já disponíveis no header)
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nome = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_perfil = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
$congregacao_id = $_SESSION['congregacao_id'] ?? null;

// Carregar funções específicas do dashboard se não foram carregadas
if (!function_exists('obterKpisDashboard')) {
    require_once __DIR__ . '/../functions/funcoes_dashboard.php';
}

// Buscar estatísticas
$estatisticas = obterKpisDashboard($pdo);

// Buscar últimas atividades
$ultimasAtividades = obterUltimasAtividades($pdo, 6);

// Registrar acesso ao dashboard
if (function_exists('registrarAtividade')) {
    registrarAtividade($pdo, $usuario_id, 'view_dashboard', 'Visualizou o dashboard');
}

// Determinar saudação baseada na hora
$hora = (int)date('H');
if ($hora >= 5 && $hora < 12) {
    $saudacao = 'Bom dia';
    $iconeSaudacao = 'sun';
} elseif ($hora >= 12 && $hora < 18) {
    $saudacao = 'Boa tarde';
    $iconeSaudacao = 'sun';
} else {
    $saudacao = 'Boa noite';
    $iconeSaudacao = 'moon';
}
?>

<!-- Conteúdo principal do Dashboard -->
<div class="container-fluid px-3 px-md-4">
    
    <!-- Hero Section com Saudação -->
    <div class="row align-items-center mb-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                    <i class="fas fa-<?= $iconeSaudacao ?> fa-2x text-primary"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold mb-0">
                        <?= $saudacao ?>, <?= htmlspecialchars(explode(' ', $usuario_nome)[0]) ?>!
                    </h1>
                    <p class="text-muted mb-0 mt-1">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?= date('l, d \d\e F \d\e Y') ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-clock me-1"></i>
                        <?= date('H:i') ?>
                    </p>
                </div>
            </div>
            <?php if($estatisticas['ultima_chamada'] ?? false): ?>
            <p class="text-muted small mb-0">
                <i class="fas fa-history me-1"></i>
                Última chamada: <?= formatarDataBrasil($estatisticas['ultima_chamada']) ?>
            </p>
            <?php endif; ?>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="card border-0 shadow-sm bg-gradient-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-white-50">Bem-vindo,</small>
                            <div class="fw-semibold text-white"><?= htmlspecialchars($usuario_nome) ?></div>
                            <small class="text-white-50"><?= ucfirst($usuario_perfil) ?></small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-user-circle fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrossel de Avisos -->
    <div class="row mb-4">
        <div class="col-12">
            <div id="carouselAvisos" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselAvisos" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#carouselAvisos" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#carouselAvisos" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner rounded-3">
                    <div class="carousel-item active">
                        <div class="alert alert-info mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle fa-lg"></i>
                            <span><strong>📢 Atenção:</strong> Sistema em fase de melhorias - Versão 3.0</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle fa-lg"></i>
                            <span><strong>✅ Novo recurso:</strong> Migração de matrículas entre trimestres disponível!</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                            <span><strong>⚠️ Importante:</strong> Registre as chamadas até domingo às 23:59!</span>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselAvisos" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselAvisos" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Total de alunos ativos no sistema">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase">Total de Alunos</p>
                            <h3 class="fw-bold mb-0"><?= number_format($estatisticas['total_alunos'] ?? 0, 0, ',', '.') ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-user-graduate fa-lg text-primary"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-chart-line me-1"></i>
                        Matriculados neste trimestre
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Classes ativas no sistema">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase">Classes Ativas</p>
                            <h3 class="fw-bold mb-0"><?= number_format($estatisticas['total_classes'] ?? 0, 0, ',', '.') ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-chalkboard fa-lg text-success"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-users me-1"></i>
                        Com chamadas registradas
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Chamadas realizadas hoje">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase">Chamadas Hoje</p>
                            <h3 class="fw-bold mb-0"><?= number_format($estatisticas['chamadas_hoje'] ?? 0, 0, ',', '.') ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clipboard-list fa-lg text-warning"></i>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Total no mês: <?= $estatisticas['total_chamadas_mes'] ?? 0 ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Média de frequência dos últimos 30 dias">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase">Frequência Média</p>
                            <h3 class="fw-bold mb-0 <?= ($estatisticas['frequencia_media'] ?? 0) < 50 ? 'text-danger' : (($estatisticas['frequencia_media'] ?? 0) < 75 ? 'text-warning' : 'text-success') ?>">
                                <?= number_format($estatisticas['frequencia_media'] ?? 0, 1, ',', '.') ?>%
                            </h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-chart-line fa-lg text-info"></i>
                        </div>
                    </div>
                    <?php if(($estatisticas['frequencia_media'] ?? 0) > 0): ?>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-<?= ($estatisticas['frequencia_media'] ?? 0) < 50 ? 'danger' : (($estatisticas['frequencia_media'] ?? 0) < 75 ? 'warning' : 'success') ?>" 
                             style="width: <?= $estatisticas['frequencia_media'] ?? 0 ?>%"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas (Menu de Atalhos) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i> Ações Rápidas
                    </h5>
                </div>
                <div class="card-body pt-2 pb-3">
                    <div class="row g-3">
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="chamada/index.php" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm action-card text-center">
                                    <div class="card-body py-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width: 56px; height: 56px;">
                                            <i class="fas fa-book-open fa-lg text-primary"></i>
                                        </div>
                                        <h6 class="fw-medium mb-0 small">Nova Chamada</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="chamada/listar.php" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm action-card text-center">
                                    <div class="card-body py-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width: 56px; height: 56px;">
                                            <i class="fas fa-list fa-lg text-success"></i>
                                        </div>
                                        <h6 class="fw-medium mb-0 small">Ver Chamadas</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="matricula/index.php" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm action-card text-center">
                                    <div class="card-body py-3">
                                        <div class="bg-info bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width: 56px; height: 56px;">
                                            <i class="fas fa-user-plus fa-lg text-info"></i>
                                        </div>
                                        <h6 class="fw-medium mb-0 small">Nova Matrícula</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="alunos/index.php" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm action-card text-center">
                                    <div class="card-body py-3">
                                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width: 56px; height: 56px;">
                                            <i class="fas fa-user-graduate fa-lg text-warning"></i>
                                        </div>
                                        <h6 class="fw-medium mb-0 small">Alunos</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="classes/index.php" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm action-card text-center">
                                    <div class="card-body py-3">
                                        <div class="bg-danger bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width: 56px; height: 56px;">
                                            <i class="fas fa-chalkboard fa-lg text-danger"></i>
                                        </div>
                                        <h6 class="fw-medium mb-0 small">Classes</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <a href="relatorios/index.php" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm action-card text-center">
                                    <div class="card-body py-3">
                                        <div class="bg-secondary bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width: 56px; height: 56px;">
                                            <i class="fas fa-chart-bar fa-lg text-secondary"></i>
                                        </div>
                                        <h6 class="fw-medium mb-0 small">Relatórios</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Chamadas e Atividades Recentes -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold mb-0">
                            <i class="fas fa-clock text-info me-2"></i> Últimas Chamadas
                        </h5>
                        <a href="chamada/listar.php" class="btn btn-sm btn-outline-primary">
                            Ver todas <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <?php if (function_exists('exibirUltimasChamadasDashboard')): ?>
                        <?php exibirUltimasChamadasDashboard($pdo, 5); ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard fa-2x mb-2 d-block"></i>
                            <p>Função de exibição de chamadas não disponível.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="fas fa-history text-success me-2"></i> Atividades Recentes
                    </h5>
                </div>
                <div class="card-body pt-2">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($ultimasAtividades)): ?>
                            <?php foreach($ultimasAtividades as $atividade): ?>
                            <div class="list-group-item px-0 py-2 border-0">
                                <div class="d-flex gap-3">
                                    <div class="bg-<?= $atividade['cor'] ?? 'primary' ?> bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" 
                                         style="width: 36px; height: 36px;">
                                        <i class="fas fa-<?= $atividade['icone'] ?? 'clock' ?> text-<?= $atividade['cor'] ?? 'primary' ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small"><?= htmlspecialchars($atividade['descricao'] ?? 'Atividade') ?></div>
                                        <div class="text-muted small">
                                            <?= timeAgo($atividade['data_hora'] ?? date('Y-m-d H:i:s')) ?>
                                            <?php if(isset($atividade['usuario_nome'])): ?>
                                                • por <?= htmlspecialchars($atividade['usuario_nome']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <p class="mb-0">Nenhuma atividade recente</p>
                                <small class="text-muted">Atividades aparecerão aqui automaticamente</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações do Sistema (Rodapé do Dashboard) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body py-2">
                    <div class="row text-center text-md-start">
                        <div class="col-md-4">
                            <small class="text-muted">
                                <i class="fas fa-database me-1"></i>
                                Versão do Sistema: 3.0
                            </small>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">
                                <i class="fas fa-code-branch me-1"></i>
                                Ambiente: Produção
                            </small>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">
                                <i class="fas fa-calendar-week me-1"></i>
                                Trimestre Atual: <?= formatarTrimestrePadrao(getTrimestreAtual()) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos específicos do Dashboard */
    .action-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .list-group-item {
        transition: background-color 0.2s ease;
    }
    .list-group-item:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .progress {
        border-radius: 2px;
        background-color: #e9ecef;
    }
    .carousel-inner .alert {
        border-radius: 12px;
        border: none;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0,0,0,0.3);
        border-radius: 50%;
        padding: 20px;
    }
</style>

<script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>