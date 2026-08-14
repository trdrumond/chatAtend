<?php
$file = 'cnf/conn.php';
//echo "<brTeste";
if (file_exists($file)) {include($file);} else {include("../".$file);}
    //$dados_form['id_fila'] = ($dados_form['id_fila']=='') ? $_POST['id_fila'] : $dados_form['id_fila'];
    if(!isset($dados_form['id_fila'])){
        $dados_form['id_fila'] = (int) ($_POST['id_fila'] ?? 0);
    }

    //depurador($dados_form);

?>

<style>
    #form-new_mon_<?php echo $dados_form['id_fila']; ?>, #form-exi_<?php echo $dados_form['id_fila']; ?> {
        display: none;
    }
    .val_range {
        font-size: 16px;
        font-style: bold;
    }
</style>

<script>
    $(document).ready(function () {
        $("#btn_new_mon_<?php echo $dados_form['id_fila']; ?>").click(function(){
            $('#form-new_mon_<?php echo $dados_form['id_fila']; ?>').show();
            $('#form-exi_<?php echo $dados_form['id_fila']; ?>').hide();
        });
        $("#btn_exi_<?php echo $dados_form['id_fila']; ?>").click(function(){
            $('#form-new_mon_<?php echo $dados_form['id_fila']; ?>').hide();
            $('#form-exi_<?php echo $dados_form['id_fila']; ?>').show();
        });
    });
</script>

                                <div class="content-1-line">
                                    <button class="btn btn-secondary bol" id="btn_new_mon_<?php echo $dados_form['id_fila']; ?>" title="Novo Campo"><i class="fas fa-pen"></i></button>
                                    <!--
                                    <button class="btn btn-secondary bol" id="btn_exi_<?php echo $dados_form['id_fila']; ?>" title="Campo Existente"><i class="fas fa-list"></i></button>
                                    -->
                                    <input id="fila_id_<?php echo $dados_form['id_fila']; ?>" class="input" type="hidden" pattern=".+" value="<?php echo $dados_form['id_fila']; ?>"  />
                                </div>

                                <div id="form-new_mon_<?php echo $dados_form['id_fila']; ?>">
                                    <div class="content-2-line">
                                        <div class="input-container">
                                            <select id="input_id_mon_<?php echo $dados_form['id_fila']; ?>">
                                                <option value="">Tipo de campo...</option>
                                                <?php
                                                    $sql="SELECT id_input, nome_input, estrutura, tipo_input from tbl_forms_mon_input order by nome_input";
                                                    $stmt = $PDO->prepare( $sql );
                                                    $result = $stmt->execute();
                                                    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($info);$y++){
                                                        echo '<option value="'.(int) $info[$y]['id_input'].'">'.stHtml($info[$y]['nome_input']).'</option>';
                                                    }
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="content-2-line">
                                        <div class="input-container">
                                            <input id="nome_campo_mon_<?php echo $dados_form['id_fila']; ?>" class="input" type="text" pattern=".+" value=""  />
                                        </div>
                                    </div>
                                    <!--
                                    <div class="content-2-line">
                                        <div class="input-container">
                                            <input id="peso_campo_mon_<?php echo $dados_form['id_fila']; ?>" class="input" type="range" min="0" max="100" step="1" pattern=".+" value="10"  />
                                        </div>
                                    </div>
                                    -->
                                    <!--
                                    <div class="content-1-line">
                                        <div class="input-container">
                                            <div id="peso_campo_mon_<?php echo $dados_form['id_fila']; ?>_value" class="val_range">10%</div>
                                        </div>
                                    </div>
                                    -->
                                    <div class="content-1-line">
                                        <button class="btn btn-secondary" id="save_config_new_mon_<?php echo $dados_form['id_fila']; ?>">Salvar</button>
                                    </div>
                                </div>



                                <div id="tbl_mon_<?php echo $dados_form['id_fila']; ?>" class="content-10-line table_config_form">
                                        <?php
                                            $file = 'staff/mon_tbl_config_form.php';
                                            //fdd_tbl_config_form
                                            if (file_exists($file)) {include($file);} else {include("../".$file);}
                                        ?>
                                </div>

                                <div id="feed_cnf_mon_<?php echo $dados_form['id_fila']; ?>"></div>


                                <script>
                                    $(document).ready(function () {

                                        /*
                                        $('#peso_campo_mon_<?php echo $dados_form['id_fila']; ?>').change(function(){
                                            var peso = $('#peso_campo_mon_<?php echo $dados_form['id_fila']; ?>').val();
                                            $('#peso_campo_mon_<?php echo $dados_form['id_fila']; ?>_value').html(peso + '%');
                                        });
                                        */

                                        $("#save_config_new_mon_<?php echo $dados_form['id_fila']; ?>").click(function(){
                                            var id_fila = <?php echo $dados_form['id_fila']; ?>;
                                            var id_input = $('#input_id_mon_<?php echo $dados_form['id_fila']; ?>').val();
                                            var nome_campo = $('#nome_campo_mon_<?php echo $dados_form['id_fila']; ?>').val();
                                            //var peso_campo = $('#peso_campo_mon_<?php echo $dados_form['id_fila']; ?>').val();
                                            saveCnf(id_fila, id_input, nome_campo);}
                                        );




                                        function saveCnf(id_fila, id_input, nome_campo){
                                            $("#feed_cnf_mon_<?php echo $dados_form['id_fila']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/mon_save_form_config.php",
                                            {
                                                id_fila, id_input, nome_campo
                                            },
                                            function (valor) {
                                                $("#feed_cnf_mon_<?php echo $dados_form['id_fila']; ?>").html(valor);
                                            });

                                        }

                                    });
                                </script>
