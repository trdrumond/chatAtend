<?php
include("../cnf/session.php");
//include("../cnf/replace.php");

//depurador($_POST);

$sqlData = " and date_format(data_hora, '%Y-%m-%d')=curdate()";

if($_POST['contrato_id']!=0){
    if($_POST['resp_id']!=''){
        $qry=" and bko_resp=".$_POST['resp_id'];
    }


    $sql = "SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' $sqlData and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['form_id']." $qry";

    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoUser = $stmt->fetch( PDO::FETCH_ASSOC );

    $sql = "SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' $sqlData  and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['form_id'];
    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );

    if($nivel_usu<4){
        //echo $infoGeral['sla'];
        $cntHoje = $infoGeral['sla'];

    } else {
        //echo $infoUser['sla'];
        $cntHoje = $infoUser['sla'];
    }
} else {
    $contratos=$infoUserConfig['contrato_id'];



    $sql="SELECT sec_to_time(avg(time_to_sec(ta))) as sla from tbl_chat_fila where status_fila>=4 and hora_fim<>'' $sqlData and contrato_id in (".$contratos.")";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dadosHoje = $stmt->fetch( PDO::FETCH_ASSOC );
    $cntHoje = $dadosHoje['sla'];
    $ex = explode(".", $cntHoje);
    $cntHoje = $ex[0];
    $cntHoje = ($cntHoje=='') ? '--:--:--' : $cntHoje;

    //echo $cntHoje;
}


$table_tma ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sum=0;
$contratos=$infoUserConfig['contrato_id'];
$sql="SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(ta))) as tma from tbl_chat_fila where ta is not null $sqlData and fila_id=id_fila) as tma from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tma']);

        $table_tma.= "<tr>";
            $table_tma.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
            $table_tma.= "<td>". $ex[0]."</td>";
        $table_tma.= "</tr>";
    }
}
$table_tma.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;

?>


<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosTma').html(valor);
var table_tma = '<?php echo $table_tma; ?>';
$('#list-tma').html(table_tma);
</script>