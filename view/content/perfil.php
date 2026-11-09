<?php
if (!isset($infoUser) || !is_array($infoUser)) {
    include(__DIR__ . '/../cnf/session.php');
}
if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}

$nomeExibir   = ucwords(strtolower(trim((string) ($infoUser['nome_completo'] ?? ''))));
$nomePartes   = $nomeExibir !== '' ? preg_split('/\s+/', $nomeExibir) : [];
$nomePrimeiro = $nomePartes[0] ?? '';
$nomeResto    = count($nomePartes) > 1 ? end($nomePartes) : '';
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
    <span class="perfil-nome" title="<?= htmlspecialchars($nomeExibir) ?>">
        <span class="perfil-nome__linha"><?= htmlspecialchars($nomePrimeiro) ?></span>
        <?php if ($nomeResto !== '') { ?>
        <span class="perfil-nome__linha"><?= htmlspecialchars($nomeResto) ?></span>
        <?php } ?>
    </span>
    <span class="perfil-nivel" title="<?= htmlspecialchars($nivelLabel) ?>">
        <?php if ($iconLabel !== '') { ?>
        <span class="perfil-nivel__icone" aria-hidden="true"><?= $iconLabel ?></span>
        <?php } ?>
        <span class="perfil-nivel__texto"><?= htmlspecialchars($nivelLabel) ?></span>
    </span>

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
