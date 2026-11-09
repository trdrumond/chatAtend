<?php
ob_start();
include('../cnf/session.php');
ob_end_clean();
if (function_exists('session_write_close')) {
    session_write_close();
}

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$bkoId = (int) ($_SESSION['dados']['id_user'] ?? 0);
$nivelId = (int) ($infoUser['nivel_id'] ?? 0);
$contratoId = (int) ($infoUser['contrato_id'] ?? 0);

if ($bkoId <= 0 || $nivelId !== 4 || $contratoId <= 0) {
    echo json_encode(['ok' => true, 'pendentes' => []]);
    exit;
}

$limite = 10;

try {
    $stmt = $PDO->prepare(
        'SELECT ci.id_chat, ci.fila_id, ci.fila_chat_id, cf.protocolo, cf.hora_fim'
        . ' FROM tbl_chat_info ci'
        . ' INNER JOIN tbl_chat_fila cf ON cf.id_fila_chat = ci.fila_chat_id'
        . ' WHERE ci.rem_chat = ?'
        . ' AND cf.status_fila = 4'
        . ' AND cf.hora_fim >= (NOW() - INTERVAL 24 HOUR)'
        . ' ORDER BY cf.hora_fim ASC, ci.id_chat ASC'
    );
    $stmt->execute([(string) $bkoId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Um registro por fila_chat_id (o chat mais recente do BKO para aquele atendimento).
    $porFilaChat = [];
    foreach ($rows as $row) {
        $filaChatId = (int) $row['fila_chat_id'];
        $filaId = (int) $row['fila_id'];
        if ($filaChatId <= 0 || $filaId <= 0) {
            continue;
        }
        if (!isset($porFilaChat[$filaChatId]) || (int) $row['id_chat'] > (int) $porFilaChat[$filaChatId]['id_chat']) {
            $porFilaChat[$filaChatId] = $row;
        }
    }

    if ($porFilaChat === []) {
        echo json_encode(['ok' => true, 'pendentes' => []]);
        exit;
    }

    $porTabela = [];
    foreach ($porFilaChat as $filaChatId => $row) {
        $tabela = 'tbl_in_pos_' . (int) $row['fila_id'] . '_' . $contratoId;
        if (!preg_match('/^tbl_in_pos_\d+_\d+$/', $tabela)) {
            continue;
        }
        $porTabela[$tabela][] = $filaChatId;
    }

    $tabelasExistentes = [];
    if ($porTabela !== []) {
        $nomes = array_keys($porTabela);
        $ph = implode(',', array_fill(0, count($nomes), '?'));
        $stmtTab = $PDO->prepare(
            'SELECT TABLE_NAME FROM information_schema.TABLES'
            . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $ph . ')'
        );
        $stmtTab->execute($nomes);
        foreach ($stmtTab->fetchAll(PDO::FETCH_COLUMN) as $nome) {
            $tabelasExistentes[strtolower((string) $nome)] = true;
        }
    }

    $gravados = [];
    $pendenteElegivel = [];
    foreach ($porTabela as $tabela => $filaChatIds) {
        if (!isset($tabelasExistentes[strtolower($tabela)])) {
            continue;
        }
        foreach ($filaChatIds as $id) {
            $pendenteElegivel[$id] = true;
        }
        $ph = implode(',', array_fill(0, count($filaChatIds), '?'));
        $stmtPos = $PDO->prepare('SELECT DISTINCT chat_id FROM ' . $tabela . ' WHERE chat_id IN (' . $ph . ')');
        $stmtPos->execute(array_values($filaChatIds));
        foreach ($stmtPos->fetchAll(PDO::FETCH_COLUMN) as $chatIdPos) {
            $gravados[(int) $chatIdPos] = true;
        }
    }

    $ordenados = array_values($porFilaChat);
    usort($ordenados, static function ($a, $b) {
        $cmp = strcmp((string) $a['hora_fim'], (string) $b['hora_fim']);
        return $cmp !== 0 ? $cmp : ((int) $a['id_chat'] <=> (int) $b['id_chat']);
    });

    $pendentes = [];
    foreach ($ordenados as $row) {
        $filaChatId = (int) $row['fila_chat_id'];
        if (!isset($pendenteElegivel[$filaChatId]) || isset($gravados[$filaChatId])) {
            continue;
        }
        $pendentes[] = [
            'chatId' => (int) $row['id_chat'],
            'filaChatId' => $filaChatId,
            'protocolo' => (string) ($row['protocolo'] ?? ''),
            'horaFim' => (string) ($row['hora_fim'] ?? ''),
        ];
        if (count($pendentes) >= $limite) {
            break;
        }
    }

    echo json_encode(['ok' => true, 'pendentes' => $pendentes], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('load_pos_pendente: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'pendentes' => []]);
}
