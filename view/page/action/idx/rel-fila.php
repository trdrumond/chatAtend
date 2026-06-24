<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/../cnf/_cnf_ui.php';
include('cnf/rotina_pendencia.php');

$cttOpts = '<option value="">Contrato</option>';
$sql = 'SELECT id_contrato, nome_contrato, uf, ativo from tbl_contrato where ativo=1 order by nome_contrato';
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($dados as $row) {
    $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}

$filaOpts = '<option value="">Fila</option>';

st_page_open('Fila de Relatórios', 'Visualização da fila por contrato');
st_page_header_close();
?>

<script type="text/javascript">


    function loadDados(contrato, fila){
            var div = '#dados_rel';
            $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
            $.post("staff/load_dados_rel_fila.php",
            {
                contrato, fila
            },
            function (valor) {
                $(div).html(valor);
            });

    }

    $(document).ready(function () {

        $("#btn_filter").click(function(){
            //console.log('Clicou no filtro');
            var referencia = $('#referencia').val();
            var contrato = $('#contrato').val();
            var fila = $('#fila').val();
            loadDados(contrato, fila, referencia);
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
?>
<button type="button" id="btn_filter" class="btn btn-solvetask btn-sm"><i class="fas fa-filter"></i> Ver Fila</button>
<?php
st_filter_bar_close();
st_panel('dados_rel');
st_page_close();
?>
<script type="text/javascript" src="js/load.js"></script>
