<?php 
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Relatório Trimestral por Congregação';

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';

// Conexão com o banco de dados
require_once __DIR__ . '/../../config/conexao.php';

// Consulta para obter os dados da VIEW
$query = "SELECT * FROM relatorio_trimestre_congregacao ORDER BY congregacao_nome, classe_nome, trimestre";
$result = $pdo->query($query);

// Totais gerais
$totais = [
    'biblias' => 0,
    'revistas' => 0,
    'visitantes' => 0,
    'ofertas' => 0
];

$dados = [];
if ($result->rowCount() > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $dados[] = $row;
        $totais['biblias'] += (int)$row['total_biblias'];
        $totais['revistas'] += (int)$row['total_revistas'];
        $totais['visitantes'] += (int)$row['total_visitantes'];
        $totais['ofertas'] += (float)$row['total_ofertas'];
    }
}

// Agrupar por congregação para os cards
$congregacoes = [];
foreach ($dados as $item) {
    $congregacoes[$item['congregacao_nome']][] = $item;
}
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
        <div>
            <h1 class="display-5 fw-bold mb-2" style="color: var(--gray-800);">
                <i class="fas fa-chart-pie me-3" style="color: var(--primary-600);"></i>
                Relatório Trimestral por Congregação
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
                        <i class="fas fa-church me-1"></i> Trimestral por Congregação
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Visão geral consolidada de bíblias, revistas, visitantes e ofertas por classe, congregação e trimestre
            </p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-modern btn-outline-secondary">
                <i class="fas fa-print me-2"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Cards de Totais Gerais -->
    <div class="row mb-4 g-4" data-aos="fade-up" data-aos-delay="100">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['biblias']) ?></div>
                <div class="stat-label">Total de Bíblias</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-magazine"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['revistas']) ?></div>
                <div class="stat-label">Total de Revistas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['visitantes']) ?></div>
                <div class="stat-label">Total de Visitantes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?= number_format($totais['ofertas'], 2, ',', '.') ?></div>
                <div class="stat-label">Total de Ofertas</div>
            </div>
        </div>
    </div>

    <!-- Cards por Congregação (Mobile) -->
    <div class="row g-4 mb-4 d-md-none" data-aos="fade-up" data-aos-delay="200">
        <?php foreach ($congregacoes as $congNome => $items): ?>
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-header-modern bg-primary">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-church me-2"></i> <?= htmlspecialchars($congNome) ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($items as $item): ?>
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-primary"><?= htmlspecialchars($item['classe_nome']) ?></strong>
                                    <span class="badge-ebd badge-primary">Trimestre <?= $item['trimestre'] ?></span>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <i class="fas fa-book text-primary me-1"></i> Bíblias: 
                                        <strong><?= $item['total_biblias'] ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-magazine text-success me-1"></i> Revistas: 
                                        <strong><?= $item['total_revistas'] ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-user-plus text-warning me-1"></i> Visitantes: 
                                        <strong><?= $item['total_visitantes'] ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-dollar-sign text-info me-1"></i> Ofertas: 
                                        <strong>R$ <?= number_format($item['total_ofertas'], 2, ',', '.') ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabela de Dados (Desktop) -->
    <div class="modern-card d-none d-md-block" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Dados por Classe e Congregação
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaRelatorio" class="custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Congregação</th>
                            <th class="text-center">Trimestre</th>
                            <th class="text-center"><i class="fas fa-book"></i> Bíblias</th>
                            <th class="text-center"><i class="fas fa-magazine"></i> Revistas</th>
                            <th class="text-center"><i class="fas fa-user-plus"></i> Visitantes</th>
                            <th class="text-end"><i class="fas fa-dollar-sign"></i> Ofertas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dados)): ?>
                            <?php foreach ($dados as $row): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-chalkboard-user me-2" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($row['classe_nome']) ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-church me-2" style="color: var(--primary-500);"></i>
                                        <?= htmlspecialchars($row['congregacao_nome']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-trimestre"><?= $row['trimestre'] ?>º Trim.</span>
                                    </td>
                                    <td class="text-center fw-semibold"><?= number_format($row['total_biblias']) ?></td>
                                    <td class="text-center fw-semibold"><?= number_format($row['total_revistas']) ?></td>
                                    <td class="text-center fw-semibold"><?= number_format($row['total_visitantes']) ?></td>
                                    <td class="text-end fw-semibold text-success">
                                        R$ <?= number_format($row['total_ofertas'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-database fa-3x mb-3 d-block" style="color: var(--gray-400);"></i>
                                    <p class="text-muted mb-0">Nenhum dado encontrado</p>
                                    <small class="text-muted">Não há registros de chamadas para o período atual</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($dados)): ?>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">TOTAIS:</td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format($totais['biblias']) ?></td>
                            <td class="text-center fw-bold bg-success bg-opacity-10"><?= number_format($totais['revistas']) ?></td>
                            <td class="text-center fw-bold bg-warning bg-opacity-10"><?= number_format($totais['visitantes']) ?></td>
                            <td class="text-end fw-bold bg-info bg-opacity-10 text-success">
                                R$ <?= number_format($totais['ofertas'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráfico Resumo (Opcional) -->
    <?php if (!empty($dados)): ?>
    <div class="modern-card mt-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern" style="background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-chart-bar me-2"></i> Resumo Gráfico
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <canvas id="graficoOfertas" style="max-height: 300px;"></canvas>
                    <p class="text-center text-muted mt-2 small">Total de Ofertas por Congregação</p>
                </div>
                <div class="col-12 col-md-6">
                    <canvas id="graficoParticipacao" style="max-height: 300px;"></canvas>
                    <p class="text-center text-muted mt-2 small">Participação por Congregação (Bíblias + Revistas + Visitantes)</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Dica de Utilização -->
    <div class="alert-ebd alert-info-ebd mt-4" data-aos="fade-up" data-aos-delay="400">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="fas fa-lightbulb fa-2x" style="color: var(--info);"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Análise de Dados:</strong>
                <span>Este relatório consolida as informações de todas as chamadas registradas. Utilize os dados para planejar recursos e avaliar o engajamento por classe e congregação.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para o relatório trimestral */
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: var(--gray-500);
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

/* Rodapé da tabela */
.table-footer {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    font-weight: 600;
}

.table-footer td {
    border-top: 2px solid var(--gray-200);
    padding: 1rem;
}

/* DataTables personalizado */
.dataTables_wrapper .dataTables_filter input {
    border-radius: 10px;
    border: 1.5px solid var(--gray-200);
    padding: 0.5rem 0.75rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.dataTables_wrapper .dataTables_length select {
    border-radius: 10px;
    border: 1.5px solid var(--gray-200);
    padding: 0.25rem 0.5rem;
}

/* Alertas personalizados */
.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

/* Print styles */
@media print {
    .navbar, .breadcrumb, .btn-modern, .alert-ebd, .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_paginate {
        display: none !important;
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
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable apenas em desktop
    if ($(window).width() >= 768) {
        if ($.fn.DataTable.isDataTable('#tabelaRelatorio')) {
            $('#tabelaRelatorio').DataTable().destroy();
        }
        
        $('#tabelaRelatorio').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            },
            order: [[1, 'asc'], [0, 'asc']],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            responsive: true,
            drawCallback: function() {
                $('.dataTables_paginate').addClass('mt-3');
            }
        });
    }
    
    // Gráficos
    <?php if (!empty($dados)): ?>
    // Agrupar dados por congregação para os gráficos
    const ofertasPorCongregacao = {};
    const participacaoPorCongregacao = {};
    
    <?php foreach ($dados as $item): ?>
        const congNome = '<?= addslashes($item['congregacao_nome']) ?>';
        const ofertas = <?= (float)$item['total_ofertas'] ?>;
        const participacao = <?= (int)$item['total_biblias'] + (int)$item['total_revistas'] + (int)$item['total_visitantes'] ?>;
        
        if (!ofertasPorCongregacao[congNome]) {
            ofertasPorCongregacao[congNome] = 0;
            participacaoPorCongregacao[congNome] = 0;
        }
        ofertasPorCongregacao[congNome] += ofertas;
        participacaoPorCongregacao[congNome] += participacao;
    <?php endforeach; ?>
    
    // Gráfico de Ofertas
    const ctxOfertas = document.getElementById('graficoOfertas');
    if (ctxOfertas) {
        new Chart(ctxOfertas, {
            type: 'bar',
            data: {
                labels: Object.keys(ofertasPorCongregacao),
                datasets: [{
                    label: 'Total de Ofertas (R$)',
                    data: Object.values(ofertasPorCongregacao),
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#059669',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: (ctx) => `R$ ${ctx.raw.toFixed(2)}` } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Valor (R$)' } } }
            }
        });
    }
    
    // Gráfico de Participação
    const ctxParticipacao = document.getElementById('graficoParticipacao');
    if (ctxParticipacao) {
        new Chart(ctxParticipacao, {
            type: 'pie',
            data: {
                labels: Object.keys(participacaoPorCongregacao),
                datasets: [{
                    data: Object.values(participacaoPorCongregacao),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} itens` } }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Inicializar AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    }
});

// Recriar DataTable ao redimensionar
$(window).on('resize', function() {
    if ($(window).width() >= 768) {
        if ($.fn.DataTable.isDataTable('#tabelaRelatorio')) {
            $('#tabelaRelatorio').DataTable().destroy();
        }
        $('#tabelaRelatorio').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            order: [[1, 'asc'], [0, 'asc']],
            pageLength: 10,
            responsive: true
        });
    }
});
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>
