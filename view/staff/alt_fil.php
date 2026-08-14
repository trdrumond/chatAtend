<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    return;
}
$stmtCtt = $PDO->prepare("SELECT contrato_id from tbl_config_fila where id_fila=?");
$stmtCtt->execute([$id]);
$rowCtt = $stmtCtt->fetch(PDO::FETCH_ASSOC);
if (!is_array($rowCtt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($rowCtt['contrato_id'] ?? 0))) {
    return;
}
$titulo = (string) ($_POST['titulo'] ?? '');
$multichat = (int) ($_POST['multichat'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;
$assuntoRaw = $_POST['assuntos'] ?? [];
if (!is_array($assuntoRaw)) {
    $assuntoRaw = [$assuntoRaw];
}
$assuntos = implode(',', array_map('intval', $assuntoRaw));

$stmt = $PDO->prepare("UPDATE tbl_config_fila SET ativo=?, nome_fila=?, assuntos_id=?, multichat=? where id_fila=?");
$result = $stmt->execute([$status, $titulo, $assuntos, $multichat, $id]);

if ($result == 1) {
    $modalId = json_encode((string) $id);
?>
<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
    actionPage('cad-fil', 'cnf');



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
