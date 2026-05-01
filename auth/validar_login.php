<?php
session_start();
require_once "../config/conexao.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Erro: Requisição inválida.");
}

// Verifica se os dados foram enviados corretamente
if (empty($_POST['email']) || empty($_POST['senha'])) {
    $_SESSION['mensagem'] = "Preencha todos os campos.";
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

try {
    // Verifica se a conexão com o banco está ativa
    if (!isset($pdo)) {
        die("Erro na conexão com o banco de dados!");
    }

    // CORRIGIDO: Removido 'status' porque a tabela usuarios NÃO tem essa coluna
    $sql = "SELECT id, nome, email, senha, perfil, congregacao_id FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // CORRIGIDO: Removida a verificação de status (não existe na tabela)
        // Como a tabela não tem status, consideramos todos os usuários como "ativos"
        
        if (password_verify($senha, $usuario['senha'])) {
            if (in_array($usuario['perfil'], ["admin", "professor"])) {
                // ✅ DEFINIR TODAS AS VARIÁVEIS DE SESSÃO NECESSÁRIAS
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome'] ?? 'Usuário';
                $_SESSION['usuario_nome'] = $usuario['nome'] ?? 'Usuário';
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['perfil'] = $usuario['perfil'];  // Importante: nome correto
                $_SESSION['usuario_perfil'] = $usuario['perfil'];
                $_SESSION['congregacao_id'] = $usuario['congregacao_id'] ?? null;
                
                // Regenerar ID da sessão por segurança
                session_regenerate_id(true);
                
                header("Location: ../views/dashboard.php");
                exit();
            } else {
                $_SESSION['mensagem'] = "Acesso restrito. Apenas administradores e professores podem acessar.";
            }
        } else {
            $_SESSION['mensagem'] = "Senha incorreta.";
        }
    } else {
        $_SESSION['mensagem'] = "Usuário não encontrado.";
    }
} catch (PDOException $e) {
    $_SESSION['mensagem'] = "Erro no banco de dados: " . $e->getMessage();
}

// Redireciona para a página de login se houver erro
header("Location: login.php");
exit();
?>