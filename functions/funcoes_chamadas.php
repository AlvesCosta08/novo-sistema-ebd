<?php
/**
 * Funções específicas para manipulação de chamadas
 * 
 * @package Escola\Functions
 * @version 2.0
 */

// Incluir funções gerais primeiro
require_once __DIR__ . '/funcoes_gerais.php';

/**
 * Obtém estatísticas mensais das chamadas
 * 
 * @param PDO $pdo Conexão com banco
 * @param int|null $trimestre Número do trimestre (1-4)
 * @return array Estatísticas
 */
function obterEstatisticasChamadasPorTrimestre($pdo, $trimestre = null) {
    $estatisticas = [
        'total_chamadas_mes' => 0,
        'total_presentes' => 0,
        'total_ausentes' => 0,
        'total_visitantes' => 0,
        'total_ofertas' => 0,
        'ultima_chamada' => null,
        'trimestre_atual' => null,
        'erro' => null
    ];
    
    try {
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        if ($trimestre === null) {
            $trimestre = getTrimestreAtual();
        }
        $estatisticas['trimestre_atual'] = $trimestre;
        
        // Formato do trimestre para busca (ex: 2026-T2)
        $trimestreBusca = formatarTrimestrePadrao($trimestre, $anoAtual);
        
        // Total de chamadas no mês/trimestre
        $sqlTotal = "SELECT COUNT(*) as total 
                     FROM chamadas 
                     WHERE MONTH(data) = :mes 
                     AND YEAR(data) = :ano
                     AND trimestre = :trimestre";
        
        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtTotal->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtTotal->bindParam(':trimestre', $trimestreBusca, PDO::PARAM_STR);
        $stmtTotal->execute();
        
        $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        $estatisticas['total_chamadas_mes'] = (int)($resultTotal['total'] ?? 0);
        
        // Totais acumulados
        $sqlTotais = "SELECT 
                         SUM(oferta_classe) as total_ofertas,
                         SUM(total_visitantes) as total_visitantes,
                         SUM(total_biblias) as total_biblias,
                         SUM(total_revistas) as total_revistas
                      FROM chamadas 
                      WHERE MONTH(data) = :mes 
                      AND YEAR(data) = :ano
                      AND trimestre = :trimestre";
        
        $stmtTotais = $pdo->prepare($sqlTotais);
        $stmtTotais->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtTotais->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtTotais->bindParam(':trimestre', $trimestreBusca, PDO::PARAM_STR);
        $stmtTotais->execute();
        $totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);
        
        $estatisticas['total_ofertas'] = (float)($totais['total_ofertas'] ?? 0);
        $estatisticas['total_visitantes'] = (int)($totais['total_visitantes'] ?? 0);
        
        // Presenças
        $sqlPresencas = "SELECT 
                            SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) as presentes,
                            SUM(CASE WHEN p.presente = 'ausente' THEN 1 ELSE 0 END) as ausentes
                         FROM presencas p
                         JOIN chamadas c ON p.chamada_id = c.id
                         WHERE MONTH(c.data) = :mes 
                         AND YEAR(c.data) = :ano
                         AND c.trimestre = :trimestre";
        
        $stmtPresencas = $pdo->prepare($sqlPresencas);
        $stmtPresencas->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtPresencas->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtPresencas->bindParam(':trimestre', $trimestreBusca, PDO::PARAM_STR);
        $stmtPresencas->execute();
        $presencas = $stmtPresencas->fetch(PDO::FETCH_ASSOC);
        
        $estatisticas['total_presentes'] = (int)($presencas['presentes'] ?? 0);
        $estatisticas['total_ausentes'] = (int)($presencas['ausentes'] ?? 0);
        
        // Última chamada
        $sqlUltima = "SELECT c.*, 
                             cl.nome as classe_nome, 
                             u.nome as professor_nome
                      FROM chamadas c
                      JOIN classes cl ON c.classe_id = cl.id
                      JOIN usuarios u ON c.professor_id = u.id
                      WHERE MONTH(c.data) = :mes 
                      AND YEAR(c.data) = :ano
                      AND c.trimestre = :trimestre
                      ORDER BY c.data DESC, c.id DESC
                      LIMIT 1";
        
        $stmtUltima = $pdo->prepare($sqlUltima);
        $stmtUltima->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtUltima->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtUltima->bindParam(':trimestre', $trimestreBusca, PDO::PARAM_STR);
        $stmtUltima->execute();
        
        $estatisticas['ultima_chamada'] = $stmtUltima->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $estatisticas['erro'] = "Erro ao obter estatísticas: " . $e->getMessage();
        error_log($estatisticas['erro']);
    }
    
    return $estatisticas;
}

/**
 * Obtém as últimas chamadas agrupadas por classe
 * 
 * @param PDO $pdo Conexão com banco
 * @param int|null $trimestre Número do trimestre
 * @return array Chamadas por classe
 */
function obterUltimasChamadasPorClasse($pdo, $trimestre = null) {
    $chamadasPorClasse = [];
    
    try {
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        if ($trimestre === null) {
            $trimestre = getTrimestreAtual();
        }
        
        $trimestreBusca = formatarTrimestrePadrao($trimestre, $anoAtual);
        
        $sql = "SELECT c.*, 
                       cl.nome as classe_nome, 
                       cl.id as classe_id,
                       u.nome as professor_nome,
                       MAX(c.data) as ultima_data
                FROM chamadas c
                JOIN classes cl ON c.classe_id = cl.id
                JOIN usuarios u ON c.professor_id = u.id
                WHERE MONTH(c.data) = :mes 
                AND YEAR(c.data) = :ano
                AND c.trimestre = :trimestre
                GROUP BY c.classe_id
                ORDER BY cl.nome ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmt->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmt->bindParam(':trimestre', $trimestreBusca, PDO::PARAM_STR);
        $stmt->execute();
        
        $chamadasPorClasse = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $erro = "Erro ao obter chamadas por classe: " . $e->getMessage();
        error_log($erro);
        return ['erro' => $erro];
    }
    
    return $chamadasPorClasse;
}

/**
 * Exibe as últimas chamadas por classe em formato HTML
 * 
 * @param PDO $pdo Conexão com banco
 * @param int|null $trimestre Número do trimestre
 */
function exibirUltimasChamadasPorClasse($pdo, $trimestre = null) {
    $chamadas = obterUltimasChamadasPorClasse($pdo, $trimestre);
    
    if (isset($chamadas['erro'])) {
        echo "<div class='alert alert-danger'>" . sanitizarExibicao($chamadas['erro']) . "</div>";
        return;
    }
    
    if (empty($chamadas)) {
        echo "<div class='alert alert-info'>Nenhuma chamada registrada este mês.</div>";
        return;
    }
    
    $mesAtual = date('m');
    $trimestreAtual = $trimestre ?? getTrimestreAtual();
    
    echo "<h4>Chamadas do Trimestre " . (int)$trimestreAtual . "</h4>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped table-hover'>";
    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th>Classe</th>";
    echo "<th>Data da Última Chamada</th>";
    echo "<th>Professor</th>";
    echo "<th>Oferta</th>";
    echo "<th>Visitantes</th>";
    echo "<th>Ação</th>";
    echo "</tr></thead><tbody>";
    
    foreach ($chamadas as $chamada) {
        echo "<tr>";
        echo "<td>" . sanitizarExibicao($chamada['classe_nome']) . "</td>";
        echo "<td>" . formatarDataBrasil($chamada['ultima_data']) . "</td>";
        echo "<td>" . sanitizarExibicao($chamada['professor_nome']) . "</td>";
        echo "<td>" . formatarMoedaBr($chamada['oferta_classe'] ?? 0) . "</td>";
        echo "<td>" . (int)($chamada['total_visitantes'] ?? 0) . "</td>";
        echo "<td><a href='chamada/editar.php?id=" . (int)$chamada['id'] . "' class='btn btn-sm btn-primary'>Ver Chamada</a></td>";
        echo "</tr>";
    }
    
    echo "</tbody></table></div>";
}

/**
 * Obtém estatísticas gerais de chamadas para o dashboard
 * 
 * @param PDO $pdo Conexão com banco
 * @return array Estatísticas
 */
function obterEstatisticasChamadasMensais($pdo) {
    $estatisticas = [
        'total_alunos' => 0,
        'total_classes' => 0,
        'chamadas_hoje' => 0,
        'frequencia_media' => 0,
        'ultima_chamada' => null,
        'total_chamadas_mes' => 0
    ];
    
    try {
        // Total de alunos ativos
        $stmt = $pdo->query("SELECT COUNT(*) FROM alunos WHERE status = 'ativo'");
        $estatisticas['total_alunos'] = (int)$stmt->fetchColumn();
        
        // Total de classes
        $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
        $estatisticas['total_classes'] = (int)$stmt->fetchColumn();
        
        // Chamadas hoje
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE data = CURDATE()");
        $stmt->execute();
        $estatisticas['chamadas_hoje'] = (int)$stmt->fetchColumn();
        
        // Total de chamadas no mês
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())");
        $stmt->execute();
        $estatisticas['total_chamadas_mes'] = (int)$stmt->fetchColumn();
        
        // Última chamada
        $stmt = $pdo->query("SELECT data FROM chamadas ORDER BY data DESC, id DESC LIMIT 1");
        $ultima = $stmt->fetch(PDO::FETCH_ASSOC);
        $estatisticas['ultima_chamada'] = $ultima ? $ultima['data'] : null;
        
        // Frequência média (últimos 30 dias)
        $stmt = $pdo->query("
            SELECT 
                CASE WHEN COUNT(*) = 0 THEN 0 
                ELSE SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) / COUNT(*) * 100 
                END as media
            FROM presencas p
            JOIN chamadas c ON p.chamada_id = c.id
            WHERE c.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $estatisticas['frequencia_media'] = round((float)$stmt->fetchColumn(), 2);
        
    } catch (PDOException $e) {
        error_log("Erro ao obter estatísticas: " . $e->getMessage());
    }
    
    return $estatisticas;
}