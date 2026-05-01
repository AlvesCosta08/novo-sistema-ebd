<?php
require_once('../../config/conexao.php');
include('../../views/includes/header.php');

// --- Funções ---
function calcularPeriodoTrimestre(int $trimestre): array {
    $ano = date('Y');
    $mes_inicio = ($trimestre - 1) * 3 + 1;
    $mes_fim = $mes_inicio + 2;
    $data_inicio = "$ano-" . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . "-01";
    $ultimo_dia = date("t", strtotime("$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-01"));
    $data_fim = "$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-$ultimo_dia";
    return [$data_inicio, $data_fim];
}

// --- Filtros ---
$congregacao_id = $_GET['congregacao_id'] ?? '';
$classe_id = $_GET['classe_id'] ?? '';
$trimestre = $_GET['trimestre'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Verifica se o formulário foi submetido
$form_submetido = !empty($_GET);

// Inicializa variáveis
$alunos = [];
$top_presencas = [];
$top_faltas = [];
$trimestre_sem_dados = false;

// Definir período
if (!empty($trimestre)) {
    [$data_inicio, $data_fim] = calcularPeriodoTrimestre($trimestre);
    
    // Verificar se existe alguma chamada para este trimestre
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM chamadas WHERE data BETWEEN ? AND ?");
    $stmt->execute([$data_inicio, $data_fim]);
    $result = $stmt->fetch();
    
    if ($result['total'] == 0) {
        $trimestre_sem_dados = true;
    }
} else {
    $data_inicio = $data_inicio ?: date('Y-m-01');
    $data_fim = $data_fim ?: date('Y-m-d');
}

// --- Consulta principal (com cálculos corrigidos) ---
if (!$trimestre_sem_dados && $form_submetido) {
    $sql = "
        SELECT 
            a.id,
            a.nome AS aluno,
            c.nome AS classe,
            cg.nome AS congregacao,
            COALESCE(total_chamadas.total_aulas, 0) AS total_aulas,
            COALESCE(presencas_aluno.total_presencas, 0) AS presencas,
            -- Cálculo de faltas: total de chamadas para a classe - presenças do aluno
            (COALESCE(total_chamadas.total_aulas, 0) - COALESCE(presencas_aluno.total_presencas, 0)) AS faltas,
            CASE 
                WHEN COALESCE(total_chamadas.total_aulas, 0) > 0 THEN
                    ROUND(
                        (COALESCE(presencas_aluno.total_presencas, 0) * 100.0) / total_chamadas.total_aulas,
                        1
                    )
                ELSE 0
            END AS frequencia
        FROM alunos a
        INNER JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
        INNER JOIN classes c ON c.id = m.classe_id
        INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
        -- Subquery: total de chamadas feitas para a classe no período
        LEFT JOIN (
            SELECT 
                ch.classe_id,
                COUNT(*) AS total_aulas
            FROM chamadas ch
            WHERE ch.data BETWEEN :inicio AND :fim
            GROUP BY ch.classe_id
        ) total_chamadas ON total_chamadas.classe_id = c.id
        -- Subquery: presenças do aluno nas chamadas da classe no período
        LEFT JOIN (
            SELECT 
                p.aluno_id,
                SUM(CASE WHEN p.presente IN ('presente', 'justificado') THEN 1 ELSE 0 END) AS total_presencas
            FROM presencas p
            INNER JOIN chamadas ch ON ch.id = p.chamada_id
            WHERE ch.data BETWEEN :inicio AND :fim
            GROUP BY p.aluno_id
        ) presencas_aluno ON presencas_aluno.aluno_id = a.id
        WHERE 1=1";

    $params = [':inicio' => $data_inicio, ':fim' => $data_fim];

    if (!empty($congregacao_id)) {
        $sql .= " AND cg.id = :congregacao";
        $params[':congregacao'] = $congregacao_id;
    }

    if (!empty($classe_id)) {
        $sql .= " AND c.id = :classe";
        $params[':classe'] = $classe_id;
    }

    $sql .= " ORDER BY frequencia DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rankings
    if (count($alunos) > 0) {
        $top_presencas = array_slice($alunos, 0, 5);
        $top_faltas = array_reverse(array_slice($alunos, -5, 5));
    }
}

// Dropdowns
$congs = $pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome")->fetchAll();
$classes = $pdo->query("SELECT id, nome FROM classes ORDER BY nome")->fetchAll();

// Valores padrão para os filtros
$data_inicio = $data_inicio ?: date('Y-m-01');
$data_fim = $data_fim ?: date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Relatório de Presenças por Aluno</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .dashboard-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #3498db;
            color: white;
            font-weight: 500;
        }
        .filter-section {
            margin-bottom: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .no-data-message {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-top: 1rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table th {
            background-color: #e9ecef;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        /* --- Psicologia das Cores --- */
        .summary-card-presencas .summary-value { color: #27ae60; }
        .summary-card-faltas .summary-value { color: #e74c3c; }
        .summary-card-frequencia .summary-value { color: #2ecc71; }
        .summary-card-alunos .summary-value { color: #3498db; }
        .summary-card-periodo .summary-value { color: #9b59b6; }

        .summary-card {
            text-align: center;
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .summary-label {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        /* Células da tabela */
        .cell-aluno { font-weight: 600; }
        .cell-presencas { color: #27ae60; font-weight: bold; }
        .cell-faltas { color: #e74c3c; font-weight: bold; }
        .cell-frequencia { color: #2ecc71; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-xl-11">
                <h2 class="text-center dashboard-title"><i class="fas fa-user-graduate me-2"></i>Relatório de Presenças por Aluno</h2>

                <!-- Filtros -->
                <div class="filter-section">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <i class="fas fa-filter me-2"></i>
                            <span>Filtros</span>
                        </div>
                        <div class="card-body">
                            <form class="row g-3" method="get">
                                <div class="col-md-3">
                                    <label for="congregacao_id" class="form-label">Congregação</label>
                                    <select name="congregacao_id" id="congregacao_id" class="form-select">
                                        <option value="">Todas</option>
                                        <?php foreach ($congs as $c) : ?>
                                            <option value="<?= $c['id'] ?>" <?= ($congregacao_id == $c['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="classe_id" class="form-label">Classe</label>
                                    <select name="classe_id" id="classe_id" class="form-select">
                                        <option value="">Todas</option>
                                        <?php foreach ($classes as $cl) : ?>
                                            <option value="<?= $cl['id'] ?>" <?= ($classe_id == $cl['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cl['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="trimestre" class="form-label">Trimestre</label>
                                    <select name="trimestre" id="trimestre" class="form-select">
                                        <option value="">Selecione</option>
                                        <?php for ($i = 1; $i <= 4; $i++) : ?>
                                            <option value="<?= $i ?>" <?= ($trimestre == $i) ? 'selected' : '' ?>><?= $i ?>º</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="data_inicio" class="form-label">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" <?= $trimestre ? 'readonly' : '' ?> />
                                </div>
                                <div class="col-md-2">
                                    <label for="data_fim" class="form-label">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" <?= $trimestre ? 'readonly' : '' ?> />
                                </div>
                                <div class="col-md-12 d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo -->
                <?php if (!$form_submetido): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="no-data-message">
                                <h5><i class="fas fa-info-circle text-info"></i> Nenhum dado para exibir</h5>
                                <p>Por favor, selecione os filtros desejados e clique em "Filtrar".</p>
                            </div>
                        </div>
                    </div>
                <?php elseif ($trimestre_sem_dados): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        O trimestre selecionado (<?= $trimestre ?>º) ainda não possui registros de chamadas.
                    </div>
                <?php elseif (empty($alunos)): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="no-data-message">
                                <h5><i class="fas fa-exclamation-triangle text-warning"></i> Nenhum resultado encontrado</h5>
                                <p>Não foram encontrados registros com os filtros selecionados.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php
                    $total_alunos = count($alunos);
                    $total_presencas = array_sum(array_column($alunos, 'presencas'));
                    $total_faltas = array_sum(array_column($alunos, 'faltas'));
                    $media_frequencia = $total_alunos > 0 ? round(array_sum(array_column($alunos, 'frequencia')) / $total_alunos, 1) : 0;
                    ?>

                    <!-- Cards de Resumo -->
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <i class="fas fa-chart-pie me-2"></i>
                            <span>Resumo Geral</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 summary-card summary-card-alunos">
                                    <div class="summary-value"><?= $total_alunos ?></div>
                                    <div class="summary-label">Alunos</div>
                                </div>
                                <div class="col-md-2 summary-card summary-card-presencas">
                                    <div class="summary-value"><?= $total_presencas ?></div>
                                    <div class="summary-label">Presenças</div>
                                </div>
                                <div class="col-md-2 summary-card summary-card-faltas">
                                    <div class="summary-value"><?= $total_faltas ?></div>
                                    <div class="summary-label">Faltas</div>
                                </div>
                                <div class="col-md-2 summary-card summary-card-frequencia">
                                    <div class="summary-value"><?= $media_frequencia ?>%</div>
                                    <div class="summary-label">Média Freq.</div>
                                </div>
                                <div class="col-md-4 summary-card summary-card-periodo">
                                    <div class="summary-value"><?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?></div>
                                    <div class="summary-label">Período</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rankings (opcional: pode ser removido se quiser foco total na tabela) -->
                    <?php if (!empty($top_presencas) || !empty($top_faltas)): ?>
                    <div class="row mb-4">
                        <!-- Top Presenças -->
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white d-flex align-items-center">
                                    <i class="fas fa-trophy me-2"></i>
                                    <span>Top 5 Presenças</span>
                                </div>
                                <div class="card-body">
                                    <?php foreach($top_presencas as $i => $aluno): ?>
                                        <div class="mb-2 pb-2 border-bottom">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= $i+1 ?>. <?= htmlspecialchars($aluno['aluno']) ?></strong>
                                                <span class="cell-presencas"><?= $aluno['presencas'] ?></span>
                                            </div>
                                            <small><?= htmlspecialchars($aluno['classe']) ?> — <?= $aluno['frequencia'] ?>%</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Top Faltas -->
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span>Top 5 Faltas</span>
                                </div>
                                <div class="card-body">
                                    <?php foreach($top_faltas as $i => $aluno): ?>
                                        <div class="mb-2 pb-2 border-bottom">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= $i+1 ?>. <?= htmlspecialchars($aluno['aluno']) ?></strong>
                                                <span class="cell-faltas"><?= $aluno['faltas'] ?></span>
                                            </div>
                                            <small><?= htmlspecialchars($aluno['classe']) ?> — <?= $aluno['frequencia'] ?>%</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tabela de Alunos -->
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-table me-2"></i>
                                <span>Relação Completa de Alunos</span>
                            </div>
                            <div>
                                <button id="exportExcel" class="btn btn-success btn-sm me-1"><i class="fas fa-file-excel me-1"></i>Excel</button>
                                <button id="exportPdf" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i>PDF</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabelaAlunos" class="table table-bordered table-striped table-hover">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>Aluno</th>
                                            <th>Classe</th>
                                            <th>Congregação</th>
                                            <th>Presenças</th>
                                            <th>Faltas</th>
                                            <th>Frequência</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($alunos as $aluno): ?>
                                            <tr>
                                                <td class="cell-aluno"><?= htmlspecialchars($aluno['aluno']) ?></td>
                                                <td><?= htmlspecialchars($aluno['classe']) ?></td>
                                                <td><?= htmlspecialchars($aluno['congregacao']) ?></td>
                                                <td class="text-center cell-presencas"><?= $aluno['presencas'] ?></td>
                                                <td class="text-center cell-faltas"><?= $aluno['faltas'] ?></td>
                                                <td class="text-center cell-frequencia"><?= $aluno['frequencia'] ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-secondary fw-bold text-center">
                                        <tr>
                                            <td colspan="3" class="text-end">Totais Gerais:</td>
                                            <td class="cell-presencas"><?= $total_presencas ?></td>
                                            <td class="cell-faltas"><?= $total_faltas ?></td>
                                            <td class="cell-frequencia"><?= $media_frequencia ?>%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
    $(document).ready(function() {
        <?php if (!empty($alunos)): ?>
        var table = $('#tabelaAlunos').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Relatorio_Presencas_Alunos',
                    exportOptions: { columns: [0,1,2,3,4,5] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Relatorio_Presencas_Alunos',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: [0,1,2,3,4,5] },
                    customize: function(doc) {
                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 10,
                            color: 'white',
                            fillColor: '#3498db',
                            alignment: 'center'
                        };
                        doc.defaultStyle.fontSize = 9;
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            },
            order: [[5, 'desc']],
            initComplete: function() {
                // Esconde botões originais do DataTables
                $('.dt-buttons').hide();
            }
        });

        $('#exportExcel').click(function() {
            table.button('.buttons-excel').trigger();
        });
        $('#exportPdf').click(function() {
            table.button('.buttons-pdf').trigger();
        });
        <?php endif; ?>

        // Atualiza datas ao mudar trimestre
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
                }
                $('input[name="data_inicio"]').val(inicio).prop('readonly', true);
                $('input[name="data_fim"]').val(fim).prop('readonly', true);
            } else {
                $('input[name="data_inicio"], input[name="data_fim"]').prop('readonly', false);
            }
        });
    });
    </script>
</body>
</html>