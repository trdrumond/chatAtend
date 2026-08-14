<?php
include("../cnf/session.php");

$bkoId = (int) ($_POST['bko'] ?? 0);
$filaId = (int) ($_POST['fila'] ?? 0);

if ($filaId < 1) {
    return;
}

$stmt = $PDO->prepare("SELECT contrato_id from tbl_chat_fila_secondary where id_fila_chat=?");
$stmt->execute([$filaId]);
$filaRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!is_array($filaRow)) {
    $stmt = $PDO->prepare("SELECT contrato_id from tbl_chat_fila where id_fila_chat=?");
    $stmt->execute([$filaId]);
    $filaRow = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!is_array($filaRow) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($filaRow['contrato_id'] ?? 0))) {
    return;
}

$stmt = $PDO->prepare("UPDATE tbl_chat_fila SET bko_resp=? where id_fila_chat=?");
$result = $stmt->execute([$bkoId, $filaId]);

$stmt = $PDO->prepare("UPDATE tbl_chat_fila_secondary SET bko_resp=? where id_fila_chat=?");
$result = $stmt->execute([$bkoId, $filaId]);

if ($result == 1) {
    $stmt = $PDO->prepare("UPDATE tbl_chat_info SET rem_chat=? where fila_chat_id=?");
    $result = $stmt->execute([$bkoId, $filaId]);

    $stmt = $PDO->prepare("UPDATE tbl_chat_info_secondary SET rem_chat=? where fila_chat_id=?");
    $result = $stmt->execute([$bkoId, $filaId]);

    if ($result == 1) {
        $stmt = $PDO->prepare("UPDATE tbl_pend_info SET bko_resp=? where chat_id=?");
        $result = $stmt->execute([$bkoId, $filaId]);

        if ($result == 1) {
            $stmt = $PDO->prepare("SELECT id_chat from tbl_chat_info where fila_chat_id=?");
            $stmt->execute([$filaId]);
            $dds = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $PDO->prepare("SELECT id_chat from tbl_chat_info_secondary where fila_chat_id=?");
            $stmt->execute([$filaId]);
            $dds = $stmt->fetch(PDO::FETCH_ASSOC);

            $idChat = (int) ($dds['id_chat'] ?? 0);
            ?>
<script>
abreDetailAlt(<?= $idChat ?>);

function abreDetailAlt(id) {
    $.post("staff/load_hist_pend.php", {
            id
        },
        function(valor) {
            $('#div_detail').html(
                '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
            setTimeout(function() {
                $('#div_detail').html(valor);
            }, 500);
        });
}
</script>
<?php
        }
    }
}

?>

