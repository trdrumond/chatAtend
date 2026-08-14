<?php
$file = 'cnf/conn.php';
if (file_exists($file)) {include($file);} else {include("../".$file);}
    //$dados_servico['id_servico'] = ($dados_servico['id_servico']=='') ? $_POST['id_servico'] : $dados_servico['id_servico'];
    if(!isset($dados_servico['id_servico'])){
        $dados_servico['id_servico'] = (int) ($_POST['id_servico'] ?? 0);
    }
    $servicoId = (int) ($dados_servico['id_servico'] ?? 0);
    $dados_servico['id_servico'] = $servicoId;
    //depurador($dados_servico);

?>
                                <div class="content-2_5-line">
                                    <div class="input-container">
                                        <input id="servico_id_<?php echo $servicoId; ?>" class="input" type="hidden" pattern=".+" value="<?php echo $servicoId; ?>"  />
                                        <select id="input_id_<?php echo $servicoId; ?>">
                                            <option value="">Tipo de campo...</option>
                                            <?php
                                                $sql="SELECT id_input, nome_input, estrutura, tipo_input from tbl_servicos_input order by nome_input";
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
                                <div class="content-2_5-line">
                                    <div class="input-container">
                                        <input id="nome_campo_<?php echo $servicoId; ?>" class="input" type="text" pattern=".+" value=""  />
                                    </div>
                                </div>
                                <div class="content-2_5-line">
                                    <button class="btn btn-secondary" id="save_config_<?php echo $servicoId; ?>">Salvar</button>
                                </div>
                                <div id="tbl_<?php echo $servicoId; ?>" class="content-10-line table_config_servicos">
                                        <?php
                                            //depurador($dados_servico);
                                            //include("staff/tbl_config_servicos.php");
                                            $file = 'staff/tbl_config_servicos.php';
                                            if (file_exists($file)) {include($file);} else {include("../".$file);}
                                        ?>
                                </div>

                                <div id="feed_cnf_<?php echo $servicoId; ?>"></div>


                                <script>
                                    $(document).ready(function () {
                                        $("#status_<?php echo $servicoId; ?>").click(function(){
                                            var id = <?php echo $servicoId; ?>;
                                            var status = $('#status_<?php echo $servicoId; ?>:checked').val();
                                            var agencias = $('#age_alt_<?php echo $servicoId; ?>').val();
                                            //console.log("clicou no botão");
                                            //console.log(id);
                                            //console.log(status);
                                            altCtt(id, status, agencias);}
                                        );

                                        $("#save_config_<?php echo $servicoId; ?>").click(function(){
                                            var id_servico = <?php echo $servicoId; ?>;
                                            var id_input = $('#input_id_<?php echo $servicoId; ?>').val();
                                            var nome_campo = $('#nome_campo_<?php echo $servicoId; ?>').val();
                                            //console.log("clicou no botão");
                                            //console.log(id_servico);
                                            //console.log(id_input);
                                            //console.log(nome_campo);
                                            saveCnf(id_servico, id_input, nome_campo);}
                                        );



                                        function altCtt(id, status){
                                            $("#feed_alt_<?php echo $servicoId; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/alt_ser.php",
                                            {
                                                id: id, status: status
                                            },
                                            function (valor) {
                                                $("#feed_alt_<?php echo $servicoId; ?>").html(valor);
                                            });

                                        }

                                        function saveCnf(id_servico, id_input, nome_campo){
                                            $("#feed_cnf_<?php echo $servicoId; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/save_ser_config.php",
                                            {
                                                id_servico, id_input, nome_campo
                                            },
                                            function (valor) {
                                                $("#feed_cnf_<?php echo $servicoId; ?>").html(valor);
                                            });

                                        }
                                    });
                                </script>
