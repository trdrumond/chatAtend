<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

//var_dump($_POST);

$filasPost = $_POST['filas'] ?? [];
if (!is_array($filasPost)) {
    $filasPost = [$filasPost];
}
$filasIds = array_values(array_filter(array_map('intval', $filasPost)));
$filas = implode(",", $filasIds);
$userId = (int) ($_POST['id'] ?? 0);
if ($userId < 1) {
    return;
}
$stmtTgt = $PDO->prepare("SELECT contrato_id from tbl_user where id_user=?");
$stmtTgt->execute([$userId]);
$tgt = $stmtTgt->fetch(PDO::FETCH_ASSOC);
if (!is_array($tgt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($tgt['contrato_id'] ?? 0))) {
    return;
}

$sql = "SELECT user_id from tbl_user_filas where user_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$userId]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);



if (($dados['user_id'] ?? '') != '') {
    $sql = "UPDATE tbl_user_filas SET filas=? where user_id=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$filas, $userId]);
} else {
    $sql = "INSERT INTO tbl_user_filas (user_id, filas) VALUES (?, ?)";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$userId, $filas]);
}

if ($result == 1) {
    clearUserLayoutCache($userId);
?>
<script>
$("#modal_filas").modal('hide');
actionPage('cad-usu', 'cnf');



function actionPage(action, sec) {
    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
    //console.log('A ação é: ' + action);
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
