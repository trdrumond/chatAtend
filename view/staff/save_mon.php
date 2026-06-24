<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($infoUser);
//depurador($_POST);

$arr_keys = array_keys($_POST);

/*
* 1 = text
* 3 = checkbok
* 4 = select
*/


$sql = "SELECT a.id_campo, a.nome_campo, a.input_id, b.ativo, b.qualif FROM tbl_forms_mon_input_campo a, tbl_forms_mon_input_campo_cnf b where a.id_campo=b.campo_id and b.ativo=1 and a.fila_id=".$_POST['fila_id'];
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$campo = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($campo);
$arrPt=array();
for($x=0;$x<count($campo);$x++){
    //$campo[$x]['nome_campo']
    //echo "<br>".$_POST[$campo[$x]['nome_campo']];
    if($campo[$x]['input_id']==3){
        $sqlOpt = "SELECT valor_mon_option from tbl_forms_mon_input_option where campo_id=".$campo[$x]['id_campo']." and value_option='".$_POST[$campo[$x]['nome_campo']]."'";
    }
    if($campo[$x]['input_id']==4){
        $sqlOpt = "SELECT valor_mon_option from tbl_forms_mon_input_option where campo_id=".$campo[$x]['id_campo']." and desc_option='".$_POST[$campo[$x]['nome_campo']]."'";
    }
    if($campo[$x]['input_id']==1){

        $sqlOpt = "SELECT valor_mon_option from tbl_forms_mon_input_option where campo_id=".$campo[$x]['id_campo'];
    }
    $stmt = $PDO->prepare($sqlOpt);
    $result = $stmt->execute();
    $opt = $stmt->fetch( PDO::FETCH_ASSOC );
    //echo " - ".$opt['valor_mon_option'];
    //echo " - ".$campo[$x]['nome_campo'];
    if($campo[$x]['input_id']!=1){
        $arrPt[$campo[$x]['nome_campo']] = $opt['valor_mon_option'];
    } else {
        if($_POST[$campo[$x]['nome_campo']]!=''){
            $arrPt[$campo[$x]['nome_campo']] = $opt['valor_mon_option'];
        } else {
            $arrPt[$campo[$x]['nome_campo']] = "0";
        }
    }



}

//depurador($arrPt);
$avaliacao = array_sum($arrPt);
//echo "<br>soma: ". $avaliacao ;

$sqlInsert ="INSERT INTO tbl_in_mon_".$_POST['fila_id']."_".$_POST['contrato_id'];
$sqlInsert .=" (data_hora, fila_id, chat_id, resp_mon, avaliacao";
for($x=0;$x<count($campo);$x++){
    $sqlInsert.= ", ".$campo[$x]['nome_campo']."";
    $sqlInsert.= ", pt_".$campo[$x]['nome_campo']."";
}
$sqlInsert .=") VALUES (now(),'".$_POST['fila_id']."', '".$_POST['chat_id']."', '".$_POST['resp_mon']."', '".$avaliacao."'";
for($x=0;$x<count($campo);$x++){
    $sqlInsert.= ",'".$_POST[$campo[$x]['nome_campo']]."'";
    $arrPt[$campo[$x]['nome_campo']] = ($arrPt[$campo[$x]['nome_campo']]==null) ? "0" : $arrPt[$campo[$x]['nome_campo']];
    $sqlInsert.= ",'".$arrPt[$campo[$x]['nome_campo']]."'";
}
$sqlInsert .=")";


//echo "<br><br>".$sqlInsert;

$stmt = $PDO->prepare( $sqlInsert );
$resultDados = $stmt->execute();
//echo $resultDados;

if($resultDados==1){
    ?>
        <script>

            var id_chat = '<?=$_POST['chat_id'];?>';
            var contrato = '<?=$_POST['contrato_id'];?>';
            var fila = '<?=$_POST['fila_id'];?>';
            var div_mon = '#monitoria_' + id_chat;
            $(div_mon).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

            $.post("staff/load_monitoria.php",
            {
                id_chat, contrato, fila
            },
            function (valor) {
                //$(div_mon).show('slow');

                $(div_mon).html(valor);

            });



        </script>
    <?php

}

?>
