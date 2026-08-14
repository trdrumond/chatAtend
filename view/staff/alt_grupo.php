<?php
include("../cnf/session.php");

if ((int) ($infoUser['nivel_id'] ?? 99) >= 1) {
    return;
}

$comId = (int) ($_POST['com'] ?? 0);
$nomeGrupo = (string) ($_POST['nome_grupo'] ?? '');

if ($comId < 1) {
    return;
}

$stmtGrupo = $PDO->prepare("SELECT id_com, contrato_id, grupo_com from tbl_com_info where id_com=?");
$stmtGrupo->execute([$comId]);
$infoGrupo = $stmtGrupo->fetch(PDO::FETCH_ASSOC);
if (!is_array($infoGrupo) || empty($infoGrupo['id_com'])) {
    return;
}

$contrato = (int) ($infoGrupo['contrato_id'] ?? 0);
if ($contrato < 1) {
    return;
}
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
    return;
}

$colParts = ['1'];
$userId = (int) ($infoUser['id_user'] ?? 0);
if ($userId !== 1) {
    $colParts[] = (string) $userId;
}

$included = [1 => true];
if ($userId > 0) {
    $included[$userId] = true;
}

$stmtDest = $PDO->prepare("SELECT id_user, contrato_id FROM tbl_user WHERE id_user=?");
$colIds = array_values(array_filter(array_map('intval', (array) ($_POST['col'] ?? []))));
foreach ($colIds as $colId) {
    if ($colId < 1 || $colId === $userId || isset($included[$colId])) {
        continue;
    }
    $stmtDest->execute([$colId]);
    $destUser = $stmtDest->fetch(PDO::FETCH_ASSOC);
    if (!is_array($destUser) || empty($destUser['id_user'])) {
        continue;
    }
    $contratoDest = (int) ($destUser['contrato_id'] ?? 0);
    if ($contratoDest < 1) {
        continue;
    }
    if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoDest)) {
        continue;
    }
    $colParts[] = (string) $colId;
    $included[$colId] = true;
}

$cols = "'" . implode("','", $colParts) . "'";

$sql = "UPDATE tbl_com_info SET grupo_nome=? where id_com=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$nomeGrupo, $comId]);

if (!empty($_POST['col'])) {
    $sql = 'UPDATE tbl_com_config SET cols=?, equipe_adm=0, equipe_bko=0, equipe_ate=0 where grupo_com_id=?';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$cols, $comId]);
}

if ($result == 1) {
    $modalId = json_encode((string) $comId);
?>

<script>
$("#alt_group_" + <?= $modalId ?>).modal('hide');
Swal.fire({
    position: 'bottom-start',
    icon: 'success',
    title: 'Grupo Alterado!',
    showConfirmButton: false,
    timer: 1500
});

setTimeout(() => {
    actionPage('com-idx', 'idx');
    loadComList(0, <?= $modalId ?>);
}, "1000");

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
