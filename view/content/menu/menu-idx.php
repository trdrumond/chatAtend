<?php
include("../cnf/session.php");
if (!isset($menu_idx) || !is_array($menu_idx)) {
    $menu_idx = array_fill(0, 7, '0');
}
if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}
?>
<?php
    if ($_SESSION["dados"]['nivel_id'] == "4") {
        $btn_dash = 'dash-idx';
    } else {
        $btn_dash = 'dash-fila';
    }
?>

<?php if ($_SESSION["dados"]['nivel_id'] == "4") { ?>
<div id="menu_bko">
    <?php if ($menu_idx[1] == '1') { ?>
    <span class="span-menu" id="dash-ate"><i class="fas fa-tasks"></i> Fila</span>
    <?php } ?>
    <?php if ($menu_idx[2] == '1') { ?>
    <span class="span-menu" id="my-score"><i class="fas fa-chart-bar"></i> Meu Score</span>
    <?php } ?>
    <?php if ($menu_idx[4] == '1') { ?>
    <span class="span-menu" id="hist-dash"><i class="fas fa-folder-open"></i> Histórico</span>
    <?php } ?>
    <?php if ($menu_idx[5] == '1') { ?>
    <span class="span-menu" id="hist-pend"><i class="fas fa-exclamation-circle"></i> Pendências <span id="dadosPendMen"></span></span>
    <?php } ?>
    <?php if ($infoUser['comunicacao'] == 1) { ?>
    <span class="span-menu" id="com-idx"><i class="fab fa-rocketchat"></i> Comunicação <span id="countCom"></span></span>
    <?php } ?>
</div>
<?php } ?>

<?php if ($_SESSION["dados"]['nivel_id'] == "5") { ?>
<div id="menu_ate">
    <?php if ($menu_idx[1] == '1') { ?>
    <span class="span-menu" id="dash-cha"><i class="fas fa-tasks"></i> Fila</span>
    <?php } ?>
    <?php if ($menu_idx[4] == '1') { ?>
    <span class="span-menu" id="hist-dash"><i class="fas fa-folder-open"></i> Histórico</span>
    <?php } ?>
    <?php if ($menu_idx[5] == '1') { ?>
    <span class="span-menu" id="hist-pend"><i class="fas fa-exclamation-circle"></i> Pendências <span id="dadosPendMen"></span></span>
    <?php } ?>
    <?php if ($infoUser['comunicacao'] == 1) { ?>
    <span class="span-menu" id="com-idx"><i class="fab fa-rocketchat"></i> Comunicação <span id="countCom"></span></span>
    <?php } ?>
</div>
<?php } ?>

<?php if ($_SESSION["dados"]['nivel_id'] != "4" && $_SESSION["dados"]['nivel_id'] != "5") { ?>
<?php if ($menu_idx[0] == '1') { ?>
<span class="span-menu" id="dash-fila"><i class="fas fa-tachometer-alt"></i> Dashboard</span>
<?php } ?>
<?php if (!empty($menu_idx[6]) && $menu_idx[6] == '1') { ?>
<span class="span-menu" id="gov-analytics"><i class="fas fa-shield-alt"></i> Governança</span>
<span class="span-menu" id="ia-insights"><i class="fas fa-robot"></i> Insights IA</span>
<?php } ?>
<?php if ($menu_idx[1] == '1') { ?>
<span class="span-menu" id="rel-fila"><i class="fas fa-tasks"></i> Fila</span>
<?php } ?>
<?php if ($menu_idx[2] == '1') { ?>
<span class="span-menu" id="rel-dash"><i class="fas fa-chart-line"></i> Relatórios</span>
<?php } ?>
<?php if ($menu_idx[3] == '1') { ?>
<span class="span-menu" id="rel-ind"><i class="fas fa-chart-line"></i> Indicadores</span>
<?php } ?>
<?php if ($menu_idx[4] == '1') { ?>
<span class="span-menu" id="hist-dash"><i class="fas fa-folder-open"></i> Histórico</span>
<?php } ?>
<?php if ($menu_idx[5] == '1') { ?>
<span class="span-menu" id="hist-pend"><i class="fas fa-exclamation-circle"></i> Pendências</span>
<?php } ?>
<?php if ($infoUser['comunicacao'] == 1) { ?>
<span class="span-menu" id="com-idx"><i class="fab fa-rocketchat"></i> Comunicação <span id="countCom"></span></span>
<?php } ?>
<?php } ?>

<span class="span-menu" id="sair"><i class="fas fa-door-open"></i> Sair</span>
<div id="logout"></div>

<script>
$("#my-score").click(function () {
    actionPageScore('dash-scor', 'idx', '<?= $_SESSION["dados"]['id_user'] ?>');
});
</script>
