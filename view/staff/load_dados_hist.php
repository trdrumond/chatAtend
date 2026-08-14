<style>
#info_hist {
    float: left;
}

#info_detail {
    display: none;
    width: 50%;
    height: 400px;
    margin: 10px;
    float: left;
}

.info_detail {
    width: 48%;
}

.info_hist {
    width: 99%;
}

.tr_sel {
    color: #B7202F;
}
</style>
<?php
    include("../cnf/session.php");
    include("../cnf/replace.php");
    include('../cnf/rotina_pendencia.php');


    //depurador($_POST);
    //depurador($_SESSION["dados"]);

    $contrato = (int) ($_POST['contrato'] ?? 0);
    $de = preg_replace('/[^0-9\-]/', '', (string) ($_POST['de'] ?? ''));
    $ate = preg_replace('/[^0-9\-]/', '', (string) ($_POST['ate'] ?? ''));
    $fila = (int) ($_POST['fila'] ?? 0);
    if ($de === '' || $ate === '') {
        $de = date('Y-m-d');
        $ate = date('Y-m-d');
    }
    if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
        echo '<p class="text-danger">Contrato não autorizado.</p>';
        return;
    }

    $sql="SELECT a.id_fila_chat, e.id_chat, a.protocolo, date_format(a.data_hora, '%d/%m/%Y %H:%i:%s') as hora_registro, a.fila_id, d.nome_fila, b.titulo_assunto"
    .", (concat(f.nome, ' ', f.sobrenome)) as nome_ate"
    .", (SELECT a.nome_empresa from tbl_empresa a, tbl_user b where b.id_user=ate_resp and a.id_empresa=b.empresa_id) as empresa_ate"
    .", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as nome_bko"
    .", c.nome_situacao, date_format(a.hora_inicio, '%H:%i:%s') as hora_inicio, date_format(a.hora_fim, '%H:%i:%s') as hora_fim, a.ta, a.te"
    ." from tbl_chat_fila_secondary a, tbl_assunto b, tbl_situacao_chat c, tbl_config_fila d, tbl_chat_info_secondary e, tbl_user f"
    ." where a.assunto_id=b.id_assunto"
    ." and a.status_fila=c.id_situacao"
    ." and a.ate_resp=f.id_user"
    ." and a.fila_id=d.id_fila"
    ." and a.id_fila_chat=e.fila_chat_id"
    ." and a.contrato_id=?"
    ." and date_format(a.data_hora, '%Y-%m-%d') BETWEEN ? AND ?";

    $params = [$contrato, $de, $ate];

    if($_SESSION["dados"]['nivel_id']=="5"){
        $sql.=" and a.ate_resp=?";
        $params[] = (int) $_SESSION["dados"]['id_user'];
    }

    if($_SESSION["dados"]['nivel_id']=="4"){
        $sql.=" and a.bko_resp=?";
        $params[] = (int) $_SESSION["dados"]['id_user'];
    }

    if($fila > 0){
        $sql.=" and a.fila_id=?";
        $params[] = $fila;
    }

    $sql.=" order by a.data_hora asc";

    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute($params);
    $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($dados);
?>
<div id="info_hist" class="info_hist">
    <table id="tbl_rel" class="table table-hover">
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
                    <center>ASSUNTO</center>
                </th>
                <th>
                    <center>EMPRESA</center>
                </th>
                <th>
                    <center>ATENDENTE</center>
                </th>
                <th>
                    <center>BACKOFFICE</center>
                </th>
                <?php if($infoUser['nivel_id']<=4){ ?>
                <th>
                    <center>MONIT.</center>
                </th>
                <?php } ?>
                <th>
                    <center></center>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                    for($x=0;$x<count($dados);$x++){
                        $tableMon = 'tbl_in_mon_' . (int) $dados[$x]['fila_id'] . '_' . $contrato;
                        if (preg_match('/^tbl_in_mon_\d+_\d+$/', $tableMon)) {
                            $sql = "SELECT id_mon from {$tableMon} where chat_id=?";
                            $stmt = $PDO->prepare($sql);
                            $stmt->execute([(int) $dados[$x]['id_chat']]);
                            $dadosMon = $stmt->fetch(PDO::FETCH_ASSOC);
                        } else {
                            $dadosMon = [];
                        }
                        if (!is_array($dadosMon)) {
                            $dadosMon = [];
                        }
                        $monit = (($dadosMon['id_mon'] ?? '')=='') ? '<i class="far fa-times-circle" style="color: red;"></i>' : '<i class="far fa-check-circle" style="color: green"></i>';

                        echo '<tr id="tr_'.(int) $dados[$x]['id_chat'].'">';
                            echo '<td>'.stHtml($dados[$x]['protocolo']).'</td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_situacao']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['hora_registro']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['titulo_assunto']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['empresa_ate']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_ate']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_bko']).'</center></td>';
                            if($infoUser['nivel_id']<=4){
                                echo '<td><center>'.$monit.'</center></td>';
                            }
                            echo '<td><center><i class="fas fa-info-circle fa-2x pointer" onclick="abreDetail('.(int) $dados[$x]['id_chat'].')"></i></center></td>';
                        echo '</tr>';
                    }
                ?>
        </tbody>
    </table>

    <script>
    function detail(id) {
        var tr = '#tr_' + id;
        var qtd = <?=count($dados);?>;
        for (var i = 0; i <= qtd; i++) {
            var tr_clean = '#tr_' + i;
            $(tr_clean).removeClass('tr_sel');
        }
        $(tr).toggleClass('tr_sel');
        $('#info_hist').removeClass('info_hist');
        $('#info_hist').addClass('info_detail');

        $.post("staff/load_hist.php", {
                id
            },
            function(valor) {
                $('#info_detail').show('slow');
                $('#info_detail').html(
                    '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                $('#info_detail').html(valor);

            });
    }

    function closeDetail(id) {
        var tr = '#tr_' + id;
        $(tr).removeClass('tr_sel');
        $('#info_hist').removeClass('info_detail');
        $('#info_hist').addClass('info_hist');
        $('#info_detail').hide();
    }

    function abreDetail(id) {
        $('#fundo_detail').fadeIn();
        $('#div_detail').html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        $.post("staff/load_hist.php", {
                id
            },
            function(valor) {
                //$('#info_detail').show('slow');
                $('#div_detail').html(
                    '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                setTimeout(function() {
                    $('#div_detail').html(valor);
                }, 500);


            });
    }

    $(document).ready(function() {
        $('#tbl_rel').DataTable({
            //"order": [[ 2, "asc" ]],
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
</div>

<div id="info_detail"></div>


<div id="fundo_detail" class="gw-modal-fundo">
    <div id="mod_detail" class="gw-modal-large">
        <h5>Informações do Chat</h5><br />
        <div class="row" id="div_detail">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>


<script>
$("#fundo_detail, .close").click(function() {
    $("#fundo_detail").hide();
    $("#div_detail").html('');
});
$("#mod_detail").click(function(e) {
    e.stopPropagation();
});
</script>
<?php //include("../cnf/replace_msg.php");?>