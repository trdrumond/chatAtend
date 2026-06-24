<?php
$file = 'cnf/conn.php';
//echo "<brTeste";
if (file_exists($file)) {include($file);} else {include("../".$file);}
    //$dados_form['id_fila'] = ($dados_form['id_fila']=='') ? $_POST['id_fila'] : $dados_form['id_fila'];
    if(!isset($dados_form['id_fila'])){
        $dados_form['id_fila'] = $_POST['id_fila'];
    }

    //depurador($dados_form);

?>

<style>
    #form-new_hr_<?php echo $dados_form['id_fila']; ?>, #form-exi_hr_<?php echo $dados_form['id_fila']; ?> {
        display: none;
    }
</style>

<script>
    $(document).ready(function () {
        $("#btn_new_hr_<?php echo $dados_form['id_fila']; ?>").click(function(){
            $('#form-new_hr_<?php echo $dados_form['id_fila']; ?>').show();
            $('#form-exi_hr_<?php echo $dados_form['id_fila']; ?>').hide();
        });
        $("#btn_exi_<?php echo $dados_form['id_fila']; ?>").click(function(){
            $('#form-new_hr_<?php echo $dados_form['id_fila']; ?>').hide();
            $('#form-exi_hr_<?php echo $dados_form['id_fila']; ?>').show();
        });
    });
</script>

                                <div class="content-1-line">
                                    <button class="btn btn-secondary bol" id="btn_new_hr_<?php echo $dados_form['id_fila']; ?>" title="Novo Campo"><i class="fas fa-pen"></i></button>
                                    <!--
                                    <button class="btn btn-secondary bol" id="btn_exi_<?php echo $dados_form['id_fila']; ?>" title="Campo Existente"><i class="fas fa-list"></i></button>
                                    -->
                                    <input id="fila_id_<?php echo $dados_form['id_fila']; ?>" class="input" type="hidden" pattern=".+" value="<?php echo $dados_form['id_fila']; ?>"  />
                                </div>

                                <div id="form-new_hr_<?php echo $dados_form['id_fila']; ?>">
                                    <div class="content-2_5-line">
                                        <div class="input-container">
                                            <input id="inicio_hr_<?php echo $dados_form['id_fila']; ?>" class="input" type="time" pattern=".+" value=""  />
                                            <label for="inicio_hr_<?php echo $dados_form['id_fila']; ?>">Início</label>
                                        </div>
                                    </div>
                                    <div class="content-2_5-line">
                                        <div class="input-container">
                                            <input id="fim_hr_<?php echo $dados_form['id_fila']; ?>" class="input" type="time" pattern=".+" value=""  />
                                            <label for="fim_hr_<?php echo $dados_form['id_fila']; ?>">Fim</label>
                                        </div>
                                    </div>
                                    <div class="content-1-line">
                                        <button class="btn btn-secondary" id="save_config_new_hr_<?php echo $dados_form['id_fila']; ?>">Salvar</button>
                                    </div>
                                </div>


                                <div id="tbl_hr_<?php echo $dados_form['id_fila']; ?>" class="content-10-line table_config_form">
                                        <?php
                                            $file = 'staff/hr_tbl_config_form.php';
                                            //fdd_tbl_config_form
                                            if (file_exists($file)) {include($file);} else {include("../".$file);}
                                        ?>
                                </div>

                                <div id="feed_cnf_hr_<?php echo $dados_form['id_fila']; ?>"></div>


                                <script>
                                    $(document).ready(function () {

                                        $("#save_config_new_hr_<?php echo $dados_form['id_fila']; ?>").click(function(){
                                            var id_fila = <?php echo $dados_form['id_fila']; ?>;
                                            var inicio_hr = $('#inicio_hr_<?php echo $dados_form['id_fila']; ?>').val();
                                            var fim_hr = $('#fim_hr_<?php echo $dados_form['id_fila']; ?>').val();
                                            //console.log("clicou no botão");
                                            //console.log(id_fila);
                                            //console.log(id_input);
                                            //console.log(nome_campo);
                                            saveCnfHr(id_fila, inicio_hr, fim_hr);}
                                        );

                                        function saveCnfHr(id_fila, inicio_hr, fim_hr){
                                            $("#feed_cnf_hr_<?php echo $dados_form['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/hr_save_form_config.php",
                                            {
                                                id_fila, inicio_hr, fim_hr
                                            },
                                            function (valor) {
                                                $("#feed_cnf_hr_<?php echo $dados_form['id_fila']; ?>").html(valor);
                                            });

                                        }

                                    });
                                </script>
