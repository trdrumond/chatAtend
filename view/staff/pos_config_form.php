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
    #form-new_<?php echo $dados_form['id_fila']; ?>, #form-exi_<?php echo $dados_form['id_fila']; ?> {
        display: none;
    }
</style>

<script>
    $(document).ready(function () {
        $("#btn_new_<?php echo $dados_form['id_fila']; ?>").click(function(){
            $('#form-new_<?php echo $dados_form['id_fila']; ?>').show();
            $('#form-exi_<?php echo $dados_form['id_fila']; ?>').hide();
        });
        $("#btn_exi_<?php echo $dados_form['id_fila']; ?>").click(function(){
            $('#form-new_<?php echo $dados_form['id_fila']; ?>').hide();
            $('#form-exi_<?php echo $dados_form['id_fila']; ?>').show();
        });
    });
</script>

                                <div class="content-1-line">
                                    <button class="btn btn-secondary bol" id="btn_new_<?php echo $dados_form['id_fila']; ?>" title="Novo Campo"><i class="fas fa-pen"></i></button>
                                    <!--
                                    <button class="btn btn-secondary bol" id="btn_exi_<?php echo $dados_form['id_fila']; ?>" title="Campo Existente"><i class="fas fa-list"></i></button>
                                    -->
                                    <input id="fila_id_<?php echo $dados_form['id_fila']; ?>" class="input" type="hidden" pattern=".+" value="<?php echo $dados_form['id_fila']; ?>"  />
                                </div>

                                <div id="form-new_<?php echo $dados_form['id_fila']; ?>">
                                    <div class="content-2_5-line">
                                        <div class="input-container">
                                            <select id="input_id_<?php echo $dados_form['id_fila']; ?>">
                                                <option value="">Tipo de campo...</option>
                                                <?php
                                                    $sql="SELECT id_input, nome_input, estrutura, tipo_input from tbl_forms_pos_input order by nome_input";
                                                    $stmt = $PDO->prepare( $sql );
                                                    $result = $stmt->execute();
                                                    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($info);$y++){
                                                        echo '<option value="'.$info[$y]['id_input'].'">'.$info[$y]['nome_input'].'</option>';
                                                    }
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="content-2_5-line">
                                        <div class="input-container">
                                            <input id="nome_campo_<?php echo $dados_form['id_fila']; ?>" class="input" type="text" pattern=".+" value=""  />
                                        </div>
                                    </div>
                                    <div class="content-1-line">
                                        <button class="btn btn-secondary" id="save_config_new_<?php echo $dados_form['id_fila']; ?>">Salvar</button>
                                    </div>
                                </div>

                                <div id="form-exi_<?php echo $dados_form['id_fila']; ?>">
                                    <div class="content-2_5-line">
                                        <div class="input-container">
                                            <select id="input_campos_exi_<?php echo $dados_form['id_fila']; ?>">
                                                <option value="">Selecione o campo...</option>
                                                <?php
                                                    $sql="SELECT id_campo, desc_campo from tbl_forms_pos_input_campo order by desc_campo";
                                                    $stmt = $PDO->prepare( $sql );
                                                    $result = $stmt->execute();
                                                    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($info);$y++){
                                                        echo '<option value="'.$info[$y]['id_campo'].'">'.$info[$y]['desc_campo'].'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="content-1-line">
                                        <button class="btn btn-secondary" id="save_config_exi_<?php echo $dados_form['id_fila']; ?>">Salvar</button>
                                    </div>
                                </div>


                                <div id="tbl_<?php echo $dados_form['id_fila']; ?>" class="content-10-line table_config_form">
                                        <?php
                                            $file = 'staff/pos_tbl_config_form.php';
                                            //fdd_tbl_config_form
                                            if (file_exists($file)) {include($file);} else {include("../".$file);}
                                        ?>
                                </div>

                                <div id="feed_cnf_<?php echo $dados_form['id_fila']; ?>"></div>


                                <script>
                                    $(document).ready(function () {

                                        $("#save_config_new_<?php echo $dados_form['id_fila']; ?>").click(function(){
                                            var id_fila = <?php echo $dados_form['id_fila']; ?>;
                                            var id_input = $('#input_id_<?php echo $dados_form['id_fila']; ?>').val();
                                            var nome_campo = $('#nome_campo_<?php echo $dados_form['id_fila']; ?>').val();
                                            //console.log("clicou no botão");
                                            //console.log(id_fila);
                                            //console.log(id_input);
                                            //console.log(nome_campo);
                                            saveCnf(id_fila, id_input, nome_campo);}
                                        );

                                        $("#save_config_exi_<?php echo $dados_form['id_fila']; ?>").click(function(){
                                            var id_fila = <?php echo $dados_form['id_fila']; ?>;
                                            var id_campo = $('#input_campos_exi_<?php echo $dados_form['id_fila']; ?>').val();
                                            saveCnfExi(id_fila, id_campo);}
                                        );



                                        function saveCnf(id_fila, id_input, nome_campo){
                                            $("#feed_cnf_<?php echo $dados_form['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/pos_save_form_config.php",
                                            {
                                                id_fila, id_input, nome_campo
                                            },
                                            function (valor) {
                                                $("#feed_cnf_<?php echo $dados_form['id_fila']; ?>").html(valor);
                                            });

                                        }

                                        function saveCnfExi(id_fila, id_campo){
                                            $("#feed_cnf_<?php echo $dados_form['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/pos_save_form_config_exi.php",
                                            {
                                                id_fila, id_campo
                                            },
                                            function (valor) {
                                                $("#feed_cnf_<?php echo $dados_form['id_fila']; ?>").html(valor);
                                            });

                                        }
                                    });
                                </script>
