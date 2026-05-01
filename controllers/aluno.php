<?php
require_once '../models/aluno.php';
require_once '../models/classe.php';
require_once '../config/conexao.php';

header('Content-Type: application/json');

$aluno = new Aluno($pdo);
$classe = new Classe($pdo);

// Função para validar os dados
function validarDados($dados) {
    if (empty($dados['nome']) || empty($dados['data_nascimento']) || empty($dados['telefone']) || empty($dados['classe_id'])) {
        return ["status" => "error", "message" => "Todos os campos são obrigatórios."];
    }
    return null;  // Dados válidos
}

$acao = $_GET['acao'] ?? '';

switch ($acao) {
    case 'listar':
        echo json_encode(["status" => "success", "data" => $aluno->listar()]);
        break;

    case 'buscar':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID inválido"]);
            break;
        }

        echo json_encode($aluno->buscar($id));
        break;

        case 'salvar':
            // Remover qualquer ID enviado indevidamente
            unset($_POST['id']);
        
            $dados = [
                'nome' => $_POST['nome'] ?? '',
                'data_nascimento' => $_POST['data_nascimento'] ?? '',
                'telefone' => $_POST['telefone'] ?? '',
                'classe_id' => $_POST['classe_id'] ?? ''
            ];
        
            // Valida os dados antes de salvar
            $erro = validarDados($dados);  
            if ($erro) {
                echo json_encode(["status" => "error", "message" => $erro]);
                break;
            }
        
            $resultado = $aluno->salvar($dados);
            echo json_encode($resultado);
            break;
        
        
        case 'editar':
                // Verifica se o ID foi enviado corretamente
                $id = $_POST['id'] ?? null;
            
                if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
                    echo json_encode(["status" => "error", "message" => "ID inválido"]);
                    break;
                }
            
                $dados = [
                    'nome' => $_POST['nome'] ?? '',
                    'data_nascimento' => $_POST['data_nascimento'] ?? '',
                    'telefone' => $_POST['telefone'] ?? '',
                    'classe_id' => $_POST['classe_id'] ?? ''
                ];
            
                // Valida os dados antes de editar
                $erro = validarDados($dados);
                if ($erro) {
                    echo json_encode(["status" => "error", "message" => $erro]);
                    break;
                }
            
                $resultado = $aluno->editar($id, $dados);
                echo json_encode($resultado);
                break;
            

        case 'excluir':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }

            echo json_encode($aluno->excluir($id));
            break;

        case 'listar_classes':
            echo json_encode(["status" => "success", "data" => $classe->listar()]);
            break;

        default:
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Ação inválida"]);
            break;
}
?>