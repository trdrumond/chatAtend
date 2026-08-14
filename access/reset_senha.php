<?php
include_once("../access/conn_config.php");

$contrato = (string) ($_POST['contrato'] ?? '');
$login = (string) ($_POST['login'] ?? '');
$email = (string) ($_POST['email'] ?? '');

$sql = "SELECT value, pref from config where ativo=1 and value=?";
$stmt = $PDO_CONF->prepare($sql);
$stmt->execute([$contrato]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($dados) || empty($dados['pref']) || empty($dados['value'])) {
    echo '<div class="alert alert-danger">Contrato inválido.</div>';
    exit;
}

$prefSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $dados['pref']);
$valueSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $dados['value']);
$end = 'https://' . $prefSafe . '.logos-ma.com.br/chat-' . $valueSafe;
?>

<script>
resetSenha(<?= json_encode($login) ?>, <?= json_encode($email) ?>);

function resetSenha(login, email) {
    $("#feedback").html(
        '<div id="error" class="alert alert-info" role="alert"><center><img src="imagem/loading.gif" width="80"><br>Validando dados...</center></div>'
    );

    $.post(<?= json_encode($end . '/view/staff/newpass.php') ?>, {
            login,
            email
        },
        function(valor) {
            $("#feedback").html(valor);
        });

}
</script>
