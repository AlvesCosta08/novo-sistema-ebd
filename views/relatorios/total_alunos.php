<?php
// Incluir o arquivo de conexão PDO
require_once '../../config/conexao.php';
require_once '../includes/header.php';

// Definir a localidade para o Brasil
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'portuguese');

// Consultar os dados da view 'resumo_presenca'
$query = "SELECT aluno_id, aluno_nome, total_presentes, total_ausentes, classe_nome, congregacao_nome, trimestre
          FROM resumo_presenca";
$stmt = $pdo->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC); // Armazenar resultados em um array
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Presenças - Alunos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Estilos Personalizados -->
    <style>
        /* Seus estilos personalizados aqui... */
    </style>
</head>
<body>
    <div class="container">
        <h2>Frequência de Alunos</h2>
        
        <!-- Tabela para Desktop -->
        <table id="presencaTable" class="table table-striped table-hover d-none d-md-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome do Aluno</th>
                    <th>Presenças</th>
                    <th>Faltas</th>
                    <th>Classe</th>
                    <th>Congregação</th>
                    <th>Trimestre</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['aluno_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['aluno_nome']); ?></td>
                        <td><?php echo $row['total_presentes']; ?></td>
                        <td><?php echo $row['total_ausentes']; ?></td>
                        <td><?php echo htmlspecialchars($row['classe_nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['congregacao_nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['trimestre']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts aqui... -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script>
        // Inicializar o DataTable
        $(document).ready(function() {
            $('#presencaTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/Portuguese.json'
                }
            });
        });
    </script>
</body>
</html>

<?php
// Fechar a conexão com o banco
$pdo = null;
?>