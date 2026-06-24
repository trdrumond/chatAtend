<?php
include("../cnf/session.php");

//depurador($_POST);


if($_POST['contrato_id']!=0){

    $sql = "SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=2  and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['fila_id'];
    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );

    $cntHoje = $infoGeral['qtd'];
} else {
    $contratos=$infoUserConfig['contrato_id'];



    $sql="SELECT count(*) as qtd from tbl_chat_fila where status_fila=2 and contrato_id in (".$contratos.")";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dadosHoje = $stmt->fetch( PDO::FETCH_ASSOC );
    $cntHoje = $dadosHoje['qtd'];
    $cntHoje = ($cntHoje=='') ? '---' : $cntHoje;

    //echo $cntHoje;
}




$table_atend ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sum=0;
$contratos=$infoUserConfig['contrato_id'];
$sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=2 and date_format(hora_inicio, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tma']);

        $table_atend.= "<tr>";
            $table_atend.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
            $table_atend.= "<td>". $dadosContratos[$z]['qtd']."</td>";
        $table_atend.= "</tr>";
    }
}
$table_atend.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;

?>


<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosAtend').html(valor);
var table_atend = '<?php echo $table_atend; ?>';
$('#list-atend').html(table_atend);
</script>


