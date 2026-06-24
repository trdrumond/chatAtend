<?php
include("../cnf/session.php");
//id_fila, inicio_hr, fim_hr



    $sql="DELETE FROM tbl_fila_horario WHERE id_hr=".$_POST['id'];
    echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    if($result==1){

        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
        ?>
             <script>
                load(<?php echo $_POST['id_fila']; ?>);

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





?>
