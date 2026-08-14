<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    return;
}
$stmtCtt = $PDO->prepare("SELECT contrato_id from tbl_agencia where id_agencia=?");
$stmtCtt->execute([$id]);
$rowCtt = $stmtCtt->fetch(PDO::FETCH_ASSOC);
if (!is_array($rowCtt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($rowCtt['contrato_id'] ?? 0))) {
    return;
}
$regional = (int) ($_POST['regional'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_agencia SET regional_id=?, ativo=? where id_agencia=?");
$result = $stmt->execute([$regional, $status, $id]);

if ($result == 1) {
    $modalId = json_encode((string) $id);
?>
<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
    actionPage('cad-age', 'cnf');



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
