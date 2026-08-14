<?php
$infoUser = [];
$tmpDash = 10000;
$tmpDash_ = 10000;
$showPainelIdx = false;

require_once __DIR__ . '/../cnf/session.php';

if (!is_array($infoUser)) {
    $infoUser = [];
}
$tmpDash_ = (int)($tmpDash ?? 10000);
$tempGraf = $tmpDash_ * 3;

if ((int)($infoUser['nivel_id'] ?? 0) > 1) {
    $serv['contrato_id'] = $infoUser['contrato_id'] ?? 0;
    $serv['fila_id'] = $infoUser['fila_id'] ?? 0;
} else {
    $serv['contrato_id'] = 0;
    $serv['fila_id'] = 0;
}
$showPainelIdx = (int)($infoUser['nivel_id'] ?? 99) < 4;

//depurador($infoUser);
?>
<!-- <meta http-equiv="refresh" content="600"> -->
<script>
if (typeof dash_trt !== "undefined") {
    clearInterval(dash_trt);
}
</script>


<style>
.img_perfil_online {
    width: 48px;
    height: 48px;
    object-fit: cover;
}

.label_count_fila {
    font-size: 22px;
    font-weight: 800;
}

.online,
.atendimento,
.offline,
.logout,
.pausa,
.indisp,
.pos,
.fila_count {
    border-width: 5px;
    border-style: solid;
}

.online {
    border-color: #31CAF0;
}

.indisp {
    border-color: #0C7C98;
}

.pos {
    border-color: #888800;
}

.pausa {
    border-color: #8C0000;
}

.atendimento {
    border-color: #FF8F59;
}

.fila_count {
    border-color: #36D900;
}



.offline {
    border-color: #CCCCCC;
    color: #CCCCCC;
    -webkit-filter: grayscale(100%);
    filter: grayscale(100%);
    filter: gray;
    /* IE */
}

.logout {
    border-color: #555555;
    -webkit-filter: grayscale(100%);
    filter: grayscale(100%);
    filter: gray;
    /* IE */
}



.leg-online {
    background: #31CAF0;
    color: #000000;
    text-align: center;
}

.leg-pos {
    background: #888800;
    color: #FFFFFF;
    text-align: center;
}

.leg-indisp {
    background: #0C7C98;
    color: #FFFFFF;
    text-align: center;
}



.leg-pausa {
    background: #8C0000;
    color: #FFFFFF;
}

.leg-atendimento {
    background: #FF8F59;
    color: #000000;
}

.leg-offline {
    background: #CCCCCC;
    color: #000000;
}

.leg-hora {
    background: #FFFFFF;
    color: #000000;
    font-size: 30px;
}

.leg-logout {
    background: #555555;
    color: #FFFFFF;
}

.chart-100 {
    width: 95%;
    float: left;
    margin: 5px;
}

.chart-50 {
    width: 47%;
    float: left;
    margin: 5px;
}

.user_dash {
    width: 100%;
    overflow: auto;

}

.dash_fila {
    min-height: 260px;
    height: auto;
    width: 100%;
    overflow: auto;

}

.user_dash_bloco {
    min-height: 320px;
    height: auto;
    overflow: auto;
    float: none;
}

.user_dash {
    width: 99%;
}

.dash_fila {
    width: 99%;
}



.chartdiv {
    width: 90%;
    min-height: 160px;
    max-height: none;
    height: 100%;
}

.btn_atualizar {
    background-color: #CCCCCC;
    color: #FFFFFF;
    border-radius: 10px;
    border: 0;
    padding: 5px;
    margin: 5px;
}

.sem_fila {
    opacity: .50;
    color: #292562;
}

<?php
$dashOperTheme = '';
if (empty($_GET)) {
    $dashOperTheme = 'dash-oper--wall';
} elseif (isset($_GET['op']) && $_GET['op'] === 'geral') {
    $dashOperTheme = 'dash-oper--geral';
}
?>
</style>

<script type="text/javascript" src="dadosIdx.js"></script>

<script type="text/javascript">
function loadOnline(id_contrato, id_fila) {
    var div = '#div_user_' + id_contrato + '_' + id_fila;
    $.post("../view/staff/painel_load_online.php", {
            id_contrato,
            id_fila
        },
        function(valor) {
            $(div).html(valor);
        });
}

function loadHora() {
    var div = '#div_hora';
    $.post("../view/staff/painel_load_hora.php",
        function(valor) {
            $(div).html(valor);
        });
}



function loadFilaAtiva(contrato, fila) {
    var div = '#div_fila_' + fila;
    $.post("../view/staff/painel_load_fila_ativa.php", {
            fila
        },
        function(valor) {
            $(div).html(valor);
        });
}




function loadInfoFila(contrato, fila) {
    var div = '#div_fila_' + contrato + '_' + fila;
    $.post("../view/staff/painel_load_info_fila.php", {
            fila
        },
        function(valor) {
            $(div).html(valor);
        });
}

loaddadosIdx();

function loaddadosIdx() {
    dadosIdx(0, 0, 0);
}
</script>


<div class="dash-oper-workspace <?= $dashOperTheme ?>">
<div id="topo" class="dash-oper-top">
    <div id="legend" class="dash-oper-legend">
        <div class="legend-dash leg-online">Livre</div>
        <div class="legend-dash leg-indisp">Indisp.</div>
        <div class="legend-dash leg-atendimento">Em Atendimento</div>
        <div class="legend-dash leg-pos">Pós</div>
        <div class="legend-dash leg-pausa">Em Pausa</div>
        <div class="legend-dash leg-logout">Logout</div>
        <div class="legend-dash leg-offline">Offline</div>
        <!-- <div class="legend-dash leg-hora" id="div_hora"><?=date('H:i')?></div> -->
    </div>
    <div id="indicadores" class="dash-oper-kpis">

        <div class="bloco_info_painel">
            <div class="til">Bko</div>
            <div id="dadosOn" class="inf info1">---</div>
        </div>

        <div class="bloco_info_painel">
            <div class="til">Em Fila</div>
            <div id="dadosFila" class="inf info2">---</div>
        </div>
        <div class="bloco_info_painel">
            <div class="til">Em Atend.</div>
            <div id="dadosAtend" class="inf info3">---</div>
        </div>

        <?php
        if (!isset($showPainelIdx)) {
            $showPainelIdx = (int)(($infoUser ?? [])['nivel_id'] ?? 99) < 4;
        }
        if ($showPainelIdx) {
        ?>
        <div class="bloco_info_painel">
            <div class="til">Conc.</div>
            <div id="dadosConcluido" class="inf info4">---</div>
        </div>

        <div class="bloco_info_painel">
            <div class="til">Pend.</div>
            <div id="dadosPend" class="inf info7">---</div>
        </div>

        <div class="bloco_info_painel">
            <div class="til">TMA</div>
            <div id="dadosTma" class="inf info5">--:--:--</div>
        </div>
        <?php } ?>

        <div class="bloco_info_painel">
            <div class="til">TME</div>
            <div id="dadosTme" class="inf info6">--:--:--</div>
        </div>
    </div>

</div>


<div id="dashboard">


    <?php
                if (!isset($PDO)) {
                    require_once __DIR__ . '/../cnf/session.php';
                }
                $sql="SELECT id_contrato from tbl_contrato where ativo=1 and nome_contrato<>'Matriz'";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare($sql);
                $result = $stmt->execute();
                $infoContrato = $stmt->fetch( PDO::FETCH_ASSOC );

                $contratoPainelId = (int) ($infoContrato['id_contrato'] ?? 0);
                $sql="SELECT a.id_contrato, concat(a.nome_contrato, '/', a.uf) as nome, b.id_fila, b.nome_fila from tbl_contrato a, tbl_config_fila b where a.ativo=1 and a.id_contrato=? and b.contrato_id=a.id_contrato and b.ativo=1 order by id_fila asc";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare($sql);
                $result = $stmt->execute([$contratoPainelId]);
                $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
                //depurador($dadosContratos);
            ?>

    <div class="dash-oper-filas">


        <?php
                        for($x=0;$x<count($dadosContratos);$x++){
                            $ls=$dadosContratos[$x];

                            echo '  <div class="painel_div dash-oper-card" id="div_'.$ls['id_contrato'].'_'.$ls['id_fila'].'">';
                                echo '<div class="card">';
                                    echo '<div class="card-header" title="'.htmlspecialchars(trim($ls['nome_fila']), ENT_QUOTES, 'UTF-8').'">';
                                        $ls['nome_fila'] = trim($ls['nome_fila']);
                                        echo '<h5>'.htmlspecialchars($ls['nome_fila'], ENT_QUOTES, 'UTF-8').'</h5>';
                                    echo '</div>';
                                    echo '<div class="card-body">';
                                        include("load_dados_dash_ind_painel_indice.php");
                                        echo '<div id="div_user_'.$ls['id_contrato'].'_'.$ls['id_fila'].'" class="user_dash"></div>';
                                    echo '</div>';
                                echo '</div>';

                                echo '  <script>
                                            loadOnline('.$ls['id_contrato'].', '.$ls['id_fila'].');
                                        </script>';
                                    ?>
        <?php
                            echo '</div>';
                        }
                    ?>
    </div>
</div>

<script>
function load() {
    <?php
                for($x=0;$x<count($dadosContratos);$x++){
                    $ls=$dadosContratos[$x];
                    echo 'loadOnline('.$ls['id_contrato'].', '.$ls['id_fila'].');';
                    echo 'loaddadosIdx();';
                    echo 'loadHora();';
                }
            ?>

}
//setInterval(function(){ load(); }, 300000);
</script>

<div id="dadosDashInd" style="width: 100% !important; float: left;"></div>
</div>
