<?php 
require_once '../../config/conexao.php';
require_once '../includes/header.php';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Trimestral por Congregação</title>
    <!-- Incluindo o Bootstrap para estilização moderna -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJ03vNf8pS8J7kX8z8mT5p7PYlJj6o0oG5SboO51KTd3uTZOrRQx12FFuGGD" crossorigin="anonymous">
    <style>
        body {
            padding: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center mb-4">Relatório Trimestral por Congregação</h1>
    
    <div class="table-container">
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Classe</th>
                    <th>Congregação</th>
                    <th>Trimestre</th>
                    <th>Total de Bíblias</th>
                    <th>Total de Revistas</th>
                    <th>Total de Visitantes</th>
                    <th>Total de Ofertas</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aqui, o PHP vai preencher os dados da VIEW relatorio_trimestre_congregacao -->
                <?php
                // Conexão com o banco de dados
                include('../../config/conexao.php');

                // Consulta para obter os dados da VIEW
                $query = "SELECT * FROM relatorio_trimestre_congregacao";
                $result = $pdo->query($query);

                // Verificando se existem resultados
                if ($result->rowCount() > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['classe_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['congregacao_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['trimestre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_biblias']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_revistas']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_visitantes']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_ofertas']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Nenhum dado encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Incluindo o JavaScript do Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-bH7PZ5oY91WV1qzr+lgzTbF0gJ+hQsl1mckY1vBOkqBp0FgVKTZkHjm0Ut+8qfgV" crossorigin="anonymous"></script>
</body>
</html>
