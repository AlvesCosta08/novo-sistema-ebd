<?php
/**
 * Funções utilitárias gerais do sistema Escola
 * 
 * @package Escola\Functions
 * @version 2.0
 */

// Prevenir acesso direto
if (!defined('ESCOLA_APP')) {
    define('ESCOLA_APP', true);
}

/**
 * Formata data no padrão brasileiro (DD/MM/YYYY)
 * 
 * @param string|null $data Data no formato MySQL (Y-m-d ou Y-m-d H:i:s)
 * @param string $formato Formato de saída (padrão: 'd/m/Y')
 * @return string Data formatada ou 'N/A' se inválida
 */
function formatarDataBrasil($data, $formato = 'd/m/Y') {
    if (!$data) {
        return 'N/A';
    }
    
    try {
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
 * Formata hora no padrão brasileiro (HH:MM)
 * 
 * @param string|null $hora Hora no formato H:i:s
 * @return string Hora formatada ou 'N/A'
 */
function formatarHoraBrasil($hora) {
    if (!$hora) return 'N/A';
    return date('H:i', strtotime($hora));
}

/**
 * Formata data e hora completas (DD/MM/YYYY HH:MM)
 * 
 * @param string|null $datetime Data/hora MySQL
 * @return string Data/hora formatada
 */
function formatarDateTimeBrasil($datetime) {
    if (!$datetime) return 'N/A';
    return formatarDataBrasil($datetime) . ' ' . formatarHoraBrasil($datetime);
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
 * @param int $flags Flags para htmlspecialchars
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
 * Obtém o trimestre atual baseado na data atual
 * 
 * @return int Número do trimestre (1-4)
 */
function getTrimestreAtual() {
    $mes = date('n');
    if ($mes >= 1 && $mes <= 3) return 1;
    if ($mes >= 4 && $mes <= 6) return 2;
    if ($mes >= 7 && $mes <= 9) return 3;
    return 4;
}

/**
 * Converte número do trimestre para formato ANO-TRIMESTRE
 * 
 * @param int $trimestre Número do trimestre (1-4)
 * @param int|null $ano Ano (opcional, usa ano atual se null)
 * @return string Formato ANO-T (ex: 2026-T2)
 */
function formatarTrimestrePadrao($trimestre, $ano = null) {
    $ano = $ano ?? date('Y');
    return $ano . '-T' . $trimestre;
}

/**
 * Extrai número do trimestre de string formatada (ex: 2026-T2 → 2)
 * 
 * @param string $trimestreStr String no formato ANO-TRIMESTRE
 * @return int|null Número do trimestre ou null
 */
function extrairNumeroTrimestre($trimestreStr) {
    if (preg_match('/-T([1-4])$/', $trimestreStr, $matches)) {
        return (int)$matches[1];
    }
    if (preg_match('/^[1-4]$/', $trimestreStr)) {
        return (int)$trimestreStr;
    }
    return null;
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

/**
 * Converte timestamp em texto amigável (ex: "há 5 minutos")
 * 
 * @param string $datetime Data/hora no formato MySQL
 * @return string Texto formatado
 */
function timeAgo($datetime) {
    if (!$datetime) return 'N/A';
    
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'há ' . $diff . ' segundos';
    } elseif ($diff < 3600) {
        $minutos = floor($diff / 60);
        return 'há ' . $minutos . ' minuto' . ($minutos > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $horas = floor($diff / 3600);
        return 'há ' . $horas . ' hora' . ($horas > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $dias = floor($diff / 86400);
        return 'há ' . $dias . ' dia' . ($dias > 1 ? 's' : '');
    } else {
        return formatarDataBrasil($datetime);
    }
}

/**
 * Gera slug amigável para URLs
 * 
 * @param string $text Texto original
 * @return string Slug
 */
function gerarSlug($text) {
    $text = preg_replace('/[áàãâä]/ui', 'a', $text);
    $text = preg_replace('/[éèêë]/ui', 'e', $text);
    $text = preg_replace('/[íìîï]/ui', 'i', $text);
    $text = preg_replace('/[óòõôö]/ui', 'o', $text);
    $text = preg_replace('/[úùûü]/ui', 'u', $text);
    $text = preg_replace('/[ç]/ui', 'c', $text);
    $text = preg_replace('/[^a-z0-9]/i', '-', $text);
    $text = strtolower(trim($text, '-'));
    return $text;
}