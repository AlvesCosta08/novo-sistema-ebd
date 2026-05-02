<?php
// models/chamada.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$caminhoConfig = __DIR__ . '/../config/conexao.php';
if (!file_exists($caminhoConfig)) {
    throw new Exception("Arquivo de configuração não encontrado: " . $caminhoConfig);
}
require_once $caminhoConfig;

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
        if (preg_match('/^T?0?([1-4])$/i', $trimestre, $matches)) {
            $anoUsar = $ano ?: date('Y');
            return $anoUsar . '-T' . $matches[1];
        }
        return $trimestre;
    }

    public function extrairNumeroTrimestre($trimestre) {
        if (empty($trimestre)) return null;
        if (preg_match('/-T([1-4])$/i', $trimestre, $matches)) return $matches[1];
        if (preg_match('/^[1-4]$/', $trimestre)) return $trimestre;
        if (preg_match('/^(\d{4})[Tt]([1-4])$/', $trimestre, $matches)) return $matches[2];
        return null;
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

    public function getClassesByCongregacao($congregacao_id) {
        try {
            $query = "SELECT DISTINCT c.id, c.nome 
                      FROM classes c
                      INNER JOIN matriculas m ON m.classe_id = c.id
                      WHERE m.congregacao_id = :congregacao_id AND m.status = 'ativo'
                      ORDER BY FIELD(c.nome, 'MATERNAL','PRIMÁRIOS','JUNIORES','ADOLESCENTES','JOVENS','ADULTOS','DISCIPULADO'), c.nome ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar classes: " . $e->getMessage());
            return [];
        }
    }

    public function getAlunosByClasse($classe_id, $congregacao_id, $trimestre) {
        try {
            $query = "SELECT DISTINCT a.id, a.nome
                      FROM alunos a
                      INNER JOIN matriculas m ON m.aluno_id = a.id
                      WHERE m.classe_id = :classe_id
                        AND m.congregacao_id = :congregacao_id
                        AND m.trimestre = :trimestre
                        AND m.status = 'ativo' AND a.status = 'ativo'
                      ORDER BY a.nome ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':classe_id', $classe_id, PDO::PARAM_INT);
            $stmt->bindValue(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
            $stmt->bindValue(':trimestre', $trimestre, PDO::PARAM_STR);
            $stmt->execute();
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($alunos)) {
                $numeroTrimestre = $this->extrairNumeroTrimestre($trimestre);
                if ($numeroTrimestre) {
                    $queryFlex = "SELECT DISTINCT a.id, a.nome
                                  FROM alunos a
                                  INNER JOIN matriculas m ON m.aluno_id = a.id
                                  WHERE m.classe_id = :classe_id
                                    AND m.congregacao_id = :congregacao_id
                                    AND (m.trimestre LIKE CONCAT('%-T', :numero) OR m.trimestre = :trimestre)
                                    AND m.status = 'ativo' AND a.status = 'ativo'
                                  ORDER BY a.nome ASC";
                    $stmtFlex = $this->pdo->prepare($queryFlex);
                    $stmtFlex->bindValue(':classe_id', $classe_id, PDO::PARAM_INT);
                    $stmtFlex->bindValue(':congregacao_id', $congregacao_id, PDO::PARAM_INT);
                    $stmtFlex->bindValue(':numero', $numeroTrimestre, PDO::PARAM_STR);
                    $stmtFlex->bindValue(':trimestre', $trimestre, PDO::PARAM_STR);
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

    public function registrarChamada($data, $trimestre, $classeId, $professorId, $alunos, $ofertaClasse = 0, $total_visitantes = 0, $total_biblias = 0, $total_revistas = 0) {
        try {
            if (!DateTime::createFromFormat('Y-m-d', $data)) {
                throw new Exception("Formato de data inválido. Use YYYY-MM-DD");
            }
            $trimestrePadronizado = $this->padronizarTrimestre($trimestre);
            $stmtClasse = $this->pdo->prepare("
                SELECT DISTINCT m.congregacao_id 
                FROM matriculas m
                WHERE m.classe_id = :classe 
                AND (m.trimestre = :trimestre OR m.trimestre LIKE CONCAT('%-T', :numero))
                LIMIT 1
            ");
            $numeroTrimestre = $this->extrairNumeroTrimestre($trimestrePadronizado);
            $stmtClasse->execute([
                ':classe' => $classeId,
                ':trimestre' => $trimestrePadronizado,
                ':numero' => $numeroTrimestre
            ]);
            $congregacaoId = $stmtClasse->fetchColumn();
            if (!$congregacaoId) {
                throw new Exception("Não foi possível identificar a congregação para esta classe no trimestre especificado.");
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
                    $statusPresenca = in_array($aluno['status'], ['presente', 'ausente', 'justificado']) ? $aluno['status'] : 'ausente';
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

    // ==================== LISTAR CHAMADAS (CORRIGIDO) ====================
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

            $sql = "SELECT 
                        c.*,
                        cong.nome AS nome_congregacao,
                        cl.nome AS nome_classe,
                        u.nome AS nome_professor,
                        COALESCE(pres.total_presentes, 0) AS total_presentes,
                        COALESCE(pres.total_ausentes, 0) AS total_ausentes,
                        COALESCE(pres.total_justificados, 0) AS total_justificados,
                        COALESCE(pres.total_marcacoes, 0) AS total_marcacoes
                    FROM chamadas c
                    INNER JOIN congregacoes cong ON cong.id = c.congregacao_id
                    INNER JOIN classes cl ON cl.id = c.classe_id
                    INNER JOIN usuarios u ON u.id = c.professor_id
                    LEFT JOIN (
                        SELECT 
                            chamada_id,
                            SUM(CASE WHEN presente = 'presente' THEN 1 ELSE 0 END) AS total_presentes,
                            SUM(CASE WHEN presente = 'ausente' THEN 1 ELSE 0 END) AS total_ausentes,
                            SUM(CASE WHEN presente = 'justificado' THEN 1 ELSE 0 END) AS total_justificados,
                            COUNT(*) AS total_marcacoes
                        FROM presencas
                        GROUP BY chamada_id
                    ) pres ON pres.chamada_id = c.id";

            if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
            $sql .= " ORDER BY c.data DESC, c.id DESC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) $stmt->bindValue($key, $value);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as &$row) {
                $row['total_presentes'] = (int)$row['total_presentes'];
                $row['total_ausentes'] = (int)$row['total_ausentes'];
                $row['total_justificados'] = (int)$row['total_justificados'];
                $row['oferta_classe'] = (float)$row['oferta_classe'];
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao listar chamadas: " . $e->getMessage());
            return [];
        }
    }

    // ==================== DETALHES DA CHAMADA (CORRIGIDO COM LOGS) ====================
public function getChamadaDetalhada($chamadaId) {
    try {
        $id = (int)$chamadaId;
        error_log("getChamadaDetalhada: buscando chamada ID $id");
        
        // Usa LEFT JOIN para evitar erro se classe ou congregação não existirem
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
                   COALESCE(cong.nome, 'Não definida') AS nome_congregacao,
                   COALESCE(cl.nome, 'Classe não encontrada') AS nome_classe,
                   COALESCE(u.nome, 'Professor não encontrado') AS nome_professor
            FROM chamadas c
            LEFT JOIN congregacoes cong ON cong.id = c.congregacao_id
            LEFT JOIN classes cl ON cl.id = c.classe_id
            LEFT JOIN usuarios u ON u.id = c.professor_id
            WHERE c.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $chamada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$chamada) {
            error_log("getChamadaDetalhada: Nenhum registro encontrado para ID $id");
            throw new Exception("Chamada não encontrada.");
        }
        
        // Busca presenças (não depende de JOINs externos)
        $stmtPres = $this->pdo->prepare("
            SELECT p.aluno_id, a.nome, p.presente 
            FROM presencas p 
            INNER JOIN alunos a ON a.id = p.aluno_id 
            WHERE p.chamada_id = :id 
            ORDER BY a.nome
        ");
        $stmtPres->execute([':id' => $id]);
        $chamada['alunos'] = $stmtPres->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("getChamadaDetalhada: Chamada ID $id carregada com " . count($chamada['alunos']) . " alunos");
        return $chamada;
    } catch (PDOException $e) {
        error_log("Erro ao detalhar chamada: " . $e->getMessage());
        throw new Exception("Falha ao buscar detalhes da chamada: " . $e->getMessage());
    }
}

    public function atualizarChamada($chamadaId, $data, $trimestre, $classeId, $professorId, $alunos, $ofertaClasse = 0, $total_visitantes = 0, $total_biblias = 0, $total_revistas = 0) {
        try {
            if (!DateTime::createFromFormat('Y-m-d', $data)) throw new Exception("Formato de data inválido.");
            $trimestrePadronizado = $this->padronizarTrimestre($trimestre);
            $this->pdo->beginTransaction();
            $sql = "UPDATE chamadas SET 
                        data = :data, trimestre = :trimestre, classe_id = :classe_id,
                        professor_id = :professor_id, oferta_classe = :oferta_classe,
                        total_visitantes = :total_visitantes, total_biblias = :total_biblias,
                        total_revistas = :total_revistas
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':data' => $data, ':trimestre' => $trimestrePadronizado,
                ':classe_id' => (int)$classeId, ':professor_id' => (int)$professorId,
                ':oferta_classe' => number_format((float)$ofertaClasse, 2, '.', ''),
                ':total_visitantes' => (int)$total_visitantes, ':total_biblias' => (int)$total_biblias,
                ':total_revistas' => (int)$total_revistas, ':id' => (int)$chamadaId
            ]);
            $stmtDel = $this->pdo->prepare("DELETE FROM presencas WHERE chamada_id = :id");
            $stmtDel->execute([':id' => (int)$chamadaId]);
            if (!empty($alunos)) {
                $sqlPresenca = "INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (:chamada_id, :aluno_id, :presente)";
                $stmtPresenca = $this->pdo->prepare($sqlPresenca);
                foreach ($alunos as $aluno) {
                    if (!isset($aluno['id']) || !isset($aluno['status'])) throw new Exception("Dados do aluno incompletos.");
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

    public function corrigirTrimestresAntigos($ano = null) {
        try {
            $anoAtual = $ano ?: date('Y');
            $sqlCheck = "SELECT COUNT(*) as total FROM chamadas WHERE trimestre REGEXP '^[1-4]$' AND YEAR(data) = :ano";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':ano' => $anoAtual]);
            $total = $stmtCheck->fetchColumn();
            if ($total == 0) {
                return ['sucesso' => true, 'mensagem' => "Nenhum registro para corrigir no ano {$anoAtual}.", 'dados' => ['atualizadas' => 0]];
            }
            $sql = "UPDATE chamadas SET trimestre = CONCAT(:ano, '-T', trimestre) WHERE trimestre REGEXP '^[1-4]$' AND YEAR(data) = :ano";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':ano' => $anoAtual]);
            $atualizadas = $stmt->rowCount();
            return ['sucesso' => true, 'mensagem' => "{$atualizadas} registro(s) atualizado(s) para o formato padronizado.", 'dados' => ['atualizadas' => $atualizadas]];
        } catch (PDOException $e) {
            error_log("Erro ao corrigir trimestres: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao corrigir trimestres: ' . $e->getMessage()];
        }
    }
}
?>