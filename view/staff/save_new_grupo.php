<?php
include("../cnf/session.php");

if ((int) ($infoUser['nivel_id'] ?? 99) >= 1 || (int) ($infoUser['grupos'] ?? 0) !== 1) {
    return;
}

$contratoSessao = (int) ($infoUser['contrato_id'] ?? 0);
if ($contratoSessao < 1) {
    return;
}
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoSessao)) {
    return;
}

$colsIds = [1];
$userId = (int) ($infoUser['id_user'] ?? 0);
if ($userId != 1) {
    $colsIds[] = $userId;
}

$colPost = $_POST['col'] ?? [];
if (!is_array($colPost)) {
    $colPost = [$colPost];
}

$stmtDest = $PDO->prepare("SELECT id_user, contrato_id FROM tbl_user WHERE id_user=?");
$included = array_flip($colsIds);
for ($x = 0; $x < count($colPost); $x++) {
    $colId = (int) $colPost[$x];
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
    $colsIds[] = $colId;
    $included[$colId] = true;
}
$cols = "'" . implode("','", $colsIds) . "'";

$stmt = $PDO->prepare("SELECT count(grupo_com) as qtd from tbl_com_info where grupo_com<>''");
$result = $stmt->execute();
$qtd_group = $stmt->fetch(PDO::FETCH_ASSOC);
$qtd_group = ($qtd_group['qtd'] ?? 0) + 1;

$sql = 'INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES (?, ?, ?, ?, ?)';
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([
    $contratoSessao,
    0,
    0,
    $qtd_group,
    (string) ($_POST['nome_grupo'] ?? ''),
]);

if ($result == 1) {
    $stmt = $PDO->prepare("SELECT id_com from tbl_com_info where grupo_com=?");
    $result = $stmt->execute([$qtd_group]);
    $com_group = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = 'INSERT INTO tbl_com_config (grupo_com_id, cols) VALUES (?, ?)';
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$com_group['id_com'] ?? 0, $cols]);

    if ($result == 1) {
?>
    <script>

        Swal.fire({
            position: 'bottom-start',
            icon: 'success',
            title: 'Novo grupo criado!',
            showConfirmButton: false,
            timer: 1500
        });
        $("#new_group").modal('hide');
        actionPage('com-idx', 'idx');



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
