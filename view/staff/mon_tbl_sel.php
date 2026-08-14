<?php


    //echo "<br>TESTE 0";
    $file = 'cnf/conn.php';

    if (file_exists($file)) {include($file);} else {include("../".$file);}
    //echo "<br>TESTE 1";
    //depurador($_POST);

    if(!isset($info['fila_id'])){
        $info['fila_id'] = (int) ($_POST['id_fila'] ?? 0);
    } else {
        $info['fila_id'] = (int) $info['fila_id'];
    }
    if(!isset($info['campo_id'])){
        $info['campo_id'] = (int) ($_POST['id_campo'] ?? 0);
    } else {
        $info['campo_id'] = (int) $info['campo_id'];
    }
    if(!isset($info['input_id'])){
        $info['input_id'] = (int) ($_POST['id_input'] ?? 0);
    } else {
        $info['input_id'] = (int) $info['input_id'];
    }


    $sql="SELECT id_option, fila_id, campo_id, desc_option, referencia, value_option, ativo, valor_mon_option from tbl_forms_mon_input_option where fila_id=? and campo_id=? and input_id=? order by desc_option asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute([(int) $info['fila_id'], (int) $info['campo_id'], (int) $info['input_id']]);
    $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($info);
    if(count($dados)>0){
        //echo "<br>TESTE 3";

?>


<div class="container">
  <div class="row">
    <div class="col-4 tbl-title"><strong>OPÇÃO</strong></div>
    <div class="col-2 tbl-title"><center><strong>VALOR MON.</strong></center></div>
    <div class="col-2 tbl-title"><center><strong>ATIVO</strong></center></div>
  </div>
  <?php

            for($y=0;$y<count($dados);$y++){
                $tbl_color = ($y % 2 == 0) ? 'tbl-white':'tbl-red';
                if($dados[$y]['ativo']==1){ $chk= 'checked'; } else{ $chk= '';}
                if($dados[$y]['opt_correta']==1){ $chkOpt= 'checked'; } else{ $chkOpt= '';}
                echo "<div class='row'>";
                    echo "<div class='col-4 $tbl_color'>".$dados[$y]['desc_option']."</div>";

                    echo "<div class='col-2 $tbl_color'>
                                <center>

                                    <div class='input-container'>
                                        <input id='valor_sel_mon_".$dados[$y]['id_option']."' class='input' type='number' maxlength='3' min='0' max='100' step='5' value='".$dados[$y]['valor_mon_option']."' style='text-align: center !important'/>
                                        <div for='feed_alt_val_".$dados[$y]['id_option']."'></div>
                                    </div>
                                </center>
                          </div>";

                    echo "<div class='col-2 $tbl_color'><center>";
                        echo "<div class='switch'>
                                <input type='checkbox' id='status_mon_".$dados[$y]['id_option']."' ".$chk.">
                                <label for='status_mon_".$dados[$y]['id_option']."'></label>
                                <div for='feed_alt_mon_".$dados[$y]['id_option']."'></div>
                              </div>";
                    echo "</center></div>";
                echo "</div>";
                ?>
                    <script>
                        $(document).ready(function () {
                            $("#status_mon_<?php echo $dados[$y]['id_option']; ?>").click(function(){
                                var id = <?php echo $dados[$y]['id_option']; ?>;
                                var status = $('#status_mon_<?php echo $dados[$y]['id_option']; ?>:checked').val();
                                altSel(id, status);}
                            );

                            $("#valor_sel_mon_<?php echo $dados[$y]['id_option']; ?>").change(function(){
                                var id = <?php echo $dados[$y]['id_option']; ?>;
                                var valor = $('#valor_sel_mon_<?php echo $dados[$y]['id_option']; ?>').val();
                                altSelMon(id, valor);}
                            );


                                    function altSel(id, status){
                                        $("#feed_alt_mon_<?php echo $dados[$y]['id_option']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                        $.post("staff/mon_alt_sel.php",
                                        {
                                            id: id, status: status
                                        },
                                        function (valor) {
                                            $("#feed_alt_mon_<?php echo $dados[$y]['id_option']; ?>").html(valor);
                                        });

                                    }
                                    function altSelMon(id, valor){
                                        $("#feed_alt_mon_<?php echo $dados[$y]['id_option']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                        $.post("staff/mon_alt_sel_mon.php",
                                        {
                                            id: id, valor: valor
                                        },
                                        function (valor) {
                                            $("#feed_alt_val_<?php echo $dados[$y]['id_option']; ?>").html(valor);
                                        });

                                    }

                        });
                    </script>
                <?php
            }

        ?>
</div>
<?php } else { echo "<center><br>Sem dados configurados!</center>";}?>
