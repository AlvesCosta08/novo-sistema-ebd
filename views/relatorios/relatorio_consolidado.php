<?php
// views/relatorios/relatorio_consolidado.php
// Relatório consolidado de classes com totais gerais

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$relatorioController = new RelatorioController();

// Capturar filtros
$trimestre_filtro = $_GET['trimestre'] ?? '';
$congregacao_filtro = $_GET['congregacao'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Buscar dados usando o controller
$dados = $relatorioController->getRelatorioConsolidado($trimestre_filtro, $congregacao_filtro, $data_inicio, $data_fim);

// Calcular totais usando o controller
$totais = $relatorioController->calcularTotais($dados);

$pageTitle = 'Relatório Consolidado de Classes';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-chart-pie me-3" style="color: var(--primary-600);"></i>
                Relatório Consolidado de Classes
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php" style="color: var(--primary-600);">
                            <i class="fas fa-chart-line me-1"></i> Relatórios
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-chart-pie me-1"></i> Consolidado
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Visão geral consolidada de matrículas, frequências e recursos por classe e congregação
            </p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="exportarCSV()" class="btn btn-modern btn-success">
                <i class="fas fa-file-csv me-2"></i> Exportar CSV
            </button>
            <button onclick="window.print()" class="btn btn-modern btn-primary">
                <i class="fas fa-print me-2"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Cards de Resumo Geral -->
    <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="100">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['matriculados'], 0, ',', '.') ?></div>
                <div class="stat-label">Total Matrículas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['presentes'], 0, ',', '.') ?></div>
                <div class="stat-label">Total Presenças</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['ausentes'], 0, ',', '.') ?></div>
                <div class="stat-label">Total Ausências</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['justificados'], 0, ',', '.') ?></div>
                <div class="stat-label">Justificados</div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="150">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['biblias'], 0, ',', '.') ?></div>
                <div class="stat-label">Total Bíblias</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-magazine"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['revistas'], 0, ',', '.') ?></div>
                <div class="stat-label">Total Revistas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$totais['visitantes'], 0, ',', '.') ?></div>
                <div class="stat-label">Visitantes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value"><?= RelatorioController::formatarMoeda($totais['oferta']) ?></div>
                <div class="stat-label">Total Ofertas</div>
            </div>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-gray-100">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-4" id="formFiltros">
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Data Início
                    </label>
                    <input type="date" name="data_inicio" class="form-control" 
                           value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar-check me-1 text-primary"></i> Data Fim
                    </label>
                    <input type="date" name="data_fim" class="form-control" 
                           value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                    </label>
                    <input type="text" name="trimestre" class="form-control" 
                           placeholder="Ex: 2024-1 ou 2025-2" 
                           value="<?= htmlspecialchars($trimestre_filtro) ?>">
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i> Formato: AAAA-T (Ex: 2024-1, 2025-2)
                    </small>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <input type="text" name="congregacao" class="form-control" 
                           placeholder="Nome da congregação" 
                           value="<?= htmlspecialchars($congregacao_filtro) ?>">
                </div>
                
                <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-modern btn-modern-primary">
                            <i class="fas fa-search me-2"></i> Filtrar
                        </button>
                        <a href="relatorio_consolidado.php" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Mobile (Resumo) -->
    <div class="d-md-none mb-4" data-aos="fade-up" data-aos-delay="250">
        <div class="modern-card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-primary bg-opacity-10 rounded">
                            <i class="fas fa-users fa-2x text-primary"></i>
                            <h5 class="mb-0 mt-2"><?= number_format((float)$totais['matriculados'], 0, ',', '.') ?></h5>
                            <small>Matrículas</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-success bg-opacity-10 rounded">
                            <i class="fas fa-user-check fa-2x text-success"></i>
                            <h5 class="mb-0 mt-2"><?= number_format((float)$totais['presentes'], 0, ',', '.') ?></h5>
                            <small>Presenças</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-danger bg-opacity-10 rounded">
                            <i class="fas fa-user-times fa-2x text-danger"></i>
                            <h5 class="mb-0 mt-2"><?= number_format((float)$totais['ausentes'], 0, ',', '.') ?></h5>
                            <small>Ausências</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-2 bg-warning bg-opacity-10 rounded">
                            <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                            <h5 class="mb-0 mt-2"><?= RelatorioController::formatarMoeda($totais['oferta']) ?></h5>
                            <small>Ofertas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Dados Consolidados (Desktop) -->
    <div class="modern-card d-none d-md-block" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Dados Consolidados
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaConsolidada" class="custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Congregação</th>
                            <th>Classe</th>
                            <th class="text-center">Trimestre</th>
                            <th class="text-center">Matriculados</th>
                            <th class="text-center">Presentes</th>
                            <th class="text-center">Ausentes</th>
                            <th class="text-center">Justificados</th>
                            <th class="text-center">Bíblias</th>
                            <th class="text-center">Revistas</th>
                            <th class="text-center">Visitantes</th>
                            <th class="text-end">Oferta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dados)): ?>
                            <?php foreach ($dados as $linha): ?>
                                <tr>
                                    <td><i class="fas fa-church me-2" style="color: var(--primary-500);"></i><?= htmlspecialchars($linha['congregacao']) ?></td>
                                    <td><i class="fas fa-chalkboard-user me-2" style="color: var(--success);"></i><?= htmlspecialchars($linha['classe']) ?></td>
                                    <td class="text-center"><span class="badge-trimestre"><?= htmlspecialchars($linha['trimestre']) ?></span></td>
                                    <td class="text-center fw-semibold"><?= number_format((float)$linha['matriculados'], 0, ',', '.') ?></td>
                                    <td class="text-center fw-semibold text-success"><?= number_format((float)$linha['presentes'], 0, ',', '.') ?></td>
                                    <td class="text-center fw-semibold text-danger"><?= number_format((float)$linha['ausentes'], 0, ',', '.') ?></td>
                                    <td class="text-center fw-semibold text-warning"><?= number_format((float)$linha['justificados'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format((float)$linha['biblias'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format((float)$linha['revistas'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format((float)$linha['visitantes'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold text-success"><?= RelatorioController::formatarMoeda($linha['oferta']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <i class="fas fa-database fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                                    <p class="text-muted mb-0">Nenhum dado encontrado</p>
                                    <small class="text-muted">Tente ajustar os filtros de busca</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($dados)): ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">TOTAIS GERAIS:</td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format((float)$totais['matriculados'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-success bg-opacity-10 text-success"><?= number_format((float)$totais['presentes'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-danger bg-opacity-10 text-danger"><?= number_format((float)$totais['ausentes'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-warning bg-opacity-10 text-warning"><?= number_format((float)$totais['justificados'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-info bg-opacity-10"><?= number_format((float)$totais['biblias'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-secondary bg-opacity-10"><?= number_format((float)$totais['revistas'], 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format((float)$totais['visitantes'], 0, ',', '.') ?></td>
                            <td class="text-end fw-bold bg-success bg-opacity-10 text-success"><?= RelatorioController::formatarMoeda($totais['oferta']) ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Cards Mobile (Lista de Classes) -->
    <div class="d-md-none" data-aos="fade-up" data-aos-delay="350">
        <?php if (!empty($dados)): ?>
            <?php foreach ($dados as $linha): ?>
                <div class="modern-card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-chalkboard-user me-2" style="color: var(--success);"></i>
                                    <?= htmlspecialchars($linha['classe']) ?>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-church me-1"></i> <?= htmlspecialchars($linha['congregacao']) ?>
                                </small>
                            </div>
                            <span class="badge-trimestre"><?= htmlspecialchars($linha['trimestre']) ?></span>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-4 text-center">
                                <div class="p-1">
                                    <div class="fw-bold text-primary"><?= number_format((float)$linha['matriculados'], 0, ',', '.') ?></div>
                                    <small class="text-muted">Matr.</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-1">
                                    <div class="fw-bold text-success"><?= number_format((float)$linha['presentes'], 0, ',', '.') ?></div>
                                    <small class="text-muted">Pres.</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-1">
                                    <div class="fw-bold text-danger"><?= number_format((float)$linha['ausentes'], 0, ',', '.') ?></div>
                                    <small class="text-muted">Faltas</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="d-flex justify-content-between small">
                                    <span><i class="fas fa-book"></i> Bíblias:</span>
                                    <span class="fw-bold"><?= number_format((float)$linha['biblias'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex justify-content-between small">
                                    <span><i class="fas fa-magazine"></i> Revistas:</span>
                                    <span class="fw-bold"><?= number_format((float)$linha['revistas'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small">
                                <span><i class="fas fa-user-plus"></i> Visitantes:</span>
                                <span class="fw-bold"><?= number_format((float)$linha['visitantes'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-dollar-sign text-success"></i> Ofertas:</span>
                                <span class="fw-bold text-success"><?= RelatorioController::formatarMoeda($linha['oferta']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Dica de Análise -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="400">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-chart-line fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Análise de Dados:</strong>
                <span>Este relatório consolida todas as informações por trimestre ou período. Utilize os filtros para focar em períodos específicos e acompanhe o crescimento das classes.</span>
            </div>
        </div>
    </div>
</div>

<style>
.badge-trimestre {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.table-footer {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 2px solid var(--gray-200);
    padding: 1rem;
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

.btn-modern-primary {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
}

.btn-modern-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

@media print {
    .navbar, .breadcrumb, .btn-modern, .alert-ebd, .dt-buttons, .d-md-none {
        display: none !important;
    }
    
    .d-none.d-md-block {
        display: block !important;
    }
    
    body {
        padding: 0;
        margin: 0;
    }
}
</style>

<script>
function exportarCSV() {
    const formData = new FormData(document.getElementById('formFiltros'));
    const params = new URLSearchParams(formData).toString();
    window.location.href = 'exportar_relatorio.php?tipo=consolidado&' + params;
}

$(document).ready(function() {
    <?php if (!empty($dados)): ?>
    $('#tabelaConsolidada').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        order: [[0, 'asc'], [1, 'asc']],
        pageLength: 10,
        responsive: true,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    <?php endif; ?>
    
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