<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Relatório Consolidado de Classes';

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';

// Conexão com o banco de dados
require_once __DIR__ . '/../../config/conexao.php';

function formatarMoeda($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

// Capturar filtros
$trimestre_filtro = $_GET['trimestre'] ?? '';
$congregacao_filtro = $_GET['congregacao'] ?? '';

// Totais gerais
$totais = [
    'matriculados' => 0,
    'presentes' => 0,
    'ausentes' => 0,
    'justificados' => 0,
    'biblias' => 0,
    'revistas' => 0,
    'visitantes' => 0,
    'oferta' => 0
];

$sql = "SELECT 
            cg.nome AS congregacao,
            cl.nome AS classe,
            m.trimestre,
            COUNT(DISTINCT m.aluno_id) AS matriculados,
            COUNT(DISTINCT CASE WHEN p.presente = 'presente' THEN p.aluno_id END) AS presentes,
            COUNT(DISTINCT CASE WHEN p.presente = 'ausente' THEN p.aluno_id END) AS ausentes,
            COUNT(DISTINCT CASE WHEN p.presente = 'justificado' THEN p.aluno_id END) AS justificados,
            COALESCE(SUM(ch.total_biblias), 0) AS biblias,
            COALESCE(SUM(ch.total_revistas), 0) AS revistas,
            COALESCE(SUM(ch.total_visitantes), 0) AS visitantes,
            COALESCE(SUM(ch.oferta_classe), 0) AS oferta
        FROM congregacoes cg
        JOIN matriculas m ON m.congregacao_id = cg.id
        JOIN classes cl ON cl.id = m.classe_id
        LEFT JOIN chamadas ch ON ch.classe_id = cl.id
        LEFT JOIN presencas p ON p.chamada_id = ch.id
        WHERE m.status = 'ativo'";

$params = [];

if (!empty($trimestre_filtro)) {
    $sql .= " AND m.trimestre = :trimestre";
    $params[':trimestre'] = $trimestre_filtro;
}

if (!empty($congregacao_filtro)) {
    $sql .= " AND cg.nome LIKE :congregacao";
    $params[':congregacao'] = '%' . $congregacao_filtro . '%';
}

$sql .= " GROUP BY cg.nome, cl.nome, m.trimestre ORDER BY cg.nome, cl.nome";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totais
foreach ($dados as $linha) {
    $totais['matriculados'] += $linha['matriculados'];
    $totais['presentes'] += $linha['presentes'];
    $totais['ausentes'] += $linha['ausentes'];
    $totais['justificados'] += $linha['justificados'];
    $totais['biblias'] += $linha['biblias'];
    $totais['revistas'] += $linha['revistas'];
    $totais['visitantes'] += $linha['visitantes'];
    $totais['oferta'] += $linha['oferta'];
}
?>

<!-- Conteúdo principal -->
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
        <div>
            <button onclick="window.print()" class="btn btn-modern btn-outline-secondary">
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
                <div class="stat-value"><?= number_format($totais['matriculados']) ?></div>
                <div class="stat-label">Total Matrículas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['presentes']) ?></div>
                <div class="stat-label">Total Presenças</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['ausentes']) ?></div>
                <div class="stat-label">Total Ausências</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['justificados']) ?></div>
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
                <div class="stat-value"><?= number_format($totais['biblias']) ?></div>
                <div class="stat-label">Total Bíblias</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-magazine"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['revistas']) ?></div>
                <div class="stat-label">Total Revistas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value"><?= number_format($totais['visitantes']) ?></div>
                <div class="stat-label">Visitantes</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value"><?= formatarMoeda($totais['oferta']) ?></div>
                <div class="stat-label">Total Ofertas</div>
            </div>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-4">
                <div class="col-12 col-md-4">
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
                
                <div class="col-12 col-md-4">
                    <label class="form-label">
                        <i class="fas fa-church me-1 text-primary"></i> Congregação
                    </label>
                    <input type="text" name="congregacao" class="form-control" 
                           placeholder="Nome da congregação" 
                           value="<?= htmlspecialchars($congregacao_filtro) ?>">
                </div>
                
                <div class="col-12 col-md-4 d-flex align-items-end">
                    <div class="d-flex gap-2 flex-wrap w-100">
                        <button type="submit" class="btn btn-modern btn-modern-primary flex-grow-1">
                            <i class="fas fa-search me-2"></i> Filtrar
                        </button>
                        <a href="relatorio_consolidado.php" class="btn btn-modern btn-outline-secondary">
                            <i class="fas fa-eraser me-2"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Dados Consolidados -->
    <div class="modern-card" data-aos="fade-up" data-aos-delay="300">
        <div class="card-header-modern bg-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 text-white">
                <i class="fas fa-table me-2"></i> Dados Consolidados
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabelaConsolidada" class="custom-table mb-0" style="width:100%">
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
                                    <td class="text-center fw-semibold"><?= number_format($linha['matriculados']) ?></td>
                                    <td class="text-center fw-semibold text-success"><?= number_format($linha['presentes']) ?></td>
                                    <td class="text-center fw-semibold text-danger"><?= number_format($linha['ausentes']) ?></td>
                                    <td class="text-center fw-semibold text-warning"><?= number_format($linha['justificados']) ?></td>
                                    <td class="text-center"><?= number_format($linha['biblias']) ?></td>
                                    <td class="text-center"><?= number_format($linha['revistas']) ?></td>
                                    <td class="text-center"><?= number_format($linha['visitantes']) ?></td>
                                    <td class="text-end fw-bold text-success"><?= formatarMoeda($linha['oferta']) ?></td>
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
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format($totais['matriculados']) ?></td>
                            <td class="text-center fw-bold bg-success bg-opacity-10 text-success"><?= number_format($totais['presentes']) ?></td>
                            <td class="text-center fw-bold bg-danger bg-opacity-10 text-danger"><?= number_format($totais['ausentes']) ?></td>
                            <td class="text-center fw-bold bg-warning bg-opacity-10 text-warning"><?= number_format($totais['justificados']) ?></td>
                            <td class="text-center fw-bold bg-info bg-opacity-10"><?= number_format($totais['biblias']) ?></td>
                            <td class="text-center fw-bold bg-secondary bg-opacity-10"><?= number_format($totais['revistas']) ?></td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format($totais['visitantes']) ?></td>
                            <td class="text-end fw-bold bg-success bg-opacity-10 text-success"><?= formatarMoeda($totais['oferta']) ?></td>
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
                <strong class="d-block mb-1">Análise de Dados:</strong>
                <span>Este relatório consolida todas as informações por trimestre. Utilize os filtros para focar em períodos específicos e acompanhe o crescimento das classes.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para o relatório consolidado */
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

/* Alertas personalizados */
.alert-info-ebd {
    background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
    border-left: 4px solid var(--info);
    border-radius: 12px;
    padding: 1rem 1.25rem;
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

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--gradient-primary) !important;
    border: none !important;
    color: white !important;
}

/* Print styles */
@media print {
    .navbar, .breadcrumb, .btn-modern, .alert-ebd, .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_paginate,
    .card-header-modern .d-flex .btn {
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
    
    .card-header-modern {
        background: #2c3e50 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
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
    
    .table-footer td {
        font-size: 0.8rem;
    }
}
</style>

<script>
$(document).ready(function() {
    <?php if (!empty($dados)): ?>
    // Inicializar DataTable
    var table = $('#tabelaConsolidada').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn-excel',
                title: 'Relatorio_Consolidado',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv me-1"></i> CSV',
                className: 'btn-csv',
                title: 'Relatorio_Consolidado',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn-pdf',
                title: 'Relatorio_Consolidado',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] },
                customize: function(doc) {
                    doc.styles.tableHeader = {
                        bold: true,
                        fontSize: 10,
                        color: 'white',
                        fillColor: '#3b82f6',
                        alignment: 'center'
                    };
                    doc.defaultStyle.fontSize = 9;
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-1"></i> Imprimir',
                className: 'btn-print',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            }
        ],
        order: [[0, 'asc'], [1, 'asc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        responsive: true,
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3');
        }
    });
    
    // Estilizar os botões do DataTable
    $('.dt-buttons').addClass('d-flex gap-2 mb-3');
    $('.buttons-excel').addClass('btn-modern').css({'background': '#27ae60', 'color': 'white', 'border': 'none'});
    $('.buttons-csv').addClass('btn-modern').css({'background': '#3498db', 'color': 'white', 'border': 'none'});
    $('.buttons-pdf').addClass('btn-modern').css({'background': '#e74c3c', 'color': 'white', 'border': 'none'});
    $('.buttons-print').addClass('btn-modern').css({'background': '#7f8c8d', 'color': 'white', 'border': 'none'});
    
    // Mover botões para o local desejado
    $('.dt-buttons').appendTo('.card-header-modern .d-flex');
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
</script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>