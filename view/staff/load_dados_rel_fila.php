<?php
include("../cnf/session.php");



////include("../cnf/replace.php");

//depurador($_POST);

$contrato = isset($_POST['contrato']) ? trim($_POST['contrato']) : '';
$fila = isset($_POST['fila']) ? trim($_POST['fila']) : '';

if ($contrato === '') {
    echo '<p class="text-warning">Selecione um contrato para carregar a fila.</p>';
    return;
}

$sql = "SELECT a.id_fila_chat, a.protocolo, date_format(a.data_hora, '%d/%m/%Y %H:%i:%s') as hora_registro, timediff(curtime(), date_format(a.data_hora, '%H:%i:%s')) as tempo_decorrido, a.te, d.nome_fila, b.titulo_assunto"
    . ", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=ate_resp) as nome_ate"
    . ", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as nome_bko"
    . ", c.nome_situacao, date_format(a.hora_inicio, '%H:%i:%s') as hora_inicio, date_format(a.hora_fim, '%H:%i:%s') as hora_fim, a.ta, a.te"
    . " from tbl_chat_fila a, tbl_assunto b, tbl_situacao_chat c, tbl_config_fila d"
    . " where a.assunto_id=b.id_assunto"
    . " and a.status_fila=c.id_situacao"
    . " and a.fila_id=d.id_fila"
    . " and a.status_fila < 4"
    . " and a.contrato_id = :contrato";

if ($fila !== '') {
    $sql .= " and a.fila_id = :fila";
}

//echo $sql;
$stmt = $PDO->prepare($sql);
$stmt->bindValue(':contrato', $contrato, PDO::PARAM_INT);
if ($fila !== '') {
    $stmt->bindValue(':fila', $fila, PDO::PARAM_INT);
}
$result = $stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
//depurador($dados);
?>
<?php if ($fila !== '') { ?>
<button id="btn_fin_fila" class="btn btn-danger" title="Derruba Fila">Derrubar Fila</button>
<div id="feed_derruba"></div>
<?php } ?>
<br>
<table id="tbl_rel">
    <thead>
        <tr>
            <th>PROTOCOLO</th>
            <th>
                <center>SITUAÇÃO</center>
            </th>
            <th>
                <center>HORA REG.</center>
            </th>
            <th>
                <center>TEMPO ESPERA</center>
            </th>
            <th>
                <center>FILA</center>
            </th>
            <th>
                <center>ASSUNTO</center>
            </th>
            <th>
                <center>SOLICITANTE</center>
            </th>
            <th>
                <center>BACKOFFICE</center>
            </th>
            <?php if($infoUser['nivel_id']==0){ ?>
            <th></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
            for($x=0;$x<count($dados);$x++){
                $dados[$x]['te'] = ($dados[$x]['te'] == '') ? $dados[$x]['tempo_decorrido'] : $dados[$x]['te'];



                echo '<tr>';
                    echo '<td>'.$dados[$x]['protocolo'].'</td>';
                    echo '<td><center>'.$dados[$x]['nome_situacao'].'</center></td>';
                    echo '<td><center>'.$dados[$x]['hora_registro'].'</center></td>';
                    echo '<td><center>'.$dados[$x]['te'].'</center></td>';
                    echo '<td><center>'.$dados[$x]['nome_fila'].'</center></td>';
                    echo '<td><center>'.$dados[$x]['titulo_assunto'].'</center></td>';
                    echo '<td><center>'.ucwords(strtolower($dados[$x]['nome_ate'])).'</center></td>';
                    echo '<td><center>'.ucwords(strtolower($dados[$x]['nome_bko'])).'</center></td>';
                    if($infoUser['nivel_id']==0){
                        echo '<td><center><div id="fin_'.$dados[$x]['id_fila_chat'].'"><button id="btn_fin_'.$dados[$x]['id_fila_chat'].'" class="btn btn-danger" title="Finalizar atendimento"><i class="fas fa-times-circle"></i></button></div></center></td>';
                    }

                ?>
        <script>
        $('#btn_fin_<?=$dados[$x]['id_fila_chat'];?>').click(function() {
            //console.log('clicou no btn cancel');
            cancelFila(<?=$dados[$x]['id_fila_chat'];?>);
        });
        </script>
        <?php
                echo '</tr>';
            }
        ?>
    </tbody>
</table>

<script>
function cancelFila(id_fila) {
    console.log(id_fila);
    var div = '#fin_' + id_fila;
    $.post("staff/save_cancelFila.php", {
            id_fila
        },
        function(valor) {
            $(div).html(valor);
        });
}

$('#btn_fin_fila').click(function() {
    var id = <?= (int)$fila ?>;
    var div = '#feed_derruba';
    $.post("staff/derruba_fila.php", {
            id
        },
        function(valor) {
            $(div).html(valor);
            sendAtend();
            sendBko();
        });
});


$(document).ready(function() {
    $('#tbl_rel').DataTable({
        "order": [
            [2, "asc"]
        ],
        //dom: 'Bfrtip',
        "scrollY": "300px",
        //"scrollX": true,
        //"scrollCollapse": true,
        "paging": false,
        "ordering": false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
        },
        //buttons: { buttons: [ 'excel' ] }
    });
});
</script>