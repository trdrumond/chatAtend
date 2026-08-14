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

    $de = preg_replace('/[^0-9\-]/', '', (string) ($_POST['de'] ?? ''));
    $ate = preg_replace('/[^0-9\-]/', '', (string) ($_POST['ate'] ?? ''));
    $contratoId = (int) ($_POST['contrato'] ?? 0);
    $filaId = (int) ($_POST['fila'] ?? 0);
    $nivelId = (int) ($_SESSION['dados']['nivel_id'] ?? 0);
    $userId = (int) ($_SESSION['dados']['id_user'] ?? 0);
    if ($de === '' || $ate === '') {
        $de = date('Y-m-d');
        $ate = date('Y-m-d');
    }
    if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
        echo '<p class="text-danger">Contrato não autorizado.</p>';
        return;
    }

    $sql="SELECT f.id_pend, a.id_fila_chat, e.id_chat, a.protocolo, date_format(a.data_hora, '%d/%m/%Y %H:%i:%s') as hora_registro, a.fila_id, d.nome_fila, b.titulo_assunto"
    .", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=f.ate_resp) as nome_ate, f.situacao_id, f.chat_id as chat_pend"
    .", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=f.bko_resp) as nome_bko"
    .", c.nome_situacao, a.status_fila, e.status_chat, date_format(a.hora_inicio, '%H:%i:%s') as hora_inicio, date_format(a.hora_fim, '%H:%i:%s') as hora_fim, a.ta, a.te"
    .", f.motivo, f.info_fim, f.data_hora_fim, f.data_hora_visualizacao"
    ." from tbl_chat_fila_secondary a, tbl_assunto b, tbl_situacao_chat c, tbl_config_fila d, tbl_chat_info_secondary e, tbl_pend_info f"
    ." where a.assunto_id=b.id_assunto"
    ." and a.status_fila=c.id_situacao"
    ." and (f.situacao_id=3 or f.situacao_id=4)"
    ." and a.id_fila_chat=f.chat_id"
    ." and a.fila_id=d.id_fila"
    ." and a.id_fila_chat=e.fila_chat_id"
    ." and date_format(a.data_hora, '%Y-%m-%d') BETWEEN ? AND ?";

    $params = [$de, $ate];

    if($nivelId !== 0){
        $sql.=" and a.contrato_id=?";
        $params[] = $contratoId;
    }

    if($nivelId === 5){
        $sql.=" and f.ate_resp=?";
        $params[] = $userId;
    }

    if($nivelId === 4){
        $sql.=" and f.bko_resp=?";
        $params[] = $userId;
    }

    if($filaId !== 0){
        $sql.=" and a.fila_id=?";
        $params[] = $filaId;
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
                <th>
                    <center>STATUS.</center>
                </th>
                <th>
                    <center></center>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                    for($x=0;$x<count($dados);$x++){
                        if($dados[$x]['data_hora_fim']==''){
                            $pend ='<i class="far fa-times-circle fa-2x" style="color: red;" title="Pendente"></i>';
                        } else if($dados[$x]['data_hora_fim']!='' && $dados[$x]['data_hora_visualizacao']==''){
                            $pend ='<i class="fas fa-exclamation-triangle fa-2x" style="color: #F9F900;" title="Pendência visualização do solicitante"></i>';
                        } else {
                            $pend ='<i class="far fa-check-circle fa-2x" style="color: green" title="Pendência finalizada"></i>';
                        }

                        echo '<tr id="tr_'.(int) $dados[$x]['id_chat'].'">';
                            echo '<td>'.stHtml($dados[$x]['protocolo']).'</td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_situacao']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['hora_registro']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_fila']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['titulo_assunto']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_ate']).'</center></td>';
                            echo '<td><center>'.stHtml($dados[$x]['nome_bko']).'</center></td>';
                            echo '<td><center>'.$pend.'</center></td>';
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

        $.post("staff/load_hist_pend.php", {
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
        $.post("staff/load_hist_pend.php", {
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
    <div id="mod_detail" class="gw-modal-egg">
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
