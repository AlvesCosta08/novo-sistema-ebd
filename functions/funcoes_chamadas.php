<?php
/**
 * Funções específicas para manipulação de chamadas com filtro por trimestre
 * 
 * @package Escola\Functions
 * @version 2.1
 */

// Incluir funções gerais primeiro (evita redeclaração)
require_once __DIR__ . '/funcoes_gerais.php';
require_once __DIR__ . '/../config/conexao.php';

/**
 * Obtém estatísticas mensais das chamadas com filtro por trimestre
 * 
 * @param PDO $pdo Instância da conexão com o banco de dados
 * @param int|null $trimestre Número do trimestre (1-4). Se null, usa o atual.
 * @return array Array com as estatísticas ou mensagem de erro
 */
function obterEstatisticasChamadasPorTrimestre($pdo, $trimestre = null) {
    $estatisticas = [
        'total_chamadas_mes' => 0,
        'ultima_chamada' => null,
        'trimestre_atual' => null,
        'erro' => null
    ];
    
    try {
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        if ($trimestre === null) {
            $trimestre = obterTrimestrePorMes($mesAtual);
        }
        $estatisticas['trimestre_atual'] = $trimestre;
        
        $sqlTotal = "SELECT COUNT(*) as total 
                     FROM chamadas 
                     WHERE MONTH(data_chamada) = :mes 
                     AND YEAR(data_chamada) = :ano
                     AND trimestre = :trimestre";
        
        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtTotal->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtTotal->bindParam(':trimestre', $trimestre, PDO::PARAM_INT);
        $stmtTotal->execute();
        
        $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        $estatisticas['total_chamadas_mes'] = (int)($resultTotal['total'] ?? 0);
        
        $sqlUltima = "SELECT c.*, 
                             t.nome as turma_nome, 
                             u.nome as professor_nome,
                             c.trimestre as trimestre_chamada
                      FROM chamadas c
                      JOIN turmas t ON c.turma_id = t.id
                      JOIN usuarios u ON c.professor_id = u.id
                      WHERE MONTH(c.data_chamada) = :mes 
                      AND YEAR(c.data_chamada) = :ano
                      AND c.trimestre = :trimestre
                      ORDER BY c.data_chamada DESC, c.criado_em DESC
                      LIMIT 1";
        
        $stmtUltima = $pdo->prepare($sqlUltima);
        $stmtUltima->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtUltima->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtUltima->bindParam(':trimestre', $trimestre, PDO::PARAM_INT);
        $stmtUltima->execute();
        
        $estatisticas['ultima_chamada'] = $stmtUltima->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $estatisticas['erro'] = "Erro ao obter estatísticas: " . $e->getMessage();
        error_log($estatisticas['erro']);
    }
    
    return $estatisticas;
}

/**
 * Obtém as últimas chamadas do mês agrupadas por turma com filtro por trimestre
 */
function obterUltimasChamadasPorTurma($pdo, $trimestre = null) {
    $chamadasPorTurma = [];
    
    try {
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        if ($trimestre === null) {
            $trimestre = obterTrimestrePorMes($mesAtual);
        }
        
        $sql = "SELECT c.*, 
                       t.nome as turma_nome, 
                       t.id as turma_id,
                       u.nome as professor_nome,
                       c.trimestre as trimestre_chamada,
                       MAX(c.data_chamada) as ultima_data
                FROM chamadas c
                JOIN turmas t ON c.turma_id = t.id
                JOIN usuarios u ON c.professor_id = u.id
                WHERE MONTH(c.data_chamada) = :mes 
                AND YEAR(c.data_chamada) = :ano
                AND c.trimestre = :trimestre
                GROUP BY c.turma_id
                ORDER BY t.nome ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmt->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmt->bindParam(':trimestre', $trimestre, PDO::PARAM_INT);
        $stmt->execute();
        
        $chamadasPorTurma = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $erro = "Erro ao obter chamadas por turma: " . $e->getMessage();
        error_log($erro);
        return ['erro' => $erro];
    }
    
    return $chamadasPorTurma;
}

/**
 * Exibe as últimas chamadas por turma em formato HTML
 */
function exibirUltimasChamadasPorTurma($pdo, $trimestre = null) {
    $chamadas = obterUltimasChamadasPorTurma($pdo, $trimestre);
    
    if (isset($chamadas['erro'])) {
        echo "<div class='alert alert-danger'>" . sanitizarExibicao($chamadas['erro']) . "</div>";
        return;
    }
    
    if (empty($chamadas)) {
        echo "<div class='alert alert-info'>Nenhuma chamada registrada este mês.</div>";
        return;
    }
    
    $mesAtual = date('m');
    $trimestreAtual = $trimestre ?? obterTrimestrePorMes($mesAtual);
    
    echo "<h4>Chamadas do Trimestre " . (int)$trimestreAtual . "</h4>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped table-hover'>";
    echo "<thead class='thead-dark'>";
    echo "<tr>";
    echo "<th>Turma</th><th>Trimestre</th><th>Última Chamada</th>";
    echo "<th>Bíblias</th><th>Revistas</th><th>Visitantes</th>";
    echo "</tr></thead><tbody>";
    
    foreach ($chamadas as $chamada) {
        echo "<tr>";
        echo "<td>" . sanitizarExibicao($chamada['turma_nome']) . "</td>";
        echo "<td>" . (int)($chamada['trimestre_chamada'] ?? 0) . "</td>";
        echo "<td>" . formatarDataBrasil($chamada['data_chamada']) . "</td>";
        echo "<td>" . (int)($chamada['total_biblias'] ?? 0) . "</td>";
        echo "<td>" . (int)($chamada['total_revistas'] ?? 0) . "</td>";
        echo "<td>" . (int)($chamada['total_visitantes'] ?? 0) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table></div>";
}
?>