<?php 
// views/relatorios/index.php
// Página inicial da central de relatórios

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Relatórios E.B.D';
require_once __DIR__ . '/../../includes/header.php'; 
?>

<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="mb-4" data-aos="fade-down">
        <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
            <i class="fas fa-chart-line me-3" style="color: var(--primary-600);"></i>
            Central de Relatórios E.B.D
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-chart-line me-1"></i> Relatórios
                </li>
            </ol>
        </nav>
        <p class="text-muted mt-2 mb-0">
            <i class="fas fa-info-circle me-1"></i>
            Acesse relatórios estatísticos, análises de frequência e gestão da Escola Bíblica Dominical
        </p>
    </div>

    <!-- Seção 1: Relatórios de Frequência -->
    <div class="mb-4" data-aos="fade-up">
        <h3 class="section-title">
            <i class="fas fa-calendar-check me-2" style="color: var(--primary-500);"></i>
            Relatórios de Frequência
        </h3>
        <p class="text-muted mb-3">Acompanhe a assiduidade dos alunos e classes</p>
    </div>

    <div class="row g-4 mb-5" data-aos="fade-up" data-aos-delay="100">
        <!-- Relatório Consolidado -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="relatorio_consolidado.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-primary">
                        <i class="fas fa-chart-pie fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Consolidado de Classes</h5>
                        <p class="report-description">Visão geral por classe: matrículas, presenças, ofertas</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Presenças por Aluno -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="presencas_aluno.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-danger">
                        <i class="fas fa-user-check fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Presenças por Aluno</h5>
                        <p class="report-description">Análise individual de frequência por período</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Frequência de Alunos -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="frequencia_alunos.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-warning">
                        <i class="fas fa-chart-line fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Frequência de Alunos</h5>
                        <p class="report-description">Resumo consolidado de presenças/ausências</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Seção 2: Relatórios de Gestão -->
    <div class="mb-4" data-aos="fade-up" data-aos-delay="150">
        <h3 class="section-title">
            <i class="fas fa-chart-simple me-2" style="color: var(--success);"></i>
            Relatórios de Gestão
        </h3>
        <p class="text-muted mb-3">Gerencie alunos, aniversariantes e estatísticas da EBD</p>
    </div>

    <div class="row g-4 mb-5" data-aos="fade-up" data-aos-delay="200">
        <!-- Aniversariantes -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="aniversariantes.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-success">
                        <i class="fas fa-birthday-cake fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Aniversariantes</h5>
                        <p class="report-description">Aniversariantes do mês por classe</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório Geral -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="relatorio_geral.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-secondary">
                        <i class="fas fa-chart-bar fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Relatório Geral</h5>
                        <p class="report-description">Visão completa com todos os indicadores</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório por Período -->
        <div class="col-12 col-md-6 col-lg-4">
            <a href="presencas_aluno.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-info">
                        <i class="fas fa-calendar-alt fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Por Período</h5>
                        <p class="report-description">Análise temporal com filtro de trimestre/datas</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Seção 3: Relatórios Financeiros -->
    <div class="mb-4" data-aos="fade-up" data-aos-delay="250">
        <h3 class="section-title">
            <i class="fas fa-dollar-sign me-2" style="color: var(--warning);"></i>
            Relatórios Financeiros
        </h3>
        <p class="text-muted mb-3">Acompanhamento de ofertas e recursos da EBD</p>
    </div>

    <div class="row g-4 mb-5" data-aos="fade-up" data-aos-delay="300">
        <!-- Ofertas por Classe -->
        <div class="col-12 col-md-6">
            <a href="relatorio_consolidado.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-warning">
                        <i class="fas fa-coins fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Ofertas por Classe</h5>
                        <p class="report-description">Consolidação de ofertas no relatório consolidado</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recursos por Classe -->
        <div class="col-12 col-md-6">
            <a href="relatorio_consolidado.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-info">
                        <i class="fas fa-book-open fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Recursos por Classe</h5>
                        <p class="report-description">Bíblias e revistas utilizadas por classe</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="row mt-5" data-aos="fade-up" data-aos-delay="350">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-chart-simple me-2"></i> Relatórios Disponíveis
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="stat-circle bg-primary bg-opacity-10">
                                <i class="fas fa-chart-pie fa-2x text-primary"></i>
                            </div>
                            <h4 class="mt-2 mb-0">4</h4>
                            <small class="text-muted">Relatórios de Frequência</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-circle bg-success bg-opacity-10">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                            <h4 class="mt-2 mb-0">3</h4>
                            <small class="text-muted">Relatórios de Gestão</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-circle bg-warning bg-opacity-10">
                                <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                            </div>
                            <h4 class="mt-2 mb-0">2</h4>
                            <small class="text-muted">Relatórios Financeiros</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-circle bg-info bg-opacity-10">
                                <i class="fas fa-calendar-alt fa-2x text-info"></i>
                            </div>
                            <h4 class="mt-2 mb-0">Períodos</h4>
                            <small class="text-muted">Trimestres/Datas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dica de Relatórios -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="400">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-lightbulb fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Dica:</strong>
                <span>Utilize os relatórios para acompanhar o desempenho das classes e identificar alunos com baixa frequência para acompanhamento pastoral. Os relatórios podem ser filtrados por período, classe e congregação.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para a página de relatórios */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-200);
    display: inline-block;
}

.report-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--gray-200);
    height: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.report-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.report-card:hover::before {
    transform: scaleX(1);
}

.report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: var(--primary-200);
}

.report-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.report-card:hover .report-icon {
    transform: scale(1.05);
}

.report-content {
    flex: 1;
}

.report-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.25rem;
}

.report-description {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.report-link {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--primary-600);
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
}

.report-card:hover .report-link {
    gap: 0.5rem;
    color: var(--primary-700);
}

.stat-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.stat-circle:hover {
    transform: scale(1.05);
}

.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

.btn-modern {
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.btn-modern:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
}

@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .report-card {
        padding: 1rem;
    }
    
    .report-icon {
        width: 50px;
        height: 50px;
    }
    
    .report-icon i {
        font-size: 1.5rem !important;
    }
    
    .report-title {
        font-size: 0.9rem;
    }
    
    .report-description {
        font-size: 0.7rem;
    }
    
    .stat-circle {
        width: 55px;
        height: 55px;
    }
    
    .stat-circle i {
        font-size: 1.5rem !important;
    }
    
    .section-title {
        font-size: 1.1rem;
    }
}

@media print {
    .navbar, .breadcrumb, .btn-modern, .alert-ebd, .stat-circle {
        display: none !important;
    }
    
    .modern-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>