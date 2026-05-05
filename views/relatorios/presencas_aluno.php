<?php
// views/relatorios/presencas_aluno.php
// Relatório de presenças por aluno com análise individual

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$relatorioController = new RelatorioController();

// Filtros
$congregacao_id = $_GET['congregacao_id'] ?? '';
$classe_id = $_GET['classe_id'] ?? '';
$trimestre = $_GET['trimestre'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

$form_submetido = !empty($_GET);

// Buscar dados usando o controller
$resultado = $relatorioController->getPresencasPorAluno($congregacao_id, $classe_id, $data_inicio, $data_fim, $trimestre);

$alunos = $resultado['dados'];
$trimestre_sem_dados = $resultado['trimestre_sem_dados'];
$data_inicio = $resultado['data_inicio'];
$data_fim = $resultado['data_fim'];
$top_presencas = $resultado['top_presencas'];
$top_faltas = $resultado['top_faltas'];

// Dropdowns usando o controller
$congs = $relatorioController->getCongregacoes();
$classes = $relatorioController->getClasses();

$pageTitle = 'Relatório de Presenças por Aluno';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-user-graduate me-3" style="color: var(--primary-600);"></i>
                Relatório de Presenças por Aluno
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
                        <i class="fas fa-user-check me-1"></i> Presenças por Aluno
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Análise detalhada de frequência individual por aluno, classe e congregação
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

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="get" class="row g-4" id="formFiltros">
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
                        <i class="fas fa-users me-1 text-primary"></i> Classe
                    </label>
                    <select name="classe_id" class="form-select">
                        <option value="">Todas as classes</option>
                        <?php foreach ($classes as $cl) : ?>
                            <option value="<?= $cl['id'] ?>" <?= ($classe_id == $cl['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-chart-line me-1 text-primary"></i> Trimestre
                    </label>
                    <select name="trimestre" id="trimestre" class="form-select">
                        <option value="">Personalizado</option>
                        <?php for ($i = 1; $i <= 4; $i++) : ?>
                            <option value="<?= $i ?>" <?= ($trimestre == $i) ? 'selected' : '' ?>><?= $i ?>º Trimestre</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Data Início
                    </label>
                    <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" <?= $trimestre ? 'readonly' : '' ?> />
                </div>
                
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <i class="fas fa-calendar-check me-1 text-primary"></i> Data Fim
                    </label>
                    <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" <?= $trimestre ? 'readonly' : '' ?> />
                </div>
                
                <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-modern btn-modern-primary">
                            <i class="fas fa-search me-2"></i> Filtrar
                        </button>
                        <a href="presencas_aluno.php" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Conteúdo Condicional -->
    <?php if (!$form_submetido): ?>
        <div class="modern-card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body text-center py-5">
                <i class="fas fa-filter fa-4x mb-3" style="color: var(--gray-400);"></i>
                <h5 class="text-muted">Nenhum dado para exibir</h5>
                <p class="text-muted">Por favor, selecione os filtros desejados e clique em "Filtrar".</p>
            </div>
        </div>
    <?php elseif ($trimestre_sem_dados): ?>
        <div class="alert alert-warning" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-exclamation-triangle me-2"></i>
            O trimestre selecionado (<?= $trimestre ?>º) ainda não possui registros de chamadas.
        </div>
    <?php elseif (empty($alunos)): ?>
        <div class="modern-card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x mb-3" style="color: var(--gray-400);"></i>
                <h5 class="text-muted">Nenhum resultado encontrado</h5>
                <p class="text-muted">Não foram encontrados registros com os filtros selecionados.</p>
            </div>
        </div>
    <?php else: 
        $total_alunos = count($alunos);
        $total_presencas = array_sum(array_column($alunos, 'presencas'));
        $total_faltas = array_sum(array_column($alunos, 'faltas'));
        $media_frequencia = $total_alunos > 0 ? round(array_sum(array_column($alunos, 'frequencia')) / $total_alunos, 1) : 0;
    ?>

        <!-- Cards de Resumo Geral -->
        <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="200">
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= number_format($total_alunos, 0, ',', '.') ?></div>
                    <div class="stat-label">Total de Alunos</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value"><?= number_format($total_presencas, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Presenças</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-value"><?= number_format($total_faltas, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Faltas</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value"><?= number_format($media_frequencia, 1, ',', '.') ?>%</div>
                    <div class="stat-label">Média Frequência</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value fs-6"><?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?></div>
                    <div class="stat-label">Período Analisado</div>
                </div>
            </div>
        </div>

        <!-- Rankings Top 5 -->
        <?php if (!empty($top_presencas) || !empty($top_faltas)): ?>
        <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="250">
            <div class="col-12 col-md-6">
                <div class="modern-card h-100">
                    <div class="card-header-modern bg-success">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-trophy me-2"></i> Top 5 Maiores Presenças
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach($top_presencas as $i => $aluno): ?>
                            <div class="ranking-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="ranking-position bg-success text-white"><?= $i+1 ?></span>
                                    <div>
                                        <strong class="d-block"><?= htmlspecialchars($aluno['aluno']) ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars($aluno['classe']) ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success"><?= number_format($aluno['presencas'], 0, ',', '.') ?> presenças</span>
                                    <br>
                                    <small><?= number_format($aluno['frequencia'], 1, ',', '.') ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6">
                <div class="modern-card h-100">
                    <div class="card-header-modern" style="background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i> Top 5 Maiores Faltas
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach($top_faltas as $i => $aluno): ?>
                            <div class="ranking-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="ranking-position bg-danger text-white"><?= $i+1 ?></span>
                                    <div>
                                        <strong class="d-block"><?= htmlspecialchars($aluno['aluno']) ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars($aluno['classe']) ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-danger"><?= number_format($aluno['faltas'], 0, ',', '.') ?> faltas</span>
                                    <br>
                                    <small><?= number_format($aluno['frequencia'], 1, ',', '.') ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela Completa -->
        <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
            <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-table me-2"></i> Relação Completa de Alunos
                </h5>
                <div class="d-flex gap-2">
                    <button onclick="exportarCSV()" class="btn btn-sm" style="background: #27ae60; color: white;">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelaAlunos" class="custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Classe</th>
                                <th>Congregação</th>
                                <th class="text-center">Presenças</th>
                                <th class="text-center">Faltas</th>
                                <th class="text-center">Frequência</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($alunos as $aluno): ?>
                                <tr>
                                    <td><i class="fas fa-user-graduate me-2"></i><?= htmlspecialchars($aluno['aluno']) ?></td>
                                    <td><i class="fas fa-chalkboard-user me-2"></i><?= htmlspecialchars($aluno['classe']) ?></td>
                                    <td><i class="fas fa-church me-2"></i><?= htmlspecialchars($aluno['congregacao']) ?></td>
                                    <td class="text-center fw-bold text-success"><?= number_format($aluno['presencas'], 0, ',', '.') ?></td>
                                    <td class="text-center fw-bold text-danger"><?= number_format($aluno['faltas'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <div class="progress-container">
                                            <div class="progress-bar-custom" style="width: <?= $aluno['frequencia'] ?>%;">
                                                <span><?= number_format($aluno['frequencia'], 1, ',', '.') ?>%</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-footer">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Totais Gerais:</td>
                                <td class="text-center fw-bold text-success"><?= number_format($total_presencas, 0, ',', '.') ?></td>
                                <td class="text-center fw-bold text-danger"><?= number_format($total_faltas, 0, ',', '.') ?></td>
                                <td class="text-center fw-bold text-info"><?= number_format($media_frequencia, 1, ',', '.') ?>%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-4" data-aos="fade-up" data-aos-delay="350">
            <i class="fas fa-chart-line me-2"></i>
            <strong>Análise de Frequência:</strong> Alunos com frequência abaixo de 75% merecem atenção especial.
        </div>
    <?php endif; ?>
</div>

<style>
.ranking-position {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
}

.ranking-item {
    transition: background-color 0.2s ease;
}

.ranking-item:hover {
    background-color: var(--gray-50);
}

.progress-container {
    width: 100%;
    background-color: var(--gray-200);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}

.progress-bar-custom {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    border-radius: 20px;
    padding: 0.25rem 0.5rem;
    text-align: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    transition: width 0.5s ease;
}

.table-footer {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 2px solid var(--gray-200);
    padding: 1rem;
}

.alert-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
    border-left: 4px solid var(--warning);
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
    .btn-modern, .alert, .progress-container, .btn-sm {
        display: none !important;
    }
}
</style>

<script>
function exportarCSV() {
    const formData = new FormData(document.getElementById('formFiltros'));
    const params = new URLSearchParams(formData).toString();
    window.location.href = 'exportar_relatorio.php?tipo=presencas_aluno&' + params;
}

$(document).ready(function() {
    <?php if (!empty($alunos)): ?>
    $('#tabelaAlunos').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
        order: [[5, 'desc']],
        pageLength: 10,
        responsive: true,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    <?php endif; ?>
    
    $('select[name="trimestre"]').change(function() {
        const trimestre = $(this).val();
        const ano = new Date().getFullYear();
        let inicio, fim;
        
        if (trimestre) {
            switch(trimestre) {
                case '1': inicio = `${ano}-01-01`; fim = `${ano}-03-31`; break;
                case '2': inicio = `${ano}-04-01`; fim = `${ano}-06-30`; break;
                case '3': inicio = `${ano}-07-01`; fim = `${ano}-09-30`; break;
                case '4': inicio = `${ano}-10-01`; fim = `${ano}-12-31`; break;
                default: inicio = `${ano}-01-01`; fim = `${ano}-12-31`;
            }
            $('input[name="data_inicio"]').val(inicio).prop('readonly', true);
            $('input[name="data_fim"]').val(fim).prop('readonly', true);
        } else {
            $('input[name="data_inicio"], input[name="data_fim"]').prop('readonly', false);
        }
    });
    
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, once: true, offset: 50 });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>