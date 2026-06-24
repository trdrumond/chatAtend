<?php
include("../cnf/session.php");
//depurador($_POST);
if ($_POST['id_fila'] == '') {
    $_POST['id_fila'] = 0;
}
?>
<!-- GRAFICO 1 -->
<style>
#graf_1_<?=$_POST['id_fila'];

?> {
    width: 100%;
    max-height: 130px;
    height: 130px;
}

#titulo {
    width: 100%;
    text-align: center;
}

.quadro {
    width: 49%;
    height: 190px;
    float: left;
    margin: 1px;
    padding-top: 40px;
    padding-left: 5px;
    padding-right: 5px;
    border-radius: 2px;
}

.tit_info,
.info {
    width: 100%;
    float: left;
    font-size: 35px;
    text-align: center;
}
</style>
<?php


// Normaliza listas de IDs para evitar lixo em IN() e melhorar plano de execução
$idContratoPost = isset($_POST['id_contrato']) ? $_POST['id_contrato'] : 0;
$idFilaPost     = isset($_POST['id_fila']) ? $_POST['id_fila'] : 0;

if ($idContratoPost != 0) {
    $contratos = array_filter(array_map('intval', explode(',', $idContratoPost)));
    $filas     = array_filter(array_map('intval', explode(',', $idFilaPost)));

    $listaContratos = $contratos ? implode(',', $contratos) : '0';
    $listaFilas     = $filas ? implode(',', $filas) : '0';

    $qryContrato = " and contrato_id in (" . $listaContratos . ")";
    $qryFila = " and fila_id IN (" . $listaFilas . ")";
} else {
    $qryContrato = '';
    $qryFila = "";
}

//$statusFila = "status_fila IN (4, 6, 7, 10)";

$sql = "SELECT count(*) as qtd_concluido,"
    //."(SELECT count(*) from tbl_chat_fila where status_fila=1 $qryContrato $qryFila) as qtd_fila,"
    //."(SELECT count(*) from tbl_chat_fila where status_fila=2 $qryContrato $qryFila) as qtd_atend,"
    . " (SELECT count(id_pend) as qtd FROM tbl_pend_info"
    . "    WHERE situacao_id=3"
    . "      AND data_hora_fim is null"
    . "      AND data_hora >= CURDATE()"
    . "      AND data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
    . "      $qryFila) as qtd_pend,"
    . " (SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila"
    . "    WHERE status_fila>=4"
    . "      AND hora_fim<>''"
    . "      AND hora_inicio >= CURDATE()"
    . "      AND hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
    . "      $qryContrato $qryFila) as tma,"
    . " (SELECT sec_to_time(avg(time_to_sec(te))) as sla FROM tbl_chat_fila"
    . "    WHERE status_fila>=4"
    . "      AND hora_fim"
    . "      AND hora_inicio >= CURDATE()"
    . "      AND hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
    . "      $qryContrato $qryFila) as tme"
    . " from tbl_chat_fila"
    . " where status_fila>=4"
    . "   and hora_fim<>''"
    . "   and hora_inicio >= CURDATE()"
    . "   and hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
    . "   $qryContrato $qryFila";

//echo "<br>".$sql;

$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dadosHoje = $stmt->fetch(PDO::FETCH_ASSOC);
//depurador($dados);

$tma = explode(".", $dadosHoje['tma']);
$dadosHoje['tma'] = $tma[0];
if ($dadosHoje['tma'] == '') {
    $dadosHoje['tma'] = '--:--:--';
}
$tme = explode(".", $dadosHoje['tme']);
$dadosHoje['tme'] = $tme[0];
if ($dadosHoje['tme'] == '') {
    $dadosHoje['tme'] = '--:--:--';
}
//if($dadosHoje['qtd_fila']==0){$dadosHoje['qtd_fila']="---";}
//if($dadosHoje['qtd_atend']==0){$dadosHoje['qtd_atend']="---";}
if ($dadosHoje['qtd_concluido'] == 0) {
    $dadosHoje['qtd_concluido'] = "---";
}
if ($dadosHoje['qtd_pend'] == 0) {
    $dadosHoje['qtd_pend'] = "---";
}

?>

<div id="titulo">
    <h7><strong>Hoje (<?= date('d/m/Y') ?>)</strong></h7>
</div>

<div id="graf_1_<?= $_POST['id_fila']; ?>">
    <!--
    <div class="quadro info2">
        <div class="tit_info">Em Fila</div>
        <div class="info"><?= $dadosHoje['qtd_fila']; ?></div>
    </div>
    <div class="quadro info3">
        <div class="tit_info">Em Atend.</div>
        <div class="info"><?= $dadosHoje['qtd_atend']; ?></div>
    </div>
    -->
    <div class="quadro info4">
        <div class="tit_info">Concluído</div>
        <div class="info"><?= $dadosHoje['qtd_concluido']; ?></div>
    </div>
    <div class="quadro info7">
        <div class="tit_info">Pendência</div>
        <div class="info"><?= $dadosHoje['qtd_pend']; ?></div>
    </div>
    <div class="quadro info5">
        <div class="tit_info">TMA</div>
        <div class="info"><?= $dadosHoje['tma']; ?></div>
    </div>
    <div class="quadro info6">
        <div class="tit_info">TME</div>
        <div class="info"><?= $dadosHoje['tme']; ?></div>
    </div>
</div>



<!-- -------------------------------------------------------------------------------------------- -->

<!-- GRAFICO 2 -->
<?php
/*
$sql = "SELECT count(*) as qtd,"
    . "       c.status_fila,"
    . "       s.nome_situacao as nome_status"
    . "  from tbl_chat_fila c"
    . "  left join tbl_situacao_chat s on s.id_situacao = c.status_fila"
    . " where c.data_hora >= CURDATE()"
    . "   and c.data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
    . "   $qryFila"
    . " group by c.status_fila, s.nome_situacao"
    . " order by qtd desc";

//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$ddChats_2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
//depurador($ddChats_2);

?>


<div class="chart-100">
    <style>
    #titulo {
        width: 100%;
        text-align: center;
    }
    </style>

    <div id="graf_2_<?php echo $_POST['id_fila']; ?>" class="chartdiv"></div>
</div>


<script type="text/javascript">
google.charts.load("current", {
    packages: ["corechart"]
});

google.charts.setOnLoadCallback(drawChart);

function drawChart() {

    var data = google.visualization.arrayToDataTable([
        ['Situação', 'Quantidade'],
        <?php
            for ($y = 0; $y < count($ddChats_2); $y++) {
                $ls = $ddChats_2[$y];
                echo '["' . $ls['nome_status'] . '",' . $ls['qtd'] . ' ],';
            }
            ?>
    ]);

    var options = {
        //is3D: true,
        title: 'Quantidade por Status (Hoje)',
        slices: {
            0: {
                offset: 0.1,
                color: '#252159'
            },
            1: {
                offset: 0.1,

                color: '#6760C1'
            },
            2: {
                offset: 0.1,
                color: '#AFABDE'
            },
            3: {
                offset: 0.1,
                color: '#35868C'
            },
            5: {

                offset: 0.1,
                color: '#56B7BE'
            },
            6: {
                offset: 0.1
            },
            7: {
                offset: 0.1

            },
            8: {
                offset: 0.1
            }
        },

    };

    var chart = new google.visualization.PieChart(document.getElementById('graf_2_<?php echo $_POST['id_fila']; ?>'));

    chart
        .draw(data, options);
}
</script>
<?php */ ?>
