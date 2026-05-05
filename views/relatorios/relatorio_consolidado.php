<?php
// views/relatorios/relatorio_consolidado.php
// Relatório consolidado de classes - Versão Premium UX

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$relatorioController = new RelatorioController();

$trimestre_filtro = $_GET['trimestre'] ?? '';
$congregacao_filtro = $_GET['congregacao'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$form_submetido = !empty($_GET);

$dados = [];
$totais = [
    'matriculados' => 0, 'presentes' => 0, 'ausentes' => 0, 'justificados' => 0,
    'biblias' => 0, 'revistas' => 0, 'visitantes' => 0, 'oferta' => 0
];

if ($form_submetido) {
    $dados = $relatorioController->getRelatorioConsolidado($trimestre_filtro, $congregacao_filtro, $data_inicio, $data_fim);
    $totais = $relatorioController->calcularTotais($dados);
}

$pageTitle = 'Relatório Consolidado de Classes';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-3 px-md-4">
    <!-- Cabeçalho -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4" data-aos="fade-down">
        <div>
            <div class="d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="fas fa-chart-pie fa-2x"></i>
                </div>
                <div>
                    <h1 class="h2 fw-bold mb-1">Relatório Consolidado</h1>
                    <p class="text-muted small mb-0">Visão geral de matrículas, frequências e recursos</p>
                </div>
            </div>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/views/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Relatórios</a></li>
                    <li class="breadcrumb-item active">Consolidado</li>
                </ol>
            </nav>
        </div>
        <?php if ($form_submetido && !empty($dados)): ?>
        <div class="d-flex gap-2 w-100 w-md-auto">
            <button onclick="exportarCSV()" class="btn-action btn-export" aria-label="Exportar dados para CSV">
                <i class="fas fa-download"></i> <span class="d-none d-sm-inline">Exportar</span>
            </button>
            <button onclick="window.print()" class="btn-action btn-print" aria-label="Imprimir relatório">
                <i class="fas fa-print"></i> <span class="d-none d-sm-inline">Imprimir</span>
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card filter-card mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header bg-primary text-white py-3">
            <i class="fas fa-sliders-h me-2"></i> Filtros de Pesquisa
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="GET" id="formFiltros">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="fas fa-calendar-alt me-1 text-primary"></i> Data Início
                        </label>
                        <input type="date" name="data_inicio" class="form-control filter-input" 
                               value="<?= htmlspecialchars($data_inicio) ?>">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="fas fa-calendar-check me-1 text-primary"></i> Data Fim
                        </label>
                        <input type="date" name="data_fim" class="form-control filter-input" 
                               value="<?= htmlspecialchars($data_fim) ?>">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                        </label>
                        <input type="text" name="trimestre" class="form-control filter-input" 
                               placeholder="Ex: 2026-T2" value="<?= htmlspecialchars($trimestre_filtro) ?>">
                        <small class="text-muted d-block mt-1 small">Formato: AAAA-T (2026-T2)</small>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold">
                            <i class="fas fa-church me-1 text-primary"></i> Congregação
                        </label>
                        <input type="text" name="congregacao" class="form-control filter-input" 
                               placeholder="Nome da congregação" value="<?= htmlspecialchars($congregacao_filtro) ?>">
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-2">
                            <button type="submit" class="btn btn-primary px-4" id="btnFiltrar">
                                <i class="fas fa-search me-2"></i> Aplicar Filtros
                            </button>
                            <a href="relatorio_consolidado.php" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-undo-alt me-2"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$form_submetido): ?>
        <!-- Estado Inicial -->
        <div class="empty-state text-center py-5" data-aos="fade-up" data-aos-delay="200">
            <div class="empty-state-icon mx-auto mb-4">
                <i class="fas fa-filter fa-3x"></i>
            </div>
            <h5 class="text-muted">Nenhum filtro aplicado</h5>
            <p class="text-muted small">Selecione os filtros acima e clique em "Aplicar Filtros"</p>
            <div class="alert alert-info d-inline-block mt-3 small">
                <i class="fas fa-info-circle me-2"></i> Utilize os filtros para buscar dados por período
            </div>
        </div>
    <?php elseif (empty($dados)): ?>
        <!-- Sem dados -->
        <div class="empty-state text-center py-5" data-aos="fade-up" data-aos-delay="200">
            <div class="empty-state-icon mx-auto mb-4">
                <i class="fas fa-database fa-3x"></i>
            </div>
            <h5 class="text-muted">Nenhum resultado encontrado</h5>
            <p class="text-muted small">Tente outros filtros ou limpe a busca</p>
            <a href="relatorio_consolidado.php" class="btn btn-outline-secondary mt-2">
                <i class="fas fa-undo-alt me-2"></i> Limpar Filtros
            </a>
        </div>
    <?php else: ?>
        <!-- Cards Stats -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="150">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['matriculados'], 0, ',', '.') ?></span>
                    <span class="stat-label">Matrículas</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success-soft text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['presentes'], 0, ',', '.') ?></span>
                    <span class="stat-label">Presenças</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-danger-soft text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['ausentes'], 0, ',', '.') ?></span>
                    <span class="stat-label">Ausências</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-warning-soft text-warning">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['justificados'], 0, ',', '.') ?></span>
                    <span class="stat-label">Justificados</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-info-soft text-info">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['biblias'], 0, ',', '.') ?></span>
                    <span class="stat-label">Bíblias</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-secondary-soft text-secondary">
                    <i class="fas fa-magazine"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['revistas'], 0, ',', '.') ?></span>
                    <span class="stat-label">Revistas</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft text-primary">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= number_format((float)$totais['visitantes'], 0, ',', '.') ?></span>
                    <span class="stat-label">Visitantes</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success-soft text-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= RelatorioController::formatarMoeda($totais['oferta']) ?></span>
                    <span class="stat-label">Ofertas</span>
                </div>
            </div>
        </div>

        <!-- Tabela Desktop -->
        <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="300">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <span><i class="fas fa-table me-2"></i> Dados por Classe</span>
                <span class="badge bg-light text-dark"><?= count($dados) ?> classes</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelaConsolidada" class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Congregação</th>
                                <th>Classe</th>
                                <th class="text-center">Trimestre</th>
                                <th class="text-center">Matr.</th>
                                <th class="text-center">Pres.</th>
                                <th class="text-center">Faltas</th>
                                <th class="text-center">Just.</th>
                                <th class="text-center">Bíblias</th>
                                <th class="text-center">Revistas</th>
                                <th class="text-center">Visit.</th>
                                <th class="text-end">Ofertas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados as $linha): ?>
                            <tr>
                                <td><i class="fas fa-church text-primary me-2"></i><?= htmlspecialchars($linha['congregacao']) ?></td>
                                <td><i class="fas fa-chalkboard-user text-success me-2"></i><?= htmlspecialchars($linha['classe']) ?></td>
                                <td class="text-center"><span class="badge-trimestre"><?= htmlspecialchars($linha['trimestre']) ?></span></td>
                                <td class="text-center fw-semibold"><?= number_format((float)$linha['matriculados'], 0, ',', '.') ?></td>
                                <td class="text-center text-success fw-semibold"><?= number_format((float)$linha['presentes'], 0, ',', '.') ?></td>
                                <td class="text-center text-danger fw-semibold"><?= number_format((float)$linha['ausentes'], 0, ',', '.') ?></td>
                                <td class="text-center text-warning fw-semibold"><?= number_format((float)$linha['justificados'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format((float)$linha['biblias'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format((float)$linha['revistas'], 0, ',', '.') ?></td>
                                <td class="text-center"><?= number_format((float)$linha['visitantes'], 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-success"><?= RelatorioController::formatarMoeda($linha['oferta']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">TOTAIS:</td>
                                <td class="text-center fw-bold"><?= number_format((float)$totais['matriculados'], 0, ',', '.') ?></td>
                                <td class="text-center fw-bold text-success"><?= number_format((float)$totais['presentes'], 0, ',', '.') ?></td>
                                <td class="text-center fw-bold text-danger"><?= number_format((float)$totais['ausentes'], 0, ',', '.') ?></td>
                                <td class="text-center fw-bold text-warning"><?= number_format((float)$totais['justificados'], 0, ',', '.') ?></td>
                                <td class="text-center fw-bold"><?= number_format((float)$totais['biblias'], 0, ',', '.') ?></td>
                                <td class="text-center fw-bold"><?= number_format((float)$totais['revistas'], 0, ',', '.') ?></td>
                                <td class="text-center fw-bold"><?= number_format((float)$totais['visitantes'], 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-success"><?= RelatorioController::formatarMoeda($totais['oferta']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cards Mobile (Lista de Classes) -->
        <div class="d-block d-md-none mt-4" data-aos="fade-up" data-aos-delay="350">
            <?php foreach ($dados as $linha): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-chalkboard-user text-success me-2"></i>
                                <?= htmlspecialchars($linha['classe']) ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-church me-1"></i> <?= htmlspecialchars($linha['congregacao']) ?>
                            </small>
                        </div>
                        <span class="badge-trimestre"><?= htmlspecialchars($linha['trimestre']) ?></span>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4 text-center">
                            <div class="py-2 bg-light rounded">
                                <div class="fw-bold text-primary"><?= number_format((float)$linha['matriculados'], 0, ',', '.') ?></div>
                                <small class="text-muted">Matr.</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="py-2 bg-light rounded">
                                <div class="fw-bold text-success"><?= number_format((float)$linha['presentes'], 0, ',', '.') ?></div>
                                <small class="text-muted">Pres.</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="py-2 bg-light rounded">
                                <div class="fw-bold text-danger"><?= number_format((float)$linha['ausentes'], 0, ',', '.') ?></div>
                                <small class="text-muted">Faltas</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <div class="d-flex justify-content-between small py-1">
                                <span><i class="fas fa-book"></i> Bíblias:</span>
                                <span class="fw-bold"><?= number_format((float)$linha['biblias'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between small py-1">
                                <span><i class="fas fa-magazine"></i> Revistas:</span>
                                <span class="fw-bold"><?= number_format((float)$linha['revistas'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between small py-1">
                                <span><i class="fas fa-user-plus"></i> Visitantes:</span>
                                <span class="fw-bold"><?= number_format((float)$linha['visitantes'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-dollar-sign text-success"></i> Ofertas:</span>
                                <span class="fw-bold text-success"><?= RelatorioController::formatarMoeda($linha['oferta']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Dica -->
        <div class="alert alert-primary bg-light border-0 mt-4 small" data-aos="fade-up" data-aos-delay="400">
            <i class="fas fa-chart-line me-2"></i>
            <strong>Análise:</strong> A frequência ideal é acima de 75%. Classes com baixa frequência merecem atenção especial.
        </div>
    <?php endif; ?>
</div>

<style>
/* ============================================
   ESTILOS UX PREMIUM - RESPONSIVO
   ============================================ */

/* Header Icon */
.header-icon {
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, var(--primary-600, #4f46e5) 0%, var(--primary-700, #4338ca) 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

/* Action Buttons */
.btn-action {
    padding: 8px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-export {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-print {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
}

/* Filter Input */
.filter-input {
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 12px;
    transition: all 0.2s ease;
}

.filter-input:focus {
    border-color: var(--primary-600, #4f46e5);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    border: 1px solid #f0f0f0;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.bg-primary-soft { background: rgba(79, 70, 229, 0.1); }
.bg-success-soft { background: rgba(16, 185, 129, 0.1); }
.bg-danger-soft { background: rgba(239, 68, 68, 0.1); }
.bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
.bg-info-soft { background: rgba(59, 130, 246, 0.1); }
.bg-secondary-soft { background: rgba(107, 114, 128, 0.1); }

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: 800;
    display: block;
    line-height: 1.2;
    color: #1f2937;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

/* Badge Trimestre */
.badge-trimestre {
    background: linear-gradient(135deg, var(--primary-600, #4f46e5) 0%, var(--primary-700, #4338ca) 100%);
    color: white;
    padding: 4px 10px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

/* Empty State */
.empty-state-icon {
    width: 80px;
    height: 80px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

/* Tabela */
.table th, .table td {
    vertical-align: middle;
    padding: 12px 8px;
    font-size: 14px;
}

/* Responsividade Extra */
@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .stat-card {
        padding: 12px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    .stat-value {
        font-size: 18px;
    }
    
    .stat-label {
        font-size: 10px;
    }
    
    .btn-action {
        flex: 1;
        text-align: center;
    }
    
    .table th, .table td {
        font-size: 12px;
        padding: 8px 4px;
    }
}

/* Impressão */
@media print {
    .filter-card, .btn-action, .empty-state, .alert-primary, .card-header .badge {
        display: none !important;
    }
    
    .stat-card, .card {
        box-shadow: none;
        border: 1px solid #ddd;
        break-inside: avoid;
    }
    
    .stats-grid {
        break-inside: avoid;
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
    <?php if ($form_submetido && !empty($dados)): ?>
    $('#tabelaConsolidada').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
        order: [[1, 'asc']],
        pageLength: 10,
        responsive: true,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    <?php endif; ?>
    
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 500, once: true, offset: 50 });
    }
});

// Loading state no botão de filtro
document.getElementById('btnFiltrar')?.addEventListener('click', function(e) {
    if (this.form.checkValidity()) {
        this.innerHTML = '<i class="fas fa-spinner fa-pulse me-2"></i> Carregando...';
        this.disabled = true;
        this.form.submit();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>