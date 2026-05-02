// functions/funcoes_seguranca.php
<?php

/**
 * Funções de segurança avançadas
 */

// Gerar hash de senha com custo aumentado
function gerarSenhaHash($senha) {
    $options = [
        'cost' => 12,  // Custo mais alto (padrão é 10)
        'algo' => PASSWORD_ARGON2ID  // Usar Argon2id (mais seguro que bcrypt)
    ];
    return password_hash($senha, PASSWORD_ARGON2ID, $options);
}

// Verificar senha com tempo constante
function verificarSenha($senha, $hash) {
    return password_verify($senha, $hash);
}

// Gerar token CSRF
function gerarCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar CSRF token
function verificarCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        throw new Exception('CSRF token inválido');
    }
    return true;
}

// Sanitizar entrada
function sanitizarEntrada($dados) {
    if (is_array($dados)) {
        return array_map('sanitizarEntrada', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

// Gerar ID único para sessão
function gerarIdSessaoSeguro() {
    return bin2hex(random_bytes(32));
}

// Rate limiting por IP
function rateLimit($key, $limit = 10, $time = 60) {
    $cache_file = __DIR__ . "/../cache/rate_limit_{$key}.txt";
    $current_time = time();
    
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        if ($data['time'] + $time > $current_time && $data['count'] >= $limit) {
            return false;
        }
        if ($data['time'] + $time < $current_time) {
            $data['count'] = 1;
            $data['time'] = $current_time;
        } else {
            $data['count']++;
        }
    } else {
        $data = ['count' => 1, 'time' => $current_time];
    }
    
    file_put_contents($cache_file, json_encode($data));
    return true;
}