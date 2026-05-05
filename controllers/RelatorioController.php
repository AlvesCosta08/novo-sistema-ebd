<?php
// controllers/RelatorioController.php
// Controlador responsável por todos os relatórios do sistema

require_once __DIR__ . '/../config/conexao.php';

class RelatorioController {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Relatório Consolidado de Classes
     * Retorna dados agregados por classe: matrículas, presenças, recursos e ofertas
     */
/**
 * Relatório Consolidado de Classes
 * Suporte a filtros: trimestre, congregacao, data_inicio, data_fim
 */
public function getRelatorioConsolidado($trimestre = null, $congregacao = null, $data_inicio = null, $data_fim = null) {
    $sql = "SELECT 
                cg.nome AS congregacao,
                cl.nome AS classe,
                m.trimestre,
                COUNT(DISTINCT m.aluno_id) AS matriculados,
                COUNT(DISTINCT CASE WHEN p.presente = 'presente' THEN p.aluno_id END) AS presentes,
                COUNT(DISTINCT CASE WHEN p.presente = 'ausente' THEN p.aluno_id END) AS ausentes,
                COUNT(DISTINCT CASE WHEN p.presente = 'justificado' THEN p.aluno_id END) AS justificados,
                COALESCE(SUM(ch.total_biblias), 0) AS biblias,
                COALESCE(SUM(ch.total_revistas), 0) AS revistas,
                COALESCE(SUM(ch.total_visitantes), 0) AS visitantes,
                COALESCE(SUM(ch.oferta_classe), 0) AS oferta
            FROM classes cl
            INNER JOIN matriculas m ON m.classe_id = cl.id AND m.status = 'ativo'
            INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
            LEFT JOIN chamadas ch ON ch.classe_id = cl.id
            LEFT JOIN presencas p ON p.chamada_id = ch.id AND p.aluno_id = m.aluno_id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($trimestre)) {
        $sql .= " AND m.trimestre = :trimestre";
        $params[':trimestre'] = $trimestre;
    }
    
    if (!empty($congregacao)) {
        $sql .= " AND cg.nome LIKE :congregacao";
        $params[':congregacao'] = '%' . $congregacao . '%';
    }
    
    if (!empty($data_inicio) && !empty($data_fim)) {
        $sql .= " AND ch.data BETWEEN :data_inicio AND :data_fim";
        $params[':data_inicio'] = $data_inicio;
        $params[':data_fim'] = $data_fim;
    }
    
    $sql .= " GROUP BY cg.nome, cl.nome, m.trimestre ORDER BY cg.nome, cl.nome";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    /**
     * Relatório de Presenças por Aluno
     * Retorna análise individual de frequência por período
     */
    public function getPresencasPorAluno($congregacao_id = null, $classe_id = null, $data_inicio = null, $data_fim = null, $trimestre = null) {
        // Definir período
        if (!empty($trimestre)) {
            list($data_inicio, $data_fim) = $this->calcularPeriodoTrimestre($trimestre);
        } else {
            $data_inicio = $data_inicio ?: date('Y-m-01');
            $data_fim = $data_fim ?: date('Y-m-d');
        }
        
        // Verificar se há chamadas no período
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM chamadas WHERE data BETWEEN ? AND ?");
        $stmt->execute([$data_inicio, $data_fim]);
        $result = $stmt->fetch();
        
        if ($result['total'] == 0) {
            return [
                'dados' => [], 
                'trimestre_sem_dados' => true, 
                'data_inicio' => $data_inicio, 
                'data_fim' => $data_fim,
                'top_presencas' => [],
                'top_faltas' => []
            ];
        }
        
        $sql = "SELECT 
                    a.id,
                    a.nome AS aluno,
                    c.nome AS classe,
                    cg.nome AS congregacao,
                    COALESCE(total_chamadas.total_aulas, 0) AS total_aulas,
                    COALESCE(presencas_aluno.total_presencas, 0) AS presencas,
                    (COALESCE(total_chamadas.total_aulas, 0) - COALESCE(presencas_aluno.total_presencas, 0)) AS faltas,
                    CASE 
                        WHEN COALESCE(total_chamadas.total_aulas, 0) > 0 THEN
                            ROUND((COALESCE(presencas_aluno.total_presencas, 0) * 100.0) / total_chamadas.total_aulas, 1)
                        ELSE 0
                    END AS frequencia
                FROM alunos a
                INNER JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
                INNER JOIN classes c ON c.id = m.classe_id
                INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
                LEFT JOIN (
                    SELECT ch.classe_id, COUNT(*) AS total_aulas
                    FROM chamadas ch
                    WHERE ch.data BETWEEN :inicio AND :fim
                    GROUP BY ch.classe_id
                ) total_chamadas ON total_chamadas.classe_id = c.id
                LEFT JOIN (
                    SELECT p.aluno_id, COUNT(*) AS total_presencas
                    FROM presencas p
                    INNER JOIN chamadas ch ON ch.id = p.chamada_id
                    WHERE ch.data BETWEEN :inicio AND :fim
                    AND p.presente = 'presente'
                    GROUP BY p.aluno_id
                ) presencas_aluno ON presencas_aluno.aluno_id = a.id
                WHERE 1=1";
        
        $params = [':inicio' => $data_inicio, ':fim' => $data_fim];
        
        if (!empty($congregacao_id)) {
            $sql .= " AND cg.id = :congregacao";
            $params[':congregacao'] = $congregacao_id;
        }
        
        if (!empty($classe_id)) {
            $sql .= " AND c.id = :classe";
            $params[':classe'] = $classe_id;
        }
        
        $sql .= " ORDER BY frequencia DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'dados' => $dados,
            'trimestre_sem_dados' => false,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'top_presencas' => array_slice($dados, 0, 5),
            'top_faltas' => array_reverse(array_slice($dados, -5, 5))
        ];
    }
    
    /**
     * Relatório de Frequência de Alunos (Resumo)
     * Usa view resumo_presenca ou consulta direta
     */
    public function getFrequenciaAlunos() {
        try {
            // Tenta usar a view primeiro
            $query = "SELECT 
                        a.id AS aluno_id,
                        a.nome AS aluno_nome,
                        COALESCE(SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END), 0) AS total_presentes,
                        COALESCE(SUM(CASE WHEN p.presente = 'ausente' THEN 1 ELSE 0 END), 0) AS total_ausentes,
                        c.nome AS classe_nome,
                        cg.nome AS congregacao_nome,
                        m.trimestre
                      FROM alunos a
                      INNER JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
                      INNER JOIN classes c ON c.id = m.classe_id
                      INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
                      LEFT JOIN chamadas ch ON ch.classe_id = c.id
                      LEFT JOIN presencas p ON p.chamada_id = ch.id AND p.aluno_id = a.id
                      GROUP BY a.id, a.nome, c.nome, cg.nome, m.trimestre
                      ORDER BY a.nome";
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback: consulta simplificada
            $query = "SELECT 
                        a.id AS aluno_id,
                        a.nome AS aluno_nome,
                        0 AS total_presentes,
                        0 AS total_ausentes,
                        c.nome AS classe_nome,
                        cg.nome AS congregacao_nome,
                        m.trimestre
                      FROM alunos a
                      INNER JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
                      INNER JOIN classes c ON c.id = m.classe_id
                      INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
                      GROUP BY a.id, a.nome, c.nome, cg.nome, m.trimestre
                      ORDER BY a.nome";
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * Relatório de Aniversariantes
     * Retorna alunos aniversariantes do mês selecionado
     */
    public function getAniversariantes($mes = null, $classe_id = null) {
        $mes = $mes ?: date('m');
        
        $sql = "SELECT 
                    a.id,
                    a.nome,
                    a.data_nascimento,
                    a.telefone,
                    c.nome AS classe_nome,
                    cg.nome AS congregacao_nome,
                    TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) AS idade
                FROM alunos a
                INNER JOIN matriculas m ON m.aluno_id = a.id AND m.status = 'ativo'
                INNER JOIN classes c ON c.id = m.classe_id
                INNER JOIN congregacoes cg ON cg.id = m.congregacao_id
                WHERE MONTH(a.data_nascimento) = :mes";
        
        $params = [':mes' => $mes];
        
        if (!empty($classe_id)) {
            $sql .= " AND c.id = :classe_id";
            $params[':classe_id'] = $classe_id;
        }
        
        $sql .= " ORDER BY DAY(a.data_nascimento), a.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Relatório Geral de Presenças
     * Visão completa por classe com todos os indicadores
     */
    public function getRelatorioGeral($data_inicio = null, $data_fim = null, $congregacao_id = null, $trimestre = null) {
        $data_inicio = $data_inicio ?: date('Y-m-01');
        $data_fim = $data_fim ?: date('Y-m-d');
        
        $sql = "SELECT 
                    c.id AS classe_id,
                    c.nome AS classe_nome,
                    cg.nome AS congregacao_nome,
                    m.trimestre,
                    COUNT(DISTINCT m.aluno_id) AS total_matriculados,
                    COALESCE(pres.total_presencas, 0) AS total_presencas,
                    COALESCE(pres.total_faltas, 0) AS total_faltas,
                    COALESCE(cham.total_visitantes, 0) AS total_visitantes,
                    COALESCE(cham.total_biblias, 0) AS total_biblias,
                    COALESCE(cham.total_revistas, 0) AS total_revistas,
                    COALESCE(cham.total_ofertas, 0) AS total_ofertas
                FROM classes c
                LEFT JOIN matriculas m ON m.classe_id = c.id AND m.status = 'ativo'
                LEFT JOIN congregacoes cg ON cg.id = m.congregacao_id
                LEFT JOIN (
                    SELECT 
                        ch.classe_id,
                        SUM(ch.total_visitantes) AS total_visitantes,
                        SUM(ch.total_biblias) AS total_biblias,
                        SUM(ch.total_revistas) AS total_revistas,
                        SUM(CAST(ch.oferta_classe AS DECIMAL(10,2))) AS total_ofertas
                    FROM chamadas ch
                    WHERE ch.data BETWEEN :data_inicio AND :data_fim
                    GROUP BY ch.classe_id
                ) cham ON cham.classe_id = c.id
                LEFT JOIN (
                    SELECT 
                        m.classe_id,
                        m.trimestre,
                        SUM(CASE WHEN p.presente = 'presente' THEN 1 ELSE 0 END) AS total_presencas,
                        SUM(CASE WHEN p.presente = 'ausente' THEN 1 ELSE 0 END) AS total_faltas
                    FROM matriculas m
                    INNER JOIN chamadas ch ON ch.classe_id = m.classe_id AND ch.data BETWEEN :data_inicio AND :data_fim
                    INNER JOIN presencas p ON p.chamada_id = ch.id AND p.aluno_id = m.aluno_id
                    WHERE m.status = 'ativo'
                    GROUP BY m.classe_id, m.trimestre
                ) pres ON pres.classe_id = c.id AND pres.trimestre = m.trimestre
                WHERE 1=1";
        
        $params = [':data_inicio' => $data_inicio, ':data_fim' => $data_fim];
        
        if (!empty($congregacao_id)) {
            $sql .= " AND m.congregacao_id = :congregacao_id";
            $params[':congregacao_id'] = $congregacao_id;
        }
        
        if (!empty($trimestre)) {
            $sql .= " AND m.trimestre = :trimestre";
            $params[':trimestre'] = $trimestre;
        }
        
        $sql .= " GROUP BY c.id, c.nome, cg.nome, m.trimestre ORDER BY c.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar classes para filtro
     */
    public function getClasses() {
        $stmt = $this->pdo->prepare("SELECT id, nome FROM classes ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar congregações para filtro
     */
    public function getCongregacoes() {
        $stmt = $this->pdo->prepare("SELECT id, nome FROM congregacoes ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar alunos de uma classe para chamada
     */
    public function getAlunosPorClasse($classe_id, $trimestre = null) {
        $trimestre = $trimestre ?: $this->getTrimestreAtual();
        
        $sql = "SELECT a.id, a.nome, a.telefone
                FROM alunos a
                INNER JOIN matriculas m ON m.aluno_id = a.id
                WHERE m.classe_id = :classe_id 
                AND m.status = 'ativo'
                AND m.trimestre = :trimestre
                ORDER BY a.nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':classe_id' => $classe_id,
            ':trimestre' => $trimestre
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar presenças de uma chamada específica
     */
    public function getPresencasPorChamada($chamada_id) {
        $sql = "SELECT aluno_id, presente FROM presencas WHERE chamada_id = :chamada_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':chamada_id' => $chamada_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $presencas = [];
        foreach ($result as $row) {
            $presencas[$row['aluno_id']] = $row['presente'];
        }
        return $presencas;
    }
    
    /**
     * Salvar presenças de uma chamada
     */
    public function salvarPresencas($chamada_id, $presencas) {
        // Remove presenças antigas
        $stmt = $this->pdo->prepare("DELETE FROM presencas WHERE chamada_id = :chamada_id");
        $stmt->execute([':chamada_id' => $chamada_id]);
        
        // Insere novas presenças
        $sql = "INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (:chamada_id, :aluno_id, :presente)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($presencas as $aluno_id => $presente) {
            $stmt->execute([
                ':chamada_id' => $chamada_id,
                ':aluno_id' => $aluno_id,
                ':presente' => $presente
            ]);
        }
        
        return true;
    }
    
    /**
     * Get trimestre atual baseado na data
     */
    public function getTrimestreAtual() {
        $mes = (int)date('m');
        $ano = date('Y');
        
        if ($mes >= 1 && $mes <= 3) {
            return $ano . '-T1';
        } elseif ($mes >= 4 && $mes <= 6) {
            return $ano . '-T2';
        } elseif ($mes >= 7 && $mes <= 9) {
            return $ano . '-T3';
        } else {
            return $ano . '-T4';
        }
    }
    
    /**
     * Calcular período do trimestre
     */
    private function calcularPeriodoTrimestre($trimestre) {
        // Formato esperado: 2025-T2 ou apenas o número 2
        if (strpos($trimestre, '-') !== false) {
            list($ano, $t) = explode('-', $trimestre);
            $trimestre_num = (int)str_replace('T', '', $t);
        } else {
            $ano = date('Y');
            $trimestre_num = (int)$trimestre;
        }
        
        $mes_inicio = ($trimestre_num - 1) * 3 + 1;
        $mes_fim = $mes_inicio + 2;
        $data_inicio = "$ano-" . str_pad($mes_inicio, 2, '0', STR_PAD_LEFT) . "-01";
        $ultimo_dia = date("t", strtotime("$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-01"));
        $data_fim = "$ano-" . str_pad($mes_fim, 2, '0', STR_PAD_LEFT) . "-$ultimo_dia";
        return [$data_inicio, $data_fim];
    }
    
    /**
     * Calcular totais gerais a partir dos dados
     */
    public function calcularTotais($dados) {
        $totais = [
            'matriculados' => 0,
            'presentes' => 0,
            'ausentes' => 0,
            'justificados' => 0,
            'biblias' => 0,
            'revistas' => 0,
            'visitantes' => 0,
            'oferta' => 0
        ];
        
        foreach ($dados as $linha) {
            $totais['matriculados'] += (float)($linha['matriculados'] ?? 0);
            $totais['presentes'] += (float)($linha['presentes'] ?? 0);
            $totais['ausentes'] += (float)($linha['ausentes'] ?? 0);
            $totais['justificados'] += (float)($linha['justificados'] ?? 0);
            $totais['biblias'] += (float)($linha['biblias'] ?? 0);
            $totais['revistas'] += (float)($linha['revistas'] ?? 0);
            $totais['visitantes'] += (float)($linha['visitantes'] ?? 0);
            $totais['oferta'] += (float)($linha['oferta'] ?? 0);
        }
        
        return $totais;
    }
    
    /**
     * Formatar valor para moeda brasileira
     */
    public static function formatarMoeda($valor) {
        $valorFloat = (float)$valor;
        if ($valorFloat == 0) {
            return 'R$ 0,00';
        }
        return 'R$ ' . number_format($valorFloat, 2, ',', '.');
    }
}