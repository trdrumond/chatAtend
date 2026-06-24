<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

if($_POST['acao']==''){

    $sqlPause = "UPDATE tbl_pause SET hora_out=now() where hora_out is null and date_format(hora_in, '%Y-%m-%d')=curdate() and user_id=".$_SESSION['dados']['id_user'];
    //echo "<br>".$sqlPause;
    $stmt = $PDO->prepare( $sqlPause );
    $resultDem = $stmt->execute();

    $sqlPause = "UPDATE tbl_tma_atend SET date_disp=now() where fila_id is null and fila_chat_id is null and chat_id is null and resp_id=".$_SESSION['dados']['id_user'];
    //echo "<br>".$sqlPause;
    $stmt = $PDO->prepare( $sqlPause );
    $resultDem = $stmt->execute();

    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Disponivel');

?>

    <script>
        Swal.fire({
            position: 'bottom-start',
            icon: 'success',
            title: 'Oba! Voltamos aos atendimentos!',
            showConfirmButton: false,
            timer: 1500
        });
        //$("#new_registro").modal('hide');

        setTimeout(function(){
           document.location.reload(true);
        }, 500);


    </script>
<?php }

if($_POST['acao']=='pause'){
    $sqlVer = "SELECT user_id FROM tbl_pause WHERE hora_out IS NULL AND date_format(hora_in, '%Y-%m-%d')=curdate() AND user_id=".$_SESSION['dados']['id_user']." LIMIT 1";
    $stmt = $PDO->prepare($sqlVer);
    $stmt->execute();
    $jaEmPausa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($jaEmPausa['user_id'])) {
        $sqlPause = "INSERT INTO tbl_pause (user_id, hora_in, pause_id) VALUES ('".$_SESSION['dados']['id_user']."', now(), 1)";
        //echo "<br>".$sqlPause;
        $stmt = $PDO->prepare( $sqlPause );
        $resultDem = $stmt->execute();
        logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Pausa');
    }
}
