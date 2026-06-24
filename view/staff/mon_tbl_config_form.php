<?php


    //echo "<br>TESTE 0";
    $file = 'cnf/conn.php';
    if (file_exists($file)) {include($file);} else {include("../".$file);}
    //echo "<br>TESTE 1";
    if(!isset($dados_form['id_fila'])){
        $dados_form['id_fila'] = $_POST['id_filas'];
    }
    //echo "<br>TESTE 2";


    $sql="SELECT a.campo_id as id_campo, a.fila_id as id_fila, b.nome_fila, a.input_id, c.nome_input, c.tipo_input, d.desc_campo, d.nome_campo, a.ativo, a.qualif, a.date_time, a.ordem, a.obg FROM tbl_forms_mon_input_campo_cnf a, tbl_config_fila b, tbl_forms_mon_input c, tbl_forms_mon_input_campo d where a.fila_id=".$dados_form['id_fila']." and a.fila_id=b.id_fila and a.input_id=c.id_input and a.campo_id=d.id_campo order by ordem asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($info);
if(count($info)>0){
    //echo "<br>TESTE 3";
//echo count($info);
?>


<div class="container">
  <div class="row">
    <div class="col-1 tbl-title"><strong>ORDEM</strong></div>
    <div class="col-4 tbl-title"><strong>CAMPO</strong></div>
    <div class="col-3 tbl-title"><center><strong>TIPO</strong></center></div>
    <!-- <div class="col-2 tbl-title"><center><strong>PESO</strong></center></div> -->
    <!-- <div class="col-3 tbl-title"><center><strong>OBRIGATÓRIO</strong></center></div> -->
    <div class="col-1 tbl-title"><center><strong>ATIVO</strong></center></div>
    <div class="col-1 tbl-title"><center><strong>QUALIF.</strong></center></div>
    <div class="col-1 tbl-title"><center><strong>CONFIG</strong></center></div>
  </div>
  <?php

            for($y=0;$y<count($info);$y++){

                if($info[$y]['ativo']==1){ $chk= 'checked'; } else{ $chk= '';}
                if($info[$y]['qualif']==1){ $chk_qualif= 'checked'; } else{ $chk_qualif= '';}
                if($info[$y]['obg']==1){ $chk_obg= 'checked'; } else{ $chk_obg= '';}
                $info[$y]['ordem'];
                $sel_ordem  = '<select name="ordem_'.$info[$y]['id_campo'].'" id="ordem_'.$info[$y]['id_campo'].'">';
                                for($op=1;$op<=count($info);$op++){
                                    if($op==$info[$y]['ordem']){$sel='selected';} else {$sel='';}
                                    $sel_ordem .= '<option value="'.$op.'" '.$sel.'>'.$op.'</option>';
                                }
                $sel_ordem .= '</select>';

                $tbl_color = ($y % 2 == 0) ? 'tbl-white':'tbl-red';
                echo "<div class='row'>";
                    echo "<div class='col-1 $tbl_color'>".$sel_ordem."</div>";
                    echo "<div class='col-4 $tbl_color'>".$info[$y]['desc_campo']."</div>";
                    echo "<div class='col-3 $tbl_color'><center>".$info[$y]['nome_input']."</center></div>";
                    /*
                    echo "<div class='col-3 $tbl_color'><center>";
                            echo "<div class='switch'>
                                    <input type='checkbox' id='status_campo_mon_obg_mon_".$info[$y]['id_campo']."' ".$chk_obg.">
                                    <label for='status_campo_mon_obg_mon_".$info[$y]['id_campo']."'></label>
                                    <div for='feed_alt_campo_mon_obg_mon_".$info[$y]['id_campo']."'></div>
                                </div>";
                    echo "</center></div>";
                    */
                    echo "<div class='col-1 $tbl_color'><center>";
                            echo "<div class='switch'>
                                    <input type='checkbox' id='status_campo_mon_".$info[$y]['id_campo']."' ".$chk.">
                                    <label for='status_campo_mon_".$info[$y]['id_campo']."'></label>
                                    <div for='feed_alt_campo_mon_".$info[$y]['id_campo']."'></div>
                                </div>";
                    echo "</center></div>";
                    echo "<div class='col-1 $tbl_color'><center>";
                    echo "<div class='switch'>
                            <input type='checkbox' id='status_qualif_mon_".$info[$y]['id_campo']."' ".$chk_qualif.">
                            <label for='status_qualif_mon_".$info[$y]['id_campo']."'></label>
                            <div for='feed_alt_qualif_mon_".$info[$y]['id_campo']."'></div>
                        </div>";
            echo "</center></div>";
                    echo "<div class='col-1 $tbl_color'><center>";
                        //if(($info[$y]['tipo_input']=='select')||($info[$y]['tipo_input']=='checkbox')){
                            echo "<button id='config_campo_form_mon_".$info[$y]['id_campo']."_".$info[$y]['id_fila']."' class='btn'><i class='fas fa-cogs'></i></button>";
                        //}
                    echo "</center></div>";


                        ?>
                        <script>

                            $("#config_campo_form_mon_<?php echo $info[$y]['id_campo']; ?>_<?php echo $info[$y]['id_fila']; ?>").click(function(){
                                //console.log('click config');
                                var id_campo = '<?php echo $info[$y]['id_campo']; ?>';
                                var fila_id = '<?php echo $info[$y]['id_fila']; ?>';

                                loadConfigServMon(id_campo, fila_id);}
                            );

                            function loadConfigServMon(id_campo, id_fila){
                                //console.log('abre func');
                                var div = '#div_config_form_mon_' + id_fila;
                                $(div).html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/mon_config_form_options.php",
                                {
                                    id_campo, id_fila
                                },
                                function (valor) {
                                    $(div).html(valor);
                                });

                            }

                            $("#status_campo_mon_<?php echo $info[$y]['id_campo']; ?>").click(function(){
                                var id = <?php echo $info[$y]['id_campo']; ?>;
                                var status = $('#status_campo_mon_<?php echo $info[$y]['id_campo']; ?>:checked').val();
                                //console.log("Campo clicado: " + id);
                                altSelMon(id, status);}
                            );

                            $("#status_qualif_mon_<?php echo $info[$y]['id_campo']; ?>").click(function(){
                                var id = <?php echo $info[$y]['id_campo']; ?>;
                                var status = $('#status_qualif_mon_<?php echo $info[$y]['id_campo']; ?>:checked').val();
                                //console.log("Campo clicado: " + id);
                                altSelMonQualif(id, status);}
                            );

                            $("#status_campo_mon_obg_mon_<?php echo $info[$y]['id_campo']; ?>").click(function(){
                                var id = <?php echo $info[$y]['id_campo']; ?>;
                                var status = $('#status_campo_mon_obg_mon_<?php echo $info[$y]['id_campo']; ?>:checked').val();
                                //console.log("Campo clicado: " + id);
                                altSelMonObg(id, status);}
                            );


                            function altSelMon(id, status){
                                $("#feed_alt_campo_mon_<?php echo $info[$y]['id_campo']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/mon_alt_campo.php",
                                {
                                    id: id, status: status
                                },
                                function (valor) {
                                    $("#feed_alt_campo_mon_<?php echo $info[$y]['id_campo']; ?>").html(valor);
                                });

                            }
                            function altSelMonQualif(id, status){
                                $("#feed_alt_qualif_mon_<?php echo $info[$y]['id_campo']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/mon_alt_campo_qualif.php",
                                {
                                    id: id, status: status
                                },
                                function (valor) {
                                    $("#feed_alt_qualif_mon_<?php echo $info[$y]['id_campo']; ?>").html(valor);
                                });

                            }

                            function altSelMonObg(id, status){
                                $("#feed_alt_campo_mon_obg_mon_<?php echo $info[$y]['id_campo']; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/mon_alt_campo_obg.php",
                                {
                                    id: id, status: status
                                },
                                function (valor) {
                                    $("#feed_alt_campo_mon_obg_mon_<?php echo $info[$y]['id_campo']; ?>").html(valor);
                                });

                            }

                            $("#ordem_<?php echo $info[$y]['id_campo']; ?>").change(function(){
                                var id_campo = <?php echo $info[$y]['id_campo']; ?>;
                                var ordem = $(this).val();
                                var total = '<?php echo count($info); ?>';
                                var fila = '<?php echo $dados_form['id_fila']; ?>';
                                //console.log(id_campo);
                                //console.log(ordem);
                                ordemCampoMon(id_campo, ordem, total, fila);
                            });


                            function ordemCampoMon(id_campo, ordem, total, fila){
                                $("#teste_campo_mon").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                                $.post("staff/mon_alt_ordem_input.php",
                                {
                                    id_campo, ordem, total, fila
                                },
                                function (valor) {
                                    //$("#div_config_form_mon_<?php echo $dados_form['id_fila']; ?>").html(valor);
                                    $("#teste_campo_mon").html(valor);
                                });

                            }
                        </script>
                        <?php
                echo "</div>";

            }


        ?>
</div>
<div id='teste_campo_mon'></div>
<?php } else { echo "<center><br>Sem dados configurados!</center>";}?>
