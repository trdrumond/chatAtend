<?php
include("../cnf/session.php");

function limpaAbandonoPendente($idFila)
{
    if (!isset($_SESSION['fila_abandono_pendente']) || !is_array($_SESSION['fila_abandono_pendente'])) {
        return;
    }

    unset($_SESSION['fila_abandono_pendente'][$idFila]);
    if (empty($_SESSION['fila_abandono_pendente'])) {
        unset($_SESSION['fila_abandono_pendente']);
    }
}

function cancelaFila($PDO, $idFila, $motivoCancela)
{
    $idFila = (int) $idFila;
    $motivoCancela = (string) $motivoCancela;

    $sql = "SELECT id_fila_chat, timediff(now(), data_hora) as te_diff, status_fila, hora_inicio from tbl_chat_fila where id_fila_chat=?";
    $st = $PDO->prepare($sql);
    $st->execute([$idFila]);
    $infFila = $st->fetch(PDO::FETCH_ASSOC);

    if (!is_array($infFila) || ($infFila['status_fila'] ?? '') != 1 || ($infFila['hora_inicio'] ?? '') !== '') {
        return false;
    }

    $sql = "UPDATE tbl_chat_fila SET status_fila=8, bko_resp=0, hora_inicio='0000-00-00 00:00:00', hora_fim='0000-00-00 00:00:00', te=?, ta='00:00:00', motivo_cancela=? where id_fila_chat=?";
    $stmt = $PDO->prepare($sql);
    return $stmt->execute([$infFila['te_diff'], $motivoCancela, $idFila]);
}

$idFila = isset($_POST['idFila']) ? (int) $_POST['idFila'] : 0;
$motivoCancela = isset($_POST['motivo_cancela']) ? trim((string) $_POST['motivo_cancela']) : '';
$autoAbandono = isset($_POST['auto_abandono']) ? (int) $_POST['auto_abandono'] : 0;

if ($idFila <= 0 || $motivoCancela === '') {
    exit;
}

if ($autoAbandono === 1 && strcasecmp($motivoCancela, 'Abandono de fila') === 0) {
    $delayAbandono = 30;
    $sessionId = session_id();
    $marcacao = time();

    if (!isset($_SESSION['fila_abandono_pendente']) || !is_array($_SESSION['fila_abandono_pendente'])) {
        $_SESSION['fila_abandono_pendente'] = [];
    }

    $_SESSION['fila_abandono_pendente'][$idFila] = [
        'marcacao' => $marcacao,
        'motivo' => $motivoCancela,
    ];

    session_write_close();
    ignore_user_abort(true);
    sleep($delayAbandono);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_id($sessionId);
        session_start();
    }

    $pendente = $_SESSION['fila_abandono_pendente'][$idFila] ?? null;
    if (!$pendente || (int) $pendente['marcacao'] !== $marcacao) {
        limpaAbandonoPendente($idFila);
        session_write_close();
        exit;
    }

    limpaAbandonoPendente($idFila);
    session_write_close();

    cancelaFila($PDO, $idFila, $motivoCancela);
    exit;
}

limpaAbandonoPendente($idFila);
$result = cancelaFila($PDO, $idFila, $motivoCancela);

if ($result == 1) {
    echo '<script>location.reload();</script>';
}

?>
