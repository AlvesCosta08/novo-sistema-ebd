<?php
// views/relatorios/frequencia_alunos.php
// Relatório de frequência de alunos com suporte a mobile e desktop

require_once __DIR__ . '/../../controllers/RelatorioController.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit();
}

$relatorioController = new RelatorioController();

// Buscar dados usando o controller (suporta view ou fallback)
$results = $relatorioController->getFrequenciaAlunos();

// Calcular totais gerais
$total_alunos = count($results);
$total_presencas = array_sum(array_column($results, 'total_presentes'));
$total_ausencias = array_sum(array_column($results, 'total_ausentes'));
$media_frequencia = ($total_presencas + $total_ausencias) > 0 
    ? round(($total_presencas / ($total_presencas + $total_ausencias)) * 100, 1) 
    : 0;

$pageTitle = 'Frequência de Alunos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-user-check me-3" style="color: var(--primary-600);"></i>
                Frequência de Alunos
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
                        <i class="fas fa-user-check me-1"></i> Frequência
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Resumo consolidado de presenças e ausências por aluno, classe e congregação
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
                <div class="stat-value"><?= number_format((float)$total_alunos, 0, ',', '.') ?></div>
                <div class="stat-label">Total de Alunos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_presencas, 0, ',', '.') ?></div>
                <div class="stat-label">Total Presenças</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-value"><?= number_format((float)$total_ausencias, 0, ',', '.') ?></div>
                <div class="stat-label">Total Faltas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?= number_format($media_frequencia, 1, ',', '.') ?>%</div>
                <div class="stat-label">Média Frequência</div>
            </div>
        </div>
    </div>

    <!-- Cards Mobile (Alternativa para dispositivos móveis) -->
    <div class="d-md-none mb-4" data-aos="fade-up" data-aos-delay="150">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $row): 
                $total_aulas = (float)$row['total_presentes'] + (float)$row['total_ausentes'];
                $frequencia = $total_aulas > 0 
                    ? round(((float)$row['total_presentes'] / $total_aulas) * 100, 1) 
                    : 0;
                
                $frequenciaCor = '';
                $frequenciaText = '';
                if ($frequencia >= 75) {
                    $frequenciaCor = '#10b981';
                    $frequenciaText = 'Excelente';
                } elseif ($frequencia >= 50) {
                    $frequenciaCor = '#f59e0b';
                    $frequenciaText = 'Atenção';
                } else {
                    $frequenciaCor = '#ef4444';
                    $frequenciaText = 'Crítico';
                }
            ?>
                <div class="modern-card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-user-graduate me-2" style="color: var(--primary-500);"></i>
                                    <?= htmlspecialchars($row['aluno_nome']) ?>
                                </h6>
                                <small class="text-muted">#<?= $row['aluno_id'] ?></small>
                            </div>
                            <span class="badge-trimestre"><?= htmlspecialchars($row['trimestre']) ?></span>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-6">
                                <div class="text-center p-2" style="background: rgba(16, 185, 129, 0.1); border-radius: 10px;">
                                    <i class="fas fa-user-check text-success"></i>
                                    <div class="fw-bold text-success"><?= number_format((float)$row['total_presentes'], 0, ',', '.') ?></div>
                                    <small class="text-muted">Presenças</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2" style="background: rgba(239, 68, 68, 0.1); border-radius: 10px;">
                                    <i class="fas fa-user-times text-danger"></i>
                                    <div class="fw-bold text-danger"><?= number_format((float)$row['total_ausentes'], 0, ',', '.') ?></div>
                                    <small class="text-muted">Faltas</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fas fa-chalkboard-user me-1"></i> <?= htmlspecialchars($row['classe_nome']) ?></span>
                                <span><i class="fas fa-church me-1"></i> <?= htmlspecialchars($row['congregacao_nome']) ?></span>
                            </div>
                            <div class="progress-container mt-2">
                                <div class="progress-bar-custom" style="width: <?= $frequencia ?>%; background: <?= $frequenciaCor ?>;">
                                    <span><?= number_format($frequencia, 1, ',', '.') ?>% - <?= $frequenciaText ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="modern-card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-database fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                    <p class="text-muted mb-0">Nenhum dado encontrado</p>
                    <small class="text-muted">Não há registros de frequência para exibir</small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabela de Dados (Desktop) -->
    <div class="modern-card d-none d-md-block" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Lista de Frequência por Aluno
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaFrequencia" class="custom-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 80px">ID</th>
                            <th>Aluno</th>
                            <th class="text-center">Presenças</th>
                            <th class="text-center">Faltas</th>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th class="text-center">Trimestre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $row): 
                                $total_aulas = (float)$row['total_presentes'] + (float)$row['total_ausentes'];
                                $frequencia = $total_aulas > 0 
                                    ? round(((float)$row['total_presentes'] / $total_aulas) * 100, 1) 
                                    : 0;
                                
                                $frequenciaCor = '';
                                $frequenciaText = '';
                                if ($frequencia >= 75) {
                                    $frequenciaCor = 'text-success';
                                    $frequenciaText = 'Excelente';
                                } elseif ($frequencia >= 50) {
                                    $frequenciaCor = 'text-warning';
                                    $frequenciaText = 'Atenção';
                                } else {
                                    $frequenciaCor = 'text-danger';
                                    $frequenciaText = 'Crítico';
                                }
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge-id">#<?= $row['aluno_id'] ?></span>
                                    </table>
                                    <td>
                                        <i class="fas fa-user-graduate me-2" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($row['aluno_nome']) ?>
                                        <br>
                                        <small class="<?= $frequenciaCor ?>">
                                            <i class="fas fa-chart-line me-1"></i> 
                                            <?= number_format($frequencia, 1, ',', '.') ?>% - <?= $frequenciaText ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-presenca"><?= number_format((float)$row['total_presentes'], 0, ',', '.') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-falta"><?= number_format((float)$row['total_ausentes'], 0, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <i class="fas fa-chalkboard-user me-2" style="color: var(--success);"></i>
                                        <?= htmlspecialchars($row['classe_nome']) ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-church me-2" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($row['congregacao_nome']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-trimestre"><?= htmlspecialchars($row['trimestre']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-database fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                                    <p class="text-muted mb-0">Nenhum dado encontrado</p>
                                    <small class="text-muted">Não há registros de frequência para exibir</small>
                                 </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($results)): ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="2" class="text-end fw-bold">TOTAIS GERAIS:</td>
                            <td class="text-center fw-bold bg-success bg-opacity-10 text-success"><?= number_format((float)$total_presencas, 0, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-danger bg-opacity-10 text-danger"><?= number_format((float)$total_ausencias, 0, ',', '.') ?></td>
                            <td colspan="3" class="text-center fw-bold bg-info bg-opacity-10">
                                <i class="fas fa-chart-line me-1"></i> Média Geral: <?= number_format($media_frequencia, 1, ',', '.') ?>%
                             </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Dica de Análise -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="300">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-chart-line fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Análise de Frequência:</strong>
                <span>Alunos com frequência abaixo de 75% merecem atenção especial. Utilize este relatório para acompanhamento pastoral e ações de incentivo à assiduidade.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para a página de frequência */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
}

/* Badges */
.badge-id {
    background: var(--gray-100);
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-family: monospace;
    font-size: 0.8rem;
    color: var(--gray-700);
}

.badge-presenca {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.badge-falta {
    background: linear-gradient(135deg, var(--danger) 0%, #b91c1c 100%);
    color: white;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.badge-trimestre {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Barra de progresso */
.progress-container {
    width: 100%;
    background-color: var(--gray-200);
    border-radius: 20px;
    overflow: hidden;
}

.progress-bar-custom {
    border-radius: 20px;
    padding: 0.25rem 0.5rem;
    text-align: center;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    transition: width 0.5s ease;
}

/* Rodapé da tabela */
.table-footer {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 2px solid var(--gray-200);
    padding: 1rem;
}

/* Alertas personalizados */
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

/* Print styles */
@media print {
    .navbar, .breadcrumb, .btn-modern, .alert-ebd, .d-md-none {
        display: none !important;
    }
    
    .d-none.d-md-block {
        display: block !important;
    }
    
    body {
        padding: 0;
        margin: 0;
    }
    
    .modern-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .stat-card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}

/* Responsividade */
@media (max-width: 768px) {
    .display-5 {
        font-size: 1.5rem;
    }
    
    .stat-card .stat-value {
        font-size: 1.25rem;
    }
}
</style>

<script>
function exportarCSV() {
    window.location.href = 'exportar_relatorio.php?tipo=frequencia_alunos';
}

$(document).ready(function() {
    <?php if (!empty($results)): ?>
    $('#tabelaFrequencia').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        order: [[1, 'asc']],
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