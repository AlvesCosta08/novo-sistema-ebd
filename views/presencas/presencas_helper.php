<?php
require_once '../../config/conexao.php';

$acao = $_POST['acao'] ?? '';

switch ($acao) {
    case 'listar':
        listarPresencas($pdo);
        break;
    case 'buscar':
        buscarPresenca($pdo);
        break;
    case 'excluir':
        excluirPresenca($pdo);
        break;
    case 'carregar_selects':
        carregarSelects($pdo);
        break;
    default:
        salvarPresenca($pdo);
}

// ---------------- FUNÇÕES ----------------

function listarPresencas($pdo) {
    $sql = "SELECT 
                p.id,
                p.presente,
                DATE_FORMAT(c.data, '%d/%m/%Y') AS data_chamada,
                a.nome AS aluno_nome,
                cl.nome AS classe_nome
            FROM presencas p
            JOIN chamadas c ON p.chamada_id = c.id
            JOIN alunos a ON p.aluno_id = a.id
            JOIN classes cl ON a.classe_id = cl.id
            ORDER BY c.data DESC, a.nome ASC";
    
    $stmt = $pdo->query($sql);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $dados]);
}

function buscarPresenca($pdo) {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM presencas WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount()) {
        echo json_encode(['sucesso' => true, 'dados' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Presença não encontrada.']);
    }
}

function salvarPresenca($pdo) {
    $id         = $_POST['id'] ?? '';
    $chamada_id = $_POST['chamada_id'] ?? '';
    $aluno_id   = $_POST['aluno_id'] ?? '';
    $presente   = $_POST['presente'] ?? '';

    if (empty($chamada_id) || empty($aluno_id) || empty($presente)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
        return;
    }

    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO presencas (chamada_id, aluno_id, presente) VALUES (?, ?, ?)");
        $sucesso = $stmt->execute([$chamada_id, $aluno_id, $presente]);
        $mensagem = $sucesso ? 'Presença registrada com sucesso!' : 'Erro ao registrar presença.';
    } else {
        $stmt = $pdo->prepare("UPDATE presencas SET chamada_id = ?, aluno_id = ?, presente = ? WHERE id = ?");
        $sucesso = $stmt->execute([$chamada_id, $aluno_id, $presente, $id]);
        $mensagem = $sucesso ? 'Presença atualizada com sucesso!' : 'Erro ao atualizar presença.';
    }

    echo json_encode(['sucesso' => $sucesso, 'mensagem' => $mensagem]);
}

function excluirPresenca($pdo) {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM presencas WHERE id = ?");
    $sucesso = $stmt->execute([$id]);
    echo json_encode([
        'sucesso' => $sucesso,
        'mensagem' => $sucesso ? 'Presença excluída com sucesso.' : 'Erro ao excluir presença.'
    ]);
}

function carregarSelects($pdo) {
    $chamadas = $pdo->query("SELECT id, DATE_FORMAT(data, '%d/%m/%Y') AS nome FROM chamadas ORDER BY data DESC")->fetchAll(PDO::FETCH_ASSOC);
    $alunos   = $pdo->query("SELECT id, nome FROM alunos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['sucesso' => true, 'chamadas' => $chamadas, 'alunos' => $alunos]);
}
