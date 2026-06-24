<?php


    //echo "<br>TESTE 0";
    $file = 'cnf/conn.php';
    if (file_exists($file)) {include($file);} else {include("../".$file);}
    //echo "<br>TESTE 1";
    if(!isset($dados_servico['id_servico'])){
        $dados_servico['id_servico'] = $_POST['id_servicos'];
    }
    //echo "<br>TESTE 2";


    $sql="SELECT id_campo, servico_id, (SELECT nome_servico from tbl_servicos where id_servico=servico_id) as nome_servico, input_id, (SELECT nome_input from tbl_servicos_input where id_input=input_id) as nome_input, (SELECT tipo_input from tbl_servicos_input where id_input=input_id) as tipo_input, desc_campo, nome_campo, ativo, date_time from tbl_servicos_input_campo where servico_id=".$dados_servico['id_servico']." order by desc_campo asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($info);
if(count($info)>0){
    //echo "<br>TESTE 3";

?>


<div class="container">
  <div class="row">
    <div class="col-3 tbl-title"><strong>CAMPO</strong></div>
    <div class="col-3 tbl-title"><strong>TIPO</strong></div>
    <div class="col-3 tbl-title"><center><strong>ATIVO</strong></center></div>
    <div class="col-3 tbl-title"><center><strong>CONFIG</strong></center></div>
  </div>
  <?php

            for($y=0;$y<count($info);$y++){

                if($info[$y]['ativo']==1){ $chk= 'checked'; } else{ $chk= '';}

                $tbl_color = ($y % 2 == 0) ? 'tbl-white':'tbl-red';
                echo "<div class='row'>";
                    echo "<div class='col-3 $tbl_color'>".$info[$y]['desc_campo']."</div>";
                    echo "<div class='col-3 $tbl_color'>".$info[$y]['nome_input']."</div>";
                    echo "<div class='col-3 $tbl_color'><center>";
                            echo "<div class='switch'>
                                    <input type='checkbox' id='status_".$info[$y]['id_campo']."' ".$chk.">
                                    <label for='status_".$info[$y]['id_campo']."'></label>
                                    <div for='feed_alt_".$info[$y]['id_campo']."'></div>
                                </div>";
                    echo "</center></div>";
                    echo "<div class='col-3 $tbl_color'><center>";
                        if(($info[$y]['tipo_input']=='select')||($info[$y]['tipo_input']=='checkbox')){
                            echo "<i class='fas fa-cogs pointer' id='config_campo_serv_".$info[$y]['id_campo']."'></i></center>";
                        }

                    echo "</div>";
                        ?>
                        <script>
                            $("#config_campo_serv_<?php echo $info[$y]['id_campo']; ?>").click(function(){
                                var id_campo = <?php echo $info[$y]['id_campo']; ?>;
                                //console.log(id_campo);
                                loadConfigServ(id_campo);}
                            );

                            function loadConfigServ(id_campo){
                                $("#div_config_serv_<?php echo $dados_servico['id_servico']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/config_serv_options.php",
                                {
                                    id_campo
                                },
                                function (valor) {
                                    $("#div_config_serv_<?php echo $dados_servico['id_servico']; ?>").html(valor);
                                });

                            }

                            $("#status_<?php echo $info[$y]['id_campo']; ?>").click(function(){
                                var id = <?php echo $info[$y]['id_campo']; ?>;
                                var status = $('#status_<?php echo $info[$y]['id_campo']; ?>:checked').val();
                                altSel(id, status);}
                            );

                                    function altSel(id, status){
                                        $("#feed_alt_<?php echo $info[$y]['id_campo']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                        $.post("staff/alt_campo.php",
                                        {
                                            id: id, status: status
                                        },
                                        function (valor) {
                                            $("#feed_alt_<?php echo $info[$y]['id_campo']; ?>").html(valor);
                                        });

                                    }
                        </script>
                        <?php
                echo "</div>";
            }

        ?>
</div>
<?php } else { echo "<center><br>Sem dados configurados!</center>";}?>
