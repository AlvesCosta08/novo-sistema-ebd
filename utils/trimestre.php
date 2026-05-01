<?php
/**
 * Utilitário para cálculo e validação de trimestres acadêmicos
 * Formato: AAAA-T1, AAAA-T2, AAAA-T3, AAAA-T4
 */

/**
 * Calcula o trimestre atual e próximo com base na data do servidor
 * 
 * @return array ['atual' => string, 'proximo' => string]
 */
function calcularTrimestres() {
    $mes = (int) date('n'); // 1 a 12
    $ano = (int) date('Y');
    
    // Definição padrão: T1=Jan-Mar, T2=Abr-Jun, T3=Jul-Set, T4=Out-Dez
    if ($mes <= 3) {
        $trimestre_atual = 1;
    } elseif ($mes <= 6) {
        $trimestre_atual = 2;
    } elseif ($mes <= 9) {
        $trimestre_atual = 3;
    } else {
        $trimestre_atual = 4;
    }
    
    $trimestre_proximo = $trimestre_atual + 1;
    $ano_proximo = $ano;
    
    // Se estiver no T4, o próximo é T1 do ano seguinte
    if ($trimestre_proximo > 4) {
        $trimestre_proximo = 1;
        $ano_proximo++;
    }
    
    return [
        'atual' => sprintf('%04d-T%d', $ano, $trimestre_atual),
        'proximo' => sprintf('%04d-T%d', $ano_proximo, $trimestre_proximo)
    ];
}

/**
 * Valida se uma string segue o formato de trimestre: AAAA-T[1-4]
 * 
 * @param string $trimestre Valor a validar
 * @return bool True se válido, false caso contrário
 */
function validarFormatoTrimestre($trimestre) {
    return preg_match('/^\d{4}-T[1-4]$/', trim($trimestre)) === 1;
}

/**
 * Calcula o próximo trimestre a partir de um trimestre base
 * 
 * @param string $trimestre_base Ex: '2026-T2'
 * @return string|null Próximo trimestre ou null se inválido
 */
function calcularProximoTrimestre($trimestre_base) {
    if (!validarFormatoTrimestre($trimestre_base)) {
        return null;
    }
    
    list($ano, $tri) = explode('-T', $trimestre_base);
    $ano = (int) $ano;
    $tri = (int) $tri;
    
    $tri_proximo = $tri + 1;
    $ano_proximo = $ano;
    
    if ($tri_proximo > 4) {
        $tri_proximo = 1;
        $ano_proximo++;
    }
    
    return sprintf('%04d-T%d', $ano_proximo, $tri_proximo);
}

/**
 * Lista todos os trimestres em um intervalo de anos
 * 
 * @param int $ano_inicio Ano inicial
 * @param int $ano_fim Ano final (opcional, padrão: ano atual + 2)
 * @return array Lista de trimestres no formato ['2026-T1', '2026-T2', ...]
 */
function listarTrimestres($ano_inicio, $ano_fim = null) {
    if ($ano_fim === null) {
        $ano_fim = (int) date('Y') + 2;
    }
    
    $trimestres = [];
    for ($ano = $ano_inicio; $ano <= $ano_fim; $ano++) {
        for ($tri = 1; $tri <= 4; $tri++) {
            $trimestres[] = sprintf('%04d-T%d', $ano, $tri);
        }
    }
    return $trimestres;
}
?>