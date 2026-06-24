<?php
include("../cnf/session.php");

//depurador($_POST);

$assuntos = implode(",", $_POST['assunto']);


$sql="INSERT INTO tbl_config_fila (nome_fila, contrato_id, assuntos_id, multichat) VALUES ('".$_POST['titulo']."', '".$_POST['contrato']."', '".$assuntos."', '".$_POST['multichat']."')";

echo $sql;


$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();



if($result==1){

        $sql="SELECT id_fila, contrato_id from tbl_config_fila where ativo=1 and nome_fila='".$_POST['titulo']."' and contrato_id=".$_POST['contrato'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $info = $stmt->fetch( PDO::FETCH_ASSOC );


        $create="CREATE TABLE `tbl_in_pos_".$info['id_fila']."_".$info['contrato_id']."` ("
            ."`id_fila_pos` int(11) NOT NULL AUTO_INCREMENT,"
            ."`data_hora` timestamp NOT NULL DEFAULT current_timestamp(),"
            ."`situacao_id` int(2) NOT NULL DEFAULT 1,"
            ."`fila_id` int(11) DEFAULT NULL,"
            ."`chat_id` int(11) DEFAULT NULL,"
            ."`tp` varchar(12) DEFAULT NULL,"
            ."PRIMARY KEY (`id_fila_pos`)"
            .") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        //echo "<br>".$create;
        $stmt = $PDO->prepare( $create );
        $result = $stmt->execute();

        $create="CREATE TABLE `tbl_in_mon_".$info['id_fila']."_".$info['contrato_id']."` ("
            ."`id_mon` int(11) NOT NULL AUTO_INCREMENT,"
            ."`data_hora` timestamp NOT NULL DEFAULT current_timestamp(),"
            ."`fila_id` int(11) DEFAULT NULL,"
            ."`chat_id` int(11) DEFAULT NULL,"
            ."`resp_mon` int(11) DEFAULT NULL,"
            ."PRIMARY KEY (`id_mon`)"
            .") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        //echo "<br>".$create;
        $stmt = $PDO->prepare( $create );
        $result = $stmt->execute();


        if($result==1){

            $idx="ALTER TABLE `tbl_in_pos_".$info['id_fila']."_".$info['contrato_id']."`"
                ."ADD INDEX `idx` (`id_fila_pos`,`data_hora`,`situacao_id`,`fila_id`,`chat_id`);";
            //echo "<br>".$idx;
            $stmt = $PDO->prepare( $idx );
            $result = $stmt->execute();

            $idx="ALTER TABLE `tbl_in_mon_".$info['id_fila']."_".$info['contrato_id']."`"
                ."ADD INDEX `idx` (`id_mon`,`data_hora`,`fila_id`,`chat_id`,`resp_mon`);";
            //echo "<br>".$idx;
            $stmt = $PDO->prepare( $idx );
            $result = $stmt->execute();


?>
<script>
    Swal.fire({
        position: 'bottom-start',
        icon: 'success',
        title: 'Gravado com sucesso!',
        showConfirmButton: false,
        timer: 1500
    });
    $("#new_registro").modal('hide');
    actionPage('cad-fil', 'cnf');



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
    }}

    ?>
