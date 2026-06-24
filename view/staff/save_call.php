<?php
include("../cnf/session.php");

//depurador($_POST);

function procuraProtocol($PDO){
    $protocol = date('ymd') . rand(1, 99) . time_to_sec(date('H:i:s'));
    $sql="SELECT protocolo from tbl_chat_fila where protocolo ='".$protocol."'";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $ddprt = $stmt->fetch( PDO::FETCH_ASSOC );

    if($ddprt['protocolo']==''){
        return $protocol;
    }else{
        procuraProtocol($PDO);
        return $protocol . rand(0, 99);
    }
}

$sqlVer="SELECT ativo from tbl_config_fila where  id_fila=".$_POST['fila'];
//echo "<br>".$sqlVer;
$stmt = $PDO->prepare($sqlVer);
$result = $stmt->execute();
$verFila = $stmt->fetch( PDO::FETCH_ASSOC );

if($verFila['ativo']==0){
    echo '<br><div style="color: red">A fila selecionada não esta mais ativa!</div>';
    echo '<script>
            $("#call").prop( "disabled", false );
            setTimeout(() => {
                document.location.reload(true);
                }, "1000");
          </script>';

} else {
    //$protocolo = date('ymd') . rand(1, 99) . time_to_sec(date('H:i:s'));
    $protocolo = procuraProtocol($PDO);

    if($protocolo==''){
        $protocolo = procuraProtocol($PDO);
    }



    $sql="INSERT INTO tbl_chat_fila (protocolo, contrato_id, fila_id, assunto_id, ate_resp, motivo)";

    $sql .=" VALUES ('".$protocolo."', '".$infoUser['contrato_id']."', '".$_POST['fila']."', '".$_POST['assunto']."', '".$infoUser['id_user']."', '".$_POST['motivo']."')";

    //echo "<br>".$sql;

    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    if($result==1){
    ?>
    <script>

        actionPage('chat-fila', 'idx');



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
}
    ?>
