<?php
include("../cnf/session.php");
include('../cnf/rotina_pendencia.php');

$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$formId = (int) ($_POST['form_id'] ?? 0);

if ($contratoId != 0) {
    $tableDem = 'tbl_in_dem_' . $formId . '_' . $contratoId;
    if (!preg_match('/^tbl_in_dem_\d+_\d+$/', $tableDem)) {
        echo 0;
        return;
    }
    $sql = "SELECT count(*) as qtd FROM {$tableDem} where situacao_id=3";
    $stmt = $PDO->prepare($sql);
    $stmt->execute();
    $infoGeral = $stmt->fetch(PDO::FETCH_ASSOC);

    $infoGeral['qtd'] = (($infoGeral['qtd'] ?? '') == '') ? 0 : $infoGeral['qtd'];

    echo $infoGeral['qtd'];
} else {
    $cttBind = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql = "SELECT a.id_contrato, concat(a.nome_contrato, '-', a.uf) as nome, b.id_form, b.nome_forms from tbl_contrato a, tbl_forms_dem b where a.ativo=1 and a.id_contrato in (" . $cttBind['ph'] . ") and b.contrato_id=a.id_contrato and b.ativo=1 order by nome";
    $stmt = $PDO->prepare($sql);
    $stmt->execute($cttBind['ids']);
    $dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 0;
    for ($z = 0; $z < count($dadosContratos); $z++) {
        if ($dadosContratos[$z]['id_contrato'] != '' && $dadosContratos[$z]['id_form'] != '') {
            $tableDem = 'tbl_in_dem_' . (int) $dadosContratos[$z]['id_form'] . '_' . (int) $dadosContratos[$z]['id_contrato'];
            if (!preg_match('/^tbl_in_dem_\d+_\d+$/', $tableDem)) {
                continue;
            }
            $sql = "SELECT count(*) as qtd FROM {$tableDem} where situacao_id=3";
            $stmt = $PDO->prepare($sql);
            $stmt->execute();
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $count + (int) ($info['qtd'] ?? 0);
        }
    }
    $count = ($count == '') ? '---' : $count;
    echo $count;
}

?>
