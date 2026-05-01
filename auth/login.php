<?php 
session_start();

// Captura mensagem de erro ou sucesso, se existir
$mensagem = isset($_SESSION['mensagem']) ? $_SESSION['mensagem'] : '';
unset($_SESSION['mensagem']); // Remove a mensagem da sessão após exibição

// Define a classe do alerta com base na mensagem
$alertClass = (stripos($mensagem, 'sucesso') !== false) ? 'alert-success' : 'alert-danger';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escola Bíblica - Login</title>
    <link rel="icon" href="../assets/images/biblia.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/tela_login.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="card-header text-center">
            <img id="img_logo_form" src="../assets/images/biblia.png" alt="Logo">
            <h4 id="texto_form">Escola Bíblica Dominical</h4>
            <p>ADTC2 - MARANGUAPE</p>
        </div>
        <div class="card-body">
            <?php if (!empty($mensagem)): ?>
                <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                    <i class="fas <?= ($alertClass == 'alert-success') ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($mensagem); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="validar_login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn btn-login">Entrar</button>
            </form>
            <div class="text-center mt-3">
                <a href="#" class="forgot-password">Esqueceu a senha?</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>