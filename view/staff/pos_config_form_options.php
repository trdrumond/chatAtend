<?php
include("../cnf/conn.php");
//depurador($_POST);

$sql="SELECT a.campo_id, a.fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=a.fila_id) as nome_forms, a.input_id, (SELECT nome_input from tbl_forms_pos_input where id_input=a.input_id) as nome_input, (SELECT tipo_input from tbl_forms_pos_input where id_input=a.input_id) as tipo_input, desc_campo, nome_campo, ativo, b.date_time from tbl_forms_pos_input_campo_cnf a, tbl_forms_pos_input_campo b where a.campo_id=b.id_campo and a.campo_id=".$_POST['id_campo'];
//echo "<br>".$sql."<br>";
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$info = $stmt->fetch( PDO::FETCH_ASSOC );




?>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="nome_forms" class="input" type="text" pattern=".+" value="<?=$info['nome_forms']?>" />
        <label for="nome_forms">Fila</label>
    </div>
</div>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="desc_campo" class="input" type="text" pattern=".+" value="<?=$info['desc_campo']?>" />
        <label for="desc_campo">Campo</label>
    </div>
</div>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="nome_input" class="input" type="text" pattern=".+" value="<?=$info['nome_input']?>" />
        <label for="nome_input">Tipo Campo</label>
    </div>
</div>
<div class="content-10-line">
    <?php
        if($info['tipo_input']=='checkbox'){
            $sql_opt_1="SELECT id_option, desc_option, referencia from tbl_forms_pos_input_option where referencia='opcao_chk_1' and campo_id=".$_POST['id_campo'];
            //echo "<br>".$sql_opt_1;
            $stmt = $PDO->prepare( $sql_opt_1 );
            $result = $stmt->execute();
            $option_1 = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($option_1);

            $sql_opt_2="SELECT id_option, desc_option, referencia from tbl_forms_pos_input_option where referencia='opcao_chk_2' and campo_id=".$_POST['id_campo'];
            //echo "<br>".$sql_opt_2;
            $stmt = $PDO->prepare( $sql_opt_2 );
            $result = $stmt->execute();
            $option_2 = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($option_2);

            $option_1['desc_option'] = ($option_1['desc_option']=='')?'':$option_1['desc_option'];
            $option_2['desc_option'] = ($option_2['desc_option']=='')?'':$option_2['desc_option'];
            ?>
            <div class="content-2_5-line">
                <div class="input-container">
                    <input id="opcao_chk_1" class="input" type="text" pattern=".+" value="<?php echo $option_1['desc_option']; ?>"/>
                    <label for="opcao_chk_1">Texto Opção 1</label>
                </div>
            </div>
            <div class="content-2_5-line">
                <div class="input-container">
                    <input id="opcao_chk_2" class="input" type="text" pattern=".+" value="<?php echo $option_2['desc_option']; ?>"/>
                    <label for="opcao_chk_2">Texto Opção 2</label>
                </div>
            </div>
            <div class="content-2_5-line">
                <button class="btn btn-secondary" id="btn_save_chk">Salvar</button>
                <button class="btn btn-secondary" id="btn_voltar_radio">Voltar</button>
            </div>
            <div class="content-2_5-line" id="save_chk"></div>
            <script>
                $(document).ready(function () {

                    $("#btn_save_chk").click(function(){
                        var id_fila = <?php echo $info['fila_id']; ?>;
                        var id_input = <?php echo $info['input_id']; ?>;
                        var id_campo = <?php echo $info['campo_id']; ?>;
                        var tipo_input = '<?php echo $info['tipo_input']; ?>';
                        var opcao_chk_1 = $('#opcao_chk_1').val();
                        var opcao_chk_2 = $('#opcao_chk_2').val();
                        saveChk(id_fila, id_input, id_campo, tipo_input, opcao_chk_1, opcao_chk_2);
                    });

                    function saveChk(id_fila, id_input, id_campo, tipo_input, opcao_chk_1, opcao_chk_2){
                        $("#save_chk").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/pos_save_input_option.php",
                        {
                            id_fila, id_input, id_campo, tipo_input, opcao_chk_1, opcao_chk_2
                        },
                        function (valor) {
                            $("#save_chk").html(valor);
                        });

                    }

                    $("#btn_voltar_radio").click(function(){
                        loadConfigServ(<?php echo $info['fila_id']; ?>);
                    });

                    function loadConfigServ(id_fila){
                        $("#div_config_form_<?php echo $info['fila_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/pos_config_form.php",
                        {
                            id_fila
                        },
                        function (valor) {
                            $("#div_config_form_<?php echo $info['fila_id']; ?>").html(valor);
                        });

                    }
                });
            </script>

            <?php
        }

        if($info['tipo_input']=='select'){
            ?>
            <br>
            <div class="content-2_5-line">
                <div class="input-container">
                    <input id="opcao_sel" class="input" type="text" pattern=".+" value=""/>
                    <label for="opcao_sel">Opção</label>
                </div>
            </div>
            <div class="content-2_5-line">
                <button class="btn btn-secondary" id="btn_save_sel">Salvar</button>
                <button class="btn btn-secondary" id="btn_voltar_sel">Voltar</button>
            </div>
            <div class="content-2_5-line" id="save_sel"></div>


            <script>
                $(document).ready(function () {

                    $("#btn_save_sel").click(function(){
                        var id_fila = <?php echo $info['fila_id']; ?>;
                        var id_input = <?php echo $info['input_id']; ?>;
                        var id_campo = <?php echo $info['campo_id']; ?>;
                        var tipo_input = '<?php echo $info['tipo_input']; ?>';
                        var opcao_sel = $('#opcao_sel').val();
                        saveChk(id_fila, id_input, id_campo, tipo_input, opcao_sel);}
                    );


                    function saveChk(id_fila, id_input, id_campo, tipo_input, opcao_sel){
                        $("#save_sel").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');

                        $.post("staff/pos_save_input_option.php",
                        {
                            id_fila, id_input, id_campo, tipo_input, opcao_sel
                        },
                        function (valor) {
                            $("#save_sel").html(valor);
                            $("#opcao_sel").val('');

                        });

                    }

                    $("#btn_voltar_sel").click(function(){
                        loadConfigServ(<?php echo $info['fila_id']; ?>);
                    });

                    function loadConfigServ(id_fila){
                        $("#div_config_form_<?php echo $info['fila_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/pos_config_form.php",
                        {
                            id_fila
                        },
                        function (valor) {
                            $("#div_config_form_<?php echo $info['fila_id']; ?>").html(valor);
                        });

                    }
                });
            </script>

            <div id="tbl_sel" class="content-10-line"><?php include("pos_tbl_sel.php"); ?></div>
            <?php
        }
    ?>
</div>

