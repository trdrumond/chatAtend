<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';


//depurador($_POST);


$sql="UPDATE tbl_user_img_perfil SET img=? where user_id=?";

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$img_vazio, (int) ($_POST['id'] ?? 0)]);


if($result==1){

    clearUserLayoutCache((int) $_POST['id']);

    ?>

<script>
Swal.fire({
    position: 'bottom-start',
    icon: 'success',
    title: 'Imagem resetada com sucesso!',
    showConfirmButton: false,
    timer: 1500
});
$("#modal_alt").modal('hide');
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
