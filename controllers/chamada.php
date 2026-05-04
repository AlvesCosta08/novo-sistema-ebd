<?php
// controllers/chamada.php - VERSÃO CORRIGIDA

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

function sendResponse($status, $data = null, $message = null) {
    $response = ['status' => $status];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

try {
    require_once __DIR__ . '/../config/conexao.php';
    require_once __DIR__ . '/../models/chamada.php';
} catch (Exception $e) {
    sendResponse('error', null, 'Erro de configuração: ' . $e->getMessage());
}

if (!isset($pdo) || !$pdo) {
    sendResponse('error', null, 'Erro crítico: conexão não estabelecida.');
}

$method = $_SERVER['REQUEST_METHOD'];
$input = ($method === 'GET') ? $_GET : json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE && !empty($_POST)) {
    $input = $_POST;
}

$acao = $input['acao'] ?? '';
if (empty($acao)) {
    sendResponse('error', null, 'Ação não especificada.');
}

try {
    $chamadaModel = new Chamada($pdo);
} catch (Exception $e) {
    sendResponse('error', null, 'Falha ao inicializar módulo: ' . $e->getMessage());
}

try {
    switch ($acao) {
        case 'getCongregacoes':
            sendResponse('success', $chamadaModel->getCongregacoes());
            break;

        case 'getClassesByCongregacao':
            $congregacao_id = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            sendResponse('success', $chamadaModel->getClassesByCongregacao($congregacao_id));
            break;

        case 'getAlunosByClasse':
            $classe_id = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $congregacao_id = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            $trimestre = $input['trimestre'] ?? '';
            
            if (!$classe_id || !$congregacao_id) {
                sendResponse('error', null, 'Classe e congregação são obrigatórios.');
            }
            
            $alunos = $chamadaModel->getAlunosByClasse($classe_id, $congregacao_id, $trimestre);
            sendResponse('success', $alunos);
            break;

        case 'salvarChamada':
            $required = ['data', 'classe', 'professor', 'alunos'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                sendResponse('error', null, 'Campos obrigatórios faltando: ' . implode(', ', $missing));
            }
            
            if (!DateTime::createFromFormat('Y-m-d', $input['data'])) {
                sendResponse('error', null, 'Formato de data inválido. Use YYYY-MM-DD');
            }
            
            if (!is_array($input['alunos']) || empty($input['alunos'])) {
                sendResponse('error', null, 'Lista de alunos inválida ou vazia.');
            }

            $trimestre = $input['trimestre'] ?? date('Y') . '-T' . ceil(date('n') / 3);
            
            $resultado = $chamadaModel->registrarChamada(
                $input['data'], 
                $trimestre, 
                (int)$input['classe'], 
                (int)$input['professor'],
                $input['alunos'], 
                floatval($input['oferta_classe'] ?? 0),
                intval($input['total_visitantes'] ?? 0), 
                intval($input['total_biblias'] ?? 0),
                intval($input['total_revistas'] ?? 0)
            );
            
            if ($resultado['sucesso']) {
                sendResponse('success', ['chamada_id' => $resultado['chamada_id']], $resultado['mensagem']);
            } else {
                sendResponse('error', null, $resultado['mensagem']);
            }
            break;

        case 'listarChamadas':
            $filtros = [];
            if (!empty($input['congregacao_id'])) $filtros['congregacao_id'] = (int)$input['congregacao_id'];
            if (!empty($input['classe_id'])) $filtros['classe_id'] = (int)$input['classe_id'];
            if (!empty($input['trimestre'])) $filtros['trimestre'] = $input['trimestre'];
            if (!empty($input['data_inicio'])) $filtros['data_inicio'] = $input['data_inicio'];
            if (!empty($input['data_fim'])) $filtros['data_fim'] = $input['data_fim'];
            
            sendResponse('success', $chamadaModel->listarChamadas($filtros));
            break;

        case 'getChamada':
            $chamadaId = filter_var($input['chamada_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$chamadaId) {
                sendResponse('error', null, 'ID da chamada inválido.');
            }
            sendResponse('success', $chamadaModel->getChamadaDetalhada($chamadaId));
            break;

        case 'atualizarChamada':
            $required = ['chamada_id', 'data', 'classe', 'professor', 'alunos'];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    sendResponse('error', null, "Campo obrigatório faltando: $field");
                }
            }
            
            $chamadaId = filter_var($input['chamada_id'], FILTER_VALIDATE_INT);
            if (!$chamadaId) {
                sendResponse('error', null, 'ID da chamada inválido.');
            }
            
            $resultado = $chamadaModel->atualizarChamada(
                $chamadaId, 
                $input['data'], 
                $input['trimestre'] ?? date('Y') . '-T' . ceil(date('n') / 3),
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
                sendResponse('error', null, $resultado['mensagem']);
            }
            break;

        case 'excluirChamada':
            $chamadaId = filter_var($input['chamada_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$chamadaId) {
                sendResponse('error', null, 'ID da chamada inválido.');
            }
            $resultado = $chamadaModel->excluirChamada($chamadaId);
            sendResponse($resultado['sucesso'] ? 'success' : 'error', null, $resultado['mensagem']);
            break;

        case 'verificarChamadaExistente':
            $data = $input['data'] ?? '';
            $classeId = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $congregacaoId = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            
            if (!$data || !$classeId) {
                sendResponse('error', null, 'Data e classe são obrigatórios.');
            }
            
            $chamada = $chamadaModel->verificarChamadaExistente($data, $classeId, $congregacaoId);
            sendResponse('success', ['existe' => !empty($chamada), 'chamada' => $chamada]);
            break;

        default:
            sendResponse('error', null, 'Ação inválida: ' . htmlspecialchars($acao));
    }
} catch (Exception $e) {
    error_log("Erro no Controller: " . $e->getMessage());
    sendResponse('error', null, 'Erro interno: ' . $e->getMessage());
}
?>