<?php
include("../cnf/conn.php");
//depurador($_POST);

if($_POST['tipo_input']=='checkbox'){
    $very_1="SELECT id_option from tbl_servicos_input_option where referencia='opcao_chk_1' and campo_id=".$_POST['id_campo']." and servico_id=".$_POST['id_servico']." and input_id=".$_POST['id_input'];
    $stmt = $PDO->prepare( $very_1 );
    $result = $stmt->execute();
    $ver_1 = $stmt->fetch( PDO::FETCH_ASSOC );

    if($ver_1['id_option']!=''){
        $sql_1 = "UPDATE tbl_servicos_input_option SET desc_option='".$_POST['opcao_chk_1']."', value_option='".nomeCampoInput($_POST['opcao_chk_1'])."' where referencia='opcao_chk_1' and campo_id=".$_POST['id_campo']." and servico_id=".$_POST['id_servico']." and input_id=".$_POST['id_input'];
    } else {
        $sql_1="INSERT INTO tbl_servicos_input_option (servico_id, input_id, campo_id, desc_option, value_option, referencia) VALUES ('".$_POST['id_servico']."', '".$_POST['id_input']."', '".$_POST['id_campo']."', '".$_POST['opcao_chk_1']."', '".nomeCampoInput($_POST['opcao_chk_1'])."', 'opcao_chk_1')";
    }


    //echo $sql_1;
    $stmt = $PDO->prepare( $sql_1 );
    $result_1 = $stmt->execute();


    $very_2="SELECT id_option from tbl_servicos_input_option where referencia='opcao_chk_2' and campo_id=".$_POST['id_campo']." and servico_id=".$_POST['id_servico']." and input_id=".$_POST['id_input'];
    $stmt = $PDO->prepare( $very_2 );
    $result = $stmt->execute();
    $ver_2 = $stmt->fetch( PDO::FETCH_ASSOC );

    if($ver_2['id_option']!=''){
        $sql_2 = "UPDATE tbl_servicos_input_option SET desc_option='".$_POST['opcao_chk_2']."', value_option='".nomeCampoInput($_POST['opcao_chk_2'])."' where referencia='opcao_chk_2' and campo_id=".$_POST['id_campo']." and servico_id=".$_POST['id_servico']." and input_id=".$_POST['id_input'];
    } else {
        $sql_2="INSERT INTO tbl_servicos_input_option (servico_id, input_id, campo_id, desc_option, value_option, referencia) VALUES ('".$_POST['id_servico']."', '".$_POST['id_input']."', '".$_POST['id_campo']."', '".$_POST['opcao_chk_2']."', '".nomeCampoInput($_POST['opcao_chk_2'])."', 'opcao_chk_2')";
    }

    //echo $sql_2;
    $stmt = $PDO->prepare( $sql_2 );
    $result_2 = $stmt->execute();
    if(($result_1==1)&&($result_2==1)){
        ?>
            <script>
                loadConfigServ(<?php echo $_POST['id_servico']; ?>);
                function loadConfigServ(id_servico){
                    $("#div_config_serv_<?php echo $_POST['id_servico']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/config_servicos.php",
                    {
                        id_servico
                    },
                    function (valor) {
                        $("#div_config_serv_<?php echo $_POST['id_servico']; ?>").html(valor);
                    });

                }
            </script>

        <?php
    }

}

if($_POST['tipo_input']=='select'){
    //depurador($_POST);
    $very_1="SELECT count(id_option) as qtd from tbl_servicos_input_option where referencia='select' and campo_id=".$_POST['id_campo']." and servico_id=".$_POST['id_servico']." and input_id=".$_POST['id_input'];
    $stmt = $PDO->prepare( $very_1 );
    $result = $stmt->execute();
    $qtd = $stmt->fetch( PDO::FETCH_ASSOC );
    $value_option = 'sel_'.($qtd['qtd']+1);
    $sqlInsert="INSERT INTO tbl_servicos_input_option (servico_id, input_id, campo_id, desc_option, value_option, referencia) VALUES ('".$_POST['id_servico']."', '".$_POST['id_input']."', '".$_POST['id_campo']."', '".$_POST['opcao_sel']."', '".$value_option."', 'select')";
    //echo "<br>".$sqlInsert;
    $stmt = $PDO->prepare( $sqlInsert );
    $result_1 = $stmt->execute();
    if(($result_1==1)){

        ?>
            <script>

                loadTable('<?=$_POST['id_servico']?>', '<?=$_POST['id_campo']?>', '<?=$_POST['id_input']?>');
                $("#btn_save_sel").val('');


                function loadTable(id_servico, id_campo, id_input){
                    $("#tbl_sel").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                    //console.log('Tentou abrir pagina');
                    $.post("staff/tbl_sel.php",
                    {
                        id_servico, id_campo, id_input
                    },
                    function (valor) {
                        $("#tbl_sel").html(valor);
                    });
                }

                function loadConfigServ(id_servico){
                    $("#div_config_serv_<?php echo $_POST['id_servico']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/config_servicos.php",
                    {
                        id_servico
                    },
                    function (valor) {
                        $("#div_config_serv_<?php echo $_POST['id_servico']; ?>").html(valor);
                    });

                }

            </script>

        <?php
    }

}

?>
