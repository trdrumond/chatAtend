<?php


    //echo "<br>TESTE 0";
    $file = 'cnf/conn.php';
    if (file_exists($file)) {include($file);} else {include("../".$file);}
    //echo "<br>TESTE 1";
    if(!isset($dados_form['id_fila'])){
        $dados_form['id_fila'] = (int) ($_POST['id_filas'] ?? 0);
    } else {
        $dados_form['id_fila'] = (int) $dados_form['id_fila'];
    }
    //echo "<br>TESTE 2";



    $sql="SELECT id_hr, fila_id, inicio_hr, fim_hr, ativo from tbl_fila_horario where fila_id=? order by id_hr asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute([(int) $dados_form['id_fila']]);
    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($info);
if(count($info)>0){
    //echo "<br>TESTE 3";
//echo count($info);
?>


<div class="container">
    <div class='row'>
        <div class="col-10">
            <p>* Para que a fila funcione em tempo integral basta desativar todos os horários configurados ou excluí-los;<br>
            ** A fila funcionará apenas dentro do período configurado e ativo.</p>
        </div>
    </div>
  <div class="row">
    <div class="col-2 tbl-title"><strong>INICIO</strong></div>
    <div class="col-2 tbl-title"><strong>FIM</strong></div>
    <div class="col-2 tbl-title"><center><strong>ATIVO</strong></center></div>
    <div class="col-2 tbl-title"><center><strong>APAGAR</strong></center></div>
  </div>
  <?php

            for($y=0;$y<count($info);$y++){

                if($info[$y]['ativo']==1){ $chk= 'checked'; } else{ $chk= '';}

                $tbl_color = ($y % 2 == 0) ? 'tbl-white':'tbl-red';
                echo "<div class='row'>";
                    echo "<div class='col-2 $tbl_color'>".$info[$y]['inicio_hr']."</div>";
                    echo "<div class='col-2 $tbl_color'>".$info[$y]['fim_hr']."</div>";
                    echo "<div class='col-2 $tbl_color'><center>";
                            echo "<div class='switch'>
                                    <input type='checkbox' id='status_hr_".$info[$y]['id_hr']."' ".$chk.">
                                    <label for='status_hr_".$info[$y]['id_hr']."'></label>
                                    <div id='feed_alt_hr_".$info[$y]['id_hr']."'></div>
                                </div>";
                    echo "</center></div>";
                    echo "<div class='col-2 $tbl_color'><center><span id='del_hr_".$info[$y]['id_hr']."' style='cursor: pointer;'><i class='fas fa-trash-alt'></i></span><div id='feed_del_hr_".$info[$y]['id_hr']."'></div></center></div>";


                        ?>
                        <script>


                            $("#status_hr_<?php echo $info[$y]['id_hr']; ?>").click(function(){
                                var id = <?php echo $info[$y]['id_hr']; ?>;
                                var status = $('#status_hr_<?php echo $info[$y]['id_hr']; ?>:checked').val();
                                altSelHr(id, status);}
                            );

                            $("#del_hr_<?php echo $info[$y]['id_hr']; ?>").click(function(){
                                var id = <?php echo $info[$y]['id_hr']; ?>;
                                var id_fila = <?php echo $dados_form['id_fila']; ?>;
                                delHr(id, id_fila);
                            });


                            function altSelHr(id, status){
                                $("#feed_alt_hr_<?php echo $info[$y]['id_hr']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/hr_alt_campo.php",
                                {
                                    id, status
                                },
                                function (valor) {
                                    $("#feed_alt_hr_<?php echo $info[$y]['id_hr']; ?>").html(valor);
                                });

                            }

                            function delHr(id, id_fila){
                                $("#feed_del_hr_<?php echo $info[$y]['id_hr']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/hr_del_campo.php",
                                {
                                    id, id_fila
                                },
                                function (valor) {
                                    $("#feed_del_hr_<?php echo $info[$y]['id_hr']; ?>").html(valor);
                                });
                            }

                        </script>
                        <?php
                echo "</div>";

            }


    ?>

</div>
<div id='teste_campo'></div>
<?php } else { echo "<center><br>Sem horários configurados para esta fila!</center>";}?>
