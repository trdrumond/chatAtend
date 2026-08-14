<?php
include("../cnf/session.php");

function procuraProtocol($PDO)
{
    $protocol = date('ymd') . rand(1, 99) . time_to_sec(date('H:i:s'));
    $sql = "SELECT protocolo from tbl_chat_fila where protocolo =?";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$protocol]);
    $ddprt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (($ddprt['protocolo'] ?? '') === '') {
        return $protocol;
    }

    return procuraProtocol($PDO) . rand(0, 99);
}

$filaId = (int) ($_POST['fila'] ?? 0);
$assuntoId = (int) ($_POST['assunto'] ?? 0);
$motivo = (string) ($_POST['motivo'] ?? '');

$stmt = $PDO->prepare("SELECT ativo from tbl_config_fila where id_fila=?");
$stmt->execute([$filaId]);
$verFila = $stmt->fetch(PDO::FETCH_ASSOC);

if (($verFila['ativo'] ?? '') == 0) {
    echo '<br><div style="color: red">A fila selecionada não esta mais ativa!</div>';
    echo '<script>
            $("#call").prop( "disabled", false );
            setTimeout(() => {
                document.location.reload(true);
                }, "1000");
          </script>';
} else {
    $protocolo = procuraProtocol($PDO);
    if ($protocolo === '') {
        $protocolo = procuraProtocol($PDO);
    }

    $stmt = $PDO->prepare(
        "INSERT INTO tbl_chat_fila (protocolo, contrato_id, fila_id, assunto_id, ate_resp, motivo) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $result = $stmt->execute([
        $protocolo,
        $infoUser['contrato_id'],
        $filaId,
        $assuntoId,
        $infoUser['id_user'],
        $motivo,
    ]);

    if ($result == 1) {
    ?>
    <script>

        actionPage('chat-fila', 'idx');



        function actionPage(action, sec){
            $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
            $.post("action.php",
            {
                action: action, sec: sec
            },
            function (valor) {
                $("#action-page").html(valor);
            });
        }


    </script>
<?php
    }
}
    ?>
