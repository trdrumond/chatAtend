<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

//var_dump($_POST);

$filas = implode(",", $_POST['filas']);


$sql="SELECT user_id from tbl_user_filas where user_id=".$_POST['id'];
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dados = $stmt->fetch( PDO::FETCH_ASSOC );



if($dados['user_id']!=''){
$sql="UPDATE tbl_user_filas SET filas='".$filas."' where user_id=".$_POST['id'];
} else {
    $sql="INSERT INTO tbl_user_filas (user_id, filas) VALUES ('".$_POST['id']."', '".$filas."')";
}

//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
    clearUserLayoutCache((int) $_POST['id']);
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
