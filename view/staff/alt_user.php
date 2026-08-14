<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

$idUser = (int) ($_POST['id'] ?? 0);
if ($idUser <= 0) {
    echo '<div style="color: red">Usuário inválido.</div>';
    exit;
}

$agenciaId = (int) ($_POST['agencia'] ?? 0);
if ($agenciaId <= 0) {
  echo '<div style="color: red">Agência obrigatória e deve ser válida (id &gt; 0).</div>';
  exit;
}

$stmtTgt = $PDO->prepare("SELECT contrato_id from tbl_user where id_user=?");
$stmtTgt->execute([$idUser]);
$tgt = $stmtTgt->fetch(PDO::FETCH_ASSOC);
if (!is_array($tgt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($tgt['contrato_id'] ?? 0))) {
  echo '<div style="color: red">Contrato não autorizado.</div>';
  exit;
}

$contrato = (int) ($_POST['contrato'] ?? 0);
if ($contrato < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
  echo '<div style="color: red">Contrato não autorizado.</div>';
  exit;
}

$sets = [
    'nome=?',
    'sobrenome=?',
    'email=?',
    'uf_id=?',
    'municipio_id=?',
    'contrato_id=?',
    'regional_id=?',
    'empresa_id=?',
    'agencia_id=?',
    'fila_id=?',
    'ativo=?',
    'data_update=curdate()',
];
$params = [
    (string) ($_POST['nome'] ?? ''),
    (string) ($_POST['sobrenome'] ?? ''),
    (string) ($_POST['email'] ?? ''),
    (int) ($_POST['uf'] ?? 0),
    (int) ($_POST['municipio'] ?? 0),
    $contrato,
    (int) ($_POST['regional'] ?? 0),
    (int) ($_POST['empresa'] ?? 0),
    $agenciaId,
    (int) ($_POST['fila'] ?? 0),
    (int) ($_POST['ativo'] ?? 0),
];

if ((string) ($_POST['nivel'] ?? '') !== '') {
    $sets[] = 'nivel_id=?';
    $params[] = (int) $_POST['nivel'];
}

if ((string) ($_POST['ativo'] ?? '') === '0') {
    $sets[] = 'data_inativo=now()';
} else {
    $sets[] = 'data_inativo=null';
}

$params[] = $idUser;
$sql = 'UPDATE tbl_user SET ' . implode(', ', $sets) . ' WHERE id_user=?';
$stmt = $PDO->prepare($sql);
$result = $stmt->execute($params);

if ($result == 1) {
    clearUserLayoutCache($idUser);
?>
<script>
$("#modal_alt").modal('hide');
actionPage('cad-usu', 'cnf');



function actionPage(action, sec) {
    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
    //console.log('A ação é: ' + action);
    $.post("action.php", {
            action: action,
            sec: sec
        },
        function(valor) {
            $("#action-page").html(valor);
        });
}
</script>
<?php
    }

    ?>
