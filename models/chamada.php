<?php
// models/chamada.php - VERSÃO CORRIGIDA

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Chamada {
    private $pdo;

    public function __construct($pdo) {
        if (!$pdo) throw new Exception("Conexão PDO inválida.");
        $this->pdo = $pdo;
    }

    public function padronizarTrimestre($trimestre, $ano = null) {
        if (empty($trimestre)) return null;
        $trimestre = trim($trimestre);
        if (preg_match('/^\d{4}-T[1-4]$/i', $trimestre)) return strtoupper($trimestre);
        if (preg_match('/^[1-4]$/', $trimestre)) {
            $anoUsar = $ano ?: date('Y');
            return $anoUsar . '-T' . $trimestre;
        }
        if (preg_match('/^(\d{4})[Tt]([1-4])$/', $trimestre, $matches)) {
            return $matches[1] . '-T' . $matches[2];
        }
        return $trimestre;
    }

    public function getCongregacoes() {
        try {
            $stmt = $this->pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (PDOException $e) {
            error_log("Erro ao buscar congregações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * CORREÇÃO CRÍTICA: Busca classes da tabela classes, não via alunos
     */
    public function getClassesByCongregacao($congregacao_id) {
        try {
            // CORRIGIDO: Busca todas as classes (sem dependência de alunos)
            $query = "SELECT id, nome FROM classes ORDER BY FIELD(nome, 'MATERNAL','PRIMÁRIOS','JUNIORES','ADOLESCENTES','JOVENS','ADULTOS','DISCIPULADO'), nome ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar classes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * CORREÇÃO CRÍTICA: Busca alunos diretamente da tabela alunos
     */
    public function getAlunosByClasse($classe_id, $congregacao_id, $trimestre = null) {
        try {
            // CORRIGIDO: Busca simplificada direto da tabela alunos
            $query = "SELECT id, nome FROM alunos WHERE classe_id = :classe_id AND status = 'ativo' ORDER BY nome ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':classe_id', $classe_id, PDO::PARAM_INT);
            $stmt->execute();
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("getAlunosByClasse: classe_id=$classe_id, encontrados=" . count($alunos));
            
            return $alunos;
        } catch (PDOException $e) {
            error_log("Erro ao buscar alunos: " . $e->getMessage());
            return [];
        }
    }

    public function registrarChamada($data, $trimestre, $classeId, $professorId, $alunos, $ofertaClasse = 0, $total_visitantes = 0, $total_biblias = 0, $total_revistas = 0) {
        try {
            if (!DateTime::createFromFormat('Y-m-d', $data)) {
                throw new Exception("Formato de data inválido. Use YYYY-MM-DD");
            }
            
            $trimestrePadronizado = $this->padronizarTrimestre($trimestre);
            
            // Buscar congregacao_id do professor ou da classe
            $congregacaoId = $this->getCongregacaoIdByProfessor($professorId);
            
            if (!$congregacaoId) {
                // Fallback: buscar da classe
                $congregacaoId = $this->getCongregacaoIdByClasse($classeId);
            }
            
            if (!$congregacaoId) {
                throw new Exception("Não foi possível identificar a congregação.");
            }
            
            $this->pdo->beginTransaction();
            
            // Inserir chamada
            $sqlChamada = "INSERT INTO chamadas 
                          (data, classe_id, congregacao_id, professor_id, trimestre, oferta_classe, total_visitantes, total_biblias, total_revistas) 
                          VALUES 
                          (:data, :classe_id, :congregacao_id, :professor_id, :trimestre, :oferta_classe, :total_visitantes, :total_biblias, :total_revistas)";
            
            $stmt = $this->pdo->prepare($sqlChamada);
            $stmt->execute([
                ':data' => $data,
                ':classe_id' => (int)$classeId,
                ':congregacao_id' => (int)$congregacaoId,
                ':professor_id' => (int)$professorId,
                ':trimestre' => $trimestrePadronizado,
                ':oferta_classe' => number_format((float)$ofertaClasse, 2, '.', ''),
                ':total_visitantes' => (int)$total_visitantes,
                ':total_biblias' => (int)$total_biblias,
                ':total_revistas' => (int)$total_revistas
            ]);
            
            $chamadaId = $this->pdo->lastInsertId();
            
            // Inserir presenças
            if (!empty($alunos)) {
                $sqlPresenca = "INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (:chamada_id, :aluno_id, :presente)";
                $stmtPresenca = $this->pdo->prepare($sqlPresenca);
                
                foreach ($alunos as $aluno) {
                    if (!isset($aluno['id']) || !isset($aluno['status'])) {
                        continue; // Pula alunos inválidos
                    }
                    
                    $statusPresenca = in_array($aluno['status'], ['presente', 'ausente', 'justificado']) 
                                    ? $aluno['status'] 
                                    : 'ausente';
                    
                    $stmtPresenca->execute([
                        ':chamada_id' => $chamadaId,
                        ':aluno_id' => (int)$aluno['id'],
                        ':presente' => $statusPresenca
                    ]);
                }
            }
            
            $this->pdo->commit();
            return ['sucesso' => true, 'mensagem' => 'Chamada registrada com sucesso', 'chamada_id' => $chamadaId];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Erro ao registrar chamada: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }

    private function getCongregacaoIdByProfessor($professorId) {
        try {
            $stmt = $this->pdo->prepare("SELECT congregacao_id FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $professorId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return null;
        }
    }

    private function getCongregacaoIdByClasse($classeId) {
        try {
            // Tenta buscar da tabela alunos
            $stmt = $this->pdo->prepare("SELECT DISTINCT congregacao_id FROM alunos WHERE classe_id = :classe_id AND status = 'ativo' LIMIT 1");
            $stmt->execute([':classe_id' => $classeId]);
            $result = $stmt->fetchColumn();
            if ($result) return $result;
            
            // Fallback: congregacao padrão (SEDE = 7)
            return 7;
        } catch (PDOException $e) {
            return 7; // Fallback padrão
        }
    }

    public function listarChamadas($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (!empty($filtros['congregacao_id'])) {
                $where[] = "c.congregacao_id = :congregacao_id";
                $params[':congregacao_id'] = (int)$filtros['congregacao_id'];
            }
            
            if (!empty($filtros['classe_id'])) {
                $where[] = "c.classe_id = :classe_id";
                $params[':classe_id'] = (int)$filtros['classe_id'];
            }
            
            if (!empty($filtros['trimestre'])) {
                $where[] = "c.trimestre = :trimestre";
                $params[':trimestre'] = $filtros['trimestre'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $where[] = "c.data >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $where[] = "c.data <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }

            $sql = "SELECT 
                        c.*,
                        cong.nome AS nome_congregacao,
                        cl.nome AS nome_classe,
                        u.nome AS nome_professor,
                        COALESCE(pres.total_presentes, 0) AS total_presentes,
                        COALESCE(pres.total_ausentes, 0) AS total_ausentes,
                        COALESCE(pres.total_justificados, 0) AS total_justificados
                    FROM chamadas c
                    INNER JOIN congregacoes cong ON cong.id = c.congregacao_id
                    INNER JOIN classes cl ON cl.id = c.classe_id
                    INNER JOIN usuarios u ON u.id = c.professor_id
                    LEFT JOIN (
                        SELECT 
                            chamada_id,
                            SUM(CASE WHEN presente = 'presente' THEN 1 ELSE 0 END) AS total_presentes,
                            SUM(CASE WHEN presente = 'ausente' THEN 1 ELSE 0 END) AS total_ausentes,
                            SUM(CASE WHEN presente = 'justificado' THEN 1 ELSE 0 END) AS total_justificados
                        FROM presencas
                        GROUP BY chamada_id
                    ) pres ON pres.chamada_id = c.id";

            if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
            $sql .= " ORDER BY c.data DESC, c.id DESC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) $stmt->bindValue($key, $value);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao listar chamadas: " . $e->getMessage());
            return [];
        }
    }

    public function getChamadaDetalhada($chamadaId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, 
                       cong.nome AS nome_congregacao,
                       cl.nome AS nome_classe,
                       u.nome AS nome_professor
                FROM chamadas c
                LEFT JOIN congregacoes cong ON cong.id = c.congregacao_id
                LEFT JOIN classes cl ON cl.id = c.classe_id
                LEFT JOIN usuarios u ON u.id = c.professor_id
                WHERE c.id = :id
            ");
            $stmt->execute([':id' => (int)$chamadaId]);
            $chamada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$chamada) {
                throw new Exception("Chamada não encontrada.");
            }
            
            $stmtPres = $this->pdo->prepare("
                SELECT p.aluno_id, a.nome, p.presente 
                FROM presencas p 
                INNER JOIN alunos a ON a.id = p.aluno_id 
                WHERE p.chamada_id = :id 
                ORDER BY a.nome
            ");
            $stmtPres->execute([':id' => (int)$chamadaId]);
            $chamada['alunos'] = $stmtPres->fetchAll(PDO::FETCH_ASSOC);
            
            return $chamada;
        } catch (PDOException $e) {
            error_log("Erro ao detalhar chamada: " . $e->getMessage());
            throw new Exception("Falha ao buscar detalhes da chamada: " . $e->getMessage());
        }
    }

    public function atualizarChamada($chamadaId, $data, $trimestre, $classeId, $professorId, $alunos, $ofertaClasse = 0, $total_visitantes = 0, $total_biblias = 0, $total_revistas = 0) {
        try {
            if (!DateTime::createFromFormat('Y-m-d', $data)) {
                throw new Exception("Formato de data inválido.");
            }
            
            $trimestrePadronizado = $this->padronizarTrimestre($trimestre);
            
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE chamadas SET 
                        data = :data, 
                        trimestre = :trimestre, 
                        classe_id = :classe_id,
                        professor_id = :professor_id, 
                        oferta_classe = :oferta_classe,
                        total_visitantes = :total_visitantes, 
                        total_biblias = :total_biblias,
                        total_revistas = :total_revistas
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':data' => $data,
                ':trimestre' => $trimestrePadronizado,
                ':classe_id' => (int)$classeId,
                ':professor_id' => (int)$professorId,
                ':oferta_classe' => number_format((float)$ofertaClasse, 2, '.', ''),
                ':total_visitantes' => (int)$total_visitantes,
                ':total_biblias' => (int)$total_biblias,
                ':total_revistas' => (int)$total_revistas,
                ':id' => (int)$chamadaId
            ]);
            
            // Remove presenças antigas
            $stmtDel = $this->pdo->prepare("DELETE FROM presencas WHERE chamada_id = :id");
            $stmtDel->execute([':id' => (int)$chamadaId]);
            
            // Insere novas presenças
            if (!empty($alunos)) {
                $sqlPresenca = "INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (:chamada_id, :aluno_id, :presente)";
                $stmtPresenca = $this->pdo->prepare($sqlPresenca);
                
                foreach ($alunos as $aluno) {
                    if (!isset($aluno['id']) || !isset($aluno['status'])) {
                        continue;
                    }
                    
                    $status = in_array($aluno['status'], ['presente', 'ausente', 'justificado']) 
                            ? $aluno['status'] 
                            : 'ausente';
                    
                    $stmtPresenca->execute([
                        ':chamada_id' => (int)$chamadaId,
                        ':aluno_id' => (int)$aluno['id'],
                        ':presente' => $status
                    ]);
                }
            }
            
            $this->pdo->commit();
            return ['sucesso' => true, 'mensagem' => 'Chamada atualizada com sucesso.'];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Erro ao atualizar chamada: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }

    public function excluirChamada($chamadaId) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("DELETE FROM presencas WHERE chamada_id = :id");
            $stmt->execute([':id' => (int)$chamadaId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM chamadas WHERE id = :id");
            $stmt->execute([':id' => (int)$chamadaId]);
            
            $this->pdo->commit();
            return ['sucesso' => true, 'mensagem' => 'Chamada excluída com sucesso.'];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("Erro ao excluir chamada: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }

    public function verificarChamadaExistente($data, $classeId, $congregacaoId = null) {
        try {
            $sql = "SELECT * FROM chamadas WHERE data = :data AND classe_id = :classe_id";
            $params = [':data' => $data, ':classe_id' => $classeId];
            
            if ($congregacaoId) {
                $sql .= " AND congregacao_id = :congregacao_id";
                $params[':congregacao_id'] = $congregacaoId;
            }
            
            $sql .= " LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $chamada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $chamada ? $chamada : null;
        } catch (PDOException $e) {
            error_log("Erro ao verificar chamada existente: " . $e->getMessage());
            return null;
        }
    }
}
?>