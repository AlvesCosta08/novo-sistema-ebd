<?php 
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Relatórios E.B.D';

// Incluir header padronizado
require_once __DIR__ . '/../../includes/header.php'; 
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="mb-4" data-aos="fade-down">
        <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
            <i class="fas fa-chart-line me-3" style="color: var(--primary-600);"></i>
            Relatórios E.B.D
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
            Acesse relatórios estatísticos e análises da Escola Bíblica Dominical
        </p>
    </div>

    <!-- Cards de Relatórios -->
    <div class="row g-4" data-aos="fade-up" data-aos-delay="100">
        <!-- Relatório Individual de Frequência -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="./relatorio_consolidado.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-primary">
                        <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Frequência Individual</h5>
                        <p class="report-description">Relatório consolidado de frequência por aluno</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório Geral -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="./relatorio_geral.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-danger">
                        <i class="fas fa-chart-bar fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Relatório Geral</h5>
                        <p class="report-description">Visão geral de faltas e presenças</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Aniversariantes do Mês -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="./aniversariantes.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-success">
                        <i class="fas fa-birthday-cake fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Aniversariantes</h5>
                        <p class="report-description">Aniversariantes do mês atual</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Faltas & Presenças -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="./frequencias_geral.php" class="text-decoration-none">
                <div class="report-card">
                    <div class="report-icon bg-warning">
                        <i class="fas fa-chart-pie fa-2x text-white"></i>
                    </div>
                    <div class="report-content">
                        <h5 class="report-title">Estatísticas Gerais</h5>
                        <p class="report-description">Total de faltas e presenças por classe</p>
                        <span class="report-link">Acessar <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Seção de Estatísticas Rápidas (Opcional) -->
    <div class="row mt-5" data-aos="fade-up" data-aos-delay="200">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern bg-primary">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-chart-simple me-2"></i> Estatísticas Rápidas
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4" id="statsQuick">
                        <div class="col-6 col-md-3 text-center">
                            <div class="stat-circle bg-primary bg-opacity-10">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h3 class="mt-2 mb-0" id="totalAlunos">--</h3>
                            <small class="text-muted">Total de Alunos</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="stat-circle bg-success bg-opacity-10">
                                <i class="fas fa-chalkboard-user fa-2x text-success"></i>
                            </div>
                            <h3 class="mt-2 mb-0" id="totalClasses">--</h3>
                            <small class="text-muted">Classes Ativas</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="stat-circle bg-warning bg-opacity-10">
                                <i class="fas fa-user-check fa-2x text-warning"></i>
                            </div>
                            <h3 class="mt-2 mb-0" id="totalPresencasMes">--</h3>
                            <small class="text-muted">Presenças (Mês)</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="stat-circle bg-info bg-opacity-10">
                                <i class="fas fa-dollar-sign fa-2x text-info"></i>
                            </div>
                            <h3 class="mt-2 mb-0" id="totalOfertasMes">--</h3>
                            <small class="text-muted">Ofertas (Mês)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dica de Relatórios -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="300">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-lightbulb fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Dica:</strong>
                <span>Utilize os relatórios para acompanhar o desempenho das classes e identificar alunos com baixa frequência para acompanhamento pastoral.</span>
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

/* Cards de relatórios modernos */
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
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.25rem;
}

.report-description {
    font-size: 0.8rem;
    color: var(--gray-500);
    margin-bottom: 0.5rem;
}

.report-link {
    font-size: 0.85rem;
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

/* Círculos de estatísticas */
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

/* Alertas personalizados */
.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

/* Responsividade */
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
        font-size: 1rem;
    }
    
    .stat-circle {
        width: 55px;
        height: 55px;
    }
    
    .stat-circle i {
        font-size: 1.5rem !important;
    }
}
</style>

<script>
// Função para buscar estatísticas rápidas
function carregarEstatisticas() {
    // Se você tiver uma API para buscar estatísticas, implemente aqui
    // Exemplo de chamada AJAX:
    /*
    $.ajax({
        url: '../../controllers/relatorios.php',
        method: 'POST',
        data: { acao: 'estatisticas_rapidas' },
        dataType: 'json',
        success: function(response) {
            if (response.sucesso) {
                $('#totalAlunos').text(response.total_alunos || '0');
                $('#totalClasses').text(response.total_classes || '0');
                $('#totalPresencasMes').text(response.total_presencas_mes || '0');
                $('#totalOfertasMes').text('R$ ' + (response.total_ofertas_mes || '0').toFixed(2));
            }
        },
        error: function() {
            console.log('Erro ao carregar estatísticas');
        }
    });
    */
    
    // Dados de exemplo (remova quando implementar a API)
    $('#totalAlunos').text('156');
    $('#totalClasses').text('8');
    $('#totalPresencasMes').text('1,234');
    $('#totalOfertasMes').text('R$ 2.450,00');
}

// Inicializar AOS
document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
    
    carregarEstatisticas();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>