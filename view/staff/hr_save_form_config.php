<?php
include("../cnf/session.php");
//id_fila, inicio_hr, fim_hr

if($_POST['inicio_hr']!='' && $_POST['fim_hr']!=''){

    $_POST['inicio_hr'] = $_POST['inicio_hr'].":00";
    $_POST['fim_hr'] = $_POST['fim_hr'].":00";

    $sql="INSERT INTO tbl_fila_horario (inicio_hr, fim_hr, fila_id) VALUES ('".$_POST['inicio_hr']."','".$_POST['fim_hr']."','".$_POST['id_fila']."')";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    if($result==1){

        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
        ?>
             <script>
                load(<?php echo $_POST['id_fila']; ?>);
                $("#inicio_hr_<?php echo $_POST['id_fila']; ?>").val('');
                $("#fim_hr_<?php echo $_POST['id_fila']; ?>").val('');

                function load(id_filas){

                    $("#tbl_hr_<?php echo $_POST['id_fila']; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                        $.post("staff/hr_tbl_config_form.php",
                    {
                        id_filas: id_filas
                    },
                    function (valor) {
                        $("#tbl_hr_<?php echo $_POST['id_fila']; ?>").html(valor);
                    });

                }
             </script>
        <?php
    }


} else {
    echo "<br>Preencha os campos corretamente!";
}



?>
