<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Iniciar sessão APENAS se não estiver ativa
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// ✅ Carregar configurações e funções
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../auth/valida_sessao.php';

// Carrega funções gerais
if (file_exists(__DIR__ . '/../functions/funcoes_chamadas.php')) {
    require_once __DIR__ . '/../functions/funcoes_chamadas.php';
}

// Carrega funções do dashboard
if (file_exists(__DIR__ . '/../functions/funcoes_dashboard.php')) {
    require_once __DIR__ . '/../functions/funcoes_dashboard.php';
} else {
    die("Erro crítico: Arquivo funcoes_dashboard.php não encontrado.");
}

// ✅ Configurações para o header
$pageTitle = 'Dashboard';
$pageName = 'Dashboard';
$hideBreadcrumbs = true;

// ✅ Buscar estatísticas REAIS
$estatisticas = [
    'total_alunos' => 0,
    'total_classes' => 0,
    'chamadas_hoje' => 0,
    'frequencia_media' => 0,
    'ultima_chamada' => null,
    'total_chamadas_mes' => 0
];

if (function_exists('obterEstatisticasChamadasMensais')) {
    $estatisticas = obterEstatisticasChamadasMensais($pdo);
}

// ✅ Buscar últimas atividades REAIS
$ultimasAtividades = [];
if (function_exists('obterUltimasAtividades')) {
    $ultimasAtividades = obterUltimasAtividades($pdo, 5);
}

// ✅ Registrar acesso ao dashboard
if (function_exists('registrarAtividade')) {
    registrarAtividade($pdo, $_SESSION['usuario_id'] ?? null, 'view_dashboard', 'Visualizou o dashboard');
}

// ✅ Carregar header e navbar
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<!-- ========== CONTEÚDO PRINCIPAL ========== -->
<main class="container-fluid px-3 px-md-4">
    <br>
    
    <!-- Hero Section -->
    <section class="hero-section mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 fw-bold mb-2">
                        👋 Bem-vindo, <?= htmlspecialchars(explode(' ', $_SESSION['nome'] ?? 'Usuário')[0]) ?>!
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar-check me-1"></i>
                        <?= date('l, d \d\e F \d\e Y') ?>
                    </p>
                    <?php if(isset($estatisticas['ultima_chamada']) && $estatisticas['ultima_chamada']): ?>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-history"></i> Última chamada: <?= formatarDataBrasil($estatisticas['ultima_chamada']) ?>
                    </small>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-lg bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px;">
                                    <?= strtoupper(substr($_SESSION['nome'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="text-start">
                                    <div class="fw-semibold small"><?= htmlspecialchars($_SESSION['usuario_perfil'] ?? 'Usuário') ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 150px;">
                                        <?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Carrossel de Avisos -->
    <section class="mb-4">
        <div class="container">
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
                            <span><strong>Atenção:</strong> Sistema em fase de testes - Versão Beta</span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="alert alert-success mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle fa-lg"></i>
                            <span>Novo material disponível na seção de <strong>Relatórios</strong></span>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                            <span>Registre as chamadas até <strong>domingo à noite</strong>!</span>
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
    </section>

    <!-- Cards de Estatísticas -->
    <section class="mb-4">
        <div class="container">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Total de alunos ativos no sistema">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted small mb-1">Total de Alunos</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($estatisticas['total_alunos'] ?? 0, 0, ',', '.') ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-user-graduate text-primary"></i>
                                </div>
                            </div>
                            <?php if(($estatisticas['total_alunos'] ?? 0) == 0): ?>
                            <small class="text-muted mt-2 d-block">
                                <a href="../views/alunos/index.php" class="text-decoration-none">+ Adicionar aluno</a>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Classes ativas no sistema">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted small mb-1">Classes Ativas</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($estatisticas['total_classes'] ?? 0, 0, ',', '.') ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-chalkboard text-success"></i>
                                </div>
                            </div>
                            <?php if(($estatisticas['total_classes'] ?? 0) == 0): ?>
                            <small class="text-muted mt-2 d-block">
                                <a href="../views/classes/index.php" class="text-decoration-none">+ Adicionar classe</a>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Chamadas realizadas hoje">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted small mb-1">Chamadas Hoje</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($estatisticas['chamadas_hoje'] ?? 0, 0, ',', '.') ?></h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-clipboard-list text-warning"></i>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-chart-line"></i> Mês: <?= $estatisticas['total_chamadas_mes'] ?? 0 ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm" data-bs-toggle="tooltip" title="Média de frequência dos últimos 30 dias">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted small mb-1">Frequência Média</p>
                                    <h3 class="fw-bold mb-0 <?= ($estatisticas['frequencia_media'] ?? 0) < 50 ? 'text-danger' : (($estatisticas['frequencia_media'] ?? 0) < 75 ? 'text-warning' : 'text-success') ?>">
                                        <?= number_format($estatisticas['frequencia_media'] ?? 0, 1, ',', '.') ?>%
                                    </h3>
                                </div>
                                <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-chart-line text-info"></i>
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
        </div>
    </section>

    <!-- Ações Rápidas -->
    <section class="mb-4">
        <div class="container">
            <h5 class="fw-semibold mb-3">Ações Rápidas</h5>
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="chamadas/index.php" class="card h-100 border-0 shadow-sm text-decoration-none action-card">
                        <div class="card-body text-center py-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width:56px;height:56px;">
                                <i class="fas fa-book-open text-primary fa-lg"></i>
                            </div>
                            <h6 class="fw-medium mb-0">Nova Chamada</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="chamadas/listar.php" class="card h-100 border-0 shadow-sm text-decoration-none action-card">
                        <div class="card-body text-center py-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width:56px;height:56px;">
                                <i class="fas fa-edit text-warning fa-lg"></i>
                            </div>
                            <h6 class="fw-medium mb-0">Editar Chamada</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="sorteios.php" class="card h-100 border-0 shadow-sm text-decoration-none action-card">
                        <div class="card-body text-center py-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width:56px;height:56px;">
                                <i class="fas fa-dice text-success fa-lg"></i>
                            </div>
                            <h6 class="fw-medium mb-0">Sorteios</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="alunos/index.php" class="card h-100 border-0 shadow-sm text-decoration-none action-card">
                        <div class="card-body text-center py-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width:56px;height:56px;">
                                <i class="fas fa-user-graduate text-info fa-lg"></i>
                            </div>
                            <h6 class="fw-medium mb-0">Alunos</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="classes/index.php" class="card h-100 border-0 shadow-sm text-decoration-none action-card">
                        <div class="card-body text-center py-3">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width:56px;height:56px;">
                                <i class="fas fa-chalkboard text-secondary fa-lg"></i>
                            </div>
                            <h6 class="fw-medium mb-0">Classes</h6>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="relatorios/index.php" class="card h-100 border-0 shadow-sm text-decoration-none action-card">
                        <div class="card-body text-center py-3">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3 mb-2 mx-auto" style="width:56px;height:56px;">
                                <i class="fas fa-chart-bar text-danger fa-lg"></i>
                            </div>
                            <h6 class="fw-medium mb-0">Relatórios</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Últimas Chamadas e Atividades -->
    <section>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-semibold mb-0">Últimas Chamadas</h5>
                                <a href="chamadas/listar.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <?php 
                            if (function_exists('exibirUltimasChamadasPorClasse')) {
                                exibirUltimasChamadasPorClasse($pdo);
                            } else {
                                echo '<div class="alert alert-warning">Função de exibição de chamadas não disponível.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pb-0">
                            <h5 class="fw-semibold mb-0">Atividades Recentes</h5>
                        </div>
                        <div class="card-body pt-2">
                            <div class="list-group list-group-flush">
                                <?php if (!empty($ultimasAtividades)): ?>
                                    <?php foreach($ultimasAtividades as $atividade): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex gap-3">
                                            <div class="bg-<?= $atividade['cor'] ?? 'primary' ?> bg-opacity-10 rounded-circle p-2" 
                                                 style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-<?= $atividade['icone'] ?? 'clock' ?> text-<?= $atividade['cor'] ?? 'primary' ?> small"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium small"><?= htmlspecialchars($atividade['descricao'] ?? 'Atividade') ?></div>
                                                <div class="text-muted small">
                                                    <?= timeAgo($atividade['data_hora'] ?? $atividade['data'] ?? date('Y-m-d H:i:s')) ?>
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
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Nenhuma atividade recente</p>
                                        <small class="text-muted">Atividades aparecerão aqui automaticamente</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- CSS Adicional -->
<style>
    .action-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    .avatar-lg {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 2px;
    }
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?php 
// ✅ Footer
require_once __DIR__ . '/../includes/footer.php'; 
?>