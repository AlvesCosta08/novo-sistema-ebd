<?php
// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
require_once __DIR__ . '/../../auth/valida_sessao.php';

// Configurar título da página
$pageTitle = 'Relatório Geral de Presenças';

// Incluir header padronizado
require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../../config/conexao.php';

// Filtros
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$congregacao_id = $_GET['congregacao_id'] ?? '';
$trimestre = $_GET['trimestre'] ?? '';

// Query principal com subqueries e total de aulas
$sql = "SELECT 
    c.id AS classe_id,
    c.nome AS classe_nome,
    cg.nome AS congregacao_nome,
    COALESCE(m.trimestre, '') AS trimestre,
    COUNT(DISTINCT m.aluno_id) AS total_matriculados,

    COALESCE(pres.total_presencas, 0) AS total_presencas,
    COALESCE(pres.total_faltas, 0) AS total_faltas,

    COALESCE(cham.total_visitantes, 0) AS total_visitantes,
    COALESCE(cham.total_biblias, 0) AS total_biblias,
    COALESCE(cham.total_revistas, 0) AS total_revistas,
    COALESCE(cham.total_ofertas, 0) AS total_ofertas,

    COALESCE(aulas.total_aulas, 0) AS total_aulas

FROM classes c

LEFT JOIN matriculas m ON m.classe_id = c.id AND m.status = 'ativo'
LEFT JOIN congregacoes cg ON cg.id = m.congregacao_id

LEFT JOIN (
    SELECT 
        ch.classe_id,
        SUM(ch.total_visitantes) AS total_visitantes,
        SUM(ch.total_biblias) AS total_biblias,
        SUM(ch.total_revistas) AS total_revistas,
        SUM(CAST(ch.oferta_classe AS DECIMAL(10,2))) AS total_ofertas
    FROM chamadas ch
    WHERE ch.data BETWEEN :data_inicio AND :data_fim
    GROUP BY ch.classe_id
) cham ON cham.classe_id = c.id

LEFT JOIN (
    SELECT 
        m.classe_id,
        m.trimestre,
        SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) AS total_presencas,
        SUM(CASE WHEN p.presente = 'ausente' OR p.presente = '' THEN 1 ELSE 0 END) AS total_faltas
    FROM matriculas m
    INNER JOIN chamadas ch ON ch.classe_id = m.classe_id AND ch.data BETWEEN :data_inicio AND :data_fim
    INNER JOIN presencas p ON p.chamada_id = ch.id AND p.aluno_id = m.aluno_id
    WHERE m.status = 'ativo'
    GROUP BY m.classe_id, m.trimestre
) pres ON pres.classe_id = c.id AND pres.trimestre = m.trimestre

LEFT JOIN (
    SELECT 
        classe_id,
        COUNT(*) AS total_aulas
    FROM chamadas
    WHERE data BETWEEN :data_inicio AND :data_fim
    GROUP BY classe_id
) aulas ON aulas.classe_id = c.id

WHERE 1=1
";

if (!empty($congregacao_id)) {
    $sql .= " AND m.congregacao_id = :congregacao_id";
}
if (!empty($trimestre)) {
    $sql .= " AND m.trimestre = :trimestre";
}

$sql .= " GROUP BY c.id, c.nome, cg.nome, m.trimestre, aulas.total_aulas ORDER BY c.nome";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':data_inicio', $data_inicio);
$stmt->bindParam(':data_fim', $data_fim);
if (!empty($congregacao_id)) {
    $stmt->bindParam(':congregacao_id', $congregacao_id);
}
if (!empty($trimestre)) {
    $stmt->bindParam(':trimestre', $trimestre);
}

$stmt->execute();
$relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Congregações para filtro
$congs = $pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Inicializar totais
$total_geral_matriculados = 0;
$total_geral_presencas = 0;
$total_geral_faltas = 0;
$total_geral_visitantes = 0;
$total_geral_biblias = 0;
$total_geral_revistas = 0;
$total_geral_ofertas = 0;
$total_chamadas_geral = 0;

foreach ($relatorios as $row) {
    $total_geral_matriculados += $row['total_matriculados'];
    $total_geral_presencas += $row['total_presencas'];
    $total_geral_faltas += $row['total_faltas'];
    $total_geral_visitantes += $row['total_visitantes'];
    $total_geral_biblias += $row['total_biblias'];
    $total_geral_revistas += $row['total_revistas'];
    $total_geral_ofertas += $row['total_ofertas'];
    $total_chamadas_geral += ((int)$row['total_aulas']) * ((int)$row['total_matriculados']);
}

$presenca_visitante_geral = $total_geral_presencas + $total_geral_visitantes;
$frequencia_geral = ($total_chamadas_geral > 0) ? ($presenca_visitante_geral / $total_chamadas_geral) * 100 : 0;
?>

<!-- Conteúdo principal -->
<div class="container-fluid px-4">
    <!-- Cabeçalho da Página -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-down">
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
                        <a href="index.php" style="color: var(--primary-600);">
                            <i class="fas fa-chart-line me-1"></i> Relatórios
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-chart-simple me-1"></i> Geral
                    </li>
                </ol>
            </nav>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Visão geral consolidada de presenças, recursos e frequência por classe
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
                <div class="stat-value"><?= number_format($total_geral_matriculados) ?></div>
                <div class="stat-label">Total Matrículas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value"><?= number_format($total_geral_presencas) ?></div>
                <div class="stat-label">Total Presenças</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-value"><?= number_format($total_geral_faltas) ?></div>
                <div class="stat-label">Total Faltas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value"><?= number_format($total_geral_visitantes) ?></div>
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
                <div class="stat-value"><?= number_format($total_geral_biblias) ?></div>
                <div class="stat-label">Bíblias</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-magazine"></i>
                </div>
                <div class="stat-value"><?= number_format($total_geral_revistas) ?></div>
                <div class="stat-label">Revistas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">R$ <?= number_format($total_geral_ofertas, 2, ',', '.') ?></div>
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
        <div class="card-header-modern bg-primary">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i> Filtros de Pesquisa
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-4">
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
                <table id="tabelaGeral" class="custom-table mb-0" style="width:100%">
                    <thead>
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
                            <th class="text-center">Frequência</th>
                        </td>
                    </thead>
                    <tbody>
                        <?php foreach ($relatorios as $row):
                            $total_chamadas = (int)$row['total_aulas'];
                            $total_matriculados = (int)$row['total_matriculados'];
                            $presenca_visitante = (int)$row['total_presencas'] + (int)$row['total_visitantes'];
                            
                            if ($total_matriculados > 0 && $total_chamadas > 0) {
                                $frequencia = ($presenca_visitante / ($total_matriculados * $total_chamadas)) * 100;
                            } else {
                                $frequencia = 0;
                            }
                            
                            // Cor da frequência
                            $frequenciaCor = '';
                            if ($frequencia >= 75) {
                                $frequenciaCor = 'text-success';
                            } elseif ($frequencia >= 50) {
                                $frequenciaCor = 'text-warning';
                            } else {
                                $frequenciaCor = 'text-danger';
                            }
                        ?>
                            <tr>
                                <td><i class="fas fa-chalkboard-user me-2" style="color: var(--primary-500);"></i><?= htmlspecialchars($row['classe_nome']) ?></td>
                                <td><i class="fas fa-church me-2" style="color: var(--primary-500);"></i><?= htmlspecialchars($row['congregacao_nome']) ?></td>
                                <td class="text-center"><span class="badge-trimestre"><?= htmlspecialchars($row['trimestre']) ?>º Trim.</span></td>
                                <td class="text-center fw-semibold"><?= number_format($row['total_matriculados']) ?></td>
                                <td class="text-center fw-semibold text-success"><?= number_format($row['total_presencas']) ?></td>
                                <td class="text-center fw-semibold text-danger"><?= number_format($row['total_faltas']) ?></td>
                                <td class="text-center"><?= number_format($row['total_visitantes']) ?></td>
                                <td class="text-center"><?= number_format($row['total_biblias']) ?></td>
                                <td class="text-center"><?= number_format($row['total_revistas']) ?></td>
                                <td class="text-end fw-bold text-success">R$ <?= number_format($row['total_ofertas'], 2, ',', '.') ?></td>
                                <td class="text-center fw-bold <?= $frequenciaCor ?>"><?= number_format($frequencia, 1, ',', '.') ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">TOTAIS GERAIS:</td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format($total_geral_matriculados) ?></td>
                            <td class="text-center fw-bold bg-success bg-opacity-10 text-success"><?= number_format($total_geral_presencas) ?></td>
                            <td class="text-center fw-bold bg-danger bg-opacity-10 text-danger"><?= number_format($total_geral_faltas) ?></td>
                            <td class="text-center fw-bold bg-info bg-opacity-10"><?= number_format($total_geral_visitantes) ?></td>
                            <td class="text-center fw-bold bg-warning bg-opacity-10"><?= number_format($total_geral_biblias) ?></td>
                            <td class="text-center fw-bold bg-secondary bg-opacity-10"><?= number_format($total_geral_revistas) ?></td>
                            <td class="text-end fw-bold bg-success bg-opacity-10 text-success">R$ <?= number_format($total_geral_ofertas, 2, ',', '.') ?></td>
                            <td class="text-center fw-bold bg-primary bg-opacity-10"><?= number_format($frequencia_geral, 1, ',', '.') ?>%</td>
                        </tr>
                    </tfoot>
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
/* Estilos específicos para o relatório geral */
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
    <?php if (!empty($relatorios)): ?>
    var table = $('#tabelaGeral').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn-excel',
                title: 'Relatorio_Geral_Presencas',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv me-1"></i> CSV',
                className: 'btn-csv',
                title: 'Relatorio_Geral_Presencas',
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,10] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn-pdf',
                title: 'Relatorio_Geral_Presencas',
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
        order: [[0, 'asc']],
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