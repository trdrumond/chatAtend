<?php
include("../cnf/session.php");

if($_POST['nome_campo']!=''){

    //var_dump($_POST);
    $sql_v="SELECT desc_campo from tbl_forms_pos_input_campo where desc_campo='".$_POST['nome_campo']."' and fila_id='".$_POST['id_fila']."'";
    $stmt = $PDO->prepare( $sql_v );
    $result = $stmt->execute();
    $verifica_nome = $stmt->fetch( PDO::FETCH_ASSOC );
    if($verifica_nome['desc_campo']==''){

            $sql="SELECT count(*) as qtd from tbl_forms_pos_input_campo_cnf where fila_id=".$_POST['id_fila'];
            //echo "<br>".$sql;
            $stm = $PDO->query($sql);
            $ver = $stm->fetch(PDO::FETCH_ASSOC);
            //echo "<br>";
            //var_dump($ver);

            $sql="SELECT contrato_id from tbl_config_fila where id_fila=".$_POST['id_fila'];
            //echo "<br>".$sql;
            $stm = $PDO->query($sql);
            $ctt = $stm->fetch(PDO::FETCH_ASSOC);

            $ordem = $ver['qtd']+1;
            //echo "<br>".$ordem;

            $sql="INSERT INTO tbl_forms_pos_input_campo (desc_campo, nome_campo, input_id, fila_id) VALUES ('".$_POST['nome_campo']."', '".nomeCampoInput($_POST['nome_campo'])."', '".$_POST['id_input']."', '".$_POST['id_fila']."')";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();

            if($result==1){
                $sql="SELECT id_campo from tbl_forms_pos_input_campo where desc_campo='".$_POST['nome_campo']."' and fila_id='".$_POST['id_fila']."'";
                //echo "<br>".$sql;
                $stm = $PDO->query($sql);
                $info_campo = $stm->fetch(PDO::FETCH_ASSOC);

                $sql="SELECT tipo_input, parametro from tbl_forms_pos_input where id_input='".$_POST['id_input']."'";
                //echo "<br>".$sql;
                $stm = $PDO->query($sql);
                $info_input = $stm->fetch(PDO::FETCH_ASSOC);

                //var_dump($info_campo);
                $sql="INSERT INTO tbl_forms_pos_input_campo_cnf (campo_id, fila_id, input_id, ordem) VALUES ('".$info_campo['id_campo']."', '".$_POST['id_fila']."', '".$_POST['id_input']."', '".$ordem."')";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute();
                if($result==1){

                    $add = "ADD COLUMN `".nomeCampoInput($_POST['nome_campo'])."` ".$info_input['parametro'] .";";
                    //echo "<br>".$add;

                    $alterTable="ALTER TABLE `tbl_in_pos_".$_POST['id_fila']."_".$ctt['contrato_id']."` $add ";
                    //$alterTable .="SHOW CREATE TABLE `web_projeto`.`tbl_fila_".$_POST['id_fila']."_".$ctt['contrato_id']."`;";

                    //echo "<br>".$alterTable;
                    $stmt = $PDO->prepare( $alterTable );
                    $result = $stmt->execute();
                    if($result==1){

                        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
                        ?>
                            <script>
                                load(<?php echo $_POST['id_fila']; ?>);
                                $("#fila_id_<?php echo $_POST['id_fila']; ?>").val('');
                                $("#nome_campo_<?php echo $_POST['id_fila']; ?>").val('');

                                function load(id_filas){

                                    $("#tbl_<?php echo $_POST['id_fila']; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                                        $.post("staff/pos_tbl_config_form.php",
                                    {
                                        id_filas: id_filas
                                    },
                                    function (valor) {
                                        $("#tbl_<?php echo $_POST['id_fila']; ?>").html(valor);
                                    });

                                }
                            </script>
                        <?php
                    }
                }
            }

    } else {
        echo "<br>O campo cadastrado ja existe!";
    }

} else {
    echo "<br>Preencha os campos corretamente!";
}



?>
