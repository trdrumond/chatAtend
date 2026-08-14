<?php
include("../cnf/session.php");

$nomeCampo = (string) ($_POST['nome_campo'] ?? '');
$servicoId = (int) ($_POST['id_servico'] ?? 0);
$inputId = (int) ($_POST['id_input'] ?? 0);

if ($nomeCampo !== '' && $servicoId > 0 && $inputId > 0) {
    $sql = "INSERT INTO tbl_servicos_input_campo (servico_id, input_id, desc_campo, nome_campo) VALUES (?, ?, ?, ?)";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$servicoId, $inputId, $nomeCampo, nomeCampoInput($nomeCampo)]);

    if ($result == 1) {
        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
        ?>
            <script>
                load(<?php echo $servicoId; ?>);
                $("#servico_id_<?php echo $servicoId; ?>").val('');
                $("#nome_campo_<?php echo $servicoId; ?>").val('');

                function load(id_servicos){
                    $("#tbl_<?php echo $servicoId; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                        $.post("staff/tbl_config_servicos.php",
                    {
                        id_servicos: id_servicos
                    },
                    function (valor) {
                        $("#tbl_<?php echo $servicoId; ?>").html(valor);
                    });
                }
            </script>
        <?php
    }
} else {
    echo "<br>Preencha os campos corretamente!";
}

?>

