<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/../cnf/_cnf_ui.php';
include('cnf/rotina_pendencia.php');

if ($infoUser['nivel_id'] == 4) {
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Indisponivel');
}

$cttOpts = '<option value="">Contrato</option>';
$sql = 'SELECT id_contrato, nome_contrato, uf, ativo from tbl_contrato where ativo=1 order by nome_contrato';
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($dados as $row) {
    $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}

$filaOpts = '<option value="">Fila</option>';
$hoje = date('Y-m-d');

st_page_open('Histórico', 'Histórico de atendimentos por contrato e período');
st_page_header_close();
?>

<script type="text/javascript">


    function loadDados(contrato, fila, de, ate){
            var div = '#dados_rel';
            $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
            $.post("staff/load_dados_hist.php",
            {
                contrato, fila, de, ate
            },
            function (valor) {
                $(div).html(valor);
            });

    }

    $(document).ready(function () {

        $("#btn_filter").click(function(){
            //console.log('Clicou no filtro');
            var de = $('#de').val();
            var ate = $('#ate').val();
            var contrato = $('#contrato').val();
            var fila = $('#fila').val();
            loadDados(contrato, fila, de, ate);
        });


        $("#contrato").change(function(){
            var contrato = $('#contrato').val();
            loadContrato(contrato);
        });

        function loadContrato(contrato){
            $("#fila").html('<option value="">Carregando filas...</option>');
            $.post("staff/load_rel_filas.php",
            {
                contrato: contrato
            },
            function (valor) {
                $("#fila").html(valor);
            });

        }

    });

</script>

<?php
st_filter_bar_open();
cnf_field_select('contrato', 'Contrato', $cttOpts);
cnf_field_select('fila', 'Fila', $filaOpts);
cnf_field_input('de', 'De', ['type' => 'date', 'value' => $hoje]);
cnf_field_input('ate', 'Até', ['type' => 'date', 'value' => $hoje]);
?>
<button type="button" id="btn_filter" class="btn btn-solvetask btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
<?php
st_filter_bar_close();
st_panel('dados_rel');
st_page_close();
?>
<script type="text/javascript" src="js/load.js"></script>
