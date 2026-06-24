<?php
if (!isset($infoUser) || !is_array($infoUser)) {
    include(__DIR__ . '/../cnf/session.php');
}
if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}

$nomeExibir   = ucwords(strtolower($infoUser['nome_completo'] ?? ''));
$nivelLabel   = $infoUser['nivel'] ?? '';
$iconLabel    = $infoUser['icon'] ?? '';
$imgPerfil    = $infoUser['img_perfil'] ?? 'img/default-avatar.png';
?>

<div id="img-perfil">
    <div id="user_img_perfil">
        <img src="<?= htmlspecialchars($imgPerfil) ?>"
             class="rounded-circle"
             alt="Foto de perfil"
             onerror="this.src='img/default-avatar.png'">
    </div>
    <span class="perfil-nome"><?= htmlspecialchars($nomeExibir) ?></span>
    <span class="perfil-nivel"><?= $iconLabel ?> <?= htmlspecialchars($nivelLabel) ?></span>

    <?php if ($_GET['sec'] === 'idx') { ?>
    <div class="bloco_status_server">
        <div id="statusServer" title="Status da conexão">
            <div id="sinal_server" class="signal status_neutro"></div>
        </div>
    </div>
    <?php if ($infoUser['id_user'] == 1) { ?>
    <div id="dadosLogados" title="Usuários logados">---</div>
    <?php } ?>
    <?php } ?>

    <?php if ($infoUser['nivel_id'] == 4) { ?>
    <div id="star" class="perfil-star"> -- </div>
    <?php } ?>
</div>

<div id="menu">
    <?php include("content/menu/menu-" . $_GET['sec'] . ".php"); ?>
</div>

<?php if ($infoUser['nivel_id'] == 4) { ?>
<script>
function loadStar(resp_id, fila_id, contrato_id) {
    $.post("staff/load_star.php", { resp_id, fila_id, contrato_id }, function (valor) {
        $("#star").html(valor);
    });
}
</script>
<?php } ?>
