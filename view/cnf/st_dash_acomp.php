<?php

/**
 * Formata data/hora compacta para o painel de acompanhamento do dashboard.
 */
function stDashAcompFmtHora(?string $dataHora): string
{
    if ($dataHora === null || $dataHora === '') {
        return '';
    }
    $ts = strtotime($dataHora);
    if ($ts === false) {
        return '';
    }
    $hoje = date('Y-m-d');
    $diaMsg = date('Y-m-d', $ts);
    if ($diaMsg === $hoje) {
        return date('H:i', $ts);
    }
    if (date('Y', $ts) === date('Y')) {
        return date('d/m H:i', $ts);
    }
    return date('d/m/y H:i', $ts);
}
