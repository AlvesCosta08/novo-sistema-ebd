<?php
/**
 * Funções específicas para manipulação de chamadas
 *
 * @package Escola\Functions
 * @version 2.2
 */

require_once __DIR__ . '/funcoes_gerais.php';

/**
 * Obtém estatísticas mensais das chamadas por trimestre
 */
function obterEstatisticasChamadasPorTrimestre($pdo, $trimestre = null) {
    $estatisticas = [
        'total_chamadas_mes' => 0,
        'total_presentes'    => 0,
        'total_ausentes'     => 0,
        'total_visitantes'   => 0,
        'total_ofertas'      => 0,
        'ultima_chamada'     => null,
        'trimestre_atual'    => null,
        'erro'               => null,
    ];

    try {
        $mesAtual = (int)date('m');
        $anoAtual = (int)date('Y');

        if ($trimestre === null) {
            $trimestre = getTrimestreAtual();
        }
        $estatisticas['trimestre_atual'] = $trimestre;
        $trimestreBusca = formatarTrimestrePadrao($trimestre, $anoAtual);

        // Total de chamadas no mês/trimestre
        // NOTA: tabela 'chamadas' NÃO possui coluna 'status'
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM chamadas
            WHERE MONTH(data) = :mes
              AND YEAR(data)  = :ano
              AND trimestre   = :trimestre
        ");
        $stmt->execute([':mes' => $mesAtual, ':ano' => $anoAtual, ':trimestre' => $trimestreBusca]);
        $estatisticas['total_chamadas_mes'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Totais acumulados de ofertas e visitantes
        $stmt = $pdo->prepare("
            SELECT
                COALESCE(SUM(oferta_classe),    0) AS total_ofertas,
                COALESCE(SUM(total_visitantes), 0) AS total_visitantes
            FROM chamadas
            WHERE MONTH(data) = :mes
              AND YEAR(data)  = :ano
              AND trimestre   = :trimestre
        ");
        $stmt->execute([':mes' => $mesAtual, ':ano' => $anoAtual, ':trimestre' => $trimestreBusca]);
        $totais = $stmt->fetch(PDO::FETCH_ASSOC);
        $estatisticas['total_ofertas']    = (float)($totais['total_ofertas']    ?? 0);
        $estatisticas['total_visitantes'] = (int)  ($totais['total_visitantes'] ?? 0);

        // Presenças e ausências via tabela 'presencas'
        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN p.presente = 'ausente'  THEN 1 ELSE 0 END) AS ausentes
            FROM presencas p
            JOIN chamadas c ON p.chamada_id = c.id
            WHERE MONTH(c.data) = :mes
              AND YEAR(c.data)  = :ano
              AND c.trimestre   = :trimestre
        ");
        $stmt->execute([':mes' => $mesAtual, ':ano' => $anoAtual, ':trimestre' => $trimestreBusca]);
        $presencas = $stmt->fetch(PDO::FETCH_ASSOC);
        $estatisticas['total_presentes'] = (int)($presencas['presentes'] ?? 0);
        $estatisticas['total_ausentes']  = (int)($presencas['ausentes']  ?? 0);

        // Última chamada — JOIN com 'classes' (não 'turmas')
        $stmt = $pdo->prepare("
            SELECT c.*,
                   cl.nome AS classe_nome,
                   u.nome  AS professor_nome
            FROM chamadas c
            JOIN classes  cl ON c.classe_id    = cl.id
            JOIN usuarios u  ON c.professor_id = u.id
            WHERE MONTH(c.data) = :mes
              AND YEAR(c.data)  = :ano
              AND c.trimestre   = :trimestre
            ORDER BY c.data DESC, c.id DESC
            LIMIT 1
        ");
        $stmt->execute([':mes' => $mesAtual, ':ano' => $anoAtual, ':trimestre' => $trimestreBusca]);
        $estatisticas['ultima_chamada'] = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $estatisticas['erro'] = 'Erro ao obter estatísticas de chamadas: ' . $e->getMessage();
        error_log($estatisticas['erro']);
    }

    return $estatisticas;
}

/**
 * Obtém as últimas chamadas agrupadas por classe
 */
function obterUltimasChamadasPorClasse($pdo, $trimestre = null) {
    try {
        $mesAtual = (int)date('m');
        $anoAtual = (int)date('Y');

        if ($trimestre === null) {
            $trimestre = getTrimestreAtual();
        }
        $trimestreBusca = formatarTrimestrePadrao($trimestre, $anoAtual);

        // JOIN com 'classes' (não 'turmas')
        $stmt = $pdo->prepare("
            SELECT c.*,
                   cl.nome     AS classe_nome,
                   cl.id       AS classe_id,
                   u.nome      AS professor_nome,
                   MAX(c.data) AS ultima_data
            FROM chamadas c
            JOIN classes  cl ON c.classe_id    = cl.id
            JOIN usuarios u  ON c.professor_id = u.id
            WHERE MONTH(c.data) = :mes
              AND YEAR(c.data)  = :ano
              AND c.trimestre   = :trimestre
            GROUP BY
                c.classe_id,
                cl.nome, cl.id,
                u.nome,
                c.id, c.data, c.congregacao_id, c.professor_id,
                c.oferta_classe, c.total_visitantes,
                c.total_biblias, c.total_revistas, c.criado_em
            ORDER BY cl.nome ASC
        ");
        $stmt->execute([':mes' => $mesAtual, ':ano' => $anoAtual, ':trimestre' => $trimestreBusca]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $erro = 'Erro em exibirUltimasChamadasPorClasse: ' . $e->getMessage();
        error_log($erro);
        return ['erro' => $erro];
    }
}

/**
 * Exibe as últimas chamadas por classe em formato HTML
 */
function exibirUltimasChamadasPorClasse($pdo, $trimestre = null) {
    $chamadas = obterUltimasChamadasPorClasse($pdo, $trimestre);

    if (isset($chamadas['erro'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($chamadas['erro']) . "</div>";
        return;
    }

    if (empty($chamadas)) {
        echo "<div class='alert alert-info'>Nenhuma chamada registrada este mês.</div>";
        return;
    }

    $trimestreAtual = $trimestre ?? getTrimestreAtual();

    echo "<h4>Chamadas do Trimestre " . (int)$trimestreAtual . "</h4>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped table-hover'>";
    echo "<thead class='table-dark'><tr>";
    echo "<th>Classe</th><th>Última Chamada</th><th>Professor</th><th>Oferta</th><th>Visitantes</th><th>Ação</th>";
    echo "</tr></thead><tbody>";

    foreach ($chamadas as $chamada) {
        $dataFormatada = !empty($chamada['ultima_data'])
            ? date('d/m/Y', strtotime($chamada['ultima_data']))
            : '-';
        $oferta = 'R$ ' . number_format((float)($chamada['oferta_classe'] ?? 0), 2, ',', '.');

        echo "<tr>";
        echo "<td>" . htmlspecialchars($chamada['classe_nome'])    . "</td>";
        echo "<td>" . $dataFormatada                               . "</td>";
        echo "<td>" . htmlspecialchars($chamada['professor_nome']) . "</td>";
        echo "<td>" . $oferta                                      . "</td>";
        echo "<td>" . (int)($chamada['total_visitantes'] ?? 0)     . "</td>";
        echo "<td><a href='chamada/editar.php?id=" . (int)$chamada['id'] . "' class='btn btn-sm btn-primary'>Ver</a></td>";
        echo "</tr>";
    }

    echo "</tbody></table></div>";
}

/**
 * Obtém estatísticas gerais de chamadas para o dashboard
 */
function obterEstatisticasChamadasMensais($pdo) {
    $estatisticas = [
        'total_alunos'       => 0,
        'total_classes'      => 0,
        'chamadas_hoje'      => 0,
        'frequencia_media'   => 0,
        'ultima_chamada'     => null,
        'total_chamadas_mes' => 0,
    ];

    try {
        // 'status' existe em 'alunos', NÃO em 'chamadas'
        $stmt = $pdo->query("SELECT COUNT(*) FROM alunos WHERE status = 'ativo'");
        $estatisticas['total_alunos'] = (int)$stmt->fetchColumn();

        // Tabela correta: 'classes', não 'turmas'
        $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
        $estatisticas['total_classes'] = (int)$stmt->fetchColumn();

        // Chamadas hoje — sem filtro de 'status' (coluna inexistente em chamadas)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE data = CURDATE()");
        $stmt->execute();
        $estatisticas['chamadas_hoje'] = (int)$stmt->fetchColumn();

        // Total de chamadas no mês
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM chamadas
            WHERE MONTH(data) = MONTH(CURDATE())
              AND YEAR(data)  = YEAR(CURDATE())
        ");
        $stmt->execute();
        $estatisticas['total_chamadas_mes'] = (int)$stmt->fetchColumn();

        // Última chamada
        $stmt = $pdo->query("SELECT data FROM chamadas ORDER BY data DESC, id DESC LIMIT 1");
        $ultima = $stmt->fetch(PDO::FETCH_ASSOC);
        $estatisticas['ultima_chamada'] = $ultima['data'] ?? null;

        // Frequência média (últimos 30 dias)
        $stmt = $pdo->query("
            SELECT
                CASE WHEN COUNT(*) = 0 THEN 0
                ELSE ROUND(
                    SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) / COUNT(*) * 100
                , 2) END AS media
            FROM presencas p
            JOIN chamadas c ON p.chamada_id = c.id
            WHERE c.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $estatisticas['frequencia_media'] = (float)$stmt->fetchColumn();

    } catch (PDOException $e) {
        error_log('Erro ao obter estatísticas de chamadas: ' . $e->getMessage());
    }

    return $estatisticas;
}