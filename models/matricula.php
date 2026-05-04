<?php
class Matricula {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ══════════════════════════════════════════════════════════════════════
    // LISTAR
    // ══════════════════════════════════════════════════════════════════════

    public function listarMatriculas($limit = 10, $offset = 0, $filters = []) {
        try {
            $sql = "SELECT SQL_CALC_FOUND_ROWS
                        m.id, a.nome AS aluno, c.nome AS classe,
                        cg.nome AS congregacao, u.nome AS usuario,
                        m.data_matricula, m.status, m.trimestre,
                        m.aluno_id, m.classe_id, m.congregacao_id
                    FROM matriculas m
                    JOIN alunos a  ON m.aluno_id      = a.id
                    JOIN classes c ON m.classe_id      = c.id
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
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            $data  = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = (int)$this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

            return ['data' => $data, 'recordsTotal' => $total, 'recordsFiltered' => $total];

        } catch (Exception $e) {
            error_log("Erro listarMatriculas: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrículas.");
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // CRIAR
    // ══════════════════════════════════════════════════════════════════════

    public function criarMatricula($data) {
        try {
            if (empty($data['aluno_id']) || empty($data['classe_id']) || empty($data['congregacao_id']) ||
                empty($data['status'])   || empty($data['professor_id']) || empty($data['trimestre'])) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }

            if ($this->verificarMatriculaExistenteNoMesmoTrimestre($data['aluno_id'], $data['trimestre'])) {
                throw new Exception("Este aluno já possui uma matrícula ativa neste trimestre.");
            }

            $data_matricula = !empty($data['data_matricula']) ? $data['data_matricula'] : date('Y-m-d');
            if (!strtotime($data_matricula)) {
                throw new Exception("Data de matrícula inválida.");
            }

            $sql = "INSERT INTO matriculas
                        (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    VALUES
                        (:aluno_id, :classe_id, :congregacao_id, :usuario_id, :data_matricula, :status, :trimestre)";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':aluno_id'      => $data['aluno_id'],
                ':classe_id'     => $data['classe_id'],
                ':congregacao_id'=> $data['congregacao_id'],
                ':usuario_id'    => $data['professor_id'],
                ':data_matricula'=> $data_matricula,
                ':status'        => $data['status'],
                ':trimestre'     => $data['trimestre'],
            ]);

        } catch (Exception $e) {
            error_log("Erro criarMatricula: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // ATUALIZAR — verifica duplicata ignorando o próprio registro
    // ══════════════════════════════════════════════════════════════════════

    public function atualizarMatricula($id, $data) {
        try {
            if (!$this->verificarMatriculaExistenteParaExclusao($id)) {
                throw new Exception("Matrícula não encontrada.");
            }

            // Verifica se outro registro do mesmo aluno já ocupa o trimestre destino
            if (!empty($data['aluno_id']) && !empty($data['trimestre'])) {
                $sql = "SELECT COUNT(*) FROM matriculas
                        WHERE aluno_id  = :aluno_id
                          AND trimestre = :trimestre
                          AND status    = 'ativo'
                          AND id       != :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':aluno_id'  => $data['aluno_id'],
                    ':trimestre' => $data['trimestre'],
                    ':id'        => $id,
                ]);
                if ((int)$stmt->fetchColumn() > 0) {
                    throw new Exception("Este aluno já possui outra matrícula ativa neste trimestre.");
                }
            }

            $sql = "UPDATE matriculas SET
                        aluno_id        = :aluno_id,
                        classe_id       = :classe_id,
                        congregacao_id  = :congregacao_id,
                        usuario_id      = :usuario_id,
                        trimestre       = :trimestre,
                        status          = :status
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id'            => $id,
                ':aluno_id'      => $data['aluno_id'],
                ':classe_id'     => $data['classe_id'],
                ':congregacao_id'=> $data['congregacao_id'],
                ':usuario_id'    => $data['professor_id'],
                ':trimestre'     => $data['trimestre'],
                ':status'        => $data['status'],
            ]);

        } catch (Exception $e) {
            error_log("Erro atualizarMatricula: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // EXCLUIR
    // ══════════════════════════════════════════════════════════════════════

    public function excluirMatricula($id) {
        try {
            if (!$this->verificarMatriculaExistenteParaExclusao($id)) {
                throw new Exception("Matrícula não encontrada.");
            }
            $stmt = $this->pdo->prepare("DELETE FROM matriculas WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            error_log("Erro excluirMatricula: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // VERIFICAÇÕES
    // ══════════════════════════════════════════════════════════════════════

    public function verificarMatriculaExistente($aluno_id, $classe_id, $congregacao_id) {
        $sql = "SELECT COUNT(*) FROM matriculas
                WHERE aluno_id = :aluno_id AND classe_id = :classe_id AND congregacao_id = :congregacao_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id, ':classe_id' => $classe_id, ':congregacao_id' => $congregacao_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function verificarMatriculaExistenteParaExclusao($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM matriculas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica matrícula ativa no mesmo trimestre (para CREATE)
     */
    public function verificarMatriculaExistenteNoMesmoTrimestre($aluno_id, $trimestre) {
        try {
            $sql = "SELECT COUNT(*) FROM matriculas
                    WHERE aluno_id  = :aluno_id
                      AND trimestre = :trimestre
                      AND status    = 'ativo'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id, ':trimestre' => $trimestre]);
            $count = (int)$stmt->fetchColumn();
            error_log("verificarMatriculaExistenteNoMesmoTrimestre — Aluno: $aluno_id, Trimestre: $trimestre, Count: $count");
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Erro verificarMatriculaExistenteNoMesmoTrimestre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca matrícula ativa por aluno+trimestre (para mensagens detalhadas)
     */
    public function buscarMatriculaPorAlunoETrimestre($aluno_id, $trimestre) {
        try {
            $sql = "SELECT m.*, a.nome AS aluno_nome, c.nome AS classe_nome, cg.nome AS congregacao_nome
                    FROM matriculas m
                    JOIN alunos a       ON m.aluno_id       = a.id
                    JOIN classes c      ON m.classe_id      = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.aluno_id  = :aluno_id
                      AND m.trimestre = :trimestre
                      AND m.status    = 'ativo'
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id, ':trimestre' => $trimestre]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro buscarMatriculaPorAlunoETrimestre: " . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // SELECTS
    // ══════════════════════════════════════════════════════════════════════

    public function carregarSelects() {
        try {
            $fetch = function ($sql) {
                return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            };
            return [
                'alunos'       => $fetch("SELECT id, nome FROM alunos ORDER BY nome"),
                'classes'      => $fetch("SELECT id, nome FROM classes ORDER BY nome"),
                'congregacoes' => $fetch("SELECT id, nome FROM congregacoes ORDER BY nome"),
                'usuarios'     => $fetch("SELECT id, nome FROM usuarios WHERE perfil = 'professor' ORDER BY nome"),
            ];
        } catch (Exception $e) {
            error_log("Erro carregarSelects: " . $e->getMessage());
            throw new Exception("Erro ao carregar dados dos selects.");
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // BUSCAR POR ID
    // ══════════════════════════════════════════════════════════════════════

    public function buscarMatriculaPorId($id) {
        try {
            $sql = "SELECT m.*, a.nome AS aluno, c.nome AS classe, cg.nome AS congregacao,
                           m.aluno_id, m.classe_id, m.congregacao_id, m.trimestre,
                           m.status, m.data_matricula
                    FROM matriculas m
                    JOIN alunos a       ON m.aluno_id       = a.id
                    JOIN classes c      ON m.classe_id      = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro buscarMatriculaPorId: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrícula.");
        }
    }

    /**
     * Retorna a matrícula ativa mais recente de um aluno
     */
    public function buscarMatriculaAtivaPorAlunoId($aluno_id) {
        try {
            $sql = "SELECT m.*, a.nome AS aluno, c.nome AS classe, cg.nome AS congregacao
                    FROM matriculas m
                    JOIN alunos a       ON m.aluno_id       = a.id
                    JOIN classes c      ON m.classe_id      = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.aluno_id = :aluno_id AND m.status = 'ativo'
                    ORDER BY m.data_matricula DESC
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro buscarMatriculaAtivaPorAlunoId: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrícula ativa do aluno.");
        }
    }

    /**
     * Retorna todas as matrículas de um aluno
     */
    public function buscarMatriculasPorAlunoId($aluno_id) {
        try {
            $sql = "SELECT m.*, c.nome AS classe_nome, cg.nome AS congregacao_nome
                    FROM matriculas m
                    JOIN classes c      ON m.classe_id      = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    WHERE m.aluno_id = :aluno_id
                    ORDER BY m.data_matricula DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro buscarMatriculasPorAlunoId: " . $e->getMessage());
            throw new Exception("Erro ao buscar matrículas do aluno.");
        }
    }

    public function listarMatriculasPorTrimestre($trimestre_atual) {
        try {
            $sql = "SELECT m.*, a.nome AS aluno, c.nome AS classe
                    FROM matriculas m
                    JOIN alunos a  ON m.aluno_id  = a.id
                    JOIN classes c ON m.classe_id = c.id
                    WHERE m.trimestre = :trimestre
                    ORDER BY a.nome";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':trimestre' => $trimestre_atual]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro listarMatriculasPorTrimestre: " . $e->getMessage());
            throw new Exception("Erro ao listar matrículas por trimestre.");
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // MIGRAÇÃO
    // ══════════════════════════════════════════════════════════════════════

    public function migrarMatriculasParaNovoTrimestre($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
        try {
            if ($trimestre_atual === $trimestre_novo) {
                throw new Exception("O trimestre atual e o novo não podem ser iguais.");
            }

            $manter = filter_var($manter_status, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            // Quantas já existem no destino para esta congregação
            $stmtCheck = $this->pdo->prepare(
                "SELECT COUNT(*) FROM matriculas WHERE trimestre = :novo AND congregacao_id = :cg"
            );
            $stmtCheck->execute([':novo' => $trimestre_novo, ':cg' => $congregacao_id]);
            $existentes = (int)$stmtCheck->fetchColumn();

            $sql = "INSERT INTO matriculas
                        (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    SELECT
                        m.aluno_id, m.classe_id, m.congregacao_id, m.usuario_id,
                        CURDATE(),
                        CASE WHEN :manter = 1 THEN m.status ELSE 'ativo' END,
                        :novo
                    FROM matriculas m
                    WHERE m.trimestre      = :atual
                      AND m.congregacao_id = :cg
                      AND m.status         = 'ativo'
                      AND NOT EXISTS (
                          SELECT 1 FROM matriculas m2
                          WHERE m2.aluno_id  = m.aluno_id
                            AND m2.trimestre = :novo
                      )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':atual'  => $trimestre_atual,
                ':novo'   => $trimestre_novo,
                ':cg'     => $congregacao_id,
                ':manter' => $manter,
            ]);

            $migradas = $stmt->rowCount();

            if ($migradas > 0) {
                return ['sucesso' => true, 'mensagem' => "Foram migradas {$migradas} matrícula(s) para {$trimestre_novo}.", 'quantidade' => $migradas];
            }
            if ($existentes > 0) {
                return ['sucesso' => true, 'mensagem' => "As matrículas já existem no trimestre {$trimestre_novo}.", 'quantidade' => 0];
            }
            return ['sucesso' => true, 'mensagem' => "Nenhuma matrícula ativa encontrada em {$trimestre_atual} para migrar.", 'quantidade' => 0];

        } catch (PDOException $e) {
            error_log("[MatriculaModel] PDO migração: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro interno no banco de dados ao migrar.'];
        } catch (Exception $e) {
            error_log("[MatriculaModel] Erro migração: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }
}