<?php
// ATIVAR DISPLAY DE ERROS PARA DEBUG (Remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORREÇÃO: caminho correto para o config
require_once __DIR__ . '/../config/conexao.php';

class Chamada {
    private $pdo;

    public function __construct($pdo) {
        if (!$pdo) {
            throw new Exception("Conexão PDO inválida.");
        }
        $this->pdo = $pdo;
    }

    // Método auxiliar para padronizar trimestre
    public function padronizarTrimestre($trimestre, $ano = null) {
        if (empty($trimestre)) return null;
        
        // Se já estiver no formato ANO-T (ex: 2026-T2), retorna como está
        if (preg_match('/^\d{4}-T[1-4]$/', $trimestre)) {
            return $trimestre;
        }
        
        // Se for apenas o número do trimestre (1-4)
        if (preg_match('/^[1-4]$/', $trimestre)) {
            $anoUsar = $ano ?: date('Y');
            return $anoUsar . '-T' . $trimestre;
        }
        
        // Se for formato '2026-T2' sem hífen ou com T minúsculo
        if (preg_match('/^\d{4}[Tt][1-4]$/', $trimestre)) {
            $ano = substr($trimestre, 0, 4);
            $trim = strtoupper(substr($trimestre, -1));
            return $ano . '-T' . $trim;
        }
        
        return $trimestre;
    }

    // Método auxiliar para extrair número do trimestre
    public function extrairNumeroTrimestre($trimestre) {
        if (preg_match('/-T([1-4])$/', $trimestre, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^[1-4]$/', $trimestre)) {
            return $trimestre;
        }
        return null;
    }

    // Método para buscar todas as congregações
    public function getCongregacoes() {
        try {
            $query = "SELECT id, nome FROM congregacoes ORDER BY nome ASC";
            $stmt = $this->pdo->query($query);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (PDOException $e) {
            error_log("Erro ao buscar congregações: " . $e->getMessage());
            return [];
        }
    }

    // Método para buscar as classes de uma congregação
    public function getClassesByCongregacao($congregacao_id) {
        try {
            $query = "SELECT DISTINCT c.id, c.nome 
                      FROM classes c
                      INNER JOIN matriculas m ON m.classe_id = c.id
                      WHERE m.congregacao_id = :congregacao_id 
                      AND m.status = 'ativo'
                      ORDER BY c.nome ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar classes: " . $e->getMessage());
            return [];
        }
    }

    // Método para obter os alunos de uma classe
    public function getAlunosByClasse($classe_id, $congregacao_id, $trimestre) {
        try {
            // Primeiro tenta com o trimestre exato
            $query = "
                SELECT DISTINCT a.id, a.nome
                FROM alunos a
                INNER JOIN matriculas m ON m.aluno_id = a.id
                WHERE m.classe_id = :classe_id
                  AND m.congregacao_id = :congregacao_id
                  AND m.trimestre = :trimestre
                  AND m.status = 'ativo'
                ORDER BY a.nome ASC
            ";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':classe_id', $classe_id, PDO::PARAM_INT);
            $stmt->bindValue(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
            $stmt->bindValue(':trimestre', $trimestre, PDO::PARAM_STR);
            $stmt->execute();

            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Se não encontrou, tenta buscar apenas pelo número do trimestre
            if (empty($alunos)) {
                $numeroTrimestre = $this->extrairNumeroTrimestre($trimestre);
                if ($numeroTrimestre) {
                    $queryFlex = "
                        SELECT DISTINCT a.id, a.nome
                        FROM alunos a
                        INNER JOIN matriculas m ON m.aluno_id = a.id
                        WHERE m.classe_id = :classe_id
                          AND m.congregacao_id = :congregacao_id
                          AND (m.trimestre = :numero 
                               OR m.trimestre LIKE CONCAT('%-T', :numero)
                               OR m.trimestre LIKE CONCAT(:numero, '-T%'))
                          AND m.status = 'ativo'
                        ORDER BY a.nome ASC
                    ";
                    
                    $stmtFlex = $this->pdo->prepare($queryFlex);
                    $stmtFlex->bindValue(':classe_id', $classe_id, PDO::PARAM_INT);
                    $stmtFlex->bindValue(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
                    $stmtFlex->bindValue(':numero', $numeroTrimestre, PDO::PARAM_STR);
                    $stmtFlex->execute();
                    
                    $alunos = $stmtFlex->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            return $alunos;
        } catch (PDOException $e) {
            error_log("Erro ao buscar alunos: " . $e->getMessage());
            return [];
        }
    }

    // Método para registrar a chamada
    public function registrarChamada($data, $trimestre, $classeId, $professorId, $alunos, $ofertaClasse = 0, $total_visitantes = 0, $total_biblias = 0, $total_revistas = 0) {
        try {
            if (!DateTime::createFromFormat('Y-m-d', $data)) {
                throw new Exception("Formato de data inválido. Use YYYY-MM-DD");
            }

            $trimestrePadronizado = $this->padronizarTrimestre($trimestre);
            
            // Busca o congregacao_id
            $stmtCong = $this->pdo->prepare("
                SELECT congregacao_id FROM matriculas 
                WHERE classe_id = :classe 
                AND (trimestre = :tri OR trimestre = :tri_numero)
                LIMIT 1
            ");
            $numeroTrimestre = $this->extrairNumeroTrimestre($trimestrePadronizado);
            $stmtCong->execute([
                ':classe' => $classeId, 
                ':tri' => $trimestrePadronizado,
                ':tri_numero' => $numeroTrimestre
            ]);
            
            $congregacaoId = $stmtCong->fetchColumn();

            if (!$congregacaoId) {
                throw new Exception("Não foi possível identificar a congregação para esta classe neste trimestre. Verifique as matrículas.");
            }

            $this->pdo->beginTransaction();

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

            if (!empty($alunos)) {
                $sqlPresenca = "INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (:chamada_id, :aluno_id, :presente)";
                $stmtPresenca = $this->pdo->prepare($sqlPresenca);

                foreach ($alunos as $aluno) {
                    if (!isset($aluno['id']) || !isset($aluno['status'])) {
                        throw new Exception("Dados do aluno incompletos.");
                    }
                    
                    $statusPresenca = ($aluno['status'] === 'presente') ? 'presente' : 'ausente';
                    
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
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro ao registrar chamada: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }
    
    /**
     * Lista chamadas com filtros opcionais
     */
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

            // Suporte flexível para trimestre
            if (!empty($filtros['trimestre_numero'])) {
                $numero = $filtros['trimestre_numero'];
                if (!empty($filtros['ano'])) {
                    $where[] = "(c.trimestre = :trimestre_exato OR c.trimestre = :trimestre_numero)";
                    $params[':trimestre_exato'] = $filtros['ano'] . '-T' . $numero;
                    $params[':trimestre_numero'] = $numero;
                } else {
                    $where[] = "(c.trimestre = :numero_trimestre OR c.trimestre LIKE CONCAT('%-T', :numero_trimestre))";
                    $params[':numero_trimestre'] = $numero;
                }
            } elseif (!empty($filtros['trimestre'])) {
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

            $sql = "SELECT c.*, 
                           cong.nome AS nome_congregacao, 
                           cl.nome AS nome_classe, 
                           u.nome AS nome_professor,
                           COUNT(p.id) AS total_marcacoes,
                           SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) AS total_presentes,
                           SUM(CASE WHEN p.presente = 'ausente' THEN 1 ELSE 0 END) AS total_ausentes,
                           SUM(CASE WHEN p.presente = 'justificado' THEN 1 ELSE 0 END) AS total_justificados
                    FROM chamadas c
                    INNER JOIN congregacoes cong ON cong.id = c.congregacao_id
                    INNER JOIN classes cl ON cl.id = c.classe_id
                    INNER JOIN usuarios u ON u.id = c.professor_id
                    LEFT JOIN presencas p ON p.chamada_id = c.id";

            if ($where) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " GROUP BY c.id ORDER BY c.data DESC, c.id DESC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar chamadas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna detalhes de uma chamada específica
     */
    public function getChamadaDetalhada($chamadaId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, 
                       cong.nome AS nome_congregacao,
                       cl.nome AS nome_classe,
                       u.nome AS nome_professor 
                FROM chamadas c
                INNER JOIN congregacoes cong ON cong.id = c.congregacao_id
                INNER JOIN classes cl ON cl.id = c.classe_id
                INNER JOIN usuarios u ON u.id = c.professor_id
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

    /**
     * Atualiza uma chamada existente
     */
    public function atualizarChamada($chamadaId, $data, $trimestre, $classeId, $professorId, $alunos, $ofertaClasse = 0, $total_visitantes = 0, $total_biblias = 0, $total_revistas = 0) {
        try {
            if (!DateTime::createFromFormat('Y-m-d', $data)) {
                throw new Exception("Formato de data inválido. Use YYYY-MM-DD");
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
            $this->pdo->exec("DELETE FROM presencas WHERE chamada_id = " . (int)$chamadaId);

            if (!empty($alunos)) {
                $sqlPresenca = "INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (:chamada_id, :aluno_id, :presente)";
                $stmtPresenca = $this->pdo->prepare($sqlPresenca);
                
                foreach ($alunos as $aluno) {
                    if (!isset($aluno['id']) || !isset($aluno['status'])) {
                        throw new Exception("Dados do aluno incompletos.");
                    }
                    $status = in_array($aluno['status'], ['presente', 'ausente', 'justificado']) ? $aluno['status'] : 'ausente';
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
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro ao atualizar chamada: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }

    /**
     * Exclui uma chamada e suas presenças
     */
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
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro ao excluir chamada: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }

    /**
     * Corrige trimestres antigos
     */
    public function corrigirTrimestresAntigos($ano = null) {
        try {
            $anoAtual = $ano ?: date('Y');
            
            $sqlCheck = "SELECT COUNT(*) as total FROM chamadas 
                         WHERE trimestre REGEXP '^[1-4]$' 
                         AND YEAR(data) = :ano";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':ano' => $anoAtual]);
            $total = $stmtCheck->fetchColumn();
            
            if ($total == 0) {
                return [
                    'sucesso' => true,
                    'mensagem' => "Nenhum registro para corrigir no ano {$anoAtual}.",
                    'dados' => ['atualizadas' => 0]
                ];
            }
            
            $sql = "UPDATE chamadas 
                    SET trimestre = CONCAT(:ano, '-T', trimestre)
                    WHERE trimestre REGEXP '^[1-4]$' 
                    AND YEAR(data) = :ano";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':ano' => $anoAtual]);
            $atualizadas = $stmt->rowCount();
            
            return [
                'sucesso' => true,
                'mensagem' => "{$atualizadas} registro(s) atualizado(s) para o formato padronizado.",
                'dados' => ['atualizadas' => $atualizadas]
            ];
        } catch (PDOException $e) {
            error_log("Erro ao corrigir trimestres: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao corrigir trimestres: ' . $e->getMessage()
            ];
        }
    }
}
?>