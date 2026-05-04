<?php
require_once '../config/conexao.php';

class Matricula {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lista matrículas com suporte a paginação server-side e filtros
     */
    public function listarMatriculas($limit = 10, $offset = 0, $filters = []) {
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS 
                            m.id, a.nome AS aluno, c.nome AS classe, cg.nome AS congregacao, 
                            u.nome AS usuario, m.data_matricula, m.status, m.trimestre, m.aluno_id,
                            m.classe_id, m.congregacao_id
                    FROM matriculas m
                    JOIN alunos a ON m.aluno_id = a.id
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    LEFT JOIN usuarios u ON m.usuario_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['busca'])) {
                $sql .= " AND (a.nome LIKE :busca OR c.nome LIKE :busca OR cg.nome LIKE :busca)";
                $params[':busca'] = "%{$filters['busca']}%";
            }
            if (!empty($filters['congregacao'])) {
                $sql .= " AND cg.id = :congregacao";
                $params[':congregacao'] = $filters['congregacao'];
            }
            if (!empty($filters['trimestre'])) {
                $sql .= " AND m.trimestre = :trimestre";
                $params[':trimestre'] = $filters['trimestre'];
            }
            if (!empty($filters['status'])) {
                $sql .= " AND m.status = :status";
                $params[':status'] = $filters['status'];
            }

            $sql .= " ORDER BY m.data_matricula DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalRecords = $this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
            
            return [
                'data' => $data, 
                'recordsTotal' => (int)$totalRecords,
                'recordsFiltered' => (int)$totalRecords
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao listar matrículas (Model): " . $e->getMessage());
            throw new Exception("Erro ao buscar matrículas.");
        }
    }

    public function criarMatricula($data) {
        try {
            if (empty($data['aluno_id']) || empty($data['classe_id']) || empty($data['congregacao_id']) ||
                empty($data['status']) || empty($data['professor_id']) || empty($data['trimestre'])) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }

            // Verificar se o aluno já tem matrícula no mesmo trimestre
            if ($this->verificarMatriculaExistenteNoMesmoTrimestre($data['aluno_id'], $data['trimestre'])) {
                throw new Exception("Este aluno já possui uma matrícula ativa neste trimestre.");
            }

            $data_matricula = !empty($data['data_matricula']) ? $data['data_matricula'] : date('Y-m-d');

            if (!strtotime($data_matricula)) {
                throw new Exception("Data de matrícula inválida.");
            }

            $sql = "INSERT INTO matriculas (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    VALUES (:aluno_id, :classe_id, :congregacao_id, :usuario_id, :data_matricula, :status, :trimestre)";
    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':aluno_id' => $data['aluno_id'],
                ':classe_id' => $data['classe_id'],
                ':congregacao_id' => $data['congregacao_id'],
                ':usuario_id' => $data['professor_id'],
                ':data_matricula' => $data_matricula,
                ':status' => $data['status'],
                ':trimestre' => $data['trimestre']
            ]);

        } catch (Exception $e) {
            error_log("Erro ao criar matrícula: " . $e->getMessage());
            throw new Exception("Erro ao criar matrícula: " . $e->getMessage());
        }
    }

    public function atualizarMatricula($id, $data) {
        try {
            // Verificar se a matrícula existe
            if (!$this->verificarMatriculaExistenteParaExclusao($id)) {
                throw new Exception("Matrícula não encontrada.");
            }

            $sql = "UPDATE matriculas SET 
                        aluno_id = :aluno_id, 
                        classe_id = :classe_id, 
                        congregacao_id = :congregacao_id, 
                        usuario_id = :usuario_id, 
                        trimestre = :trimestre, 
                        status = :status 
                    WHERE id = :id";
                    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':aluno_id' => $data['aluno_id'],
                ':classe_id' => $data['classe_id'],
                ':congregacao_id' => $data['congregacao_id'],
                ':usuario_id' => $data['professor_id'],
                ':trimestre' => $data['trimestre'],
                ':status' => $data['status']
            ]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar matrícula: " . $e->getMessage());
            throw new Exception("Erro ao atualizar matrícula: " . $e->getMessage());
        }
    }

    public function excluirMatricula($id) {
        try {
            // Verificar se a matrícula existe
            if (!$this->verificarMatriculaExistenteParaExclusao($id)) {
                throw new Exception("Matrícula não encontrada.");
            }

            $sql = "DELETE FROM matriculas WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            error_log("Erro ao excluir matrícula: " . $e->getMessage());
            throw new Exception("Erro ao excluir matrícula: " . $e->getMessage());
        }
    }

    public function verificarMatriculaExistente($aluno_id, $classe_id, $congregacao_id) {
        $sql = "SELECT COUNT(*) FROM matriculas 
                WHERE aluno_id = :aluno_id 
                  AND classe_id = :classe_id 
                  AND congregacao_id = :congregacao_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':classe_id' => $classe_id,
            ':congregacao_id' => $congregacao_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function verificarMatriculaExistenteParaExclusao($id) {
        $sql = "SELECT COUNT(*) FROM matriculas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica se existe matrícula ativa para o aluno no trimestre especificado
     */
    public function verificarMatriculaExistenteNoMesmoTrimestre($aluno_id, $trimestre) {
        try {
            $sql = "SELECT COUNT(*) FROM matriculas 
                    WHERE aluno_id = :aluno_id 
                      AND trimestre = :trimestre
                      AND status = 'ativo'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':trimestre' => $trimestre
            ]);
            $count = (int)$stmt->fetchColumn();
            
            error_log("Verificando matrícula - Aluno: $aluno_id, Trimestre: $trimestre, Count: $count");
            
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Erro em verificarMatriculaExistenteNoMesmoTrimestre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca matrícula por aluno e trimestre (útil para mensagens de erro detalhadas)
     */
    public function buscarMatriculaPorAlunoETrimestre($aluno_id, $trimestre) {
        try {
            $sql = "SELECT m.*, a.nome as aluno_nome, c.nome as classe_nome, cg.nome as congregacao_nome
                    FROM matriculas m
                    JOIN alunos a ON m.aluno_id = a.id
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.aluno_id = :aluno_id 
                      AND m.trimestre = :trimestre
                      AND m.status = 'ativo'
                    LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':trimestre' => $trimestre
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro buscarMatriculaPorAlunoETrimestre: " . $e->getMessage());
            return null;
        }
    }

    private function verificarMatriculaExistenteParaTrimestre($aluno_id, $classe_id, $congregacao_id, $trimestre) {
        $sql = "SELECT COUNT(*) FROM matriculas 
                WHERE aluno_id = :aluno_id 
                  AND classe_id = :classe_id
                  AND congregacao_id = :congregacao_id
                  AND trimestre = :trimestre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':classe_id' => $classe_id,
            ':congregacao_id' => $congregacao_id,
            ':trimestre' => $trimestre
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function carregarSelects() {
        try {
            $sql_alunos = "SELECT id, nome FROM alunos ORDER BY nome";
            $sql_classes = "SELECT id, nome FROM classes ORDER BY nome";
            $sql_congregacoes = "SELECT id, nome FROM congregacoes ORDER BY nome";
            $sql_usuarios = "SELECT id, nome FROM usuarios WHERE perfil = 'professor' ORDER BY nome";

            $stmt_alunos = $this->pdo->prepare($sql_alunos);
            $stmt_classes = $this->pdo->prepare($sql_classes);
            $stmt_congregacoes = $this->pdo->prepare($sql_congregacoes);
            $stmt_usuarios = $this->pdo->prepare($sql_usuarios);

            $stmt_alunos->execute();
            $stmt_classes->execute();
            $stmt_congregacoes->execute();
            $stmt_usuarios->execute();

            return [
                'alunos' => $stmt_alunos->fetchAll(PDO::FETCH_ASSOC),
                'classes' => $stmt_classes->fetchAll(PDO::FETCH_ASSOC),
                'congregacoes' => $stmt_congregacoes->fetchAll(PDO::FETCH_ASSOC),
                'usuarios' => $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Exception $e) {
            error_log("Erro ao carregar selects: " . $e->getMessage());
            throw new Exception("Erro ao carregar dados dos selects.");
        }
    }

    public function buscarMatriculaPorId($id) {
        try {
            $sql = "SELECT m.*, a.nome AS aluno, c.nome AS classe, cg.nome AS congregacao,
                           m.aluno_id, m.classe_id, m.congregacao_id, m.trimestre, m.status, m.data_matricula
                    FROM matriculas m
                    JOIN alunos a ON m.aluno_id = a.id
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar matrícula por ID: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrícula.");
        }
    }

    /**
     * Busca matrícula ativa por ID do aluno
     */
    public function buscarMatriculaAtivaPorAlunoId($aluno_id) {
        try {
            $sql = "SELECT m.*, a.nome AS aluno, c.nome AS classe, cg.nome AS congregacao
                    FROM matriculas m
                    JOIN alunos a ON m.aluno_id = a.id
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.aluno_id = :aluno_id 
                      AND m.status = 'ativo' 
                    ORDER BY m.data_matricula DESC 
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar matrícula ativa: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrícula ativa do aluno.");
        }
    }
    
    /**
     * Busca todas as matrículas de um aluno
     */
    public function buscarMatriculasPorAlunoId($aluno_id) {
        try {
            $sql = "SELECT m.*, c.nome as classe_nome, cg.nome as congregacao_nome 
                    FROM matriculas m
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.aluno_id = :aluno_id
                    ORDER BY m.data_matricula DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar matrículas do aluno: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrículas do aluno.");
        }
    }

    public function listarMatriculasPorTrimestre($trimestre_atual) {
        try {
            $stmt = $this->pdo->prepare("SELECT m.*, a.nome AS aluno, c.nome AS classe 
                                        FROM matriculas m
                                        JOIN alunos a ON m.aluno_id = a.id
                                        JOIN classes c ON m.classe_id = c.id
                                        WHERE m.trimestre = :trimestre_atual
                                        ORDER BY a.nome");
            $stmt->bindParam(':trimestre_atual', $trimestre_atual);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar matrículas por trimestre: " . $e->getMessage());
            throw new Exception("Erro ao listar matrículas por trimestre.");
        }
    }

    /**
     * Migra matrículas ativas para um novo trimestre
     */
    public function migrarMatriculasParaNovoTrimestre($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
        try {
            if ($trimestre_atual === $trimestre_novo) {
                throw new Exception("O trimestre atual e o novo trimestre não podem ser iguais.");
            }

            $manter_status_int = filter_var($manter_status, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            // Primeiro, verificar se já existem matrículas no trimestre destino
            $sql_check = "SELECT COUNT(*) FROM matriculas 
                          WHERE trimestre = :trimestre_novo 
                          AND congregacao_id = :congregacao_id";
            $stmt_check = $this->pdo->prepare($sql_check);
            $stmt_check->execute([
                ':trimestre_novo' => $trimestre_novo,
                ':congregacao_id' => $congregacao_id
            ]);
            $existentes = (int)$stmt_check->fetchColumn();

            $sql = "INSERT INTO matriculas 
                        (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    SELECT 
                        m.aluno_id, 
                        m.classe_id, 
                        m.congregacao_id, 
                        m.usuario_id, 
                        CURDATE(), 
                        CASE WHEN :manter_status = 1 THEN m.status ELSE 'ativo' END,
                        :trimestre_novo
                    FROM matriculas m
                    WHERE m.trimestre = :trimestre_atual 
                      AND m.congregacao_id = :congregacao_id
                      AND m.status = 'ativo'
                      AND NOT EXISTS (
                          SELECT 1 FROM matriculas m2 
                          WHERE m2.aluno_id = m.aluno_id 
                            AND m2.trimestre = :trimestre_novo
                      )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':trimestre_atual' => $trimestre_atual,
                ':trimestre_novo'  => $trimestre_novo,
                ':congregacao_id'  => $congregacao_id,
                ':manter_status'   => $manter_status_int
            ]);

            $matriculas_migradas = $stmt->rowCount();

            if ($matriculas_migradas > 0) {
                return [
                    'sucesso' => true,
                    'mensagem' => "Foram migradas {$matriculas_migradas} matrículas ativas para o trimestre {$trimestre_novo}.",
                    'quantidade' => $matriculas_migradas
                ];
            } elseif ($existentes > 0) {
                return [
                    'sucesso' => true,
                    'mensagem' => "As matrículas já existem no trimestre {$trimestre_novo}. Nenhuma nova migração foi necessária.",
                    'quantidade' => 0
                ];
            } else {
                return [
                    'sucesso' => true,
                    'mensagem' => "Nenhuma matrícula ativa encontrada no trimestre {$trimestre_atual} para migrar.",
                    'quantidade' => 0
                ];
            }

        } catch (PDOException $e) {
            error_log("[MatriculaModel] Erro PDO na migração: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro interno ao processar migração no banco de dados.'];
        } catch (Exception $e) {
            error_log("[MatriculaModel] Erro na migração: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }
}
?>