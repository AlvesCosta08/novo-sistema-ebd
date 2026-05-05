<?php
// views/relatorios/relatorio_geral.php
// Relatório geral de presenças com visão por classe

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$relatorioController = new RelatorioController();

// Filtros
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$congregacao_id = $_GET['congregacao_id'] ?? '';
$trimestre = $_GET['trimestre'] ?? '';

// Buscar dados
$relatorios = $relatorioController->getRelatorioGeral($data_inicio, $data_fim, $congregacao_id, $trimestre);
$congs = $relatorioController->getCongregacoes();

// Calcular totais
$total_geral_matriculados = array_sum(array_column($relatorios, 'total_matriculados'));
$total_geral_presencas = array_sum(array_column($relatorios, 'total_presencas'));
$total_geral_faltas = array_sum(array_column($relatorios, 'total_faltas'));
$total_geral_visitantes = array_sum(array_column($relatorios, 'total_visitantes'));
$total_geral_biblias = array_sum(array_column($relatorios, 'total_biblias'));
$total_geral_revistas = array_sum(array_column($relatorios, 'total_revistas'));
$total_geral_ofertas = array_sum(array_column($relatorios, 'total_ofertas'));

// Calcular frequência geral
$total_chamadas = 0;
foreach ($relatorios as $row) {
    $total_chamadas += ((int)($row['total_aulas'] ?? 0)) * ((int)($row['total_matriculados'] ?? 0));
}
$presenca_visitante = $total_geral_presencas + $total_geral_visitantes;
$frequencia_geral = ($total_chamadas > 0) ? ($presenca_visitante / $total_chamadas) * 100 : 0;

$pageTitle = 'Relatório Geral de Presenças';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Cabeçalho -->
    <div class="mb-4" data-aos="fade-down">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                    <i class="fas fa-chart-line me-3" style="color: var(--primary-600);"></i>
                    Relatório Geral de Presenças
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/views/dashboard.php" style="color: var(--primary-600);">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php" style="color: var(--primary-600);">Relatórios</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Geral</li>
                    </ol>
                </nav>
                <p class="text-muted mt-2 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Visão geral consolidada de presenças, recursos e frequência por classe
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
    </div>

    <!-- Cards de Resumo Geral -->
    <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="100">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_geral_matriculados, 0, ',', '.') ?></div>
                <div class="stat-label">Total Matrículas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_geral_presencas, 0, ',', '.') ?></div>
                <div class="stat-label">Total Presenças</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_geral_faltas, 0, ',', '.') ?></div>
                <div class="stat-label">Total Faltas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_geral_visitantes, 0, ',', '.') ?></div>
                <div class="stat-label">Visitantes</div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="150">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_geral_biblias, 0, ',', '.') ?></div>
                <div class="stat-label">Bíblias</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-magazine"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_geral_revistas, 0, ',', '.') ?></div>
                <div class="stat-label">Revistas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value"><?= RelatorioController::formatarMoeda($total_geral_ofertas) ?></div>
                <div class="stat-label">Ofertas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?= number_format($frequencia_geral, 1, ',', '.') ?>%</div>
                <div class="stat-label">Frequência Geral</div>
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
                    <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" />
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-calendar-check me-1 text-primary"></i> Data Fim
                    </label>
                    <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" />
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <select name="congregacao_id" class="form-select">
                        <option value="">Todas as congregações</option>
                        <?php foreach ($congs as $c) : ?>
                            <option value="<?= $c['id'] ?>" <?= ($congregacao_id == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                    </label>
                    <select name="trimestre" class="form-select">
                        <option value="">Todos os trimestres</option>
                        <?php for ($i = 1; $i <= 4; $i++) : ?>
                            <option value="<?= $i ?>" <?= ($trimestre == $i) ? 'selected' : '' ?>><?= $i ?>º Trimestre</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-modern btn-modern-primary">
                            <i class="fas fa-search me-2"></i> Filtrar
                        </button>
                        <a href="relatorio_geral.php" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Dados -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Dados por Classe
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaGeral" class="table table-hover mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th class="text-center">Trimestre</th>
                            <th class="text-center">Matriculados</th>
                            <th class="text-center">Presentes</th>
                            <th class="text-center">Faltas</th>
                            <th class="text-center">Visitantes</th>
                            <th class="text-center">Bíblias</th>
                            <th class="text-center">Revistas</th>
                            <th class="text-end">Ofertas (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($relatorios)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Nenhum dado encontrado com os filtros selecionados.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($relatorios as $row): ?>
                                <?php
                                // Calcular frequência da classe
                                $total_aulas_classe = (int)($row['total_aulas'] ?? 0);
                                $total_matriculados_classe = (int)($row['total_matriculados'] ?? 0);
                                $total_chamadas_classe = $total_aulas_classe * $total_matriculados_classe;
                                $presencas_classe = (int)($row['total_presencas'] ?? 0);
                                $visitantes_classe = (int)($row['total_visitantes'] ?? 0);
                                $frequencia_classe = $total_chamadas_classe > 0 
                                    ? (($presencas_classe + $visitantes_classe) / $total_chamadas_classe) * 100 
                                    : 0;
                                
                                $frequenciaCor = '';
                                if ($frequencia_classe >= 75) {
                                    $frequenciaCor = 'text-success';
                                } elseif ($frequencia_classe >= 50) {
                                    $frequenciaCor = 'text-warning';
                                } else {
                                    $frequenciaCor = 'text-danger';
                                }
                                ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <i class="fas fa-chalkboard-user me-2" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($row['classe_nome']) ?>
                                        <br>
                                        <small class="<?= $frequenciaCor ?>">
                                            <i class="fas fa-chart-line me-1"></i> 
                                            <?= number_format($frequencia_classe, 1, ',', '.') ?>% de frequência
                                        </small>
                                    </td>
                                    <td>
                                        <i class="fas fa-church me-2" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($row['congregacao_nome']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['trimestre']) ?>º Trim.</span>
                                    </td>
                                    <td class="text-center fw-semibold"><?= number_format((float)$row['total_matriculados'], 0, ',', '.') ?></td>
                                    <td class="text-center fw-semibold text-success"><?= number_format((float)$row['total_presencas'], 0, ',', '.') ?></td>
                                    <td class="text-center fw-semibold text-danger"><?= number_format((float)$row['total_faltas'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format((float)$row['total_visitantes'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format((float)$row['total_biblias'], 0, ',', '.') ?></td>
                                    <td class="text-center"><?= number_format((float)$row['total_revistas'], 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold text-success"><?= RelatorioController::formatarMoeda($row['total_ofertas']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($relatorios)): ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">TOTAIS GERAIS:</td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format((float)$total_geral_matriculados, 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-success bg-opacity-10 text-success"><?= number_format((float)$total_geral_presencas, 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-danger bg-opacity-10 text-danger"><?= number_format((float)$total_geral_faltas, 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-info bg-opacity-10"><?= number_format((float)$total_geral_visitantes, 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-warning bg-opacity-10"><?= number_format((float)$total_geral_biblias, 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-secondary bg-opacity-10"><?= number_format((float)$total_geral_revistas, 0, ',', '.') ?></td>
                            <td class="text-end fw-bold bg-success bg-opacity-10 text-success"><?= RelatorioController::formatarMoeda($total_geral_ofertas) ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Dica de Análise -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="350">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-chart-line fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Análise de Frequência:</strong>
                <span>A frequência ideal é acima de 75%. Classes com baixa frequência merecem atenção especial. Utilize os filtros para analisar períodos específicos.</span>
            </div>
        </div>
    </div>
</div>

<style>
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

.badge {
    padding: 0.35rem 0.65rem;
    border-radius: 8px;
    font-weight: 500;
}

@media print {
    .btn-modern, .breadcrumb, .alert-ebd {
        display: none !important;
    }
}
</style>

<script>
function exportarCSV() {
    const formData = new FormData(document.getElementById('formFiltros'));
    const params = new URLSearchParams(formData).toString();
    window.location.href = 'exportar_relatorio.php?tipo=relatorio_geral&' + params;
}

$(document).ready(function() {
    <?php if (!empty($relatorios)): ?>
    $('#tabelaGeral').DataTable({
        language: { 
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' 
        },
        order: [[0, 'asc']],
        pageLength: 10,
        responsive: true,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    <?php endif; ?>
    
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, once: true, offset: 50 });
    }
});
</script>

