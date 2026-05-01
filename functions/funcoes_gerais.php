<?php
/**
 * Funções utilitárias gerais do sistema Escola
 * 
 * Este arquivo deve ser incluído em todos os scripts que necessitem
 * de funções auxiliares comuns, evitando redeclarações.
 * 
 * @package Escola\Functions
 * @version 1.0
 */

// Prevenir acesso direto
if (!defined('ESCOLA_APP')) {
    define('ESCOLA_APP', true);
}

/**
 * Formata data no padrão brasileiro (DD/MM/YYYY)
 * 
 * @param string|null $data Data no formato MySQL (Y-m-d H:i:s)
 * @param string $formato Formato de saída (padrão: 'd/m/Y')
 * @return string Data formatada ou 'N/A' se inválida
 */
function formatarDataBrasil($data, $formato = 'd/m/Y') {
    if (!$data) {
        return 'N/A';
    }
    
    try {
        // Suporta timestamps Unix, strings de data ou objetos DateTime
        if (is_numeric($data)) {
            return date($formato, (int)$data);
        }
        
        $timestamp = strtotime($data);
        return $timestamp !== false ? date($formato, $timestamp) : 'N/A';
        
    } catch (Exception $e) {
        error_log("Erro ao formatar data: " . $e->getMessage());
        return 'N/A';
    }
}

/**
 * Formata valor monetário em Real Brasileiro (R$)
 * 
 * @param float|int|null $valor Valor a ser formatado
 * @param int $decimais Quantidade de casas decimais
 * @return string Valor formatado (ex: R$ 1.234,56)
 */
function formatarMoedaBr($valor, $decimais = 2) {
    if ($valor === null) {
        return 'R$ 0,00';
    }
    return 'R$ ' . number_format((float)$valor, $decimais, ',', '.');
}

/**
 * Sanitiza string para exibição segura no HTML
 * 
 * @param string|null $texto Texto a ser sanitizado
 * @param int $flags Flags para htmlspecialchars (opcional)
 * @return string Texto seguro para exibição
 */
function sanitizarExibicao($texto, $flags = ENT_QUOTES | ENT_SUBSTITUTE) {
    if ($texto === null) {
        return '';
    }
    return htmlspecialchars((string)$texto, $flags, 'UTF-8');
}

/**
 * Calcula o trimestre com base no mês informado
 * 
 * @param int|null $mes Mês (1-12). Se null, usa o mês atual.
 * @return int Número do trimestre (1-4)
 */
function obterTrimestrePorMes($mes = null) {
    if ($mes === null) {
        $mes = (int)date('m');
    }
    $mes = (int)$mes;
    
    if ($mes < 1 || $mes > 12) {
        throw new InvalidArgumentException("Mês deve estar entre 1 e 12");
    }
    
    return (int)ceil($mes / 3);
}

/**
 * Valida e sanitiza ID numérico vindo de requisição HTTP
 * 
 * @param mixed $id Valor a ser validado
 * @return int|null ID válido como inteiro, ou null se inválido
 */
function validarIdRequisicao($id) {
    if ($id === null || $id === '') {
        return null;
    }
    
    $idLimpo = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $idValido = filter_var($idLimpo, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    
    return $idValido !== false ? (int)$idValido : null;
}
?>