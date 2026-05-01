<?php

// Configurações iniciais de erro
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Inclui conexão PDO
require_once __DIR__ . '/../config/conexao.php';

// Recebe dados JSON ou POST
$input = file_get_contents('php://input');
if (!empty($input)) {
    $input = json_decode($input, true);
} else {
    $input = $_POST;
}

// Validação básica da ação
$acao = $input['acao'] ?? '';
if (empty($acao)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ação não especificada']);
    exit;
}

// Função para validar parâmetros obrigatórios
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

// Função para verificar existência de registro em uma tabela
function verificarExistencia(PDO $pdo, string $tabela, string $campo, $valor) {
    $sql = "SELECT COUNT(*) FROM {$tabela} WHERE {$campo} = :valor";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':valor' => $valor]);
    return $stmt->fetchColumn() > 0;
}

try {
    $pdo->beginTransaction();

    switch ($acao) {
        case 'getAlunosParaSorteio':
            validarParametros(['classe_id', 'congregacao_id', 'trimestre'], $input);

            $classeId = (int)$input['classe_id'];
            $congregacaoId = (int)$input['congregacao_id'];
            $trimestre = (int)$input['trimestre'];

            // Consulta corrigida para usar DISTINCT e filtrar por trimestre
            $stmt = $pdo->prepare("
                SELECT DISTINCT a.id, a.nome, c.nome AS classe_nome
                FROM alunos a
                JOIN matriculas m ON m.aluno_id = a.id 
                    AND m.status = 'ativo'
                    AND m.classe_id = :classe_id
                    AND m.congregacao_id = :congregacao_id
                    AND m.trimestre = :trimestre
                JOIN classes c ON c.id = m.classe_id
                LEFT JOIN presencas p ON p.aluno_id = a.id AND p.presente = 'presente'
                LEFT JOIN chamadas ch ON ch.id = p.chamada_id AND ch.classe_id = m.classe_id
                ORDER BY a.nome
            ");

            $stmt->execute([
                ':classe_id' => $classeId,
                ':congregacao_id' => $congregacaoId,
                ':trimestre' => $trimestre
            ]);

            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($alunos)) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [],
                    'message' => 'Nenhum aluno encontrado para o trimestre selecionado'
                ]);
                exit;
            }

            echo json_encode([
                'status' => 'success',
                'data' => $alunos
            ]);
            break;


        case 'realizarSorteio':
            validarParametros(['alunos_ids', 'classe_id', 'congregacao_id'], $input);

            $alunosIds = is_array($input['alunos_ids'])
                ? array_map('intval', $input['alunos_ids'])
                : array_map('intval', explode(',', $input['alunos_ids']));

            if (empty($alunosIds)) {
                throw new Exception("Nenhum aluno válido selecionado");
            }

            $classeId = (int)$input['classe_id'];
            $congregacaoId = (int)$input['congregacao_id'];

            // --- ALTERAÇÃO PARA CONSIDERAR O TRIMESTRE ATUAL ---
            $dataHoje = new DateTime();
            $trimestreAtual = ceil($dataHoje->format('n') / 3); // 1, 2, 3 ou 4
            $anoAtual = $dataHoje->format('Y');

            // Consulta para obter os alunos_ids que já foram sorteados no trimestre ATUAL
            // para a mesma classe e congregação.
            // Ajuste a query para usar um campo trimestre/ano se você tiver, ou calcular a partir da data.
            // Esta versão calcula trimestre e ano diretamente no SQL a partir de `data_sorteio`.
            $stmtVerifica = $pdo->prepare("
                SELECT aluno_id
                FROM sorteios
                WHERE classe_id = :classe_id
                  AND congregacao_id = :congregacao_id
                  AND YEAR(data_sorteio) = :ano_atual
                  AND CEIL(MONTH(data_sorteio) / 3) = :trimestre_atual
                  AND aluno_id IN (" . implode(',', $alunosIds) . ")
            ");

            $stmtVerifica->bindValue(':classe_id', $classeId, PDO::PARAM_INT);
            $stmtVerifica->bindValue(':congregacao_id', $congregacaoId, PDO::PARAM_INT);
            $stmtVerifica->bindValue(':ano_atual', $anoAtual, PDO::PARAM_INT);
            $stmtVerifica->bindValue(':trimestre_atual', $trimestreAtual, PDO::PARAM_INT);
            $stmtVerifica->execute();

            $sorteadosNesteTrimestre = $stmtVerifica->fetchAll(PDO::FETCH_COLUMN, 0); // Retorna array de IDs

            // Filtra os alunos elegíveis removendo os já sorteados no trimestre atual
            $alunosElegiveis = array_diff($alunosIds, $sorteadosNesteTrimestre);

            if (empty($alunosElegiveis)) {
                throw new Exception("Nenhum dos alunos selecionados está elegível para sorteio. Todos já foram sorteados neste trimestre (" . $trimestreAtual . "/" . $anoAtual . ").");
            }

            // Agora sorteia entre os ALUNOS ELEGÍVEIS
            $ganhadorId = $alunosElegiveis[array_rand($alunosElegiveis)];
            // --- FIM DA ALTERAÇÃO ---


            // Obtém dados do ganhador
            $stmt = $pdo->prepare("
                SELECT a.id, a.nome, c.nome AS classe_nome
                FROM alunos a
                JOIN matriculas m ON a.id = m.aluno_id AND m.classe_id = :classe_id
                JOIN classes c ON c.id = m.classe_id
                WHERE a.id = :aluno_id
            ");
            $stmt->execute([
                ':aluno_id' => $ganhadorId,
                ':classe_id' => $classeId
            ]);
            $ganhador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ganhador) {
                throw new Exception("Aluno sorteado não encontrado no banco de dados.");
            }

            // Registra o sorteio (usando apenas os campos existentes na sua tabela)
            $stmt = $pdo->prepare("
                INSERT INTO sorteios
                (aluno_id, classe_id, congregacao_id, data_sorteio)
                VALUES (:aluno_id, :classe_id, :congregacao_id, NOW())
            ");
            $stmt->execute([
                ':aluno_id' => $ganhadorId,
                ':classe_id' => $classeId,
                ':congregacao_id' => $congregacaoId,
                // ':trimestre' => $trimestreAtual, <-- REMOVIDO
                // ':ano' => $anoAtual              <-- REMOVIDO
            ]);

            $pdo->commit();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'ganhador' => $ganhador,
                    'data_sorteio' => date('d/m/Y H:i:s'),
                    'trimestre_sorteio' => $trimestreAtual, // Opcional: retornar trimestre
                    'ano_sorteio' => $anoAtual             // Opcional: retornar ano
                ]
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ação não reconhecida']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro no banco de dados',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}