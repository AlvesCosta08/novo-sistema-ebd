<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$base_path = dirname(__DIR__);
require_once $base_path . '/config/conexao.php';
require_once $base_path . '/models/matricula.php';
require_once $base_path . '/utils/trimestre.php';

// Garantir que a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está autenticado
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado.']);
    exit;
}

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

class MatriculaController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->model = new Matricula($pdo);
        $this->pdo = $pdo;
    }

    private function verificarPermissaoCongregacao($congregacao_id = null) {
        $perfil = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
        if ($perfil === 'admin') return true;
        $usuario_congregacao = $_SESSION['congregacao_id'] ?? null;
        return !($congregacao_id && $usuario_congregacao != $congregacao_id);
    }

    private function getUsuarioCongregacaoId() {
        $perfil = $_SESSION['perfil'] ?? $_SESSION['usuario_perfil'] ?? 'professor';
        return ($perfil === 'admin') ? null : ($_SESSION['congregacao_id'] ?? null);
    }

    // ==================== LISTAR ====================
    public function listarMatriculas() {
        try {
            $draw = $_POST['draw'] ?? 1;
            $start = $_POST['start'] ?? 0;
            $length = $_POST['length'] ?? 10;
            $filters = [
                'busca'       => $_POST['search']['value'] ?? '',
                'congregacao' => $_POST['congregacao'] ?? $this->getUsuarioCongregacaoId(),
                'trimestre'   => $_POST['trimestre'] ?? '',
                'status'      => $_POST['status'] ?? ''
            ];
            $resultado = $this->model->listarMatriculas($length, $start, $filters);
            echo json_encode([
                'draw'            => intval($draw),
                'recordsTotal'    => intval($resultado['recordsTotal']),
                'recordsFiltered' => intval($resultado['recordsFiltered'] ?? $resultado['recordsTotal']),
                'sucesso'         => true,
                'dados'           => $resultado['data']
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar matrículas: ' . $e->getMessage()]);
        }
    }

    // ==================== CRIAR ====================
    public function criarMatricula($data) {
        $congregacao_id = $data['congregacao_id'] ?? null;
        
        // LOG PARA DEBUG
        error_log("=== criarMatricula ===");
        error_log("Trimestre recebido: " . ($data['trimestre'] ?? 'NULO'));
        error_log("Aluno ID: " . ($data['aluno_id'] ?? 'NULO'));
        
        if (!$this->verificarPermissaoCongregacao($congregacao_id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permissão para criar matrícula nesta congregação.']);
            return;
        }
        
        if (empty($data['aluno_id']) || empty($data['classe_id']) || empty($data['congregacao_id']) ||
            empty($data['status']) || empty($data['professor_id']) || empty($data['trimestre'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos obrigatórios devem ser preenchidos.']);
            return;
        }
        
        // VERIFICAR APENAS NO MESMO TRIMESTRE
        $existeMesmoTrimestre = $this->model->verificarMatriculaExistenteNoMesmoTrimestre(
            $data['aluno_id'], 
            $data['trimestre']
        );
        
        error_log("Existe matrícula no mesmo trimestre? " . ($existeMesmoTrimestre ? "SIM" : "NÃO"));
        
        if ($existeMesmoTrimestre) {
            // Buscar detalhes da matrícula existente para mensagem mais clara
            $matriculaExistente = $this->model->buscarMatriculaPorAlunoETrimestre(
                $data['aluno_id'], 
                $data['trimestre']
            );
            
            $mensagem = 'Este aluno já possui uma matrícula ativa neste trimestre.';
            if ($matriculaExistente) {
                $mensagem .= " (ID: {$matriculaExistente['id']}, Status: {$matriculaExistente['status']})";
            }
            
            echo json_encode(['sucesso' => false, 'mensagem' => $mensagem]);
            return;
        }
        
        try {
            $this->model->criarMatricula($data);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula criada com sucesso.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    // ==================== BUSCAR MATRÍCULA ====================
    public function buscarMatricula($id = null, $aluno_id = null) {
        if ((empty($id) || $id == 0) && !empty($aluno_id)) {
            $matricula = $this->model->buscarMatriculaAtivaPorAlunoId($aluno_id);
            if ($matricula) {
                $id = $matricula['id'];
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma matrícula ativa encontrada para este aluno.']);
                return;
            }
        }
        if (!is_numeric($id) || empty($id) || $id == 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID de matrícula inválido.']);
            return;
        }
        try {
            $matricula = $this->model->buscarMatriculaPorId($id);
            if (!$matricula) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.']);
                return;
            }
            if (!$this->verificarPermissaoCongregacao($matricula['congregacao_id'])) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permissão para acessar esta matrícula.']);
                return;
            }
            echo json_encode(['sucesso' => true, 'dados' => $matricula]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrícula: ' . $e->getMessage()]);
        }
    }

    // ==================== BUSCAR MATRÍCULAS POR ALUNO ====================
    public function buscarMatriculasPorAluno($aluno_id) {
        if (!is_numeric($aluno_id) || empty($aluno_id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do aluno inválido.']);
            return;
        }
        try {
            $matriculas = $this->model->buscarMatriculasPorAlunoId((int)$aluno_id);
            if (!empty($matriculas)) {
                $perfil = $_SESSION['perfil'] ?? 'professor';
                if ($perfil !== 'admin') {
                    $usuario_congregacao = $_SESSION['congregacao_id'] ?? null;
                    if ($usuario_congregacao) {
                        $matriculas = array_filter($matriculas, fn($m) => $m['congregacao_id'] == $usuario_congregacao);
                        $matriculas = array_values($matriculas);
                    }
                }
            }
            echo json_encode(['sucesso' => true, 'dados' => $matriculas, 'total' => count($matriculas)]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrículas do aluno: ' . $e->getMessage()]);
        }
    }

    // ==================== BUSCAR MATRÍCULA ATIVA POR ALUNO_ID ====================
    public function buscarMatriculaAtivaPorAluno($aluno_id) {
        if (!is_numeric($aluno_id) || empty($aluno_id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do aluno inválido.']);
            return;
        }
        try {
            $matricula = $this->model->buscarMatriculaAtivaPorAlunoId((int)$aluno_id);
            if (!$matricula) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma matrícula ativa encontrada para este aluno.']);
                return;
            }
            if (!$this->verificarPermissaoCongregacao($matricula['congregacao_id'])) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permissão para acessar esta matrícula.']);
                return;
            }
            echo json_encode(['sucesso' => true, 'dados' => $matricula]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrícula ativa: ' . $e->getMessage()]);
        }
    }

    // ==================== ATUALIZAR ====================
    public function atualizarMatricula($id = null, $data = []) {
        try {
            if ((empty($id) || $id == 0) && !empty($data['aluno_id'])) {
                $matricula = $this->model->buscarMatriculaAtivaPorAlunoId($data['aluno_id']);
                if (!$matricula) {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma matrícula ativa encontrada para este aluno.']);
                    return;
                }
                $id = $matricula['id'];
            }
            if (empty($id) || $id == 0) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID da matrícula inválido.']);
                return;
            }
            $existente = $this->model->buscarMatriculaPorId($id);
            if (!$existente) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.']);
                return;
            }
            if (!$this->verificarPermissaoCongregacao($existente['congregacao_id'])) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permissão para editar esta matrícula.']);
                return;
            }
            $this->model->atualizarMatricula($id, $data);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula atualizada com sucesso.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    // ==================== EXCLUIR ====================
    public function excluirMatricula($id = null, $aluno_id = null) {
        try {
            if ((empty($id) || $id == 0) && !empty($aluno_id)) {
                $matricula = $this->model->buscarMatriculaAtivaPorAlunoId($aluno_id);
                if (!$matricula) {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma matrícula ativa encontrada para este aluno.']);
                    return;
                }
                $id = $matricula['id'];
            }
            if (empty($id) || $id == 0) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID da matrícula inválido.']);
                return;
            }
            $existente = $this->model->buscarMatriculaPorId($id);
            if (!$existente) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.']);
                return;
            }
            if (!$this->verificarPermissaoCongregacao($existente['congregacao_id'])) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permissão para excluir esta matrícula.']);
                return;
            }
            $this->model->excluirMatricula($id);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula excluída com sucesso.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    // ==================== CARREGAR SELECTS ====================
    public function carregarSelects() {
        try {
            $selects = $this->model->carregarSelects();
            
            $perfil = $_SESSION['perfil'] ?? 'professor';
            if ($perfil !== 'admin') {
                $congregacao_id = $_SESSION['congregacao_id'] ?? null;
                if ($congregacao_id && isset($selects['usuarios'])) {
                    $selects['usuarios'] = array_values($selects['usuarios']);
                }
            }
            
            echo json_encode(['sucesso' => true, 'dados' => $selects]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao carregar dados: ' . $e->getMessage()]);
        }
    }

    // ==================== GET TRIMESTRES SUGERIDOS ====================
    public function getTrimestresSugeridos() {
        try {
            $trimestres = [];
            $ano_atual = date('Y');
            $trimestre_atual = getTrimestreAtual();
            
            for ($ano = $ano_atual - 2; $ano <= $ano_atual + 1; $ano++) {
                for ($t = 1; $t <= 4; $t++) {
                    $trimestre = "{$ano}-T{$t}";
                    $trimestres[] = [
                        'valor' => $trimestre,
                        'label' => formatarTrimestre($trimestre),
                        'atual' => ($trimestre === $trimestre_atual)
                    ];
                }
            }
            
            echo json_encode(['sucesso' => true, 'dados' => $trimestres]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao obter trimestres: ' . $e->getMessage()]);
        }
    }

    // ==================== LISTAR CONGREGAÇÕES ====================
    public function listarCongregacoes() {
        try {
            $sql = "SELECT id, nome FROM congregacoes ORDER BY nome";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $congregacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $congregacoes]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar congregações: ' . $e->getMessage()]);
        }
    }

    // ==================== ESTATÍSTICAS ====================
    public function getEstatisticas() {
        try {
            $trimestre_atual = getTrimestreAtual();
            $congregacao_filter = $this->getUsuarioCongregacaoId();
            $sql = "SELECT 
                        COUNT(DISTINCT m.id) as total_matriculas,
                        COUNT(DISTINCT m.aluno_id) as total_alunos,
                        COUNT(DISTINCT CASE WHEN m.status = 'ativo' THEN m.aluno_id END) as ativos,
                        COUNT(DISTINCT c.id) as total_classes
                    FROM matriculas m
                    JOIN classes c ON m.classe_id = c.id
                    WHERE m.trimestre = :trimestre";
            
            $params = [':trimestre' => $trimestre_atual];
            
            if ($congregacao_filter) {
                $sql .= " AND m.congregacao_id = :congregacao_id";
                $params[':congregacao_id'] = $congregacao_filter;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['sucesso' => true, 'dados' => $estatisticas]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar estatísticas: ' . $e->getMessage()]);
        }
    }

    // ==================== MIGRAR MATRÍCULAS ====================
    public function migrarMatriculas($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
        try {
            if (!$this->verificarPermissaoCongregacao($congregacao_id)) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Sem permissão para migrar matrículas nesta congregação.']);
                return;
            }
            
            if (empty($trimestre_atual) || empty($trimestre_novo)) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Trimestre atual e novo trimestre são obrigatórios.']);
                return;
            }
            
            if ($trimestre_atual === $trimestre_novo) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'O trimestre atual e o novo trimestre não podem ser iguais.']);
                return;
            }
            
            $resultado = $this->model->migrarMatriculasParaNovoTrimestre(
                $trimestre_atual, 
                $trimestre_novo, 
                $congregacao_id, 
                $manter_status
            );
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao migrar matrículas: ' . $e->getMessage()]);
        }
    }
}

// ==================== ROTEAMENTO ====================
// Garantir que o PDO está definido
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Conexão com banco de dados não estabelecida.']);
    exit;
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;
if (!$acao) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetro "acao" não especificado.']);
    exit;
}

$controller = new MatriculaController($pdo);

switch ($acao) {
    case 'listarMatriculas':            $controller->listarMatriculas(); break;
    case 'criarMatricula':              $controller->criarMatricula($_POST); break;
    case 'atualizarMatricula':          $controller->atualizarMatricula($_POST['id'] ?? null, $_POST); break;
    case 'excluirMatricula':            $controller->excluirMatricula($_POST['id'] ?? null, $_POST['aluno_id'] ?? null); break;
    case 'carregarSelects':             $controller->carregarSelects(); break;
    case 'buscarMatricula':             $controller->buscarMatricula($_POST['id'] ?? null, $_POST['aluno_id'] ?? null); break;
    case 'buscarMatriculasPorAluno':    $controller->buscarMatriculasPorAluno($_POST['aluno_id'] ?? null); break;
    case 'buscarMatriculaAtivaPorAluno': $controller->buscarMatriculaAtivaPorAluno($_POST['aluno_id'] ?? null); break;
    case 'getTrimestresSugeridos':      $controller->getTrimestresSugeridos(); break;
    case 'getEstatisticas':             $controller->getEstatisticas(); break;
    case 'listarCongregacoes':          $controller->listarCongregacoes(); break;
    case 'migrarMatriculas':            $controller->migrarMatriculas(
                                            $_POST['trimestre_atual'] ?? '',
                                            $_POST['novo_trimestre'] ?? '',
                                            $_POST['congregacao_id'] ?? '',
                                            $_POST['manter_status'] ?? true
                                        ); break;
    default:
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida: ' . htmlspecialchars($acao)]);
        exit;
}