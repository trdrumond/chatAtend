<?php
include("../cnf/session.php");
//include("../cnf/replace.php");

//depurador($_POST);

$sqlData = " and date_format(data_hora, '%Y-%m-%d')=curdate()";

if($_POST['contrato_id']!=0){

    $sql = "SELECT sec_to_time(avg(time_to_sec(te))) as sla FROM tbl_chat_fila where status_fila=4 $sqlData  and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['fila_id'];
    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );
    $cntHoje = $infoGeral['sla'];
    $ex = explode(".", $cntHoje);
    $cntHoje = $ex[0];
    $cntHoje = ($cntHoje=='') ? '--:--:--' : $cntHoje;

    //echo $cntHoje;
} else {
    $contratos=$infoUserConfig['contrato_id'];



    $sql="SELECT sec_to_time(avg(time_to_sec(te))) as sla from tbl_chat_fila where status_fila=4 $sqlData and contrato_id in (".$contratos.")";
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



$table_tme ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sum=0;
$contratos=$infoUserConfig['contrato_id'];
$sql="SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(te))) as tme from tbl_chat_fila where te is not null $sqlData and fila_id=id_fila) as tme from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tme']);

        $table_tme.= "<tr>";
            $table_tme.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
            $table_tme.= "<td>". $ex[0]."</td>";
        $table_tme.= "</tr>";
    }
}
$table_tme.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;

?>


<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosTme').html(valor);
var table_tme = '<?php echo $table_tme; ?>';
$('#list-tme').html(table_tme);
</script>