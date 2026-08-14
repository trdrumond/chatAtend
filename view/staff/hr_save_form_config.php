<?php
include("../cnf/session.php");

$filaId = (int) ($_POST['id_fila'] ?? 0);
$inicioHr = (string) ($_POST['inicio_hr'] ?? '');
$fimHr = (string) ($_POST['fim_hr'] ?? '');

if ($inicioHr !== '' && $fimHr !== '' && $filaId > 0) {
    $inicioHr .= ':00';
    $fimHr .= ':00';

    $sql = "INSERT INTO tbl_fila_horario (inicio_hr, fim_hr, fila_id) VALUES (?, ?, ?)";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$inicioHr, $fimHr, $filaId]);

    if ($result == 1) {
        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
        ?>
             <script>
                load(<?php echo $filaId; ?>);
                $("#inicio_hr_<?php echo $filaId; ?>").val('');
                $("#fim_hr_<?php echo $filaId; ?>").val('');

                function load(id_filas){
                    $("#tbl_hr_<?php echo $filaId; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                        $.post("staff/hr_tbl_config_form.php",
                    {
                        id_filas: id_filas
                    },
                    function (valor) {
                        $("#tbl_hr_<?php echo $filaId; ?>").html(valor);
                    });
                }
             </script>
        <?php
    }
} else {
    echo "<br>Preencha os campos corretamente!";
}

?>

