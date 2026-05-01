<?php
require_once '../../config/conexao.php';

function formatarMoeda($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Relatório Consolidado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- DataTables + Botões -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  <style>
    body { font-family: Arial; background: #f4f4f4; margin: 30px; }
    h2 { text-align: center; margin-bottom: 30px; }
    .filtros { margin-bottom: 20px; }
    .filtros input { padding: 5px 10px; margin-right: 10px; }
    .filtros button { padding: 6px 15px; }

    table.dataTable thead { background: #2c3e50; color: #fff; }
    .money { text-align: right; font-weight: bold; color: green; }
  </style>
</head>
<body>

<h2>Relatório Consolidado de Classes</h2>

<div class="filtros">
  <form method="GET">
    <input type="text" name="trimestre" placeholder="Trimestre (Ex: 2024-1)" value="<?= $_GET['trimestre'] ?? '' ?>">
    <input type="text" name="congregacao" placeholder="Congregação" value="<?= $_GET['congregacao'] ?? '' ?>">
    <button type="submit">Filtrar</button>
    <button type="button" onclick="window.location='relatorio_consolidado.php'">Limpar</button>
  </form>
</div>

<table id="tabela" class="display nowrap" style="width:100%">
  <thead>
    <tr>
      <th>Congregação</th>
      <th>Classe</th>
      <th>Trimestre</th>
      <th>Matriculados</th>
      <th>Presentes</th>
      <th>Ausentes</th>
      <th>Justificados</th>
      <th>Bíblias</th>
      <th>Revistas</th>
      <th>Visitantes</th>
      <th>Oferta</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $sql = "SELECT 
              cg.nome AS congregacao,
              cl.nome AS classe,
              m.trimestre,
              COUNT(DISTINCT m.aluno_id) AS matriculados,
              COUNT(DISTINCT CASE WHEN p.presente = 'presente' THEN p.aluno_id END) AS presentes,
              COUNT(DISTINCT CASE WHEN p.presente = 'ausente' THEN p.aluno_id END) AS ausentes,
              COUNT(DISTINCT CASE WHEN p.presente = 'justificado' THEN p.aluno_id END) AS justificados,
              SUM(ch.total_biblias) AS biblias,
              SUM(ch.total_revistas) AS revistas,
              SUM(ch.total_visitantes) AS visitantes,
              SUM(ch.oferta_classe) AS oferta
            FROM congregacoes cg
            JOIN matriculas m ON m.congregacao_id = cg.id
            JOIN classes cl ON cl.id = m.classe_id
            LEFT JOIN chamadas ch ON ch.classe_id = cl.id
            LEFT JOIN presencas p ON p.chamada_id = ch.id
            WHERE m.status = 'ativo'";

    $params = [];

    if (!empty($_GET['trimestre'])) {
        $sql .= " AND m.trimestre = :trimestre";
        $params[':trimestre'] = $_GET['trimestre'];
    }

    if (!empty($_GET['congregacao'])) {
        $sql .= " AND cg.nome LIKE :congregacao";
        $params[':congregacao'] = '%' . $_GET['congregacao'] . '%';
    }

    $sql .= " GROUP BY cg.nome, cl.nome, m.trimestre ORDER BY cg.nome, cl.nome";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dados as $linha) {
      echo "<tr>
              <td>{$linha['congregacao']}</td>
              <td>{$linha['classe']}</td>
              <td>{$linha['trimestre']}</td>
              <td>{$linha['matriculados']}</td>
              <td>{$linha['presentes']}</td>
              <td>{$linha['ausentes']}</td>
              <td>{$linha['justificados']}</td>
              <td>{$linha['biblias']}</td>
              <td>{$linha['revistas']}</td>
              <td>{$linha['visitantes']}</td>
              <td class='money'>" . formatarMoeda($linha['oferta']) . "</td>
            </tr>";
    }
    ?>
  </tbody>
</table>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    $('#tabela').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: 'Exportar Excel' },
            { extend: 'csvHtml5', text: 'Exportar CSV' },
            { extend: 'pdfHtml5', text: 'Exportar PDF', orientation: 'landscape', pageSize: 'A4' },
            { extend: 'print', text: 'Imprimir' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        }
    });
});
</script>

</body>
</html>