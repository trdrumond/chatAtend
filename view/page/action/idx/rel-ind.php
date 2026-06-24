<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/../cnf/_cnf_ui.php';
include('cnf/rotina_pendencia.php');

if ($infoUser['nivel_id'] == 4) {
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Indisponivel');
}

if ($_SESSION['dados']['nivel_id'] == 4) {
    $btn_back = 'dash-ate';
} else {
    $btn_back = 'dash-fila';
}

$hoje = date('Y-m-d');

st_page_open('Indicadores', 'Indicadores de desempenho por dia');
st_page_header_close();
?>

<script type="text/javascript">

    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
        $.post("action.php",
        {
            action: action, sec: sec
        },
        function (valor) {
            $("#action-page").html(valor);
        });
    }

    function loadInd(dia){
        var div = '#dados_user';
        $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
        $.post("staff/load_dados_ind.php",
        {
            dia
        },
        function (valor) {
            $(div).html(valor);
        });
    }

</script>

<?php
st_filter_bar_open();
cnf_field_input('dia', 'Dia', ['type' => 'date', 'value' => $hoje, 'required' => true]);
?>
<button type="button" id="btn_filter" class="btn btn-solvetask btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
<?php
st_filter_bar_close();
st_panel('dados_user');
?>

<script>
    loadInd('<?= $hoje ?>');
</script>

<script>
    $(document).ready(function () {

        $("#btn_filter").click(function(){
            //console.log('Clicou no filtro');
            var dia = $('#dia').val();


                    $("#dados_user").html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                    //$('#tempo-decorrido').hide();
                    loadInd(dia);
                }

        );

    });
</script>

<?php st_page_close(); ?>
<script type="text/javascript" src="js/load.js"></script>
