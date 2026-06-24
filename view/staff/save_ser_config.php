<?php
include("../cnf/session.php");

if($_POST['nome_campo']!=''){

    $sql="INSERT INTO tbl_servicos_input_campo (servico_id, input_id, desc_campo, nome_campo) VALUES ('".$_POST['id_servico']."', '".$_POST['id_input']."', '".$_POST['nome_campo']."', '".nomeCampoInput($_POST['nome_campo'])."')";

    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    if($result==1){
        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
        ?>
            <script>
                load(<?php echo $_POST['id_servico']; ?>);
                $("#servico_id_<?php echo $_POST['id_servico']; ?>").val('');
                $("#nome_campo_<?php echo $_POST['id_servico']; ?>").val('');

                function load(id_servicos){

                    $("#tbl_<?php echo $_POST['id_servico']; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                        $.post("staff/tbl_config_servicos.php",
                    {
                        id_servicos: id_servicos
                    },
                    function (valor) {
                        $("#tbl_<?php echo $_POST['id_servico']; ?>").html(valor);
                    });

                }
            </script>
        <?php
    }

} else {
    echo "<br>Preencha os campos corretamente!";
}


?>
