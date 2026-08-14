<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    return;
}
$stmtCtt = $PDO->prepare("SELECT contrato_id from tbl_empresa where id_empresa=?");
$stmtCtt->execute([$id]);
$rowCtt = $stmtCtt->fetch(PDO::FETCH_ASSOC);
if (!is_array($rowCtt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($rowCtt['contrato_id'] ?? 0))) {
    return;
}
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_empresa SET ativo=? where id_empresa=?");
$result = $stmt->execute([$status, $id]);

if ($result == 1) {
    $modalId = json_encode((string) $id);
?>
<script>
$("#modal_alt_" + <?= $modalId ?>).modal('hide');
actionPage('cad-emp', 'cnf');



function actionPage(action, sec) {
    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
    $.post("action.php", {
            action: action,
            sec: sec
        },
        function(valor) {
            $("#action-page").html(valor);
        });
}
</script>
<?php
    }

    ?>
