<?php
include("../cnf/conn.php");


//depurador($_POST);

$filaId = (int) ($_POST['fila'] ?? 0);

$sql="SELECT a.id_fila_chat, a.protocolo, date_format(a.data_hora, '%H:%i:%s') as hora_registro, timediff(curtime(), date_format(a.data_hora, '%H:%i:%s')) as tempo_decorrido, d.nome_fila, b.titulo_assunto"
    .", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=ate_resp) as nome_ate"
    .", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as nome_bko"
    .", c.nome_situacao, date_format(a.hora_inicio, '%H:%i:%s') as hora_inicio, date_format(a.hora_fim, '%H:%i:%s') as hora_fim, a.ta, a.te"
    ." from tbl_chat_fila a, tbl_assunto b, tbl_situacao_chat c, tbl_config_fila d"
    ." where a.assunto_id=b.id_assunto"
    ." and a.status_fila=c.id_situacao"
    ." and a.fila_id=d.id_fila"
    ." and a.status_fila = 1";
    $sqlParams = [];
    if($filaId != 0){
        $sql .=" and a.fila_id=?";
        $sqlParams[] = $filaId;
    }

    $sql .=" order by a.data_hora asc";



//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute($sqlParams);
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($dados);
if(count($dados)>0){
?>

<table id="tbl_rel" class="table table-hover">
    <thead>
        <tr>
            <th>PROTOCOLO</th>
            <!-- <th><center>SITUAÇÃO</center></th> -->
            <!--<th><center>FILA</center></th>-->
            <th><center>TEMPO DE ESPERA</center></th>
            <!-- <th><center>FILA</center></th> -->
            <!-- <th><center>ASSUNTO</center></th> -->
            <!-- <th><center>SOLICITANTE</center></th> -->
        </tr>
    </thead>
    <tbody>
        <?php
            for($x=0;$x<count($dados);$x++){
                $idFilaChat = (int) $dados[$x]['id_fila_chat'];
                $horaRegJs = json_encode((string) $dados[$x]['hora_registro'], JSON_UNESCAPED_UNICODE);
                ?>
                <script>
                    var tempo_fila_<?= $filaId ?>_<?= $idFilaChat ?>;
                    clearInterval(tempo_fila_<?= $filaId ?>_<?= $idFilaChat ?>);
                </script>
                <?php
                echo '<tr>';
                    echo '<td>'.stHtml($dados[$x]['protocolo']).'</td>';
                    echo '<td><center><div id="tempo_'.$filaId.'_'.$idFilaChat.'">'.stHtml($dados[$x]['tempo_decorrido']).'</div></center></td>';
                echo '</tr>';
                ?>
                <script>
                    clearInterval(tempo_fila_<?= $filaId ?>_<?= $idFilaChat ?>);

                    tempo_fila_<?= $filaId ?>_<?= $idFilaChat ?> = setInterval(function(){ timeDiff_<?= $filaId ?>_<?= $idFilaChat ?>(<?= $horaRegJs ?>) }, 30000);

                    function timeDiff_<?= $filaId ?>_<?= $idFilaChat ?>(horario){
                        $.post("staff/tempo_atend_fila.php",  { horario  },  function (valor) { $("#tempo_<?= $filaId ?>_<?= $idFilaChat ?>").html(valor);  });
                    }
                </script>

                <?php
            }
        ?>
    </tbody>
</table>
<?php } else { echo '<div class="sem_fila"><center><br><i class="fas fa-exclamation-circle fa-5x sem_fila"></i><br><h4 class="sem_fila">Fila vazia!</h4></center></div>';} ?>
