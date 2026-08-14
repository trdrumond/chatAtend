<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

if ($id < 1) {
    return;
}

$stmtCtt = $PDO->prepare("SELECT contrato_id from tbl_servicos where id_servico=?");
$stmtCtt->execute([$id]);
$rowCtt = $stmtCtt->fetch(PDO::FETCH_ASSOC);
if (!is_array($rowCtt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($rowCtt['contrato_id'] ?? 0))) {
    return;
}

$sql = "UPDATE tbl_servicos SET ativo=? where id_servico=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$status, $id]);

if ($result == 1) {
    ?>
<script>
    $("#modal_alt_<?php echo $id; ?>").modal('hide');
    actionPage('cad-ser', 'cnf');

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

