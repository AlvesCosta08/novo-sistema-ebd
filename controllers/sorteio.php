<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../config/conexao.php';

header('Content-Type: application/json');

function validarParametros(array $campos, array $dados) {
    $faltantes = [];
    foreach ($campos as $campo) {
        if (!isset($dados[$campo]) || $dados[$campo] === '') {
            $faltantes[] = $campo;
        }
    }
    if (!empty($faltantes)) {
        throw new Exception('Parâmetros obrigatórios faltando: ' . implode(', ', $faltantes));
    }
}

function calcularTrimestre($data) {
    $mes = (int)$data->format('n');
    return ceil($mes / 3);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $acao = $input['acao'] ?? '';
    
    switch ($acao) {
        case 'getAlunosParaSorteio':
            validarParametros(['classe_id', 'congregacao_id', 'trimestre'], $input);
            
            $classeId = (int)$input['classe_id'];
            $congregacaoId = (int)$input['congregacao_id'];
            $trimestre = (int)$input['trimestre'];
            
            $anoAtual = date('Y');
            $trimestreBusca = "{$anoAtual}-T{$trimestre}";
            
            $sql = "
                SELECT DISTINCT a.id, a.nome, c.nome AS classe_nome
                FROM alunos a
                JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
                JOIN classes c ON c.id = m.classe_id
                WHERE m.classe_id = :classe_id
                  AND m.congregacao_id = :congregacao_id
                  AND m.trimestre = :trimestre_busca
                ORDER BY a.nome
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':classe_id' => $classeId,
                ':congregacao_id' => $congregacaoId,
                ':trimestre_busca' => $trimestreBusca
            ]);
            
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'data' => $alunos
            ]);
            break;
            
        case 'realizarSorteio':
            validarParametros(['alunos_ids', 'classe_id', 'congregacao_id'], $input);
            
            $alunosIds = array_map('intval', $input['alunos_ids']);
            $classeId = (int)$input['classe_id'];
            $congregacaoId = (int)$input['congregacao_id'];
            
            if (empty($alunosIds)) {
                throw new Exception("Nenhum aluno válido selecionado");
            }
            
            $dataHoje = new DateTime();
            $trimestreAtual = calcularTrimestre($dataHoje);
            $anoAtual = (int)$dataHoje->format('Y');
            
            // CORREÇÃO: Usar apenas placeholders nomeados (sem misturar com ?)
            // Primeiro, criamos os placeholders para o IN clause
            $placeholders = [];
            foreach ($alunosIds as $index => $id) {
                $placeholders[] = ":aluno_id_{$index}";
            }
            $inClause = implode(', ', $placeholders);
            
            $sqlVerifica = "
                SELECT aluno_id
                FROM sorteios
                WHERE classe_id = :classe_id
                  AND congregacao_id = :congregacao_id
                  AND YEAR(data_sorteio) = :ano_atual
                  AND CEIL(MONTH(data_sorteio) / 3) = :trimestre_atual
                  AND aluno_id IN ({$inClause})
            ";
            
            $stmtVerifica = $pdo->prepare($sqlVerifica);
            
            // Bind dos parâmetros fixos
            $stmtVerifica->bindValue(':classe_id', $classeId, PDO::PARAM_INT);
            $stmtVerifica->bindValue(':congregacao_id', $congregacaoId, PDO::PARAM_INT);
            $stmtVerifica->bindValue(':ano_atual', $anoAtual, PDO::PARAM_INT);
            $stmtVerifica->bindValue(':trimestre_atual', $trimestreAtual, PDO::PARAM_INT);
            
            // Bind dos parâmetros dos alunos_ids
            foreach ($alunosIds as $index => $id) {
                $stmtVerifica->bindValue(":aluno_id_{$index}", $id, PDO::PARAM_INT);
            }
            
            $stmtVerifica->execute();
            $sorteadosNesteTrimestre = $stmtVerifica->fetchAll(PDO::FETCH_COLUMN, 0);
            
            // Filtra alunos elegíveis
            $alunosElegiveis = array_diff($alunosIds, $sorteadosNesteTrimestre);
            
            if (empty($alunosElegiveis)) {
                throw new Exception("Nenhum dos alunos selecionados está elegível para sorteio. Todos já foram sorteados neste trimestre ({$trimestreAtual}/{$anoAtual}).");
            }
            
            // Sorteia entre os elegíveis
            $ganhadorId = $alunosElegiveis[array_rand($alunosElegiveis)];
            
            // Busca dados do ganhador
            $sql = "
                SELECT a.id, a.nome, c.nome AS classe_nome
                FROM alunos a
                JOIN matriculas m ON a.id = m.aluno_id AND m.classe_id = :classe_id
                JOIN classes c ON c.id = m.classe_id
                WHERE a.id = :aluno_id
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $ganhadorId,
                ':classe_id' => $classeId
            ]);
            $ganhador = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ganhador) {
                throw new Exception("Aluno sorteado não encontrado no banco de dados.");
            }
            
            // Insere sorteio
            $sql = "
                INSERT INTO sorteios (aluno_id, classe_id, congregacao_id, data_sorteio)
                VALUES (:aluno_id, :classe_id, :congregacao_id, NOW())
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $ganhadorId,
                ':classe_id' => $classeId,
                ':congregacao_id' => $congregacaoId
            ]);
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'ganhador' => $ganhador,
                    'data_sorteio' => date('d/m/Y H:i:s'),
                    'trimestre_sorteio' => $trimestreAtual,
                    'ano_sorteio' => $anoAtual
                ]
            ]);
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Ação não reconhecida']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}