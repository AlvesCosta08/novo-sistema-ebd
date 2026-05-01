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

/**
 * Função para padronizar trimestre
 * Converte formatos como '2' para '2026-T2'
 */
function padronizarTrimestre($trimestre, $ano = null) {
    if (empty($trimestre)) return null;
    
    // Remove espaços em branco
    $trimestre = trim($trimestre);
    
    // Se já estiver no formato ANO-T (ex: 2026-T2), retorna como está
    if (preg_match('/^\d{4}-T[1-4]$/i', $trimestre)) {
        // Garante que o T seja maiúsculo
        return strtoupper($trimestre);
    }
    
    // Se for apenas o número do trimestre (1-4)
    if (preg_match('/^[1-4]$/', $trimestre)) {
        $anoUsar = $ano ?: date('Y');
        return $anoUsar . '-T' . $trimestre;
    }
    
    // Se for formato '2026T2' (sem hífen)
    if (preg_match('/^(\d{4})[Tt]([1-4])$/', $trimestre, $matches)) {
        return $matches[1] . '-T' . $matches[2];
    }
    
    // Se for formato 'T2' ou 'T02'
    if (preg_match('/^T?0?([1-4])$/i', $trimestre, $matches)) {
        $anoUsar = $ano ?: date('Y');
        return $anoUsar . '-T' . $matches[1];
    }
    
    return $trimestre;
}

/**
 * Função para extrair o número do trimestre (1-4)
 */
function extrairNumeroTrimestre($trimestre) {
    if (empty($trimestre)) return null;
    
    if (preg_match('/-T([1-4])$/i', $trimestre, $matches)) {
        return $matches[1];
    }
    if (preg_match('/^[1-4]$/', $trimestre)) {
        return $trimestre;
    }
    if (preg_match('/^(\d{4})[Tt]([1-4])$/', $trimestre, $matches)) {
        return $matches[2];
    }
    return null;
}

// Instanciação da Classe
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

            $trimestrePadronizado = padronizarTrimestre($trimestre, $ano);
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
            
            $trimestrePadronizado = padronizarTrimestre($input['trimestre']);
            
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
            
            // Processamento do trimestre para busca flexível
            if (!empty($input['trimestre'])) {
                $numeroTrimestre = extrairNumeroTrimestre($input['trimestre']);
                if ($numeroTrimestre) {
                    $filtros['trimestre_numero'] = $numeroTrimestre;
                    if (!empty($input['ano'])) {
                        $filtros['ano'] = $input['ano'];
                    } elseif (preg_match('/^(\d{4})/', $input['trimestre'], $matches)) {
                        $filtros['ano'] = $matches[1];
                    }
                } else {
                    $filtros['trimestre'] = padronizarTrimestre($input['trimestre']);
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
            
            $trimestrePadronizado = padronizarTrimestre($input['trimestre']);
            
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
            
            if (!$data || !$classeId) {
                sendResponse('error', 'Data e classe são obrigatórios.');
            }
            
            try {
                // Busca chamada existente para esta data e classe
                $stmt = $pdo->prepare("
                    SELECT c.*, cl.nome as nome_classe 
                    FROM chamadas c
                    INNER JOIN classes cl ON cl.id = c.classe_id
                    WHERE c.data = :data AND c.classe_id = :classe_id
                    LIMIT 1
                ");
                $stmt->execute([':data' => $data, ':classe_id' => $classeId]);
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
                    $trimestrePadronizado = padronizarTrimestre($trimestre);
                    $where[] = "c.trimestre = :trimestre";
                    $params[':trimestre'] = $trimestrePadronizado;
                }
                
                $sql = "SELECT 
                            COUNT(DISTINCT c.id) as total_chamadas,
                            SUM(c.total_visitantes) as total_visitantes,
                            SUM(c.total_biblias) as total_biblias,
                            SUM(c.total_revistas) as total_revistas,
                            SUM(c.oferta_classe) as total_ofertas,
                            SUM(p.presente = 'presente') as total_presentes,
                            SUM(p.presente = 'ausente') as total_ausentes,
                            SUM(p.presente = 'justificado') as total_justificados
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
                sendResponse('success', $estatisticas);
            } catch (PDOException $e) {
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