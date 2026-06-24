<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

//echo "<br>".$status;

$sql="UPDATE tbl_regional SET ativo=$status where id_regional=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($_POST['agencias']!=''){
    //echo count($_POST['agencias']);
    for($x=0;$x<count($_POST['agencias']);$x++){
        //echo "<br>".$_POST['agencias'][$x];
        $sql="UPDATE tbl_agencia SET regional_id=".$_POST['id']." where id_agencia=".$_POST['agencias'][$x];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $res = $stmt->execute();
    }
}

if($result==1){
?>
<script>

    $("#modal_alt_<?php echo $_POST['id']; ?>").modal('hide');
    actionPage('cad-reg', 'cnf');



    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        //console.log('A ação é: ' + action);
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
