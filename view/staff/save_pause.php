<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

$userId = (int) $_SESSION['dados']['id_user'];

if($_POST['acao']==''){

    $sqlPause = "UPDATE tbl_pause SET hora_out=now() where hora_out is null and date_format(hora_in, '%Y-%m-%d')=curdate() and user_id=?";
    $stmt = $PDO->prepare( $sqlPause );
    $resultDem = $stmt->execute([$userId]);

    $sqlPause = "UPDATE tbl_tma_atend SET date_disp=now() where fila_id is null and fila_chat_id is null and chat_id is null and resp_id=?";
    $stmt = $PDO->prepare( $sqlPause );
    $resultDem = $stmt->execute([$userId]);

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
    $sqlVer = "SELECT user_id FROM tbl_pause WHERE hora_out IS NULL AND date_format(hora_in, '%Y-%m-%d')=curdate() AND user_id=? LIMIT 1";
    $stmt = $PDO->prepare($sqlVer);
    $stmt->execute([$userId]);
    $jaEmPausa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($jaEmPausa['user_id'])) {
        $sqlPause = "INSERT INTO tbl_pause (user_id, hora_in, pause_id) VALUES (?, now(), 1)";
        $stmt = $PDO->prepare( $sqlPause );
        $resultDem = $stmt->execute([$userId]);
        logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Pausa');
    }
}
