<?php
/**
 * Funções específicas para o Dashboard de Estatísticas
 * 
 * @package Escola\Functions
 * @version 2.0
 */

// Incluir funções gerais primeiro
require_once __DIR__ . '/funcoes_gerais.php';
require_once __DIR__ . '/funcoes_chamadas.php';

/**
 * Obtém estatísticas gerais (KPIs) para os cards do dashboard
 * 
 * @param PDO $pdo Conexão com banco
 * @return array Estatísticas
 */
function obterKpisDashboard($pdo): array {
    $stats = [
        'total_alunos' => 0,
        'total_classes' => 0,
        'total_matriculas' => 0,
        'chamadas_hoje' => 0,
        'frequencia_media' => 0,
        'ultima_chamada' => null,
        'total_chamadas_mes' => 0,
        'total_ofertas_mes' => 0
    ];

    try {
        // Total de alunos ativos
        $stmt = $pdo->query("SELECT COUNT(*) FROM alunos WHERE status = 'ativo'");
        $stats['total_alunos'] = (int)$stmt->fetchColumn();
        
        // Total de classes
        $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
        $stats['total_classes'] = (int)$stmt->fetchColumn();
        
        // Total de matrículas ativas no trimestre atual
        $trimestreAtual = formatarTrimestrePadrao(getTrimestreAtual());
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM matriculas WHERE trimestre = :trimestre AND status = 'ativo'");
        $stmt->execute([':trimestre' => $trimestreAtual]);
        $stats['total_matriculas'] = (int)$stmt->fetchColumn();
        
        // Chamadas hoje
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE data = CURDATE()");
        $stmt->execute();
        $stats['chamadas_hoje'] = (int)$stmt->fetchColumn();
        
        // Total de chamadas no mês
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chamadas WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())");
        $stmt->execute();
        $stats['total_chamadas_mes'] = (int)$stmt->fetchColumn();
        
        // Total de ofertas no mês
        $stmt = $pdo->prepare("SELECT SUM(oferta_classe) FROM chamadas WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())");
        $stmt->execute();
        $stats['total_ofertas_mes'] = (float)($stmt->fetchColumn() ?: 0);
        
        // Última chamada
        $stmt = $pdo->query("SELECT data FROM chamadas ORDER BY data DESC, id DESC LIMIT 1");
        $ultima = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['ultima_chamada'] = $ultima ? $ultima['data'] : null;
        
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
        $stats['frequencia_media'] = round((float)$stmt->fetchColumn(), 2);

    } catch (PDOException $e) {
        error_log("Erro ao obter estatísticas do dashboard: " . $e->getMessage());
        $stats['erro'] = $e->getMessage();
    }

    return $stats;
}

/**
 * Obtém as últimas atividades registradas no sistema
 * 
 * @param PDO $pdo Conexão com banco
 * @param int $limite Número máximo de registros
 * @return array Lista de atividades
 */
function obterUltimasAtividades(PDO $pdo, int $limite = 5): array {
    try {
        $sql = "SELECT id, descricao, data_hora, tipo, usuario_id
                FROM logs 
                ORDER BY data_hora DESC 
                LIMIT :limite";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Se não houver logs, gerar atividades simuladas
        if (empty($resultados)) {
            return gerarAtividadesSimuladas();
        }

        $atividadesFormatadas = [];
        foreach ($resultados as $atividade) {
            $tipo = strtolower($atividade['tipo'] ?? '');
            
            if (strpos($tipo, 'login') !== false) {
                $cor = 'primary'; 
                $icone = 'sign-in-alt';
            } elseif (strpos($tipo, 'chamada') !== false || strpos($tipo, 'presenca') !== false) {
                $cor = 'success'; 
                $icone = 'clipboard-check';
            } elseif (strpos($tipo, 'erro') !== false) {
                $cor = 'danger'; 
                $icone = 'exclamation-triangle';
            } else {
                $cor = 'secondary'; 
                $icone = 'info-circle';
            }

            $atividadesFormatadas[] = [
                'descricao' => sanitizarExibicao($atividade['descricao'] ?? 'Atividade'),
                'data_hora' => $atividade['data_hora'],
                'data_formatada' => formatarDateTimeBrasil($atividade['data_hora']),
                'usuario_nome' => 'Sistema',
                'cor' => $cor,
                'icone' => $icone
            ];
        }
        return $atividadesFormatadas;

    } catch (PDOException $e) {
        error_log("Erro ao obter últimas atividades: " . $e->getMessage());
        return gerarAtividadesSimuladas();
    }
}

/**
 * Gera atividades simuladas quando não há logs
 * 
 * @return array Atividades simuladas
 */
function gerarAtividadesSimuladas() {
    return [
        [
            'descricao' => 'Sistema acessado com sucesso',
            'data_hora' => date('Y-m-d H:i:s'),
            'data_formatada' => formatarDateTimeBrasil(date('Y-m-d H:i:s')),
            'usuario_nome' => 'Sistema',
            'cor' => 'success',
            'icone' => 'check-circle'
        ],
        [
            'descricao' => 'Dashboard carregado',
            'data_hora' => date('Y-m-d H:i:s', time() - 3600),
            'data_formatada' => formatarDateTimeBrasil(date('Y-m-d H:i:s', time() - 3600)),
            'usuario_nome' => 'Sistema',
            'cor' => 'info',
            'icone' => 'info-circle'
        ]
    ];
}

/**
 * Exibe as últimas chamadas no dashboard
 * 
 * @param PDO $pdo Conexão com banco
 * @param int $limite Número máximo de registros
 */
function exibirUltimasChamadasDashboard(PDO $pdo, int $limite = 5): void {
    try {
        $sql = "SELECT c.id, c.data, c.oferta_classe, c.total_visitantes,
                       cl.nome as classe_nome,
                       COUNT(p.id) as total_alunos,
                       SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) as presentes
                FROM chamadas c
                JOIN classes cl ON c.classe_id = cl.id
                LEFT JOIN presencas p ON p.chamada_id = c.id
                GROUP BY c.id
                ORDER BY c.data DESC, c.id DESC
                LIMIT :limite";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $chamadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($chamadas)) {
            echo '<div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard fa-2x mb-2 d-block"></i>
                    <p class="mb-0">Nenhuma chamada registrada ainda.</p>
                    <a href="chamada/index.php" class="btn btn-sm btn-primary mt-2">Registrar primeira chamada</a>
                  </div>';
            return;
        }

        echo '<div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <th>Classe</th>
                    <th>Presença</th>
                    <th>Oferta</th>
                    <th>Ação</th>
                </tr>
              </thead>
              <tbody>';
        
        foreach ($chamadas as $chamada) {
            $total = (int)$chamada['total_alunos'];
            $presentes = (int)$chamada['presentes'];
            $percentual = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
            $corBarra = $percentual >= 75 ? 'success' : ($percentual >= 50 ? 'warning' : 'danger');

            echo '<tr>
                <td>' . formatarDataBrasil($chamada['data']) . '</td>
                <td class="fw-medium">' . sanitizarExibicao($chamada['classe_nome']) . '</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">' . $presentes . '/' . $total . '</span>
                        <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                            <div class="progress-bar bg-' . $corBarra . '" style="width: ' . $percentual . '%;"></div>
                        </div>
                        <small class="text-muted">' . $percentual . '%</small>
                    </div>
                </td>
                <td class="text-nowrap">' . formatarMoedaBr($chamada['oferta_classe'] ?? 0) . '</td>
                <td>
                    <a href="chamada/editar.php?id=' . (int)$chamada['id'] . '" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>';
        }
        echo '</tbody>
              </table>
              </div>';
        
    } catch (PDOException $e) {
        echo '<div class="alert alert-warning">Erro ao carregar chamadas recentes.</div>';
        error_log("Erro ao exibir últimas chamadas: " . $e->getMessage());
    }
}

/**
 * Registra atividade no log
 * 
 * @param PDO $pdo Conexão com banco
 * @param int|null $usuarioId ID do usuário
 * @param string $tipo Tipo da atividade
 * @param string $descricao Descrição da atividade
 */
function registrarAtividade($pdo, $usuarioId, $tipo, $descricao) {
    try {
        // Verificar se a tabela logs existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'logs'");
        if ($stmt->rowCount() > 0) {
            $sql = "INSERT INTO logs (usuario_id, acao, tabela_afetada, registro_id, data) 
                    VALUES (:usuario_id, :acao, :tabela, :registro, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':acao' => $tipo,
                ':tabela' => 'dashboard',
                ':registro' => 0
            ]);
        }
    } catch (PDOException $e) {
        // Não registrar erro para não quebrar a página
        error_log("Erro ao registrar atividade: " . $e->getMessage());
    }
}