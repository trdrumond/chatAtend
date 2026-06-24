<?php
include('cnf/session.php');

if($_POST['action']=='sair'){

    $sqlInsLog="UPDATE tbl_log_diario SET ip='".$_SERVER['REMOTE_ADDR']."', date_out=now(), contrato_id='".$infoUser['contrato_id']."', municipio_id='".$infoUser['municipio_id']."', regional_id='".$infoUser['regional_id']."', agencia_id='".$infoUser['agencia_id']."', uf_id='".$infoUser['uf_id']."'  where user_id=".$idu." and data_log=curdate()";

    //echo "<br>".$sqlInsLog;
    $stmt = $PDO->prepare( $sqlInsLog );
    $execInsLog = $stmt->execute();

    if($execInsLog){
        if($infoUser['nivel_id']==4){
            $sqlInsLog="INSERT INTO tbl_log_atendimento (user_id, contrato_id, agencia_id, fila_id, data_hora, acao) VALUES ('".$idu."', '".$infoUser['contrato_id']."', '".$infoUser['agencia_id']."', '".$infoUser['fila_id']."', now(), 'Logout')";

            //echo "<br>".$sqlInsLog;
            $stmt = $PDO->prepare( $sqlInsLog );
            $execInsLog_ = $stmt->execute();

            $sqlInsLog="INSERT INTO tbl_log_atendimento_secondary (user_id, contrato_id, agencia_id, fila_id, data_hora, acao) VALUES ('".$idu."', '".$infoUser['contrato_id']."', '".$infoUser['agencia_id']."', '".$infoUser['fila_id']."', now(), 'Logout')";

            //echo "<br>".$sqlInsLog;
            $stmt = $PDO->prepare( $sqlInsLog );
            $execInsLog_ = $stmt->execute();
        }

        //echo "<br>Sair";
        session_destroy();
        ?>
<script>
Swal.fire({
    title: 'Logout realizado!',
    text: 'Logout realizado com sucesso, até a próxima!.',
    timer: 2000,
    timerProgressBar: true,
    showConfirmButton: false,
})
</script>
<meta http-equiv="refresh" content="0;../out.php" />
<?php

    }


}

?>
