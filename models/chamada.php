<?php
// controllers/chamada.php

// Configuração de Erros (Desative display_errors em produção)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Cabeçalho JSON
header('Content-Type: application/json');

// Função auxiliar de resposta
function sendResponse($status, $dataOrMessage, $message = null) {
    $response = [
        'status' => $status,
        'message' => $status === 'error' ? ($dataOrMessage ?? $message) : $message
    ];
    
    if ($status === 'success' && $dataOrMessage !== null) {
        $response['data'] = $dataOrMessage;
    }
    
    echo json_encode($response);
    exit;
}

// Inclusão de Dependências
try {
    require_once __DIR__ . '/../config/conexao.php'; 
    require_once __DIR__ . '/../models/chamada.php';
} catch (Exception $e) {
    sendResponse('error', 'Erro de configuração do servidor: ' . $e->getMessage());
}

// Verificação de Conexão
if (!isset($pdo) || !$pdo) {
    sendResponse('error', 'Erro crítico: Conexão com banco de dados não estabelecida.');
}

// Verificação de Método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Método inválido. Apenas POST é permitido.');
}

// Leitura do Input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE && empty($_POST)) {
    sendResponse('error', 'Corpo da requisição inválido ou vazio.');
}

if (empty($input)) {
    $input = $_POST;
}

$acao = $input['acao'] ?? '';
if (empty($acao)) {
    sendResponse('error', 'Ação não especificada.');
}

// Instanciação da Classe - As funções de trimestre agora estão no model
try {
    $chamadaModel = new Chamada($pdo);
} catch (Exception $e) {
    sendResponse('error', 'Falha ao inicializar o módulo de chamadas: ' . $e->getMessage());
}

// Roteamento de Ações
try {
    switch ($acao) {
        
        // ==================== CONGREGAÇÕES E CLASSES ====================
        
        case 'getCongregacoes':
            $data = $chamadaModel->getCongregacoes();
            sendResponse('success', $data);
            break;

        case 'getClassesByCongregacao':
            $congregacao_id = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$congregacao_id) {
                sendResponse('error', 'ID da congregação inválido.');
            }
            $data = $chamadaModel->getClassesByCongregacao($congregacao_id);
            sendResponse('success', $data);
            break;

        // ==================== ALUNOS ====================
        
        case 'getAlunosByClasse':
            $classe_id = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $congregacao_id = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            $trimestre = $input['trimestre'] ?? '';
            $ano = $input['ano'] ?? date('Y');

            if (!$classe_id || !$congregacao_id || empty($trimestre)) {
                sendResponse('error', 'Parâmetros incompletos (Classe, Congregação, Trimestre).');
            }

            // Usa o método padronizarTrimestre do model
            $trimestrePadronizado = $chamadaModel->padronizarTrimestre($trimestre, $ano);
            $data = $chamadaModel->getAlunosByClasse($classe_id, $congregacao_id, $trimestrePadronizado);
            sendResponse('success', $data);
            break;

        // ==================== SALVAR CHAMADA ====================
        
        case 'salvarChamada':
            // Validação dos campos obrigatórios
            $required = ['data', 'classe', 'professor', 'alunos', 'trimestre'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                sendResponse('error', 'Campos obrigatórios faltando: ' . implode(', ', $missing));
            }
            
            // Validação da data
            if (!DateTime::createFromFormat('Y-m-d', $input['data'])) {
                sendResponse('error', 'Formato de data inválido. Use YYYY-MM-DD');
            }
            
            // Validação da lista de alunos
            if (!is_array($input['alunos']) || empty($input['alunos'])) {
                sendResponse('error', 'Lista de alunos inválida ou vazia.');
            }
            
            // Usa o método padronizarTrimestre do model
            $trimestrePadronizado = $chamadaModel->padronizarTrimestre($input['trimestre']);
            
            $resultado = $chamadaModel->registrarChamada(
                $input['data'],
                $trimestrePadronizado,
                (int)$input['classe'],
                (int)$input['professor'],
                $input['alunos'],                  
                floatval($input['oferta_classe'] ?? 0),
                intval($input['total_visitantes'] ?? 0),
                intval($input['total_biblias'] ?? 0),
                intval($input['total_revistas'] ?? 0)
            );
        
            if ($resultado['sucesso']) {
                sendResponse('success', ['chamada_id' => $resultado['chamada_id'] ?? null], $resultado['mensagem']);
            } else {
                sendResponse('error', $resultado['mensagem']);
            }
            break;

        // ==================== LISTAR CHAMADAS ====================
        
        case 'listarChamadas':
            $filtros = [];
            
            if (!empty($input['congregacao_id'])) {
                $filtros['congregacao_id'] = (int)$input['congregacao_id'];
            }
            
            if (!empty($input['classe_id'])) {
                $filtros['classe_id'] = (int)$input['classe_id'];
            }
            
            // Processamento do trimestre para busca flexível - usando método do model
            if (!empty($input['trimestre'])) {
                $numeroTrimestre = $chamadaModel->extrairNumeroTrimestre($input['trimestre']);
                if ($numeroTrimestre) {
                    $filtros['trimestre_numero'] = $numeroTrimestre;
                    if (!empty($input['ano'])) {
                        $filtros['ano'] = $input['ano'];
                    } elseif (preg_match('/^(\d{4})/', $input['trimestre'], $matches)) {
                        $filtros['ano'] = $matches[1];
                    }
                } else {
                    $filtros['trimestre'] = $chamadaModel->padronizarTrimestre($input['trimestre']);
                }
            } elseif (!empty($input['trimestre_numero'])) {
                // Suporte direto para filtro por número de trimestre
                $filtros['trimestre_numero'] = $input['trimestre_numero'];
                if (!empty($input['ano'])) {
                    $filtros['ano'] = $input['ano'];
                }
            }
            
            if (!empty($input['data_inicio'])) {
                $filtros['data_inicio'] = $input['data_inicio'];
            }
            
            if (!empty($input['data_fim'])) {
                $filtros['data_fim'] = $input['data_fim'];
            }
            
            $data = $chamadaModel->listarChamadas($filtros);
            sendResponse('success', $data);
            break;

        // ==================== DETALHES DA CHAMADA ====================
        
        case 'getChamada':
            $chamadaId = filter_var($input['chamada_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$chamadaId) {
                sendResponse('error', 'ID da chamada inválido.');
            }
            
            try {
                $data = $chamadaModel->getChamadaDetalhada($chamadaId);
                sendResponse('success', $data);
            } catch (Exception $e) {
                sendResponse('error', $e->getMessage());
            }
            break;

        // ==================== ATUALIZAR CHAMADA ====================
        
        case 'atualizarChamada':
            $required = ['chamada_id', 'data', 'trimestre', 'classe', 'professor', 'alunos'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                sendResponse('error', 'Campos obrigatórios faltando: ' . implode(', ', $missing));
            }
            
            $chamadaId = filter_var($input['chamada_id'], FILTER_VALIDATE_INT);
            if (!$chamadaId) {
                sendResponse('error', 'ID da chamada inválido.');
            }
            
            if (!DateTime::createFromFormat('Y-m-d', $input['data'])) {
                sendResponse('error', 'Formato de data inválido. Use YYYY-MM-DD');
            }
            
            // Usa o método padronizarTrimestre do model
            $trimestrePadronizado = $chamadaModel->padronizarTrimestre($input['trimestre']);
            
            $resultado = $chamadaModel->atualizarChamada(
                $chamadaId,
                $input['data'],
                $trimestrePadronizado,
                (int)$input['classe'],
                (int)$input['professor'],
                $input['alunos'],
                floatval($input['oferta_classe'] ?? 0),
                intval($input['total_visitantes'] ?? 0),
                intval($input['total_biblias'] ?? 0),
                intval($input['total_revistas'] ?? 0)
            );
            
            if ($resultado['sucesso']) {
                sendResponse('success', null, $resultado['mensagem']);
            } else {
                sendResponse('error', $resultado['mensagem']);
            }
            break;

        // ==================== EXCLUIR CHAMADA ====================
        
        case 'excluirChamada':
            $chamadaId = filter_var($input['chamada_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$chamadaId) {
                sendResponse('error', 'ID da chamada inválido.');
            }
            
            $resultado = $chamadaModel->excluirChamada($chamadaId);
            if ($resultado['sucesso']) {
                sendResponse('success', null, $resultado['mensagem']);
            } else {
                sendResponse('error', $resultado['mensagem']);
            }
            break;

        // ==================== CORRIGIR TRIMESTRES ANTIGOS ====================
        
        case 'corrigirTrimestres':
            $ano = $input['ano'] ?? date('Y');
            $resultado = $chamadaModel->corrigirTrimestresAntigos($ano);
            if ($resultado['sucesso']) {
                sendResponse('success', $resultado['dados'] ?? null, $resultado['mensagem']);
            } else {
                sendResponse('error', $resultado['mensagem']);
            }
            break;
            
        // ==================== VERIFICAR CHAMADA EXISTENTE ====================
        
        case 'verificarChamadaExistente':
            $data = $input['data'] ?? '';
            $classeId = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $congregacaoId = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$data || !$classeId) {
                sendResponse('error', 'Data e classe são obrigatórios.');
            }
            
            try {
                // Busca chamada existente para esta data e classe
                $sql = "
                    SELECT c.*, cl.nome as nome_classe, cong.nome as nome_congregacao
                    FROM chamadas c
                    INNER JOIN classes cl ON cl.id = c.classe_id
                    INNER JOIN congregacoes cong ON cong.id = c.congregacao_id
                    WHERE c.data = :data AND c.classe_id = :classe_id
                ";
                
                // Se congregacao_id foi fornecido, adiciona ao filtro
                if ($congregacaoId) {
                    $sql .= " AND c.congregacao_id = :congregacao_id";
                }
                
                $sql .= " LIMIT 1";
                
                $stmt = $pdo->prepare($sql);
                $params = [':data' => $data, ':classe_id' => $classeId];
                if ($congregacaoId) {
                    $params[':congregacao_id'] = $congregacaoId;
                }
                $stmt->execute($params);
                $chamadaExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($chamadaExistente) {
                    sendResponse('success', [
                        'existe' => true,
                        'chamada' => $chamadaExistente
                    ], 'Já existe uma chamada para esta data.');
                } else {
                    sendResponse('success', ['existe' => false], 'Nenhuma chamada encontrada para esta data.');
                }
            } catch (PDOException $e) {
                sendResponse('error', 'Erro ao verificar chamada existente: ' . $e->getMessage());
            }
            break;
            
        // ==================== ESTATÍSTICAS RÁPIDAS ====================
        
        case 'getEstatisticas':
            $congregacaoId = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            $classeId = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $trimestre = $input['trimestre'] ?? '';
            
            try {
                $where = [];
                $params = [];
                
                if ($congregacaoId) {
                    $where[] = "c.congregacao_id = :congregacao_id";
                    $params[':congregacao_id'] = $congregacaoId;
                }
                
                if ($classeId) {
                    $where[] = "c.classe_id = :classe_id";
                    $params[':classe_id'] = $classeId;
                }
                
                if ($trimestre) {
                    // Usa o método padronizarTrimestre do model
                    $trimestrePadronizado = $chamadaModel->padronizarTrimestre($trimestre);
                    $where[] = "c.trimestre = :trimestre";
                    $params[':trimestre'] = $trimestrePadronizado;
                }
                
                $sql = "SELECT 
                            COUNT(DISTINCT c.id) as total_chamadas,
                            COALESCE(SUM(c.total_visitantes), 0) as total_visitantes,
                            COALESCE(SUM(c.total_biblias), 0) as total_biblias,
                            COALESCE(SUM(c.total_revistas), 0) as total_revistas,
                            COALESCE(SUM(c.oferta_classe), 0) as total_ofertas,
                            COALESCE(SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END), 0) as total_presentes,
                            COALESCE(SUM(CASE WHEN p.presente = 'ausente' THEN 1 ELSE 0 END), 0) as total_ausentes,
                            COALESCE(SUM(CASE WHEN p.presente = 'justificado' THEN 1 ELSE 0 END), 0) as total_justificados
                        FROM chamadas c
                        LEFT JOIN presencas p ON p.chamada_id = c.id";
                
                if (!empty($where)) {
                    $sql .= " WHERE " . implode(" AND ", $where);
                }
                
                $stmt = $pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                
                $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Garante que todos os campos tenham valores numéricos
                $estatisticas['total_chamadas'] = (int)($estatisticas['total_chamadas'] ?? 0);
                $estatisticas['total_visitantes'] = (int)($estatisticas['total_visitantes'] ?? 0);
                $estatisticas['total_biblias'] = (int)($estatisticas['total_biblias'] ?? 0);
                $estatisticas['total_revistas'] = (int)($estatisticas['total_revistas'] ?? 0);
                $estatisticas['total_ofertas'] = (float)($estatisticas['total_ofertas'] ?? 0);
                $estatisticas['total_presentes'] = (int)($estatisticas['total_presentes'] ?? 0);
                $estatisticas['total_ausentes'] = (int)($estatisticas['total_ausentes'] ?? 0);
                $estatisticas['total_justificados'] = (int)($estatisticas['total_justificados'] ?? 0);
                
                sendResponse('success', $estatisticas);
            } catch (PDOException $e) {
                error_log("Erro ao buscar estatísticas: " . $e->getMessage());
                sendResponse('error', 'Erro ao buscar estatísticas: ' . $e->getMessage());
            }
            break;

        default:
            sendResponse('error', 'Ação inválida: ' . htmlspecialchars($acao));
    }
} catch (Exception $e) {
    error_log("Erro no Controller de Chamadas: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendResponse('error', 'Ocorreu um erro interno ao processar sua solicitação: ' . $e->getMessage());
}
?>