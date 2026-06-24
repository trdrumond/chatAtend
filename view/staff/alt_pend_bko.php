<?php
include("../cnf/session.php");

//var_dump($_POST);




$sql="UPDATE tbl_chat_fila SET bko_resp=".$_POST['bko']." where id_fila_chat=".$_POST['fila'];

//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

$sql="UPDATE tbl_chat_fila_secondary SET bko_resp=".$_POST['bko']." where id_fila_chat=".$_POST['fila'];

//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();


if($result==1){
    $sql="UPDATE tbl_chat_info SET rem_chat=".$_POST['bko']." where fila_chat_id=".$_POST['fila'];

    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info_secondary SET rem_chat=".$_POST['bko']." where fila_chat_id=".$_POST['fila'];

    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();


    if($result==1){

         $sql="UPDATE tbl_pend_info SET bko_resp=".$_POST['bko']." where chat_id=".$_POST['fila'];

        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();


        if($result==1){

            $sql="SELECT id_chat from tbl_chat_info  where fila_chat_id=".$_POST['fila'];
            //echo "<br>".$sql;

            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $dds = $stmt->fetch( PDO::FETCH_ASSOC );

            $sql="SELECT id_chat from tbl_chat_info_secondary  where fila_chat_id=".$_POST['fila'];
            //echo "<br>".$sql;

            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $dds = $stmt->fetch( PDO::FETCH_ASSOC );

            //echo "<br>Salvo";
?>
<script>
abreDetailAlt(<?=$dds['id_chat']?>);

function abreDetailAlt(id) {
    $.post("staff/load_hist_pend.php", {
            id
        },
        function(valor) {
            $('#div_detail').html(
                '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
            setTimeout(function() {
                $('#div_detail').html(valor);
            }, 500);



        });
}
</script>
<?php
            }
        }
    }


    ?>
