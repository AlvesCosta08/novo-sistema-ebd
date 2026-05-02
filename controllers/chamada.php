<?php
// controllers/chamada.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

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

try {
    require_once __DIR__ . '/../config/conexao.php';
    require_once __DIR__ . '/../models/chamada.php';
} catch (Exception $e) {
    sendResponse('error', 'Erro de configuração: ' . $e->getMessage());
}

if (!isset($pdo) || !$pdo) {
    sendResponse('error', 'Erro crítico: conexão não estabelecida.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Método inválido. Use POST.');
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE && empty($_POST)) {
    sendResponse('error', 'Corpo da requisição inválido.');
}
if (empty($input)) $input = $_POST;

$acao = $input['acao'] ?? '';
if (empty($acao)) sendResponse('error', 'Ação não especificada.');

try {
    $chamadaModel = new Chamada($pdo);
} catch (Exception $e) {
    sendResponse('error', 'Falha ao inicializar módulo: ' . $e->getMessage());
}

try {
    switch ($acao) {
        case 'getCongregacoes':
            sendResponse('success', $chamadaModel->getCongregacoes());
            break;

        case 'getClassesByCongregacao':
            $congregacao_id = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$congregacao_id) sendResponse('error', 'ID da congregação inválido.');
            sendResponse('success', $chamadaModel->getClassesByCongregacao($congregacao_id));
            break;

        case 'getAlunosByClasse':
            $classe_id = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $congregacao_id = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            $trimestre = $input['trimestre'] ?? '';
            $ano = $input['ano'] ?? date('Y');
            if (!$classe_id || !$congregacao_id || empty($trimestre)) {
                sendResponse('error', 'Parâmetros incompletos (Classe, Congregação, Trimestre).');
            }
            $trimestrePadronizado = $chamadaModel->padronizarTrimestre($trimestre, $ano);
            sendResponse('success', $chamadaModel->getAlunosByClasse($classe_id, $congregacao_id, $trimestrePadronizado));
            break;

        case 'salvarChamada':
            $required = ['data', 'classe', 'professor', 'alunos', 'trimestre'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) $missing[] = $field;
            }
            if (!empty($missing)) sendResponse('error', 'Campos obrigatórios faltando: ' . implode(', ', $missing));
            if (!DateTime::createFromFormat('Y-m-d', $input['data'])) sendResponse('error', 'Formato de data inválido. Use YYYY-MM-DD');
            if (!is_array($input['alunos']) || empty($input['alunos'])) sendResponse('error', 'Lista de alunos inválida ou vazia.');

            $trimestrePadronizado = $chamadaModel->padronizarTrimestre($input['trimestre']);
            $resultado = $chamadaModel->registrarChamada(
                $input['data'], $trimestrePadronizado, (int)$input['classe'], (int)$input['professor'],
                $input['alunos'], floatval($input['oferta_classe'] ?? 0),
                intval($input['total_visitantes'] ?? 0), intval($input['total_biblias'] ?? 0),
                intval($input['total_revistas'] ?? 0)
            );
            if ($resultado['sucesso']) {
                sendResponse('success', ['chamada_id' => $resultado['chamada_id'] ?? null], $resultado['mensagem']);
            } else {
                sendResponse('error', $resultado['mensagem']);
            }
            break;

        case 'listarChamadas':
            $filtros = [];
            if (!empty($input['congregacao_id'])) $filtros['congregacao_id'] = (int)$input['congregacao_id'];
            if (!empty($input['classe_id'])) $filtros['classe_id'] = (int)$input['classe_id'];
            if (!empty($input['trimestre'])) {
                $numeroTrimestre = $chamadaModel->extrairNumeroTrimestre($input['trimestre']);
                if ($numeroTrimestre) {
                    $filtros['trimestre_numero'] = $numeroTrimestre;
                    if (!empty($input['ano'])) $filtros['ano'] = $input['ano'];
                    elseif (preg_match('/^(\d{4})/', $input['trimestre'], $matches)) $filtros['ano'] = $matches[1];
                } else {
                    $filtros['trimestre'] = $chamadaModel->padronizarTrimestre($input['trimestre']);
                }
            } elseif (!empty($input['trimestre_numero'])) {
                $filtros['trimestre_numero'] = $input['trimestre_numero'];
                if (!empty($input['ano'])) $filtros['ano'] = $input['ano'];
            }
            if (!empty($input['data_inicio'])) $filtros['data_inicio'] = $input['data_inicio'];
            if (!empty($input['data_fim'])) $filtros['data_fim'] = $input['data_fim'];
            sendResponse('success', $chamadaModel->listarChamadas($filtros));
            break;

        case 'getChamada':
            $chamadaId = filter_var($input['chamada_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$chamadaId) sendResponse('error', 'ID da chamada inválido.');
            try {
                sendResponse('success', $chamadaModel->getChamadaDetalhada($chamadaId));
            } catch (Exception $e) {
                sendResponse('error', $e->getMessage());
            }
            break;

        case 'atualizarChamada':
            $required = ['chamada_id', 'data', 'trimestre', 'classe', 'professor', 'alunos'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($input[$field])) $missing[] = $field;
            }
            if (!empty($missing)) sendResponse('error', 'Campos obrigatórios faltando: ' . implode(', ', $missing));
            $chamadaId = filter_var($input['chamada_id'], FILTER_VALIDATE_INT);
            if (!$chamadaId) sendResponse('error', 'ID da chamada inválido.');
            if (!DateTime::createFromFormat('Y-m-d', $input['data'])) sendResponse('error', 'Formato de data inválido.');
            $trimestrePadronizado = $chamadaModel->padronizarTrimestre($input['trimestre']);
            $resultado = $chamadaModel->atualizarChamada(
                $chamadaId, $input['data'], $trimestrePadronizado, (int)$input['classe'], (int)$input['professor'],
                $input['alunos'], floatval($input['oferta_classe'] ?? 0),
                intval($input['total_visitantes'] ?? 0), intval($input['total_biblias'] ?? 0),
                intval($input['total_revistas'] ?? 0)
            );
            if ($resultado['sucesso']) sendResponse('success', null, $resultado['mensagem']);
            else sendResponse('error', $resultado['mensagem']);
            break;

        case 'excluirChamada':
            $chamadaId = filter_var($input['chamada_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$chamadaId) sendResponse('error', 'ID da chamada inválido.');
            $resultado = $chamadaModel->excluirChamada($chamadaId);
            if ($resultado['sucesso']) sendResponse('success', null, $resultado['mensagem']);
            else sendResponse('error', $resultado['mensagem']);
            break;

        case 'corrigirTrimestres':
            $ano = $input['ano'] ?? date('Y');
            $resultado = $chamadaModel->corrigirTrimestresAntigos($ano);
            if ($resultado['sucesso']) sendResponse('success', $resultado['dados'] ?? null, $resultado['mensagem']);
            else sendResponse('error', $resultado['mensagem']);
            break;

        // ==================== VERIFICAR CHAMADA EXISTENTE (CORRIGIDO) ====================
        case 'verificarChamadaExistente':
            $data = $input['data'] ?? '';
            $classeId = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $congregacaoId = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            if (!$data || !$classeId) sendResponse('error', 'Data e classe são obrigatórios.');

            try {
                $sql = "
                    SELECT c.*, cl.nome as nome_classe, cong.nome as nome_congregacao
                    FROM chamadas c
                    INNER JOIN classes cl ON cl.id = c.classe_id
                    INNER JOIN congregacoes cong ON cong.id = c.congregacao_id
                    WHERE c.data = :data AND c.classe_id = :classe_id
                ";
                if ($congregacaoId) $sql .= " AND c.congregacao_id = :congregacao_id";
                $sql .= " LIMIT 1";

                $stmt = $pdo->prepare($sql);
                $params = [':data' => $data, ':classe_id' => $classeId];
                if ($congregacaoId) $params[':congregacao_id'] = $congregacaoId;
                $stmt->execute($params);
                $chamada = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($chamada) {
                    // GARANTE QUE O ID SEJA UM INTEIRO VÁLIDO
                    $chamada['id'] = (int)$chamada['id'];
                    sendResponse('success', [
                        'existe' => true,
                        'chamada' => $chamada
                    ], 'Já existe uma chamada para esta data.');
                } else {
                    sendResponse('success', ['existe' => false], 'Nenhuma chamada encontrada.');
                }
            } catch (PDOException $e) {
                sendResponse('error', 'Erro ao verificar chamada: ' . $e->getMessage());
            }
            break;

        case 'getEstatisticas':
            // ... (mantido igual, já funciona)
            $congregacaoId = filter_var($input['congregacao_id'] ?? 0, FILTER_VALIDATE_INT);
            $classeId = filter_var($input['classe_id'] ?? 0, FILTER_VALIDATE_INT);
            $trimestre = $input['trimestre'] ?? '';
            try {
                $where = [];
                $params = [];
                if ($congregacaoId) { $where[] = "c.congregacao_id = :congregacao_id"; $params[':congregacao_id'] = $congregacaoId; }
                if ($classeId) { $where[] = "c.classe_id = :classe_id"; $params[':classe_id'] = $classeId; }
                if ($trimestre) {
                    $trimestrePadronizado = $chamadaModel->padronizarTrimestre($trimestre);
                    $where[] = "c.trimestre = :trimestre";
                    $params[':trimestre'] = $trimestrePadronizado;
                }
                $sql = "SELECT 
                            COUNT(DISTINCT c.id) as total_chamadas,
                            COALESCE(SUM(c.total_visitantes),0) as total_visitantes,
                            COALESCE(SUM(c.total_biblias),0) as total_biblias,
                            COALESCE(SUM(c.total_revistas),0) as total_revistas,
                            COALESCE(SUM(c.oferta_classe),0) as total_ofertas,
                            COALESCE(SUM(CASE WHEN p.presente='presente' THEN 1 ELSE 0 END),0) as total_presentes,
                            COALESCE(SUM(CASE WHEN p.presente='ausente' THEN 1 ELSE 0 END),0) as total_ausentes,
                            COALESCE(SUM(CASE WHEN p.presente='justificado' THEN 1 ELSE 0 END),0) as total_justificados
                        FROM chamadas c
                        LEFT JOIN presencas p ON p.chamada_id = c.id";
                if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
                $stmt = $pdo->prepare($sql);
                foreach ($params as $k => $v) $stmt->bindValue($k, $v);
                $stmt->execute();
                $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);
                foreach ($estatisticas as $k => $v) {
                    if (strpos($k, 'total_') === 0) $estatisticas[$k] = (int)$v;
                }
                $estatisticas['total_ofertas'] = (float)($estatisticas['total_ofertas'] ?? 0);
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
    sendResponse('error', 'Erro interno: ' . $e->getMessage());
}
?>