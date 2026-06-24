<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

if($_POST['fila']){
    $sqlUpdate = "UPDATE tbl_user SET fila_id=".$_POST['fila']." where id_user=".$_POST['user'];
    //echo "<br>".$sqlPause;
    $stmt = $PDO->prepare( $sqlUpdate );
    $update = $stmt->execute();
    $infoUser['fila_id'] = $_POST['fila'];
    if($result==1){
        $sql ="SELECT ativo from tbl_config_fila where id_fila=".$infoUser['fila_id'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $filaBko = $stmt->fetch( PDO::FETCH_ASSOC );
        if($filaBko['ativo']==1){
            $classFila = 'fila_in';
            $titleFila = 'Fila Ativa';
        } else {
            $classFila = 'fila_out';
            $titleFila = 'Fila Desativada';
        }

        ?>
            <script>
                $('#btn-add-tab').attr("disabled", true);
                if ( typeof loadChatIn !== 'undefined') {
                    if ( typeof loadChatIn === 'function' ) {
                        setTimeout(function() { loadChatIn(); }, 500);
                    }
                }
                $('#idx-Fila').removeClass();
                $('#idx-Fila').addClass('<?=$classFila?>').attr({ title:"<?=$titleFila?>" });



                 Swal.fire({
                    position: 'bottom-start',
                    icon: 'success',
                    title: 'Fila configurada com sucesso!',
                    showConfirmButton: false,
                    timer: 1500
                });
            </script>
        <?php
    }

}
