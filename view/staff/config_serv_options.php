<?php
include("../cnf/conn.php");

$idCampo = (int) ($_POST['id_campo'] ?? 0);

$sql="SELECT id_campo, servico_id, (SELECT nome_servico from tbl_servicos where id_servico=servico_id) as nome_servico, input_id, (SELECT nome_input from tbl_servicos_input where id_input=input_id) as nome_input, (SELECT tipo_input from tbl_servicos_input where id_input=input_id) as tipo_input, desc_campo, nome_campo, ativo, date_time from tbl_servicos_input_campo where id_campo=?";
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$idCampo]);
$info = $stmt->fetch( PDO::FETCH_ASSOC );




?>
<div class="content-2_5-line">
    <div class="input-container">
        <input id="nome_servico" class="input" type="text" pattern=".+" value="<?=$info['nome_servico']?>" />
        <label for="nome_servico">Serviço</label>
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
            $sql_opt_1="SELECT id_option, desc_option, referencia from tbl_servicos_input_option where referencia='opcao_chk_1' and campo_id=?";
            $stmt = $PDO->prepare( $sql_opt_1 );
            $result = $stmt->execute([$idCampo]);
            $option_1 = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($option_1);

            $sql_opt_2="SELECT id_option, desc_option, referencia from tbl_servicos_input_option where referencia='opcao_chk_2' and campo_id=?";
            $stmt = $PDO->prepare( $sql_opt_2 );
            $result = $stmt->execute([$idCampo]);
            $option_2 = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($option_2);

            $option_1['desc_option'] = ($option_1['desc_option']=='')?'Sim':$option_1['desc_option'];
            $option_2['desc_option'] = ($option_2['desc_option']=='')?'Não':$option_2['desc_option'];
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
                        var id_servico = <?php echo $info['servico_id']; ?>;
                        var id_input = <?php echo $info['input_id']; ?>;
                        var id_campo = <?php echo $info['id_campo']; ?>;
                        var tipo_input = '<?php echo $info['tipo_input']; ?>';
                        var opcao_chk_1 = $('#opcao_chk_1').val();
                        var opcao_chk_2 = $('#opcao_chk_2').val();
                        //console.log("clicou no botão");
                        //console.log(id_servico);
                        saveChk(id_servico, id_input, id_campo, tipo_input, opcao_chk_1, opcao_chk_2);}
                    );

                    function saveChk(id_servico, id_input, id_campo, tipo_input, opcao_chk_1, opcao_chk_2){
                        $("#save_chk").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/save_input_option.php",
                        {
                            id_servico, id_input, id_campo, tipo_input, opcao_chk_1, opcao_chk_2
                        },
                        function (valor) {
                            $("#save_chk").html(valor);
                        });

                    }

                    $("#btn_voltar_radio").click(function(){
                        loadConfigServ(<?php echo $info['servico_id']; ?>);
                    });

                    function loadConfigServ(id_servico){
                        $("#div_config_serv_<?php echo $info['servico_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/config_servicos.php",
                        {
                            id_servico
                        },
                        function (valor) {
                            $("#div_config_serv_<?php echo $info['servico_id']; ?>").html(valor);
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
                        var id_servico = <?php echo $info['servico_id']; ?>;
                        var id_input = <?php echo $info['input_id']; ?>;
                        var id_campo = <?php echo $info['id_campo']; ?>;
                        var tipo_input = '<?php echo $info['tipo_input']; ?>';
                        var opcao_sel = $('#opcao_sel').val();
                        //console.log("clicou no botão");
                        //console.log(id_servico);
                        saveChk(id_servico, id_input, id_campo, tipo_input, opcao_sel);}
                    );


                    function saveChk(id_servico, id_input, id_campo, tipo_input, opcao_sel){
                        $("#save_sel").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $("#opcao_sel").val('');
                        $.post("staff/save_input_option.php",
                        {
                            id_servico, id_input, id_campo, tipo_input, opcao_sel
                        },
                        function (valor) {
                            $("#save_sel").html(valor);

                        });

                    }

                    $("#btn_voltar_sel").click(function(){
                        loadConfigServ(<?php echo $info['servico_id']; ?>);
                    });

                    function loadConfigServ(id_servico){
                        $("#div_config_serv_<?php echo $info['servico_id']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                        $.post("staff/config_servicos.php",
                        {
                            id_servico
                        },
                        function (valor) {
                            $("#div_config_serv_<?php echo $info['servico_id']; ?>").html(valor);
                        });

                    }
                });
            </script>

            <div id="tbl_sel" class="content-10-line"><?php include("tbl_sel.php"); ?></div>
            <?php
        }
    ?>
</div>

