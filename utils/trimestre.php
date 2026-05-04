<?php
// utils/trimestre.php

function getTrimestreAtual() {
    $mes = (int)date('m');
    $ano = date('Y');
    
    if ($mes >= 1 && $mes <= 3) {
        return "{$ano}-T1";
    } elseif ($mes >= 4 && $mes <= 6) {
        return "{$ano}-T2";
    } elseif ($mes >= 7 && $mes <= 9) {
        return "{$ano}-T3";
    } else {
        return "{$ano}-T4";
    }
}

function formatarTrimestre($trimestre) {
    if (preg_match('/(\d{4})-T(\d)/', $trimestre, $matches)) {
        $ano = $matches[1];
        $t = $matches[2];
        
        $periodos = [
            1 => '1º Trimestre (Jan-Mar)',
            2 => '2º Trimestre (Abr-Jun)',
            3 => '3º Trimestre (Jul-Set)',
            4 => '4º Trimestre (Out-Dez)'
        ];
        
        return $periodos[$t] . " de {$ano}";
    }
    
    return $trimestre;
}

function getTrimestresDisponiveis() {
    $ano_atual = date('Y');
    $trimestres = [];
    
    for ($ano = $ano_atual - 2; $ano <= $ano_atual + 1; $ano++) {
        for ($t = 1; $t <= 4; $t++) {
            $trimestre = "{$ano}-T{$t}";
            $trimestres[] = [
                'valor' => $trimestre,
                'label' => formatarTrimestre($trimestre)
            ];
        }
    }
    
    return $trimestres;
}