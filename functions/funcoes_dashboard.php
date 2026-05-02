<?php
/**
 * Funções utilitárias para o Dashboard
 *
 * @package Escola\Functions
 * @version 2.2
 */

require_once __DIR__ . '/funcoes_gerais.php';

/**
 * Coleta todos os dados necessários para o dashboard em uma única chamada.
 * Retorna array com todas as estatísticas ou valores zerados em caso de erro.
 */
function obterDadosDashboard($pdo) {
    $dados = [
        'total_alunos'     => 0,
        'total_classes'    => 0,
        'chamadas_hoje'    => 0,
        'chamadas_mes'     => 0,
        'frequencia_media' => 0.0,
        'ultimas_chamadas' => [],
        'aniversariantes'  => [],
        'ofertas'          => [
            'total_ofertas' => 0,
            'domingos'      => 0,
            'media_oferta'  => 0,
        ],
        'erro' => null,
    ];

    try {
        // ── Total de alunos ativos ──────────────────────────────────────────
        // 'status' existe em 'alunos', NÃO em 'chamadas'
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM alunos WHERE status = 'ativo'");
        $dados['total_alunos'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // ── Total de classes ────────────────────────────────────────────────
        // Tabela correta: 'classes' (não 'turmas')
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM classes");
        $dados['total_classes'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // ── Chamadas hoje ───────────────────────────────────────────────────
        // 'chamadas' NÃO tem coluna 'status'
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM chamadas WHERE data = CURDATE()");
        $stmt->execute();
        $dados['chamadas_hoje'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // ── Chamadas no mês ─────────────────────────────────────────────────
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total FROM chamadas
            WHERE MONTH(data) = MONTH(CURDATE())
              AND YEAR(data)  = YEAR(CURDATE())
        ");
        $stmt->execute();
        $dados['chamadas_mes'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // ── Frequência média (últimos 30 dias) ──────────────────────────────
        $stmt = $pdo->query("
            SELECT
                CASE WHEN COUNT(*) = 0 THEN 0
                ELSE ROUND(
                    SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END)
                    / COUNT(*) * 100
                , 1) END AS media
            FROM presencas p
            JOIN chamadas c ON p.chamada_id = c.id
            WHERE c.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $dados['frequencia_media'] = round((float)($stmt->fetch(PDO::FETCH_ASSOC)['media'] ?? 0), 1);

        // ── Últimas 5 chamadas ──────────────────────────────────────────────
        // JOIN com 'classes' (não 'turmas')
        $stmt = $pdo->prepare("
            SELECT
                c.id,
                DATE_FORMAT(c.data, '%d/%m/%Y')       AS data_formatada,
                c.oferta_classe,
                c.total_visitantes,
                c.trimestre,
                cl.nome                               AS classe_nome,
                COUNT(p.id)                           AS total_alunos,
                SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) AS presentes
            FROM chamadas c
            JOIN classes  cl ON c.classe_id   = cl.id
            LEFT JOIN presencas p ON p.chamada_id = c.id
            GROUP BY
                c.id, c.data, c.oferta_classe,
                c.total_visitantes, c.trimestre, cl.nome
            ORDER BY c.data DESC, c.id DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dados['ultimas_chamadas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Aniversariantes do mês ──────────────────────────────────────────
        $stmt = $pdo->prepare("
            SELECT
                a.nome,
                cl.nome                                           AS classe_nome,
                DAY(a.data_nascimento)                            AS dia,
                YEAR(CURDATE()) - YEAR(a.data_nascimento)         AS idade
            FROM alunos a
            JOIN matriculas m ON a.id = m.aluno_id AND m.status = 'ativo'
            JOIN classes cl   ON m.classe_id = cl.id
            WHERE MONTH(a.data_nascimento) = MONTH(CURDATE())
              AND a.status = 'ativo'
              AND YEAR(a.data_nascimento) < YEAR(CURDATE())
            ORDER BY DAY(a.data_nascimento) ASC
            LIMIT 10
        ");
        $stmt->execute();
        $dados['aniversariantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Ofertas (últimos 30 dias) ───────────────────────────────────────
        $stmt = $pdo->query("
            SELECT
                COALESCE(SUM(oferta_classe), 0)           AS total_ofertas,
                COUNT(DISTINCT data)                       AS domingos,
                COALESCE(ROUND(AVG(oferta_classe), 2), 0) AS media_oferta
            FROM chamadas
            WHERE data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $dados['ofertas'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: $dados['ofertas'];

    } catch (PDOException $e) {
        $dados['erro'] = $e->getMessage();
        error_log('Erro ao obter dados do dashboard: ' . $e->getMessage());
    }

    return $dados;
}