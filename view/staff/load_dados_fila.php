<?php
include("../cnf/session.php");

//depurador($_POST);


if($_POST['contrato_id']!=0){

    $sql = "SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=1  and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['fila_id'];
    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );

    $cntHoje = $infoGeral['qtd'];
} else {
    $contratos=$infoUserConfig['contrato_id'];



    $sql="SELECT count(*) as qtd from tbl_chat_fila where status_fila=1 and contrato_id in (".$contratos.")";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dadosHoje = $stmt->fetch( PDO::FETCH_ASSOC );
    $cntHoje = $dadosHoje['qtd'];
    $cntHoje = ($cntHoje=='') ? '---' : $cntHoje;

    //echo $cntHoje;
}



$table_fila ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sum=0;
$contratos=$infoUserConfig['contrato_id'];
$sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=1 and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tma']);

        $table_fila.= "<tr>";
            $table_fila.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
            $table_fila.= "<td>". $dadosContratos[$z]['qtd']."</td>";
        $table_fila.= "</tr>";
    }
}
$table_fila.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;

?>


<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosFila').html(valor);
var table_fila = '<?php echo $table_fila; ?>';
$('#list-fila').html(table_fila);
</script>
