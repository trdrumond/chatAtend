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


    $sql="SELECT id_option, fila_id, campo_id, desc_option, referencia, value_option, ativo from tbl_forms_pos_input_option where fila_id=? and campo_id=? and input_id=? order by desc_option asc";
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
    <div class="col-4 tbl-title"><center><strong>ATIVO</strong></center></div>
  </div>
  <?php

            for($y=0;$y<count($dados);$y++){
                $tbl_color = ($y % 2 == 0) ? 'tbl-white':'tbl-red';
                if($dados[$y]['ativo']==1){ $chk= 'checked'; } else{ $chk= '';}
                echo "<div class='row'>";
                    echo "<div class='col-4 $tbl_color'>".$dados[$y]['desc_option']."</div>";
                    echo "<div class='col-4 $tbl_color'><center>";
                        echo "<div class='switch'>
                                <input type='checkbox' id='status_".$dados[$y]['id_option']."' ".$chk.">
                                <label for='status_".$dados[$y]['id_option']."'></label>
                                <div for='feed_alt_".$dados[$y]['id_option']."'></div>
                              </div>";
                    echo "</center></div>";
                echo "</div>";
                ?>
                    <script>
                        $(document).ready(function () {
                            $("#status_<?php echo $dados[$y]['id_option']; ?>").click(function(){
                                var id = <?php echo $dados[$y]['id_option']; ?>;
                                var status = $('#status_<?php echo $dados[$y]['id_option']; ?>:checked').val();
                                altSel(id, status);}
                            );

                                    function altSel(id, status){
                                        $("#feed_alt_<?php echo $dados[$y]['id_option']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                        $.post("staff/pos_alt_sel.php",
                                        {
                                            id: id, status: status
                                        },
                                        function (valor) {
                                            $("#feed_alt_<?php echo $dados[$y]['id_option']; ?>").html(valor);
                                        });

                                    }
                        });
                    </script>
                <?php
            }

        ?>
</div>
<?php } else { echo "<center><br>Sem dados configurados!</center>";}?>
