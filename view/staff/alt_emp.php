<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

//echo "<br>".$status;

$sql="UPDATE tbl_empresa SET ativo=$status where id_empresa=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();


if($result==1){
?>
<script>
$("#modal_alt_<?php echo $_POST['id']; ?>").modal('hide');
actionPage('cad-emp', 'cnf');



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
