<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

$indice = isset($_POST['indice']) ? (int)$_POST['indice'] : 1;
$idFila = isset($_POST['idFila']) ? (int)$_POST['idFila'] : (int)$infoUser['fila_id'];
if ($idFila <= 0) {
    $idFila = (int)$infoUser['fila_id'];
}

$sqlVer = "SELECT user_id FROM tbl_pause WHERE date_format(hora_in, '%Y-%m-%d')=curdate() AND hora_out IS NULL AND user_id=? LIMIT 1";
$stmt = $PDO->prepare($sqlVer);
$stmt->execute([(int) $_SESSION['dados']['id_user']]);
$ver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!empty($ver['user_id'])) {
    echo "<script>setTimeout(function(){ if(typeof actionPageNav==='function'){actionPageNav('dash-pause','idx');}else if(typeof actionPage==='function'){actionPage('dash-pause','idx');} }, 0);</script>";
    exit;
}

$userId = (int)$_SESSION['dados']['id_user'];
$contratoId = (int)$infoUser['contrato_id'];
$protocoloAtivo = stBkoFindProtocoloAtivo($PDO, $userId, $contratoId, $indice);
if ($protocoloAtivo !== '') {
    echo '<div id="espera" data-st-bko-active="1" data-protocolo="'
        .htmlspecialchars($protocoloAtivo, ENT_QUOTES, 'UTF-8').'"></div>';
    exit;
}

$sqlQtd = "SELECT id, date_disp FROM tbl_tma_atend WHERE date_format(date_disp, '%Y-%m-%d')=curdate() AND fila_chat_id IS NULL AND date_out IS NULL AND resp_id=? ORDER BY id DESC LIMIT 1";
$stmt = $PDO->prepare($sqlQtd);
$stmt->execute([$userId]);
$userInfoTma = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($userInfoTma['id'])) {
    $PDO->prepare("INSERT INTO tbl_tma_atend (resp_id, contrato_id, date_disp) VALUES (?, ?, now())")->execute([$userId, $infoUser['id_contrato']]);
    $stmt->execute([$userId]);
    $userInfoTma = $stmt->fetch(PDO::FETCH_ASSOC);
}

$dateDisp = !empty($_POST['date_disp']) ? (string)$_POST['date_disp'] : ($userInfoTma['date_disp'] ?? date('Y-m-d H:i:s'));

$sqlQtd = "SELECT id FROM infoQtd WHERE date_disp < ? AND fila_id=?";
$stmt = $PDO->prepare($sqlQtd);
$stmt->execute([$dateDisp, $idFila]);
$infoQtd = $stmt->fetchAll(PDO::FETCH_ASSOC);

$abrirChat = function ($protocolo, $delay) use ($indice) {
    $delay = (int)$delay;
    echo "<script>setTimeout(function(){ actionPageChat(".$indice.", '".$protocolo."'); }, ".$delay.");</script>";
};

$buscaFila = function () use ($PDO, $idFila, $userId, $contratoId) {
    $emFila = [];
    if ($idFila > 0) {
        $sqlVer = "SELECT id_fila_chat, protocolo FROM tbl_chat_fila WHERE status_fila=1 AND contrato_id=? AND fila_id=? AND (bko_resp IS NULL OR bko_resp='' OR bko_resp=0) ORDER BY id_fila_chat ASC LIMIT 1";
        $stmt = $PDO->prepare($sqlVer);
        $stmt->execute([$contratoId, $idFila]);
        $emFila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    if (empty($emFila['id_fila_chat'])) {
        $stmt = $PDO->prepare("SELECT filas FROM tbl_user_filas WHERE user_id=?");
        $stmt->execute([$userId]);
        $cnfFilas = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($cnfFilas['filas'])) {
            $filaIds = array_values(array_filter(array_map('intval', explode(',', (string) $cnfFilas['filas']))));
            if (count($filaIds) > 0) {
                $ph = implode(',', array_fill(0, count($filaIds), '?'));
                $sqlVer = "SELECT id_fila_chat, protocolo FROM tbl_chat_fila WHERE status_fila=1 AND contrato_id=? AND fila_id IN (".$ph.") AND (bko_resp IS NULL OR bko_resp='' OR bko_resp=0) ORDER BY id_fila_chat ASC LIMIT 1";
                $stmt = $PDO->prepare($sqlVer);
                $stmt->execute(array_merge([$contratoId], $filaIds));
                $emFila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            }
        }
    }
    return $emFila;
};

if (count($infoQtd) === 0) {
    $emFila = $buscaFila();
    if (!empty($emFila['id_fila_chat'])) {
        $abrirChat($emFila['protocolo'], 0);
    }
}

echo '<div id="espera"></div>';
