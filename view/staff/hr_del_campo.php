<?php
include("../cnf/session.php");

$idHr = (int) ($_POST['id'] ?? 0);
$filaId = (int) ($_POST['id_fila'] ?? 0);

if ($idHr < 1) {
    return;
}

$sql = "DELETE FROM tbl_fila_horario WHERE id_hr=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$idHr]);

if ($result == 1) {
    echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
    ?>
         <script>
            load(<?php echo $filaId; ?>);

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

?>

