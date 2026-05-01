<?php
require_once '../config/conexao.php';
require_once '../models/matricula.php';
require_once '../utils/trimestre.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

class MatriculaController {
    private $model;

    public function __construct($pdo) {
        $this->model = new Matricula($pdo);
    }

    public function listarMatriculas() {
        try {
            $draw = $_POST['draw'] ?? 1;
            $start = $_POST['start'] ?? 0;
            $length = $_POST['length'] ?? 10;
            
            $filters = [
                'busca' => $_POST['search']['value'] ?? '',
                'congregacao' => $_POST['congregacao'] ?? '',
                'trimestre' => $_POST['trimestre'] ?? '',
                'status' => $_POST['status'] ?? ''
            ];

            $resultado = $this->model->listarMatriculas($length, $start, $filters);
            
            echo json_encode([
                'draw' => intval($draw),
                'recordsTotal' => intval($resultado['recordsTotal']),
                'recordsFiltered' => intval($resultado['recordsTotal']),
                'sucesso' => true,
                'dados' => $resultado['data']
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("Erro no listarMatriculas (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrículas.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function criarMatricula($data) {
        if (empty($data['aluno_id']) || empty($data['classe_id']) || 
            empty($data['congregacao_id']) || empty($data['status']) || 
            empty($data['professor_id']) || empty($data['trimestre'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos obrigatórios devem ser preenchidos.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($this->model->verificarMatriculaExistenteNoMesmoTrimestre($data['aluno_id'], $data['trimestre'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Este aluno já está matriculado em outra classe neste trimestre.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!empty($data['data_matricula']) && strtotime($data['data_matricula']) === false) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Data de matrícula inválida.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $this->model->criarMatricula($data);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula criada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro ao criar matrícula (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function buscarMatricula($id) {
        if (!is_numeric($id) || empty($id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $matricula = $this->model->buscarMatriculaPorId($id);
            if ($matricula) {
                echo json_encode(['sucesso' => true, 'dados' => $matricula], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar matrícula (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrícula.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function atualizarMatricula($id, $data) {
        if (empty($data['aluno_id']) || empty($data['classe_id']) || 
           empty($data['congregacao_id']) || empty($data['professor_id']) || 
           empty($data['trimestre']) || empty($data['status'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos obrigatórios devem ser preenchidos.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $matriculaExistente = $this->model->buscarMatriculaPorId($id);
            if (!$matriculaExistente) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($matriculaExistente['aluno_id'] != $data['aluno_id'] || 
                $matriculaExistente['trimestre'] != $data['trimestre']) {
                if ($this->model->verificarMatriculaExistenteNoMesmoTrimestre($data['aluno_id'], $data['trimestre'])) {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Este aluno já está matriculado em outra classe neste trimestre.'], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            $resultado = $this->model->atualizarMatricula($id, $data);
            
            if ($resultado) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar matrícula.'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar matrícula (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar matrícula: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function excluirMatricula($id) {
        if (!is_numeric($id) || empty($id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $this->model->verificarMatriculaExistenteParaExclusao($id);
            $this->model->excluirMatricula($id);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula excluída com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro ao excluir matrícula (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir matrícula.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function carregarSelects() {
        try {
            $dados = $this->model->carregarSelects();
            echo json_encode(['sucesso' => true, 'dados' => $dados], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro no carregarSelects (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao carregar dados dos selects.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getTrimestresSugeridos() {
        try {
            $trimestres = calcularTrimestres();
            echo json_encode(['sucesso' => true, 'dados' => $trimestres], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro ao calcular trimestres: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao calcular trimestres.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function migrarMatriculas($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
        try {
            if (empty($trimestre_atual) || empty($trimestre_novo) || empty($congregacao_id)) {
                throw new Exception("Trimestre atual, novo trimestre e congregação são obrigatórios.");
            }
            
            if ($trimestre_atual === $trimestre_novo) {
                throw new Exception("O novo trimestre deve ser diferente do trimestre atual.");
            }

            if (!validarFormatoTrimestre($trimestre_atual) || !validarFormatoTrimestre($trimestre_novo)) {
                throw new Exception("Formato de trimestre inválido. Use: AAAA-T1, AAAA-T2, AAAA-T3 ou AAAA-T4");
            }

            $manter_status = filter_var($manter_status, FILTER_VALIDATE_BOOLEAN);
                
            $resultado = $this->model->migrarMatriculasParaNovoTrimestre(
                $trimestre_atual, 
                $trimestre_novo, 
                $congregacao_id, 
                $manter_status
            );
            
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Erro no migrarMatriculas (Controller): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}

// ============================================
// ROTEAMENTO
// ============================================
global $pdo;

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Conexão com banco de dados não estabelecida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;

if (!$acao) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetro "acao" não especificado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$controller = new MatriculaController($pdo);

switch ($acao) {
    case 'listarMatriculas':
        $controller->listarMatriculas();
        break;
        
    case 'criarMatricula':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $controller->criarMatricula($data);
        } else {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'atualizarMatricula':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller->atualizarMatricula($id, $data);
        } else {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'excluirMatricula':
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $controller->excluirMatricula($id);
        break;
        
    case 'carregarSelects':
        $controller->carregarSelects();
        break;
        
    case 'buscarMatricula':
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $controller->buscarMatricula($id);
        break;
        
    case 'getTrimestresSugeridos':
        $controller->getTrimestresSugeridos();
        break;
        
    case 'migrarMatriculas':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if (!isset($data['trimestre_atual']) || !isset($data['novo_trimestre']) || !isset($data['congregacao_id'])) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos para migração.'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $controller->migrarMatriculas(
                $data['trimestre_atual'],
                $data['novo_trimestre'],
                $data['congregacao_id'],
                $data['manter_status'] ?? true
            );
        } else {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
        }
        break;           
        
    default:
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida: ' . htmlspecialchars($acao)], JSON_UNESCAPED_UNICODE);
        break;
}
?>