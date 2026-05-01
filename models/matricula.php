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
                            u.nome AS usuario, m.data_matricula, m.status, m.trimestre, m.aluno_id
                    FROM matriculas m
                    JOIN alunos a ON m.aluno_id = a.id
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    LEFT JOIN usuarios u ON m.usuario_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['busca'])) {
                $sql .= " AND (a.nome LIKE :busca OR c.nome LIKE :busca)";
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
            
            return ['data' => $data, 'recordsTotal' => $totalRecords];
            
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

            if ($this->verificarMatriculaExistente($data['aluno_id'], $data['classe_id'], $data['congregacao_id'])) {
                throw new Exception("Este aluno já está matriculado nesta classe e congregação.");
            }

            $data_matricula = !empty($data['data_matricula']) ? $data['data_matricula'] : date('Y-m-d');

            if (!strtotime($data_matricula)) {
                throw new Exception("Data de matrícula inválida.");
            }

            $sql = "INSERT INTO matriculas (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    VALUES (:aluno_id, :classe_id, :congregacao_id, :usuario_id, :data_matricula, :status, :trimestre)";
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $data['aluno_id'],
                ':classe_id' => $data['classe_id'],
                ':congregacao_id' => $data['congregacao_id'],
                ':usuario_id' => $data['professor_id'],
                ':data_matricula' => $data_matricula,
                ':status' => $data['status'],
                ':trimestre' => $data['trimestre']
            ]);

            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar matrícula: " . $e->getMessage());
            throw new Exception("Erro ao criar matrícula: " . $e->getMessage());
        }
    }

    public function atualizarMatricula($id, $data) {
        try {
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
            throw new Exception("Erro ao atualizar matrícula.");
        }
    }

    public function excluirMatricula($id) {
        try {
            $sql = "DELETE FROM matriculas WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Erro ao excluir matrícula.");
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

    public function verificarMatriculaExistenteNoMesmoTrimestre($aluno_id, $trimestre) {
        $sql = "SELECT COUNT(*) FROM matriculas 
                WHERE aluno_id = :aluno_id 
                  AND trimestre = :trimestre
                  AND status != 'inativo'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':trimestre' => $trimestre
        ]);
        return $stmt->fetchColumn() > 0;
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
        $sql_alunos = "SELECT id, nome FROM alunos ORDER BY nome";
        $sql_classes = "SELECT id, nome FROM classes ORDER BY nome";
        $sql_congregacoes = "SELECT id, nome FROM congregacoes ORDER BY nome";
        $sql_usuarios = "SELECT id, nome FROM usuarios ORDER BY nome";

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
    }

    public function buscarMatriculaPorId($id) {
        try {
            $sql = "SELECT * FROM matriculas WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar matrícula.");
        }
    }

    public function listarMatriculasPorTrimestre($trimestre_atual) {
        $stmt = $this->pdo->prepare("SELECT * FROM matriculas WHERE trimestre = :trimestre_atual");
        $stmt->bindParam(':trimestre_atual', $trimestre_atual);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Migra matrículas ativas para um novo trimestre usando query otimizada SET-based
     */
    public function migrarMatriculasParaNovoTrimestre($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
        try {
            if ($trimestre_atual === $trimestre_novo) {
                throw new Exception("O trimestre atual e o novo trimestre não podem ser iguais.");
            }

            $manter_status_int = filter_var($manter_status, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

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
                            AND m2.status != 'inativo'
                      )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':trimestre_atual' => $trimestre_atual,
                ':trimestre_novo'  => $trimestre_novo,
                ':congregacao_id'  => $congregacao_id,
                ':manter_status'   => $manter_status_int
            ]);

            $matriculas_migradas = $stmt->rowCount();

            return [
                'sucesso' => true,
                'mensagem' => $matriculas_migradas > 0
                    ? "Foram migradas $matriculas_migradas matrículas ativas para o trimestre $trimestre_novo."
                    : "Nenhuma matrícula nova foi necessária. Todos os alunos ativos já possuem matrícula no trimestre de destino."
            ];

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