<?php
include("../cnf/conn.php");
//depurador($_POST);

if($_POST['tipo_input']=='checkbox'){
    /*
    if($_POST['opt_correta']=='opt_1'){
        $chk1=1;
        $chk2=0;
    } else if($_POST['opt_correta']=='opt_2'){
        $chk1=0;
        $chk2=1;
    }
    */
    $very_1="SELECT id_option from tbl_forms_mon_input_option where referencia='opcao_chk_1_mon' and campo_id=".$_POST['id_campo']." and fila_id=".$_POST['id_fila']." and input_id=".$_POST['id_input'];
    $stmt = $PDO->prepare( $very_1 );
    $result = $stmt->execute();
    $ver_1 = $stmt->fetch( PDO::FETCH_ASSOC );

    if($ver_1['id_option']!=''){
        $sql_1 = "UPDATE tbl_forms_mon_input_option SET desc_option='".$_POST['opcao_chk_1_mon']."', value_option='".nomeCampoInput($_POST['opcao_chk_1_mon'])."', valor_mon_option='".$_POST['valor_chk_1_mon']."' where referencia='opcao_chk_1_mon' and campo_id=".$_POST['id_campo']." and fila_id=".$_POST['id_fila']." and input_id=".$_POST['id_input'];
    } else {
        $sql_1="INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, referencia, valor_mon_option) VALUES ('".$_POST['id_fila']."', '".$_POST['id_input']."', '".$_POST['id_campo']."', '".$_POST['opcao_chk_1_mon']."', '".nomeCampoInput($_POST['opcao_chk_1_mon'])."', 'opcao_chk_1_mon', '".$_POST['valor_chk_1_mon']."')";
    }


    //echo $sql_1;
    $stmt = $PDO->prepare( $sql_1 );
    $result_1 = $stmt->execute();


    $very_2="SELECT id_option from tbl_forms_mon_input_option where referencia='opcao_chk_2_mon' and campo_id=".$_POST['id_campo']." and fila_id=".$_POST['id_fila']." and input_id=".$_POST['id_input'];
    $stmt = $PDO->prepare( $very_2 );
    $result = $stmt->execute();
    $ver_2 = $stmt->fetch( PDO::FETCH_ASSOC );

    if($ver_2['id_option']!=''){
        $sql_2 = "UPDATE tbl_forms_mon_input_option SET desc_option='".$_POST['opcao_chk_2_mon']."', value_option='".nomeCampoInput($_POST['opcao_chk_2_mon'])."', valor_mon_option='".$_POST['valor_chk_2_mon']."' where referencia='opcao_chk_2_mon' and campo_id=".$_POST['id_campo']." and fila_id=".$_POST['id_fila']." and input_id=".$_POST['id_input'];
    } else {
        $sql_2="INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, referencia, valor_mon_option) VALUES ('".$_POST['id_fila']."', '".$_POST['id_input']."', '".$_POST['id_campo']."', '".$_POST['opcao_chk_2_mon']."', '".nomeCampoInput($_POST['opcao_chk_2_mon'])."', 'opcao_chk_2_mon', '".$_POST['valor_chk_2_mon']."')";
    }

    //echo $sql_2;
    $stmt = $PDO->prepare( $sql_2 );
    $result_2 = $stmt->execute();
    if(($result_1==1)&&($result_2==1)){
        ?>
            <script>
                loadConfigServ(<?php echo $_POST['id_fila']; ?>);
                function loadConfigServ(id_fila){
                    $("#div_config_form_mon_<?php echo $_POST['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/mon_config_form.php",
                    {
                        id_fila
                    },
                    function (valor) {
                        $("#div_config_form_mon_<?php echo $_POST['id_fila']; ?>").html(valor);
                    });

                }
            </script>

        <?php
    }

}

if($_POST['tipo_input']=='select'){
    //depurador($_POST);
    $very_1="SELECT count(id_option) as qtd from tbl_forms_mon_input_option where referencia='select' and campo_id=".$_POST['id_campo']." and fila_id=".$_POST['id_fila']." and input_id=".$_POST['id_input'];
    $stmt = $PDO->prepare( $very_1 );
    $result = $stmt->execute();
    $qtd = $stmt->fetch( PDO::FETCH_ASSOC );
    $value_option = 'sel_'.($qtd['qtd']+1);
    $sqlInsert="INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, valor_mon_option, referencia) VALUES ('".$_POST['id_fila']."', '".$_POST['id_input']."', '".$_POST['id_campo']."', '".$_POST['opcao_sel_mon']."', '".$value_option."', '".$_POST['valor_sel_mon']."', 'select')";
    //echo "<br>".$sqlInsert;
    $stmt = $PDO->prepare( $sqlInsert );
    $result_1 = $stmt->execute();
    if(($result_1==1)){

        ?>
            <script>

                loadTable('<?=$_POST['id_fila']?>', '<?=$_POST['id_campo']?>', '<?=$_POST['id_input']?>');
                $("#btn_save_sel").val('');


                function loadTable(id_fila, id_campo, id_input){
                    $("#tbl_sel").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..."></div>');
                    //console.log('Tentou abrir pagina');
                    $.post("staff/mon_tbl_sel.php",
                    {
                        id_fila, id_campo, id_input
                    },
                    function (valor) {
                        $("#tbl_sel").html(valor);
                    });
                }

                function loadConfigServ(id_fila){
                    $("#div_config_form_mon_<?php echo $_POST['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/mon_config_form.php",
                    {
                        id_fila
                    },
                    function (valor) {
                        $("#div_config_form_mon_<?php echo $_POST['id_fila']; ?>").html(valor);
                    });

                }

            </script>

        <?php
    }

}

if($_POST['tipo_input']=='text'){
    //depurador($_POST);

    $sql = "UPDATE tbl_forms_mon_input_option SET valor_mon_option='".$_POST['valor_txt_mon']."' where referencia='text' and campo_id=".$_POST['id_campo']." and fila_id=".$_POST['id_fila']." and input_id=".$_POST['id_input'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result_1 = $stmt->execute();
    if(($result_1==1)){

        ?>
            <script>
                loadConfigServ(<?php echo $_POST['id_fila']; ?>);
                function loadConfigServ(id_fila){
                    $("#div_config_form_mon_<?php echo $_POST['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/mon_config_form.php",
                    {
                        id_fila
                    },
                    function (valor) {
                        $("#div_config_form_mon_<?php echo $_POST['id_fila']; ?>").html(valor);
                    });

                }

            </script>

        <?php
    }

}

?>
