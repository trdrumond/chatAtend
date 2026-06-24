<?php
include("../cnf/session.php");
//depurador($_POST);
if($_POST['id_fila']==''){
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
    width: 100%;
    float: left;

}
</style>
<?php


$data_hoje = date('Y-m-d');
$data_ant = date('Y-m-d', strtotime('-5 days', strtotime(date('Y-m-d'))));



if($_POST['id_fila']!=0){
    $sqlQuery = " where fila_id=".$_POST['id_fila'];
    $sqlQuery_ = " and fila_id=".$_POST['id_fila'];
} else {
    $sqlQuery = "";
    $sqlQuery_ = "";
}

$sql="SELECT date_format(data_hora, '%d/%m') as dia, count(*) as qtd from tbl_chat_fila $sqlQuery group by date_format(data_hora, '%Y-%m-%d') order by date_format(data_hora, '%Y-%m-%d') desc limit 10";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($dados);

?>

<div id="titulo">
    <h7>Estatísticas de hoje (<?=date('d/m/Y')?>)</h7>
</div>

<div id="graf_1_<?=$_POST['id_fila'];?>">
    <div class="quadro">
        <div class="tit_info">Em Fila</div>
        <div class="info"></div>
    </div>
    <div class="quadro">
        <div class="tit_info">Em Atendimento</div>
        <div class="info"></div>
    </div>
    <div class="quadro">
        <div class="tit_info">Concluído</div>
        <div class="info"></div>
    </div>
    <div class="quadro">
        <div class="tit_info">Pendência</div>
        <div class="info"></div>
    </div>
    <div class="quadro">
        <div class="tit_info">TMA</div>
        <div class="info"></div>
    </div>
    <div class="quadro">
        <div class="tit_info">TME</div>
        <div class="info"></div>
    </div>
</div>



<!-- -------------------------------------------------------------------------------------------- -->

<!-- GRAFICO 2 -->
<?php
$sql="SELECT count(*) as qtd, status_fila, (SELECT nome_situacao from tbl_situacao_chat where id_situacao=status_fila) as nome_status from tbl_chat_fila where date_format(data_hora, '%Y-%m-%d')=curdate() $sqlQuery_ group by status_fila order by qtd desc";

//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$ddChats_2 = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($ddChats_2);

?>

<?php
 /*
        for($y=0;$y<count($ddChats_2);$y++){
            $ls=$ddChats_2[$y];
            echo '{ Situacao: "'.$ls['nome_status'].'", qtd: '.$ls['qtd'].' },';
        }
*/
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
        for($y=0;$y<count($ddChats_2);$y++){
            $ls=$ddChats_2[$y];
            echo '["'.$ls['nome_status'].'",'.$ls['qtd'].' ],';
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

                color: '#914F4D'
            },
            2: {
                offset: 0.1,
                color: '#F72720'
            },
            3: {
                offset: 0.1,
                color: '#5E4645'
            },
            5: {

                offset: 0.1,
                color: '#332D2C'
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
