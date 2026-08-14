<?php
include("../cnf/session.php");

$formId = (int) ($_POST['form_id'] ?? 0);
$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$demId = (int) ($_POST['dem_id'] ?? 0);
$pause = (int) ($_POST['pause'] ?? 0);
$userId = (int) ($_SESSION['dados']['id_user'] ?? 0);

$tableDados = 'tbl_in_dados_' . $formId . '_' . $contratoId;
$tableDem = 'tbl_in_dem_' . $formId . '_' . $contratoId;

if (
    $demId < 1
    || $formId < 1
    || !preg_match('/^tbl_in_dados_\d+_\d+$/', $tableDados)
    || !preg_match('/^tbl_in_dem_\d+_\d+$/', $tableDem)
) {
    return;
}

$stmt = $PDO->prepare(
    "SELECT id, dem_id, date_in, resp_id, timediff(now(), date_in) as sla"
    . " FROM tbl_tma_atend where dem_id=? and date_out is null"
);
$stmt->execute([$demId]);
$infoAtend = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $PDO->prepare("SELECT id_form_dados, dem_id, data_hora, resp_id FROM {$tableDados} where dem_id=?");
$stmt->execute([$demId]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($infoAtend) || !is_array($info)) {
    return;
}

$tmaId = (int) $infoAtend['id'];
$slaAtend = (string) ($infoAtend['sla'] ?? '00:00:00');

$stmt = $PDO->prepare("UPDATE tbl_tma_atend SET date_out=now(), sla=? WHERE id=?");
$stmt->execute([$slaAtend, $tmaId]);

$stmt = $PDO->prepare("SELECT sec_to_time(sum(time_to_sec(sla))) as sla FROM tbl_tma_atend where dem_id=?");
$stmt->execute([$demId]);
$infoGeral = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['sla' => '00:00:00'];
$ex = explode('.', (string) $infoGeral['sla']);
$slaGeral = $ex[0];

$stmt = $PDO->prepare("SELECT nome_campo FROM tbl_forms_dados_input_campo where form_id=?");
$stmt->execute([$formId]);
$campo = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($pause === 1) {
    $stmt = $PDO->prepare(
        "INSERT INTO tbl_pend_dados (dem_id, form_id, contrato_id, resp_id, motivo, tempo, obs)"
        . " VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $demId,
        $formId,
        $contratoId,
        $userId,
        (string) ($_POST['motivo_pend'] ?? ''),
        (string) ($_POST['tempo_pend'] ?? ''),
        (string) ($_POST['obs_pend'] ?? ''),
    ]);
}

$idFormDados = (int) $info['id_form_dados'];
$setParts = [];
$params = [];

if ($pause !== 1) {
    $setParts[] = 'data_hora_fim=now()';
    $setParts[] = 'sla=?';
    $params[] = $slaGeral;
} else {
    $setParts[] = 'data_hora_fim=null';
    $setParts[] = 'sla=null';
}

foreach ($campo as $c) {
    $nome = (string) ($c['nome_campo'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nome)) {
        continue;
    }
    $setParts[] = $nome . '=?';
    $params[] = (string) ($_POST[$nome] ?? '');
}

$params[] = $idFormDados;
$sqlAlterDados = 'UPDATE ' . $tableDados . ' SET ' . implode(', ', $setParts) . ' WHERE id_form_dados=?';
$stmt = $PDO->prepare($sqlAlterDados);
$resultDados = $stmt->execute($params);

if ($resultDados == 1) {
    if ($pause !== 1) {
        $stmt = $PDO->prepare("UPDATE {$tableDem} SET situacao_id=4, sla=? WHERE id_form_dem=?");
        $resultDem = $stmt->execute([$slaGeral, $demId]);
    } else {
        $stmt = $PDO->prepare("UPDATE {$tableDem} SET situacao_id=3, sla=null WHERE id_form_dem=?");
        $resultDem = $stmt->execute([$demId]);
    }

    if ($resultDem == 1) {
        if ($pause !== 0 && $pause !== 1 && $pause !== 99) {
            $stmt = $PDO->prepare("INSERT INTO tbl_pause (user_id, hora_in, pause_id) VALUES (?, now(), ?)");
            $stmt->execute([$userId, $pause]);
        }
        ?>

            <script>
                Swal.fire({
                    position: 'bottom-start',
                    icon: 'success',
                    title: 'Demanda finalizada com sucesso!',
                    showConfirmButton: false,
                    timer: 1500
                });

                <?php if ($pause === 99) { ?>
                    setTimeout(function(){ logout('sair'); }, 2000);
                    <?php } else { ?>
                        setTimeout(function(){ document.location.reload(true); }, 2000);
                    <?php } ?>

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

                function logout(action){
                    $.post("logout.php",
                    {
                        action: action
                    },
                    function (valor) {
                        $("#logout").html(valor);
                    });
                }

            </script>

        <?php
    }
}

?>

