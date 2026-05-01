<?php
require_once('../../config/conexao.php');
require_once('../../views/includes/header.php');

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
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Relatório Geral</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- DataTables + Buttons -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet" />

    <style>
        .dataTables_wrapper .row {
            width: 100% !important;
        }

        table.dataTable {
            width: 100% !important;
        }

        .container {
            max-width: 100% !important;
        }

        .table-responsive {
            overflow-x: visible !important;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container my-4">
        <h3 class="text-center mb-4">📊 Relatório Geral de Presenças</h3>

        <!-- Filtros -->
        <form class="row g-2 mb-4" method="get">
            <div class="col-md-3">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" />
            </div>
            <div class="col-md-3">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" />
            </div>
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
            <div class="col-md-2">
                <label for="trimestre" class="form-label">Trimestre</label>
                <select name="trimestre" id="trimestre" class="form-select">
                    <option value="">Todos</option>
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                        <option value="<?= $i ?>" <?= ($trimestre == $i) ? 'selected' : '' ?>><?= $i ?>º</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <!-- Tabela -->
        <div class="table-responsive">
            <table id="tabela" class="table table-bordered table-striped table-hover nowrap w-100">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Classe</th>
                        <th>Congregação</th>
                        <th>Trimestre</th>
                        <th>Matriculados</th>
                        <th>Presentes</th>
                        <th>Faltas</th>
                        <th>Visitantes</th>
                        <th>Bíblias</th>
                        <th>Revistas</th>
                        <th>Ofertas (R$)</th>
                        <th>Frequência</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_geral_matriculados = 0;
                    $total_geral_presencas = 0;
                    $total_geral_faltas = 0;
                    $total_geral_visitantes = 0;
                    $total_geral_biblias = 0;
                    $total_geral_revistas = 0;
                    $total_geral_ofertas = 0;
                    $total_geral_assistencia = 0;

                    foreach ($relatorios as $row) :
                        $total_geral_matriculados += $row['total_matriculados'];
                        $total_geral_presencas += $row['total_presencas'];
                        $total_geral_faltas += $row['total_faltas'];
                        $total_geral_visitantes += $row['total_visitantes'];
                        $total_geral_biblias += $row['total_biblias'];
                        $total_geral_revistas += $row['total_revistas'];
                        $total_geral_ofertas += $row['total_ofertas'];
                        $total_geral_assistencia += $row['total_presencas'] + $row['total_visitantes'];

                        $total_chamadas = (int)$row['total_aulas'];
                        $total_matriculados = (int)$row['total_matriculados'];
                        $presenca_visitante = (int)$row['total_presencas'] + (int)$row['total_visitantes'];

                        if ($total_matriculados > 0 && $total_chamadas > 0) {
                            $frequencia = ($presenca_visitante / ($total_matriculados * $total_chamadas)) * 100;
                        } else {
                            $frequencia = 0;
                        }
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['classe_nome']) ?></td>
                            <td><?= htmlspecialchars($row['congregacao_nome']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['trimestre']) ?></td>
                            <td class="text-center"><?= $row['total_matriculados'] ?></td>
                            <td class="text-center"><?= $row['total_presencas'] ?></td>
                            <td class="text-center"><?= $row['total_faltas'] ?></td>
                            <td class="text-center"><?= $row['total_visitantes'] ?></td>
                            <td class="text-center"><?= $row['total_biblias'] ?></td>
                            <td class="text-center"><?= $row['total_revistas'] ?></td>
                            <td class="text-center"><?= number_format($row['total_ofertas'], 2, ',', '.') ?></td>
                            <td class="text-center"><?= number_format($frequencia, 2, ',', '.') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold text-center">
                    <tr>
                        <td colspan="3" class="text-end">Totais Gerais:</td>
                        <td><?= $total_geral_matriculados ?></td>
                        <td><?= $total_geral_presencas ?></td>
                        <td><?= $total_geral_faltas ?></td>
                        <td><?= $total_geral_visitantes ?></td>
                        <td><?= $total_geral_biblias ?></td>
                        <td><?= $total_geral_revistas ?></td>
                        <td><?= number_format($total_geral_ofertas, 2, ',', '.') ?></td>
                        <td>
                            <?php
                            $frequencia_geral = 0;
                            $presenca_visitante_geral = $total_geral_presencas + $total_geral_visitantes;
                            $total_chamadas_geral = 0;

                            // Para calcular a frequência geral corretamente, precisaríamos somar total_aulas ponderado.
                            // Como total_aulas vem por classe, uma forma simples é pegar a média ponderada:

                            foreach ($relatorios as $row) {
                                $total_chamadas_geral += ((int)$row['total_aulas']) * ((int)$row['total_matriculados']);
                            }

                            if ($total_chamadas_geral > 0) {
                                $frequencia_geral = ($presenca_visitante_geral / $total_chamadas_geral) * 100;
                            }

                            echo number_format($frequencia_geral, 2, ',', '.') . '%';
                            ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables + Buttons -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script> 
// Renderizar variáveis em JavaScript para o PDF
?>
<script>
// Renderizar variáveis PHP no JS para usar no rodapé do PDF
const totaisGerais = {
    matriculados: <?= $total_geral_matriculados ?>,
    presencas: <?= $total_geral_presencas ?>,
    faltas: <?= $total_geral_faltas ?>,
    visitantes: <?= $total_geral_visitantes ?>,
    biblias: <?= $total_geral_biblias ?>,
    revistas: <?= $total_geral_revistas ?>,
    ofertas: "<?= number_format($total_geral_ofertas, 2, ',', '.') ?>",
    frequencia: "<?= number_format($frequencia_geral, 2, ',', '.') ?>%"
};
</script>

<!-- DataTables e botões -->
<script>
$(document).ready(function () {
    $('#tabela').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                className: 'btn btn-danger btn-sm',
                text: '📄 PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible',
                    footer: true
                },
                customize: function (doc) {
                    doc.defaultStyle.fontSize = 8;
                    doc.pageMargins = [10, 10, 10, 10];

                    const table = doc.content[1].table;
                    const body = table.body;

                    // Define largura dinâmica das colunas
                    const colCount = body[0].length;
                    table.widths = Array(colCount).fill('*');

                    // Alinhar e aplicar margem nas células das linhas de dados
                    body.forEach(row => {
                        row.forEach(cell => {
                            if (typeof cell === 'object') {
                                cell.alignment = 'center';
                                cell.margin = [0, 5, 0, 5];
                            }
                        });
                    });

                    // Adiciona linha do rodapé (Totais Gerais)
                    const rodapeTotais = [
                        { text: 'Totais Gerais:', colSpan: 3, alignment: 'left', bold: true }, {}, {},
                        { text: totaisGerais.matriculados, alignment: 'left', bold: true },
                        { text: totaisGerais.presencas, alignment: 'left', bold: true },
                        { text: totaisGerais.faltas, alignment: 'left', bold: true },
                        { text: totaisGerais.visitantes, alignment: 'left', bold: true },
                        { text: totaisGerais.biblias, alignment: 'left', bold: true },
                        { text: totaisGerais.revistas, alignment: 'left', bold: true },
                        { text: totaisGerais.ofertas, alignment: 'left', bold: true },
                        { text: totaisGerais.frequencia, alignment: 'left', bold: true }
                    ];

                    body.push(rodapeTotais);
                }
            },
            {
                extend: 'print',
                className: 'btn btn-secondary btn-sm',
                text: '🖨️ Imprimir'
            }
        ]
    });
});
</script>


</body>

</html>