<?php
include("../cnf/session.php");

//var_dump($_POST);
//echo "<br>";

$sql_v="SELECT campo_id from tbl_forms_dados_input_campo_cnf where campo_id='".$_POST['id_campo']."' and form_id='".$_POST['id_form']."'";
$stmt = $PDO->prepare( $sql_v );
$result = $stmt->execute();
$ver_campo = $stmt->fetch( PDO::FETCH_ASSOC );
//var_dump($ver_campo);
if($ver_campo['campo_id']==''){

            $sql="SELECT count(*) as qtd from tbl_forms_dados_input_campo_cnf where form_id=".$_POST['id_form'];
            //echo "<br>".$sql;
            $stm = $PDO->query($sql);
            $ver = $stm->fetch(PDO::FETCH_ASSOC);
            $ordem = $ver['qtd']+1;

            $sql="SELECT input_id from tbl_forms_dados_input_campo where id_campo=".$_POST['id_campo'];
            //echo "<br>".$sql;
            $stm = $PDO->query($sql);
            $inp = $stm->fetch(PDO::FETCH_ASSOC);

                $sql="INSERT INTO tbl_forms_dados_input_campo_cnf (campo_id, form_id, input_id, ordem) VALUES ('".$_POST['id_campo']."', '".$_POST['id_form']."', '".$inp['input_id']."', '".$ordem."')";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute();
                //echo "<br>Gravação de info config: ".$result;
                if($result==1){
                    echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
                    ?>
                        <script>
                            load(<?php echo $_POST['id_form']; ?>);
                            $("#form_id_<?php echo $_POST['id_form']; ?>").val('');
                            $("#nome_campo_<?php echo $_POST['id_form']; ?>").val('');

                            function load(id_forms){

                                $("#tbl_<?php echo $_POST['id_form']; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..."></div>');
                                    $.post("staff/fdd_tbl_config_form.php",
                                {
                                    id_forms: id_forms
                                },
                                function (valor) {
                                    $("#tbl_<?php echo $_POST['id_form']; ?>").html(valor);
                                });

                            }
                        </script>
                    <?php
                }
} else {
    echo "<br>Campo ja cadastrado para este formulário!";
}


?>
