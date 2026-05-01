<?php
require_once '../../../config/conexao.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Função para segurança
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Obter ação e parâmetros
$action = isset($_GET['action']) ? sanitize($_GET['action']) : null;
$congregacao_id = isset($_GET['congregacao_id']) ? sanitize($_GET['congregacao_id']) : null;
$classe_id = isset($_GET['classe_id']) ? sanitize($_GET['classe_id']) : null;
$trimestre = isset($_GET['trimestre']) ? sanitize($_GET['trimestre']) : null;
$data_inicio = isset($_GET['data_inicio']) ? sanitize($_GET['data_inicio']) : null;
$data_fim = isset($_GET['data_fim']) ? sanitize($_GET['data_fim']) : null;

try {
    // Endpoint para listar congregações
    if ($action === 'congregacoes') {
        $stmt = $pdo->query("SELECT id, nome FROM congregacoes ORDER BY nome");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array("success" => true, "data" => $data));
        exit;
    }

    // Endpoint para listar classes
    if ($action === 'classes') {
        $stmt = $pdo->query("SELECT id, nome FROM classes ORDER BY nome");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array("success" => true, "data" => $data));
        exit;
    }

    // Endpoint principal para o relatório
    $query = "SELECT * FROM relatorio_consolidado WHERE 1=1";
    $params = array();

    // Adicionar filtros
    if ($congregacao_id) {
        $query .= " AND congregacao_id = :congregacao_id";
        $params[':congregacao_id'] = $congregacao_id;
    }
    if ($classe_id) {
        $query .= " AND classe_id = :classe_id";
        $params[':classe_id'] = $classe_id;
    }
    if ($trimestre) {
        $query .= " AND trimestre = :trimestre";
        $params[':trimestre'] = $trimestre;
    }
    if ($data_inicio) {
        $query .= " AND data_inicio >= :data_inicio";
        $params[':data_inicio'] = $data_inicio;
    }
    if ($data_fim) {
        $query .= " AND data_fim <= :data_fim";
        $params[':data_fim'] = $data_fim;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $relatorio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totais
    $totais = array(
        'matriculados' => 0,
        'presentes' => 0,
        'ausentes' => 0,
        'justificados' => 0,
        'biblias' => 0,
        'revistas' => 0,
        'visitantes' => 0,
        'ofertas' => 0
    );

    foreach ($relatorio as $row) {
        $totais['matriculados'] += $row['total_alunos_matriculados'];
        $totais['presentes'] += $row['total_presentes'];
        $totais['ausentes'] += $row['total_ausentes'];
        $totais['justificados'] += $row['total_justificados'];
        $totais['biblias'] += $row['total_biblias'];
        $totais['revistas'] += $row['total_revistas'];
        $totais['visitantes'] += $row['total_visitantes'];
        $totais['ofertas'] += $row['total_ofertas_distintas'];
    }

    // Retornar dados
    echo json_encode(array(
        "data" => $relatorio,
        "totais" => $totais,
        "success" => true
    ));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erro no banco de dados: " . $e->getMessage()
    ));
}
?>