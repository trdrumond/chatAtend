<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $id)) {
    return;
}
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_contrato SET com=? where id_contrato=?");
$result = $stmt->execute([$status, $id]);

if ($result == 1) {
    clearLayoutCacheByContrato($PDO, $id);
?>
<script>
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
