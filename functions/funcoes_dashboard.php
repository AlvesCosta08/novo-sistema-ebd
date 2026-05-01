<?php
/**
 * Funções específicas para o Dashboard de Estatísticas
 * 
 * @package Escola\Functions
 * @version 2.1
 */

// Incluir funções gerais primeiro (evita redeclaração)
require_once __DIR__ . '/funcoes_gerais.php';
require_once __DIR__ . '/../config/conexao.php';

/**
 * Obtém estatísticas gerais (KPIs) para os cards do dashboard
 */
function obterKpisDashboardChamadas(PDO $pdo): array {
    $stats = [
        'total_alunos' => 0,
        'total_turmas' => 0,
        'chamadas_hoje' => 0,
        'frequencia_media' => 0,
        'ultima_chamada' => null,
        'total_chamadas_mes' => 0
    ];

    try {
        $stats['total_alunos'] = (int)($pdo->query("SELECT COUNT(*) FROM alunos WHERE status = 'ativo'")->fetchColumn() ?: 0);
        $stats['total_turmas'] = (int)($pdo->query("SELECT COUNT(*) FROM turmas WHERE status = 'ativo'")->fetchColumn() ?: 0);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE DATE(data_chamada) = CURDATE()");
        $stmt->execute();
        $stats['chamadas_hoje'] = (int)($stmt->fetchColumn() ?: 0);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE MONTH(data_chamada) = MONTH(CURDATE()) AND YEAR(data_chamada) = YEAR(CURDATE())");
        $stmt->execute();
        $stats['total_chamadas_mes'] = (int)($stmt->fetchColumn() ?: 0);
        
        $ultimaData = $pdo->query("SELECT data_chamada FROM chamadas ORDER BY data_chamada DESC LIMIT 1")->fetchColumn();
        $stats['ultima_chamada'] = $ultimaData ? (string)$ultimaData : null;
        
        $stmt = $pdo->query("
            SELECT 
                CASE WHEN COUNT(*) = 0 THEN 0 
                ELSE SUM(CASE WHEN status_presenca = 'presente' THEN 1 ELSE 0 END) / COUNT(*) * 100 
                END as media
            FROM itens_chamada ic
            JOIN chamadas c ON ic.chamada_id = c.id
            WHERE c.data_chamada >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stats['frequencia_media'] = round((float)($stmt->fetchColumn() ?: 0), 2);

    } catch (PDOException $e) {
        $erro = "Erro ao obter estatísticas do dashboard: " . $e->getMessage();
        error_log($erro);
        $stats['erro'] = $erro;
    }

    return $stats;
}

/**
 * Obtém as últimas atividades registradas no sistema (Logs)
 */
function obterUltimasAtividades(PDO $pdo, int $limite = 5): array {
    try {
        $sql = "SELECT a.id, a.descricao, a.data_hora, a.tipo, u.nome as usuario_nome
                FROM atividades a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.data_hora DESC LIMIT :limite";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $atividadesFormatadas = [];
        foreach ($resultados as $atividade) {
            $tipo = strtolower($atividade['tipo'] ?? '');
            
            if (strpos($tipo, 'login') !== false) {
                $cor = 'primary'; $icone = 'sign-in-alt';
            } elseif (strpos($tipo, 'chamada') !== false || strpos($tipo, 'presenca') !== false) {
                $cor = 'success'; $icone = 'clipboard-check';
            } elseif (strpos($tipo, 'erro') !== false || strpos($tipo, 'falha') !== false) {
                $cor = 'danger'; $icone = 'exclamation-triangle';
            } else {
                $cor = 'secondary'; $icone = 'info-circle';
            }

            $atividadesFormatadas[] = [
                'descricao' => sanitizarExibicao($atividade['descricao']),
                'data_hora' => $atividade['data_hora'],
                'data_formatada' => formatarDataBrasil($atividade['data_hora']),
                'usuario_nome' => sanitizarExibicao($atividade['usuario_nome'] ?? 'Sistema'),
                'cor' => $cor,
                'icone' => $icone
            ];
        }
        return $atividadesFormatadas;

    } catch (PDOException $e) {
        error_log("Erro ao obter últimas atividades: " . $e->getMessage());
        return [];
    }
}

/**
 * Exibe as últimas chamadas por turma no dashboard
 */
function exibirUltimasChamadasDashboard(PDO $pdo, int $limite = 5): void {
    try {
        $sql = "SELECT c.id, c.data_chamada, t.nome as turma_nome,
                       (SELECT COUNT(*) FROM itens_chamada ic WHERE ic.chamada_id = c.id AND ic.status_presenca = 'presente') as presentes,
                       (SELECT COUNT(*) FROM itens_chamada ic WHERE ic.chamada_id = c.id) as total_alunos
                FROM chamadas c
                JOIN turmas t ON c.turma_id = t.id
                ORDER BY c.data_chamada DESC, c.criado_em DESC LIMIT :limite";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $chamadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($chamadas)) {
            echo '<div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard fa-2x mb-2"></i>
                    <p class="mb-0">Nenhuma chamada registrada ainda.</p>
                  </div>';
            return;
        }

        echo '<div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
              <thead class="table-light"><tr><th>Data</th><th>Turma</th><th>Presença</th><th>Ação</th></tr></thead>
              <tbody>';
        
        foreach ($chamadas as $chamada) {
            $total = (int)$chamada['total_alunos'];
            $presentes = (int)$chamada['presentes'];
            $percentual = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
            $corBarra = $percentual >= 75 ? 'success' : ($percentual >= 50 ? 'warning' : 'danger');

            echo '<tr>
                <td>' . formatarDataBrasil($chamada['data_chamada']) . '</td>
                <td class="fw-medium">' . sanitizarExibicao($chamada['turma_nome']) . '</td>
                <td><div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">' . $presentes . '/' . $total . '</span>
                    <div class="progress flex-grow-1" style="height:6px;width:80px">
                        <div class="progress-bar bg-' . $corBarra . '" style="width:' . $percentual . '%"></div>
                    </div>
                </div></td>
                <td><a href="chamadas/detalhes.php?id=' . (int)$chamada['id'] . '" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                </tr>';
        }
        echo '</tbody></table></div>';

    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Erro ao carregar chamadas recentes.</div>';
        error_log("Erro ao exibir últimas chamadas: " . $e->getMessage());
    }
}
?>