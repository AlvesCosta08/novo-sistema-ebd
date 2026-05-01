<?php
session_start();
$senha = "123456"; 
function gerarSenhaHash($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}// Senha em texto puro
$senhaCriptografada = gerarSenhaHash($senha);

echo "Senha criptografada: " . $senhaCriptografada;
?>


