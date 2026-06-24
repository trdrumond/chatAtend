<?php
include("../cnf/session.php");
//include("../cnf/replace.php");
include('../cnf/rotina_pendencia.php');

//depurador($_POST);

if($_POST['contrato_id']!=0){
    $sql = "SELECT count(*) as qtd FROM tbl_in_dem_".$_POST['form_id']."_".$_POST['contrato_id']." where situacao_id=3";
    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );

    $infoGeral['qtd'] = ($infoGeral['qtd']=='')? 0 : $infoGeral['qtd'];

    echo $infoGeral['qtd'];
} else {
    $contratos=$infoUserConfig['contrato_id'];
    $sql="SELECT a.id_contrato, concat(a.nome_contrato, '-', a.uf) as nome, b.id_form, b.nome_forms from tbl_contrato a, tbl_forms_dem b where a.ativo=1 and a.id_contrato in (".$contratos.") and b.contrato_id=a.id_contrato and b.ativo=1 order by nome";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
    $count=0;
    for($z=0;$z<count($dadosContratos);$z++){
        if($dadosContratos[$z]['id_contrato']!='' && $dadosContratos[$z]['id_form']!=''){
            $sql = "SELECT count(*) as qtd FROM tbl_in_dem_".$dadosContratos[$z]['id_form']."_".$dadosContratos[$z]['id_contrato']." where situacao_id=3";
            //echo "<br><br>".$sql;
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $info = $stmt->fetch( PDO::FETCH_ASSOC );
            $count = $count + $info['qtd'];
            //echo "<br>".$count;

        }
    }
    $count = ($count=='') ? '---' : $count;
    echo $count;
}





?>
