<?php
$file = 'cnf/conn.php';
if (file_exists($file)) {include($file);} else {include("../".$file);}
    //$dados_servico['id_servico'] = ($dados_servico['id_servico']=='') ? $_POST['id_servico'] : $dados_servico['id_servico'];
    if(!isset($dados_servico['id_servico'])){
        $dados_servico['id_servico'] = $_POST['id_servico'];
    }
    //depurador($dados_servico);

?>
                                <div class="content-2_5-line">
                                    <div class="input-container">
                                        <input id="servico_id_<?php echo $dados_servico['id_servico']; ?>" class="input" type="hidden" pattern=".+" value="<?php echo $dados_servico['id_servico']; ?>"  />
                                        <select id="input_id_<?php echo $dados_servico['id_servico']; ?>">
                                            <option value="">Tipo de campo...</option>
                                            <?php
                                                $sql="SELECT id_input, nome_input, estrutura, tipo_input from tbl_servicos_input order by nome_input";
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
                                        <input id="nome_campo_<?php echo $dados_servico['id_servico']; ?>" class="input" type="text" pattern=".+" value=""  />
                                    </div>
                                </div>
                                <div class="content-2_5-line">
                                    <button class="btn btn-secondary" id="save_config_<?php echo $dados_servico['id_servico']; ?>">Salvar</button>
                                </div>
                                <div id="tbl_<?php echo $dados_servico['id_servico']; ?>" class="content-10-line table_config_servicos">
                                        <?php
                                            //depurador($dados_servico);
                                            //include("staff/tbl_config_servicos.php");
                                            $file = 'staff/tbl_config_servicos.php';
                                            if (file_exists($file)) {include($file);} else {include("../".$file);}
                                        ?>
                                </div>

                                <div id="feed_cnf_<?php echo $dados_servico['id_servico']; ?>"></div>


                                <script>
                                    $(document).ready(function () {
                                        $("#status_<?php echo $dados_servico['id_servico']; ?>").click(function(){
                                            var id = <?php echo $dados_servico['id_servico']; ?>;
                                            var status = $('#status_<?php echo $dados_servico['id_servico']; ?>:checked').val();
                                            var agencias = $('#age_alt_<?php echo $dados_servico['id_servico']; ?>').val();
                                            //console.log("clicou no botão");
                                            //console.log(id);
                                            //console.log(status);
                                            altCtt(id, status, agencias);}
                                        );

                                        $("#save_config_<?php echo $dados_servico['id_servico']; ?>").click(function(){
                                            var id_servico = <?php echo $dados_servico['id_servico']; ?>;
                                            var id_input = $('#input_id_<?php echo $dados_servico['id_servico']; ?>').val();
                                            var nome_campo = $('#nome_campo_<?php echo $dados_servico['id_servico']; ?>').val();
                                            //console.log("clicou no botão");
                                            //console.log(id_servico);
                                            //console.log(id_input);
                                            //console.log(nome_campo);
                                            saveCnf(id_servico, id_input, nome_campo);}
                                        );



                                        function altCtt(id, status){
                                            $("#feed_alt_<?php echo $dados_servico['id_servico']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/alt_ser.php",
                                            {
                                                id: id, status: status
                                            },
                                            function (valor) {
                                                $("#feed_alt_<?php echo $dados_servico['id_servico']; ?>").html(valor);
                                            });

                                        }

                                        function saveCnf(id_servico, id_input, nome_campo){
                                            $("#feed_cnf_<?php echo $dados_servico['id_servico']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                            $.post("staff/save_ser_config.php",
                                            {
                                                id_servico, id_input, nome_campo
                                            },
                                            function (valor) {
                                                $("#feed_cnf_<?php echo $dados_servico['id_servico']; ?>").html(valor);
                                            });

                                        }
                                    });
                                </script>
