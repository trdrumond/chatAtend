<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

$indice = isset($_POST['indice']) ? (int)$_POST['indice'] : 1;
$idFila = isset($_POST['idFila']) ? (int)$_POST['idFila'] : (int)$infoUser['fila_id'];
if ($idFila <= 0) {
    $idFila = (int)$infoUser['fila_id'];
}
$userId = (int)$_SESSION['dados']['id_user'];
$contratoId = (int)($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);

$sqlVer = "SELECT user_id FROM tbl_pause WHERE date_format(hora_in, '%Y-%m-%d')=curdate() AND hora_out IS NULL AND user_id=? LIMIT 1";
$stmt = $PDO->prepare($sqlVer);
$stmt->execute([$userId]);
$ver = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($ver['user_id'])) {
    echo "<script>setTimeout(function(){ if(typeof actionPageNav==='function'){actionPageNav('dash-pause','idx');}else if(typeof actionPage==='function'){actionPage('dash-pause','idx');} }, 0);</script>";
    exit;
}

$protocoloAtivo = stBkoFindProtocoloAtivo($PDO, $userId, $contratoId, $indice);
if ($protocoloAtivo !== '') {
    echo '<div id="espera" data-st-bko-active="1" data-protocolo="'
        .htmlspecialchars($protocoloAtivo, ENT_QUOTES, 'UTF-8').'"></div>';
    exit;
}

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

if (!empty($emFila['id_fila_chat'])) {
    echo "<script>setTimeout(function(){ actionPageChat(".$indice.", '".$emFila['protocolo']."'); }, 0);</script>";
}
