<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';
include('cnf/rotina_pendencia.php');

if ($infoUser['nivel_id'] == 4) {
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Indisponivel');
}

if ($_SESSION['dados']['nivel_id'] == 4) {
    $btn_back = 'dash-ate';
} else {
    $btn_back = 'dash-fila';
}

cnf_page_open('Log de Acesso', 'Histórico de acessos e atividades por dia');
cnf_page_header_close();
?>

<script type="text/javascript">
    function actionPage(action, sec) {
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
        $.post("action.php", { action: action, sec: sec }, function(valor) {
            $("#action-page").html(valor);
        });
    }

    function loadInd(dia, user) {
        var div = '#dados_log';
        $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
        $.post("staff/log_dados.php", { dia }, function(valor) {
            $(div).html(valor);
        });
    }
</script>

<div class="cnf-filter-bar st-form">
    <?php cnf_field_input('dia', 'Dia', ['type' => 'date', 'value' => date('Y-m-d'), 'required' => true]); ?>
    <button type="button" id="btn_filter" class="btn btn-solvetask btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
</div>

<div id="dados_log" class="cnf-log-panel"></div>

<script>
    loadInd('<?= date('Y-m-d') ?>');
</script>

<script>
$(document).ready(function() {
    $("#btn_filter").click(function() {
        var dia = $('#dia').val();
        $("#dados_log").html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
        loadInd(dia);
    });
});
</script>

<script type="text/javascript" src="js/load.js"></script>
</div>
