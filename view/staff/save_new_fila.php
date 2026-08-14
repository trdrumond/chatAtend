<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

if($_POST['fila']){
    $filaId = (int) $_POST['fila'];
    $userId = (int) ($_POST['user'] ?? 0);
    $sqlUpdate = "UPDATE tbl_user SET fila_id=? where id_user=?";
    $stmt = $PDO->prepare( $sqlUpdate );
    $update = $stmt->execute([$filaId, $userId]);
    $infoUser['fila_id'] = $filaId;
    if($update==1){
        $sql ="SELECT ativo from tbl_config_fila where id_fila=?";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([(int) $infoUser['fila_id']]);
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
