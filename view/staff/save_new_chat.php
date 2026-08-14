<?php
include("../cnf/session.php");

if ((int) ($infoUser['new_conv'] ?? 0) !== 1) {
    return;
}

$col = (int) ($_POST['col'] ?? 0);
$userId = (int) ($infoUser['id_user'] ?? 0);

if ($col < 1 || $col === $userId) {
    return;
}

$stmtDest = $PDO->prepare("SELECT id_user, contrato_id FROM tbl_user WHERE id_user=?");
$stmtDest->execute([$col]);
$destUser = $stmtDest->fetch(PDO::FETCH_ASSOC);
if (!is_array($destUser) || empty($destUser['id_user'])) {
    return;
}

$contratoDest = (int) ($destUser['contrato_id'] ?? 0);
if ($contratoDest < 1) {
    return;
}
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoDest)) {
    return;
}

$contratoSessao = (int) ($infoUser['contrato_id'] ?? 0);
if ($contratoSessao < 1) {
    return;
}
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoSessao)) {
    return;
}

$stmt = $PDO->prepare(
    "INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES (?, ?, ?, ?, ?)"
);
$result = $stmt->execute([
    $contratoSessao,
    $userId,
    $col,
    '',
    '',
]);

if ($result == 1) {
?>
<script>
    Swal.fire({
        position: 'bottom-start',
        icon: 'success',
        title: 'Novo Chat Iniciado!',
        showConfirmButton: false,
        timer: 1500
    });
    $("#new_registro").modal('hide');
    actionPage('com-idx', 'idx');



    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        $.post("action.php",
        {
            action: action, sec: sec
        },
        function (valor) {
            $("#action-page").html(valor);
        });
    }


</script>
<?php
    }

    ?>
