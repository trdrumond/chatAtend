<?php
require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/rotina_pendencia.php';

if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}
if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}
if (!isset($tmpDash_)) {
    $tmpDash_ = (int)($tmpDash ?? 10000);
}

//depurador($infoUser);

$tempGraf = ($tmpDash_ * 3);
$tmpDashOnline = max((int)($tmpDash ?? 10000), 5000);

function dashFilaLoadingMarkup(string $label): string
{
    return '<div class="dash-fila-loading">'
        . '<img src="img/loading.gif" alt="Carregando..." width="64">'
        . '<span>' . htmlspecialchars($label) . '</span>'
        . '</div>';
}

function dashFilaRenderPaneColumns(int $contrato, int $fila): void
{
    $key = $contrato . '_' . $fila;
    $loadTeam = dashFilaLoadingMarkup('Carregando equipe');
    $loadQueue = dashFilaLoadingMarkup('Carregando fila');
    $loadChart = dashFilaLoadingMarkup('Carregando indicadores');
?>
    <div class="dash-fila-col dash-fila-col--team">
        <header class="dash-fila-col__head">
            <i class="fas fa-users" aria-hidden="true"></i>
            <span>Equipe</span>
        </header>
        <div id="div_user_<?= $key ?>" class="user_dash_0 dash-fila-col__body"><?= $loadTeam ?></div>
    </div>
    <div class="dash-fila-col dash-fila-col--queue">
        <header class="dash-fila-col__head">
            <i class="fas fa-list-ol" aria-hidden="true"></i>
            <span>Fila de atendimento</span>
        </header>
        <div id="div_fila_<?= $key ?>" class="dash_fila dash-fila-col__body"><?= $loadQueue ?></div>
        <div id="div_info_<?= $key ?>" class="chat_info dash-fila-col__body" style="display: none"></div>
    </div>
    <div class="dash-fila-col dash-fila-col--chart">
        <header class="dash-fila-col__head">
            <i class="fas fa-chart-area" aria-hidden="true"></i>
            <span>Indicadores</span>
        </header>
        <div class="charts dash-fila-col__body">
            <div id="div_chart_<?= $key ?>" class="chart-100"><?= $loadChart ?></div>
        </div>
    </div>
<?php
}
?>
<!-- <meta http-equiv="refresh" content="600"> -->
<script>
    window._dashboardIntervals = window._dashboardIntervals || [];
    if (typeof dash_trt !== "undefined") {
        clearInterval(dash_trt);
    }
</script>


<style>
    .dash-fila-workspace #dashboard {
        width: 100%;
        float: none;
        flex: 1 1 auto;
        min-height: 0;
    }

    .dash-fila-workspace .chartdiv {
        width: 100%;
        min-height: 180px;
        height: 100%;
    }

    .dash-fila-workspace .btn_atualizar {
        background-color: #CCCCCC;
        color: #FFFFFF;
        border-radius: 10px;
        border: 0;
        padding: 5px;
        margin: 5px;
    }

    .dash-fila-live-ts {
        font-size: 11px;
        color: var(--c-muted);
        margin: 0 0 8px;
        text-align: right;
    }

    .dash-fila-live-ts .fa-circle {
        color: var(--c-success);
        font-size: 8px;
        margin-right: 4px;
    }

</style>

<script type="text/javascript">
    /*
function loadOnline(id_contrato, id_fila) {
    var div = '#div_user_' + id_contrato + '_' + id_fila;
    $.post("staff/load_online.php", {
            id_contrato,
            id_fila
        },
        function(valor) {
            $(div).html(valor);
        });
}
*/
    var loadOnlineEmExecucao = {};

    function loadOnline(id_contrato, id_fila) {
        var chave = id_contrato + '_' + id_fila;
        if (loadOnlineEmExecucao[chave]) {
            return;
        }
        loadOnlineEmExecucao[chave] = true;
        var div = '#div_user_' + id_contrato + '_' + id_fila;
        $.ajax({
            type: "post",
            url: "staff/load_online.php",
            data: {
                id_contrato: id_contrato,
                id_fila: id_fila
            },
            success: function(data) {
                $(div).html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                location.reload();
            },
            complete: function() {
                loadOnlineEmExecucao[chave] = false;
            }
        });
    }

    function loadGraf1(id_contrato, id_fila) {
        var div = '#div_chart_' + id_contrato + '_' + id_fila;
        /*
        $.post("staff/load_graf_1.php", {
                id_contrato,
                id_fila
            },
            function(valor) {
                $(div).html(valor);
            });
            */

        $.ajax({
            type: "post",
            url: "staff/load_graf_1.php",
            data: {
                id_contrato: id_contrato,
                id_fila: id_fila
            },
            success: function(data) {
                $(div).html(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                location.reload();

            }
        })
    }

    /*
    function loadGraf2(id_contrato, id_fila) {
        var div = '#div_chart_2_' + id_contrato + '_' + id_fila;
        //var div_graf = '#chart_2_'+id_contrato + '_' + id_fila;
        //$(div_graf).html('');
        $.post("staff/load_graf_2.php", {
                id_contrato,
                id_fila
            },
            function(valor) {
                $(div).html(valor);
            });
    }
    */


    function loadInfoUser(user_id, id_contrato, id_fila) {
        user_id = parseInt(user_id, 10) || 0;
        id_contrato = parseInt(id_contrato, 10) || 0;
        id_fila = parseInt(id_fila, 10) || 0;
        var paneKey = id_contrato + '_' + id_fila;
        var div = '#div_user_' + paneKey;
        var div_show = '#div_info_' + paneKey;
        var div_fila = '#div_fila_' + paneKey;
        var pane = $(div_show).closest('.tab-pane');

        if (typeof stDashAcomp !== 'undefined') {
            stDashAcomp.stop();
        }

        window.__dashInfoUserLoads = window.__dashInfoUserLoads || {};
        window.__dashInfoUserXhr = window.__dashInfoUserXhr || {};
        window.__dashInfoUserLoads[paneKey] = (window.__dashInfoUserLoads[paneKey] || 0) + 1;
        var reqId = window.__dashInfoUserLoads[paneKey];

        if (window.__dashInfoUserXhr[paneKey]) {
            window.__dashInfoUserXhr[paneKey].abort();
        }

        $(div_fila).hide();
        $(div_show).html('');
        $(div_show).data('st-user-id', user_id);
        $(div_show).css('display', 'flex').show();
        pane.addClass('dash-tab-pane--user-detail');

        $(div_show).html('<div class="dash-fila-loading"><img src="img/loading.gif" alt="Carregando..." width="64"><span>Carregando atendimentos</span></div>');
        window.__dashInfoUserXhr[paneKey] = $.ajax({
            url: 'staff/load_info_user.php',
            type: 'POST',
            data: {
                id_contrato: id_contrato,
                id_fila: id_fila,
                user_id: user_id
            },
            success: function (valor) {
                if (reqId !== window.__dashInfoUserLoads[paneKey]) {
                    return;
                }
                if (parseInt($(div_show).data('st-user-id'), 10) !== user_id) {
                    return;
                }
                $(div_show).html(valor);
            },
            complete: function () {
                if (window.__dashInfoUserXhr[paneKey] && window.__dashInfoUserXhr[paneKey].readyState === 4) {
                    window.__dashInfoUserXhr[paneKey] = null;
                }
            }
        });
    }

    function close_info(id_contrato, id_fila) {
        id_contrato = parseInt(id_contrato, 10) || 0;
        id_fila = parseInt(id_fila, 10) || 0;
        var paneKey = id_contrato + '_' + id_fila;
        var div_show = '#div_info_' + paneKey;
        var div_fila = '#div_fila_' + paneKey;
        var pane = $(div_show).closest('.tab-pane');

        if (typeof stDashAcomp !== 'undefined') {
            stDashAcomp.stop();
        }

        if (window.__dashInfoUserXhr && window.__dashInfoUserXhr[paneKey]) {
            window.__dashInfoUserXhr[paneKey].abort();
            window.__dashInfoUserXhr[paneKey] = null;
        }

        $(div_show).hide().html('').removeData('st-user-id');
        $(div_fila).show();
        pane.removeClass('dash-tab-pane--user-detail');
    }


    function loadFilaAtiva(contrato, fila) {
        var div = '#div_fila_' + contrato + '_' + fila;
        $.post("staff/load_fila_ativa.php", {
                fila: fila
            },
            function(valor) {
                $(div).html(valor);
            });
    }

    /*
    function loadRank(fila) {
        var div = '#div_rank_' + fila;
        $.post("staff/load_rank.php", {
                fila: fila
            },
            function(valor) {
                $(div).html(valor);
            });
    }
    */



    function loadInfoFila(contrato, fila) {
        var div = '#div_fila_' + contrato + '_' + fila;
        $.post("staff/load_info_fila.php", {
                fila: fila
            },
            function(valor) {
                $(div).html(valor);
            });
    }
</script>

<?php
if ($infoUser['nivel_id'] >= 1) {
    $serv['contrato_id'] = $infoUser['contrato_id'];
    $serv['fila_id'] = $infoUser['fila_id'];
} else {
    $serv['contrato_id'] = 0;
    $serv['fila_id'] = 0;
}


?>
<div id="legend" class="dash-oper-legend" style="width: 100% !important">
    <div class="legend-dash leg-online">Livre</div>
    <div class="legend-dash leg-indisp">Indisp.</div>
    <div class="legend-dash leg-atendimento">Em Atendimento</div>
    <div class="legend-dash leg-pos">Pós</div>
    <div class="legend-dash leg-pausa">Em Pausa</div>
    <div class="legend-dash leg-logout">Logout</div>
    <div class="legend-dash leg-offline">Offline</div>
</div>
<p class="dash-fila-live-ts"><i class="fas fa-circle"></i> Atualizado: <span id="dashFilaLiveTs">—</span></p>

<div id="dashboard" class="dash-fila-workspace">


    <?php

    $cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql = "SELECT a.id_contrato, concat(a.nome_contrato, '/', a.uf) as nome, b.id_fila, b.nome_fila, b.ativo, (SELECT count(id_fila_chat) from tbl_chat_fila where fila_id=id_fila and (status_fila=".ST_FILA_NA_FILA." or ".stFilaSqlAtendimentoAtivo().")) as qtd_on from tbl_contrato a, tbl_config_fila b where a.ativo=1 and a.id_contrato in (" . $cttIn['ph'] . ") and b.contrato_id=a.id_contrato order by nome asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute($cttIn['ids']);
    $dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //depurador($dadosContratos);
    ?>
    <ul class="nav nav-pills mb-3" id="myTab" role="tablist">
        <?php

        //if($infoUser['nivel_id']==0){
        $active = ' active';
        $show = ' true';
        echo '  <li class="nav-item">
                                    <button class="nav-link ' . $active . '" id="tab_0_0" data-bs-toggle="tab" data-bs-target="#div_0_0" type="button" role="tab" aria-controls="div_0_0" aria-selected="' . $show . '"><strong>GERAL</strong></button>
                                </li>';

        //}

        $active_sel = '';
        for ($x = 0; $x < count($dadosContratos); $x++) {
            $ls = $dadosContratos[$x];
            //echo "<br>".$ls['id_fila']." - ".$ls['qtd_on'];
            if (($ls['ativo'] == 1) || ($ls['ativo'] == 0 && $ls['qtd_on'] > 0)) {
                //echo "<br>".$ls['id_fila']." - ".$ls['qtd_on'];
                if ($active_sel === '') {
                    $active_sel = $x;
                }
                //echo "<br>".$x." - ".$active_sel;
                $active = ($infoUser['nivel_id'] != 0 && $x == 0) ? ' active' : '';
                $show = ($infoUser['nivel_id'] != 0 && $x == 0) ? ' true' : 'false';

                $active = '';
                $show = 'false';




                echo '  <li class="nav-item">
                                        <button class="nav-link ' . $active . '" id="tab_' . $ls['id_contrato'] . '_' . $ls['id_fila'] . '" data-bs-toggle="tab" data-bs-target="#div_' . $ls['id_contrato'] . '_' . $ls['id_fila'] . '" type="button" role="tab" aria-controls="div_' . $ls['id_contrato'] . '_' . $ls['id_fila'] . '" aria-selected="' . $show . '">' . $ls['nome_fila'] . '</button>
                                    </li>';
            }
        }
        ?>
    </ul>

    <div class="tab-content" id="myTabContent">


        <?php

        //if($infoUser['nivel_id']==0){
        $active = ' show active';

        echo '  <div class="tab-pane fade' . $active . '" id="div_0_0" role="tabpanel" aria-labelledby="tab_0_0">';
        dashFilaRenderPaneColumns(0, 0);
        echo '</div>';

        $active_sel = '';
        for ($x = 0; $x < count($dadosContratos); $x++) {

            $ls = $dadosContratos[$x];
            if (($ls['ativo'] == 1) || ($ls['ativo'] == 0 && $ls['qtd_on'] > 0)) {
                if ($active_sel === '') {
                    $active_sel = $x;
                }
                $active = ($x == $active_sel) ? ' show active' : '';
                $active = '';

                echo '  <div class="tab-pane fade' . $active . '" id="div_' . $ls['id_contrato'] . '_' . $ls['id_fila'] . '" role="tabpanel" aria-labelledby="tab_' . $ls['id_contrato'] . '_' . $ls['id_fila'] . '">';
                dashFilaRenderPaneColumns((int)$ls['id_contrato'], (int)$ls['id_fila']);
                echo '</div>';
            }
        }
        ?>


    </div>
</div>




</div>

<!-- <script type="text/javascript" src="js/load.js"></script> -->

<?php
$dashFilaLiveJs = __DIR__ . '/../js/dash-fila-live.js';
$dashFilaLiveVer = is_file($dashFilaLiveJs) ? (string) filemtime($dashFilaLiveJs) : '1';
$dashAcompChatJs = __DIR__ . '/../js/dash-acomp-chat.js';
$dashAcompChatVer = is_file($dashAcompChatJs) ? (string) filemtime($dashAcompChatJs) : '1';
?>
<script type="text/javascript" src="js/dadosIdx.js"></script>
<script type="text/javascript" src="js/dash-acomp-chat.js?<?= htmlspecialchars($dashAcompChatVer) ?>"></script>
<script type="text/javascript" src="js/dash-fila-live.js?<?= htmlspecialchars($dashFilaLiveVer) ?>"></script>
<script>
    (function() {
        var TMP_DASH = <?php echo (int)$tmpDashOnline; ?>;
        var DASH_USER = <?php echo (int)$_SESSION['dados']['id_user']; ?>;
        var DASH_FILA = <?php echo (int)$serv['fila_id']; ?>;
        var DASH_CONTRATO = <?php echo (int)$serv['contrato_id']; ?>;
        var DASH_NIVEL = <?php echo (int)$infoUser['nivel_id']; ?>;

        function refreshKpis() {
            dadosIdx(DASH_USER, DASH_FILA, DASH_CONTRATO);
        }

        refreshKpis();
        if (window.__dashIdxInterval) {
            clearInterval(window.__dashIdxInterval);
        }
        if (DASH_NIVEL < 5) {
            window.__dashIdxInterval = setInterval(refreshKpis, TMP_DASH);
        }

        if (typeof dashFilaLive !== 'undefined') {
            dashFilaLive.start({ interval: TMP_DASH });
        }

        // Abas: apenas controle visual; dados atualizados para todas via dashFilaLive
        window.dashFilaLastTabKey = window.dashFilaLastTabKey || null;

        function paneIdToKey(paneId) {
            if (!paneId) return '';
            return paneId.replace(/^div_/, '') || paneId;
        }

        function tabButtonFromEvent(e) {
            var el = e.target;
            if (!el) return null;
            if (el.getAttribute && el.getAttribute('data-bs-toggle') === 'tab') {
                return el;
            }
            return el.closest ? el.closest('[data-bs-toggle="tab"]') : null;
        }

        $(document).off('shown.bs.tab.dashFilaWorkspace', '#dashboard.dash-fila-workspace #myTab');
        $(document).on('shown.bs.tab.dashFilaWorkspace', '#dashboard.dash-fila-workspace #myTab', function(e) {
            var btn = tabButtonFromEvent(e);
            if (!btn) return;
            var paneId = btn.getAttribute('aria-controls') || (btn.getAttribute('data-bs-target') || '').replace('#', '');
            window.dashFilaLastTabKey = paneIdToKey(paneId);
            if (typeof dashFilaLive !== 'undefined') {
                dashFilaLive.refresh();
            }
        });

        var lastKey = window.dashFilaLastTabKey;
        if (lastKey) {
            var parts = lastKey.split('_');
            if (parts.length === 2) {
                var tabSelector = '#tab_' + parts[0] + '_' + parts[1];
                var paneSelector = '#div_' + parts[0] + '_' + parts[1];
                if ($(paneSelector).length) {
                    $('#myTab .nav-link').removeClass('active').attr('aria-selected', 'false');
                    $('#myTabContent .tab-pane').removeClass('show active');
                    $(tabSelector).addClass('active').attr('aria-selected', 'true');
                    $(paneSelector).addClass('show active');
                }
            }
        }
    })();
</script>