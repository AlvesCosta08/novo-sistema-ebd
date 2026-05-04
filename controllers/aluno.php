<?php
require_once '../models/aluno.php';
require_once '../models/classe.php';
require_once '../config/conexao.php';

session_start();
header('Content-Type: application/json');

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado"]);
    exit;
}

// Função para validar os dados
function validarDados($dados) {
    $erros = [];
    
    if (empty(trim($dados['nome']))) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($dados['data_nascimento'])) {
        $erros[] = "Data de nascimento é obrigatória";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dados['data_nascimento'])) {
        $erros[] = "Data de nascimento inválida";
    }
    
    if (empty(trim($dados['telefone']))) {
        $erros[] = "Telefone é obrigatório";
    }
    
    if (empty($dados['classe_id']) || !filter_var($dados['classe_id'], FILTER_VALIDATE_INT)) {
        $erros[] = "Classe inválida";
    }
    
    if (!empty($erros)) {
        return ["status" => "error", "message" => implode(", ", $erros)];
    }
    
    return null; // Dados válidos
}

$aluno = new Aluno($pdo);
$classe = new Classe($pdo);

// Determinar método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$acao = $_GET['acao'] ?? '';

// Para POST com ação específica
if ($method === 'POST' && $acao) {
    switch ($acao) {
        case 'salvar':
            // Verificar dados recebidos
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'data_nascimento' => $_POST['data_nascimento'] ?? '',
                'telefone' => trim($_POST['telefone'] ?? ''),
                'classe_id' => $_POST['classe_id'] ?? ''
            ];
            
            // Validar dados
            $erro = validarDados($dados);
            if ($erro) {
                echo json_encode($erro);
                break;
            }
            
            // Limpar telefone (remover máscara)
            $dados['telefone'] = preg_replace('/\D/', '', $dados['telefone']);
            
            $resultado = $aluno->salvar($dados);
            echo json_encode($resultado);
            break;
            
        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            
            if (!$id || $id <= 0) {
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }
            
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'data_nascimento' => $_POST['data_nascimento'] ?? '',
                'telefone' => trim($_POST['telefone'] ?? ''),
                'classe_id' => $_POST['classe_id'] ?? ''
            ];
            
            // Validar dados
            $erro = validarDados($dados);
            if ($erro) {
                echo json_encode($erro);
                break;
            }
            
            // Limpar telefone
            $dados['telefone'] = preg_replace('/\D/', '', $dados['telefone']);
            
            $resultado = $aluno->editar($id, $dados);
            echo json_encode($resultado);
            break;
            
        case 'excluir':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            
            if (!$id || $id <= 0) {
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }
            
            echo json_encode($aluno->excluir($id));
            break;
            
        default:
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Ação POST inválida"]);
            break;
    }
} 
// Para GET
else if ($method === 'GET') {
    switch ($acao) {
        case 'listar':
            $resultado = $aluno->listar();
            echo json_encode(["status" => "success", "data" => $resultado]);
            break;
            
        case 'buscar':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }
            
            echo json_encode($aluno->buscar($id));
            break;
            
        case 'listar_classes':
            $resultado = $classe->listar();
            echo json_encode(["status" => "success", "data" => $resultado]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Ação GET inválida"]);
            break;
    }
} 
else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido"]);
}
?>