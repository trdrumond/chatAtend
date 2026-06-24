<?php
include("../cnf/session.php");

//var_dump($_POST);




$sql="UPDATE tbl_config_fila SET ativo=0 where id_fila=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if( $status==0){
    $sql="SELECT id_fila_chat from tbl_chat_fila where fila_id=".$_POST['id']." and (status_fila=1 or status_fila=2)";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
    for($x=0;$x<count($dados);$x++){
        echo '<script>cancelFila('.$dados[$x]['id_fila_chat'].')</script>';

    }
}


if($result==1){
echo "<br><h2>Fila derrubada e desativada!</h2>";
    }

    ?>
<script>

function cancelFila(id_fila){
    console.log(id_fila);
    $.post("staff/save_cancelFila.php",
    {
        id_fila
    },
    function (valor) {
        sendAtend();
        sendBko();
    });
}




</script>
