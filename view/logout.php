<?php
include('cnf/session.php');

if($_POST['action']=='sair'){

    $sqlInsLog="UPDATE tbl_log_diario SET ip=?, date_out=now(), contrato_id=?, municipio_id=?, regional_id=?, agencia_id=?, uf_id=? where user_id=? and data_log=curdate()";

    //echo "<br>".$sqlInsLog;
    $stmt = $PDO->prepare( $sqlInsLog );
    $execInsLog = $stmt->execute([
        (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        (int) ($infoUser['contrato_id'] ?? 0),
        (int) ($infoUser['municipio_id'] ?? 0),
        (int) ($infoUser['regional_id'] ?? 0),
        (int) ($infoUser['agencia_id'] ?? 0),
        (int) ($infoUser['uf_id'] ?? 0),
        (int) $idu,
    ]);

    if($execInsLog){
        if($infoUser['nivel_id']==4){
            logAtendimento($PDO, (int) $idu, 'Logout');
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
