<?php
// Conectando ao banco de dados
require_once '../../config/conexao.php';
require_once '../../views/includes/header.php';

// Definir a localidade para português (Brasil)
setlocale(LC_TIME, 'pt_BR.utf8', 'pt_BR', 'portuguese');

// --- Captura e Validação dos Filtros ---
$congregacao_selecionada = $_GET['congregacao_id'] ?? '';
$classe_selecionada = $_GET['classe_id'] ?? '';
$mes_selecionado = $_GET['mes'] ?? date('m');
$ano_selecionado = $_GET['ano'] ?? date('Y');

// Determinar o trimestre atual baseado no mês selecionado
$trimestre_atual = 1;
if ($mes_selecionado >= 1 && $mes_selecionado <= 3) {
    $trimestre_atual = 1;
} elseif ($mes_selecionado >= 4 && $mes_selecionado <= 6) {
    $trimestre_atual = 2;
} elseif ($mes_selecionado >= 7 && $mes_selecionado <= 9) {
    $trimestre_atual = 3;
} elseif ($mes_selecionado >= 10 && $mes_selecionado <= 12) {
    $trimestre_atual = 4;
}

$nome_congregacao_selecionada = '';
$nome_classe_selecionada = '';

// Consulta para obter as congregações
$query_congs = "SELECT id, nome FROM congregacoes ORDER BY nome";
$result_congs = $pdo->query($query_congs);
$congregacoes = $result_congs->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obter as classes
$query_classes = "SELECT id, nome FROM classes ORDER BY nome";
$result_classes = $pdo->query($query_classes);
$classes = $result_classes->fetchAll(PDO::FETCH_ASSOC);

// Array com os meses em português
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// Gerar lista de anos (5 anos atrás até 2 anos à frente)
$ano_atual = date('Y');
$anos = range($ano_atual - 5, $ano_atual + 2);

// --- Query Principal com filtros e DISTINCT ---
$query = "SELECT DISTINCT 
            a.nome, 
            DAY(a.data_nascimento) AS dia,
            c.nome AS congregacao_nome,
            cl.nome AS classe_nome
          FROM alunos a
          INNER JOIN matriculas m ON a.id = m.aluno_id 
              AND m.status = 'ativo' 
              AND m.trimestre = :trimestre
          INNER JOIN congregacoes c ON m.congregacao_id = c.id
          INNER JOIN classes cl ON m.classe_id = cl.id
          WHERE MONTH(a.data_nascimento) = :mes 
            AND YEAR(a.data_nascimento) <= :ano
            AND a.data_nascimento != '0000-00-00'";

if ($congregacao_selecionada) {
    $query .= " AND m.congregacao_id = :congregacao_id";
}
if ($classe_selecionada) {
    $query .= " AND m.classe_id = :classe_id";
}
$query .= " ORDER BY DAY(a.data_nascimento), a.nome";

$result = $pdo->prepare($query);
$result->bindParam(':mes', $mes_selecionado, PDO::PARAM_INT);
$result->bindParam(':ano', $ano_selecionado, PDO::PARAM_INT);
$result->bindParam(':trimestre', $trimestre_atual, PDO::PARAM_INT);

if ($congregacao_selecionada) {
    $result->bindParam(':congregacao_id', $congregacao_selecionada, PDO::PARAM_INT);
    foreach ($congregacoes as $cong) {
        if ($cong['id'] == $congregacao_selecionada) {
            $nome_congregacao_selecionada = $cong['nome'];
            break;
        }
    }
}
if ($classe_selecionada) {
    $result->bindParam(':classe_id', $classe_selecionada, PDO::PARAM_INT);
    foreach ($classes as $classe) {
        if ($classe['id'] == $classe_selecionada) {
            $nome_classe_selecionada = $classe['nome'];
            break;
        }
    }
}
$result->execute();

// Organizando os aniversariantes por dia
$aniversariantes = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $aniversariantes[$row['dia']][] = [
        'nome' => $row['nome'],
        'congregacao' => $row['congregacao_nome'] ?? 'N/A',
        'classe' => $row['classe_nome'] ?? 'N/A'
    ];
}
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário de Aniversariantes</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables + Buttons CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            --success-gradient: linear-gradient(135deg, #27ae60 0%, #219653 100%);
            --danger-gradient: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            padding: 20px 15px 40px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-title {
            color: #2c3e50;
            font-weight: 700;
            position: relative;
            padding-bottom: 12px;
            margin-bottom: 1.75rem;
        }
        
        .dashboard-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.9em;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 30px;
        }
        
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: white;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        
        .form-select, .form-control {
            border-radius: 12px;
            border: 1px solid #ced4da;
            padding: 0.55rem 0.85rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 40px;
            padding: 0.55rem 1.2rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(52, 152, 219, 0.4);
        }
        
        .btn-outline-secondary {
            border-radius: 40px;
            padding: 0.55rem 1.2rem;
            font-weight: 500;
        }
        
        /* Tabela moderna */
        .table-aniversariantes thead th {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem 0.75rem;
        }
        
        .table-aniversariantes tbody td {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
            font-size: 0.95rem;
        }
        
        .table-aniversariantes .dia-badge {
            background: var(--danger-gradient);
            color: white;
            min-width: 46px;
            height: 46px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.15rem;
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        
        .table-aniversariantes .nome-destaque {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.05rem;
        }
        
        .table-aniversariantes .meta-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .table-aniversariantes .meta-info i {
            color: #3498db;
            margin-right: 6px;
            width: 18px;
            text-align: center;
        }
        
        .table-aniversariantes tbody tr {
            transition: background-color 0.2s;
        }
        
        .table-aniversariantes tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .table-footer {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* Botões de exportação personalizados */
        .btn-excel {
            background: linear-gradient(135deg, #27ae60, #1e8449);
            border: none;
            color: white;
            border-radius: 40px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .btn-excel:hover {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            transform: translateY(-1px);
        }
        .btn-pdf {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            color: white;
            border-radius: 40px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
        .btn-pdf:hover {
            background: linear-gradient(135deg, #f05b4c, #d62c1a);
            transform: translateY(-1px);
        }
        
        /* Responsividade moderna igual ao template de presenças */
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 90%;
            }
            .table-aniversariantes tbody td {
                padding: 1rem 0.9rem;
                font-size: 1rem;
            }
            .table-aniversariantes .dia-badge {
                min-width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
        }
        
        @media (max-width: 1199.98px) {
            .container-fluid {
                max-width: 95%;
            }
            .col-lg-5, .col-lg-2 {
                width: 100%;
                max-width: 100%;
            }
            .row.g-3 {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px 10px 30px;
            }
            .dashboard-title {
                font-size: 1.6rem;
            }
            .table-aniversariantes thead {
                display: none;
            }
            .table-aniversariantes tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 16px;
                padding: 1rem;
                background: white;
                box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            }
            .table-aniversariantes tbody tr td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.65rem 0;
                border: none;
                border-bottom: 1px dashed #eee;
            }
            .table-aniversariantes tbody tr td:last-child {
                border-bottom: none;
            }
            .table-aniversariantes tbody tr td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #2c3e50;
                margin-right: 1rem;
                text-transform: uppercase;
                font-size: 0.7rem;
                letter-spacing: 0.5px;
                min-width: 90px;
            }
            .btn-excel, .btn-pdf {
                padding: 0.3rem 0.8rem;
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-title {
                font-size: 1.4rem;
            }
            .table-aniversariantes tbody tr td::before {
                min-width: 70px;
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #7f8c8d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-xl-11 col-lg-12">
                
                <!-- Título com Badges -->
                <div class="text-center mb-4">
                    <h2 class="dashboard-title">
                        <i class="fas fa-birthday-cake me-2 text-primary"></i>
                        Calendário de Aniversariantes
                    </h2>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                        <span class="badge bg-primary bg-gradient">
                            <i class="far fa-calendar-alt me-1"></i> <?= $meses[$mes_selecionado] . ' ' . $ano_selecionado ?>
                        </span>
                        <?php if ($nome_congregacao_selecionada): ?>
                            <span class="badge bg-secondary bg-gradient">
                                <i class="fas fa-church me-1"></i> <?= htmlspecialchars($nome_congregacao_selecionada) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($nome_classe_selecionada): ?>
                            <span class="badge bg-info bg-gradient text-dark">
                                <i class="fas fa-users me-1"></i> <?= htmlspecialchars($nome_classe_selecionada) ?>
                            </span>
                        <?php endif; ?>
                        <span class="badge bg-success bg-gradient">
                            <i class="fas fa-layer-group me-1"></i> <?= $trimestre_atual ?>º Trimestre
                        </span>
                    </div>
                </div>

                <!-- Card de Filtros (moderno, igual ao relatório de presenças) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filtros de Pesquisa</h5>
                    </div>
                    <div class="card-body p-4">
                        <form class="row g-3 align-items-end" method="get">
                            <div class="col-lg-5">
                                <span class="section-label"><i class="far fa-calendar me-1"></i> Período</span>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="visually-hidden">Mês</label>
                                        <select name="mes" class="form-select">
                                            <?php foreach ($meses as $num => $nome_mes): ?>
                                                <option value="<?= $num ?>" <?= $num == $mes_selecionado ? 'selected' : '' ?>><?= $nome_mes ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="visually-hidden">Ano</label>
                                        <select name="ano" class="form-select">
                                            <?php foreach ($anos as $ano): ?>
                                                <option value="<?= $ano ?>" <?= $ano == $ano_selecionado ? 'selected' : '' ?>><?= $ano ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <span class="section-label"><i class="fas fa-map-marker-alt me-1"></i> Localização</span>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="visually-hidden">Congregação</label>
                                        <select name="congregacao_id" class="form-select">
                                            <option value="">Todas as congregações</option>
                                            <?php foreach ($congregacoes as $c): ?>
                                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $congregacao_selecionada ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="visually-hidden">Classe</label>
                                        <select name="classe_id" class="form-select">
                                            <option value="">Todas as classes</option>
                                            <?php foreach ($classes as $classe): ?>
                                                <option value="<?= $classe['id'] ?>" <?= $classe['id'] == $classe_selecionada ? 'selected' : '' ?>><?= htmlspecialchars($classe['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filtrar</button>
                                    <a href="?" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i> Limpar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card da Tabela com botões de exportação personalizados -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i> Aniversariantes do Mês</h5>
                        <div>
                            <button id="exportExcel" class="btn-excel me-2"><i class="fas fa-file-excel me-1"></i> Excel</button>
                            <button id="exportPdf" class="btn-pdf"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive px-3 pb-3 pt-3">
                            <table id="tabelaAniversariantes" class="table table-aniversariantes table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>Dia</th>
                                        <th>Nome</th>
                                        <th>Classe</th>
                                        <th>Congregação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_aniversariantes = 0;
                                    if (!empty($aniversariantes)) {
                                        foreach ($aniversariantes as $dia => $dados) {
                                            foreach ($dados as $item) {
                                                $dia_formatado = str_pad($dia, 2, '0', STR_PAD_LEFT);
                                                echo "<tr>";
                                                echo "<td data-label='Dia' class='text-center'><span class='dia-badge'>{$dia_formatado}</span></td>";
                                                echo "<td data-label='Nome' class='nome-destaque'>" . htmlspecialchars($item['nome']) . "</td>";
                                                echo "<td data-label='Classe' class='meta-info'><i class='fas fa-users'></i> " . htmlspecialchars($item['classe']) . "</td>";
                                                echo "<td data-label='Congregação' class='meta-info'><i class='fas fa-church'></i> " . htmlspecialchars($item['congregacao']) . "</td>";
                                                echo "</tr>";
                                                $total_aniversariantes++;
                                            }
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'><div class='empty-state'><i class='fas fa-calendar-times'></i><p>Nenhum aniversariante encontrado</p><small>Ajuste os filtros para visualizar os resultados</small></div></td></tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot class="table-footer">
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <i class="fas fa-users me-2"></i>
                                            Total de Aniversariantes: 
                                            <span class="text-primary fs-5 fw-bold"><?= $total_aniversariantes ?></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
            <?php if (!empty($aniversariantes)): ?>
            // Inicializa DataTable com botões padrão (ocultos)
            var table = $('#tabelaAniversariantes').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        className: 'd-none',
                        title: 'Aniversariantes_<?= $meses[$mes_selecionado] . '_' . $ano_selecionado ?>',
                        exportOptions: { columns: [0,1,2,3] }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'd-none',
                        title: 'Aniversariantes_<?= $meses[$mes_selecionado] . '_' . $ano_selecionado ?>',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: [0,1,2,3] },
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
                order: [[0, 'asc']],
                initComplete: function() {
                    $('.dt-buttons').hide(); // esconde botões originais
                }
            });

            // Aciona os botões personalizados
            $('#exportExcel').click(function() {
                table.button('.buttons-excel').trigger();
            });
            $('#exportPdf').click(function() {
                table.button('.buttons-pdf').trigger();
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>