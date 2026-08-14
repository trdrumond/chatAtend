<?php
include("../cnf/conn.php");
//depurador($_POST);

$idCampo = (int) ($_POST['id_campo'] ?? 0);

$sql="SELECT a.campo_id, a.fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=a.fila_id) as nome_forms, a.input_id, (SELECT nome_input from tbl_forms_mon_input where id_input=a.input_id) as nome_input, (SELECT tipo_input from tbl_forms_mon_input where id_input=a.input_id) as tipo_input, desc_campo, nome_campo, ativo, b.date_time from tbl_forms_mon_input_campo_cnf a, tbl_forms_mon_input_campo b where a.campo_id=b.id_campo and a.campo_id=?";
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$idCampo]);
$info = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($info);



?>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="nome_forms_mon" class="input" type="text" pattern=".+" value="<?=$info['nome_forms']?>" />
        <label for="nome_forms_mon">Fila</label>
    </div>
</div>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="desc_campo_mon" class="input" type="text" pattern=".+" value="<?=$info['desc_campo']?>" />
        <label for="desc_campo_mon">Campo</label>
    </div>
</div>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="nome_input_mon" class="input" type="text" pattern=".+" value="<?=$info['nome_input']?>" />
        <label for="nome_input_mon">Tipo Campo</label>
    </div>
</div>

    <?php
        if($info['tipo_input']=='checkbox'){
            $sql_opt_1="SELECT id_option, desc_option, referencia, valor_mon_option from tbl_forms_mon_input_option where referencia='opcao_chk_1_mon' and campo_id=?";
            $stmt = $PDO->prepare( $sql_opt_1 );
            $result = $stmt->execute([$idCampo]);
            $option_1 = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($option_1);

            $sql_opt_2="SELECT id_option, desc_option, referencia, valor_mon_option from tbl_forms_mon_input_option where referencia='opcao_chk_2_mon' and campo_id=?";
            $stmt = $PDO->prepare( $sql_opt_2 );
            $result = $stmt->execute([$idCampo]);
            $option_2 = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($option_2);

            $option_1['desc_option'] = ($option_1['desc_option']=='')?'':$option_1['desc_option'];
            $option_2['desc_option'] = ($option_2['desc_option']=='')?'':$option_2['desc_option'];

            $chk_opt_1 = ($option_1['opt_correta']==1)?'checked':'';
            $chk_opt_2 = ($option_2['opt_correta']==1)?'checked':'';
    ?>
        <div class="content-10-line">
                <div class="content-2_5-line">
                    <div class="input-container">
                        <input id="opcao_chk_1_mon" class="input" type="text" pattern=".+" value="<?php echo $option_1['desc_option']; ?>"/>
                        <label for="opcao_chk_1_mon">Texto Opção 1</label>
                    </div>
                </div>
                <div class="content-2_5-line">
                    <div class="input-container">
                        <input id="valor_chk_1_mon" class="input" type="number" maxlength="3" min="0" max="100" step="5" value="<?php echo $option_1['valor_mon_option']; ?>"/>
                        <label for="valor_chk_1_mon">Valor Monitoria Opção 1</label>
                    </div>
                </div>
        </div>
        <div class="content-10-line">
                <div class="content-2_5-line">
                    <div class="input-container">
                        <input id="opcao_chk_2_mon" class="input" type="text" pattern=".+" value="<?php echo $option_2['desc_option']; ?>"/>
                        <label for="opcao_chk_2_mon">Texto Opção 2</label>
                    </div>
                </div>
                <div class="content-2_5-line">
                    <div class="input-container">
                        <input id="valor_chk_2_mon" class="input" type="number" maxlength="3" min="0" max="100" step="5" value="<?php echo $option_2['valor_mon_option']; ?>"/>
                        <label for="valor_chk_2_mon">Valor Mon. Opção 2</label>
                    </div>
                </div>
                <!--
                <div class="content-2_5-line">
                    <div class="input-container" style="margin: 0 !important; padding-top: -20% !important">
                        <div class="title_label">Opção Correta</div>
                        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                            <input type="radio" class="btn-check" name="opt_correta" id="opt_correta_1" value="opt_1" autocomplete="off" <?=$chk_opt_1?>>
                            <label class="btn btn-outline-danger btn-sm" for="opt_correta_1" style="margin-top: 5px !important;">Radio 1</label>

                            <input type="radio" class="btn-check" name="opt_correta" id="opt_correta_2" value="opt_2" autocomplete="off" <?=$chk_opt_2?>>
                            <label class="btn btn-outline-danger btn-sm" for="opt_correta_2" style="margin-top: 5px !important;">Radio 2</label>
                        </div>
                    </div>
                </div>
                -->

                <div class="content-2_5-line">
                    <button class="btn btn-secondary" id="btn_save_chk_mon">Salvar</button>
                    <button class="btn btn-secondary" id="btn_voltar_radio_mon">Voltar</button>
                </div>


                <div class="content-2_5-line" id="save_chk_mon"></div>
                <script>
                    $(document).ready(function () {

                        $("#btn_save_chk_mon").click(function(){
                            var id_fila = <?php echo $info['fila_id']; ?>;
                            var id_input = <?php echo $info['input_id']; ?>;
                            var id_campo = <?php echo $info['campo_id']; ?>;
                            var tipo_input = '<?php echo $info['tipo_input']; ?>';
                            var opcao_chk_1_mon = $('#opcao_chk_1_mon').val();
                            var opcao_chk_2_mon = $('#opcao_chk_2_mon').val();
                            var valor_chk_1_mon = $('#valor_chk_1_mon').val();
                            var valor_chk_2_mon = $('#valor_chk_2_mon').val();
                            //var opt_correta = $('input[name="opt_correta"]:checked').val();
                            //console.log(opt_correta);
                            saveChk(id_fila, id_input, id_campo, tipo_input, opcao_chk_1_mon, valor_chk_1_mon, opcao_chk_2_mon, valor_chk_2_mon);
                        });

                        function saveChk(id_fila, id_input, id_campo, tipo_input, opcao_chk_1_mon, valor_chk_1_mon, opcao_chk_2_mon, valor_chk_2_mon){
                            $("#save_chk_mon").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                            $.post("staff/mon_save_input_option.php",
                            {
                                id_fila, id_input, id_campo, tipo_input, opcao_chk_1_mon, valor_chk_1_mon, opcao_chk_2_mon, valor_chk_2_mon
                            },
                            function (valor) {
                                $("#save_chk_mon").html(valor);
                            });

                        }

                        $("#btn_voltar_radio_mon").click(function(){
                            loadConfigServ(<?php echo $info['fila_id']; ?>);
                        });

                        function loadConfigServ(id_fila){
                            $("#div_config_form_mon_<?php echo $info['fila_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                            $.post("staff/mon_config_form.php",
                            {
                                id_fila
                            },
                            function (valor) {
                                $("#div_config_form_mon_<?php echo $info['fila_id']; ?>").html(valor);
                            });

                        }
                    });
                </script>
        </div>
            <?php
        }

        if($info['tipo_input']=='select'){
            ?>
            <br>
        <div class="content-10-line">
            <div class="content-2_5-line">
                <div class="input-container">
                    <input id="opcao_sel_mon" class="input" type="text" pattern=".+" value=""/>
                    <label for="opcao_sel_mon">Opção</label>
                </div>
            </div>
            <div class="content-2_5-line">
                <div class="input-container">
                    <input id="valor_sel_mon" class="input" type="number" maxlength="3" min="0" max="100" step="5" value="0"/>
                    <label for="valor_sel_mon">Valor Monitoria Opção</label>
                </div>
            </div>
            <div class="content-2_5-line">
                <button class="btn btn-secondary" id="btn_save_sel_mon">Salvar</button>
                <button class="btn btn-secondary" id="btn_voltar_sel_mon">Voltar</button>
            </div>
            <div class="content-2_5-line" id="save_sel_mon"></div>


            <script>
                $(document).ready(function () {

                    $("#btn_save_sel_mon").click(function(){
                        var id_fila = <?php echo $info['fila_id']; ?>;
                        var id_input = <?php echo $info['input_id']; ?>;
                        var id_campo = <?php echo $info['campo_id']; ?>;
                        var tipo_input = '<?php echo $info['tipo_input']; ?>';
                        var opcao_sel_mon = $('#opcao_sel_mon').val();
                        var valor_sel_mon = $('#valor_sel_mon').val();
                        saveChk(id_fila, id_input, id_campo, tipo_input, opcao_sel_mon, valor_sel_mon);}
                    );


                    function saveChk(id_fila, id_input, id_campo, tipo_input, opcao_sel_mon, valor_sel_mon){
                        $("#save_sel_mon").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');

                        $.post("staff/mon_save_input_option.php",
                        {
                            id_fila, id_input, id_campo, tipo_input, opcao_sel_mon, valor_sel_mon
                        },
                        function (valor) {
                            $("#save_sel_mon").html(valor);
                            $("#opcao_sel_mon").val('');
                            $("#valor_sel_mon").val('0');

                        });

                    }

                    $("#btn_voltar_sel_mon").click(function(){
                        loadConfigServ(<?php echo $info['fila_id']; ?>);
                    });

                    function loadConfigServ(id_fila){
                        $("#div_config_form_mon_<?php echo $info['fila_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/mon_config_form.php",
                        {
                            id_fila
                        },
                        function (valor) {
                            $("#div_config_form_mon_<?php echo $info['fila_id']; ?>").html(valor);
                        });

                    }
                });
            </script>

            <div id="tbl_sel" class="content-10-line"><?php include("mon_tbl_sel.php"); ?></div>
        </div>

    <?php } ?>

    <?php if($info['tipo_input']=='text'){
            $sql_txt="SELECT id_option, referencia, valor_mon_option from tbl_forms_mon_input_option where campo_id=?";
            $stmt = $PDO->prepare( $sql_txt );
            $result = $stmt->execute([$idCampo]);
            $opt_txt = $stmt->fetch( PDO::FETCH_ASSOC );

            if($opt_txt['id_option']==''){
                $idFila = (int) ($_POST['id_fila'] ?? 0);
                $inputId = (int) ($info['input_id'] ?? 0);
                $sqlInsert="INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, valor_mon_option, referencia) VALUES (?, ?, ?, '', '', '0', 'text')";
                $stmt = $PDO->prepare( $sqlInsert );
                $result = $stmt->execute([$idFila, $inputId, $idCampo]);

                $opt_txt['valor_mon_option']=0;
            }

            ?>
            <br>
        <div class="content-10-line">
            <div class="content-2_5-line">
                    <div class="input-container">
                        <input id="valor_txt_mon" class="input" type="number" maxlength="3" min="0" max="100" step="5" value="<?php echo $opt_txt['valor_mon_option']; ?>"/>
                        <label for="valor_chk_1_mon">Valor Monitoria</label>
                    </div>
                </div>
            <div class="content-2_5-line">
                <button class="btn btn-secondary" id="btn_save_txt_mon">Salvar</button>
                <button class="btn btn-secondary" id="btn_voltar_txt_mon">Voltar</button>
            </div>
            <div class="content-2_5-line" id="save_sel_mon"></div>


            <script>
                $(document).ready(function () {

                    $("#btn_save_txt_mon").click(function(){
                        var id_fila = <?php echo $info['fila_id']; ?>;
                        var id_input = <?php echo $info['input_id']; ?>;
                        var id_campo = <?php echo $info['campo_id']; ?>;
                        var tipo_input = '<?php echo $info['tipo_input']; ?>';
                        var valor_txt_mon = $('#valor_txt_mon').val();
                        saveChk(id_fila, id_input, id_campo, tipo_input, valor_txt_mon);}
                    );


                    function saveChk(id_fila, id_input, id_campo, tipo_input, valor_txt_mon){
                        $("#save_sel_mon").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');

                        $.post("staff/mon_save_input_option.php",
                        {
                            id_fila, id_input, id_campo, tipo_input, valor_txt_mon
                        },
                        function (valor) {
                            $("#save_sel_mon").html(valor);
                            $("#opcao_sel_mon").val('');
                            $("#valor_sel_mon").val('0');

                        });

                    }

                    $("#btn_voltar_txt_mon").click(function(){
                        loadConfigServ(<?php echo $info['fila_id']; ?>);
                    });

                    function loadConfigServ(id_fila){
                        $("#div_config_form_mon_<?php echo $info['fila_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/mon_config_form.php",
                        {
                            id_fila
                        },
                        function (valor) {
                            $("#div_config_form_mon_<?php echo $info['fila_id']; ?>").html(valor);
                        });

                    }
                });
            </script>

        </div>

    <?php } ?>

