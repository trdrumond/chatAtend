<?php
include("../cnf/session.php");

//depurador($_POST);


if($_POST['contrato_id']!=0){
    if($_POST['resp_id']!=''){
        $qry=" and bko_resp=".$_POST['resp_id'];
    }

    $sql = "SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m')=date_format(curdate(), '%Y-%m') and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['form_id']." $qry";

    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoUser = $stmt->fetch( PDO::FETCH_ASSOC );

    $sql = "SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m')=date_format(curdate(), '%Y-%m') and contrato_id=".$_POST['contrato_id']." and fila_id=".$_POST['form_id'];
    //echo "<br><br>".$sql."<br>";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );

    if($nivel_usu<4){
        $cntHoje = $infoGeral['qtd'];
    } else {
        $cntHoje = $infoUser['qtd'];
    }
} else {
    $contratos=$infoUserConfig['contrato_id'];



    $sql="SELECT count(*) as qtd from tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and contrato_id in (".$contratos.")";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dadosHoje = $stmt->fetch( PDO::FETCH_ASSOC );
    $cntHoje = $dadosHoje['qtd'];
    $cntHoje = ($cntHoje=='') ? '---' : $cntHoje;

    $sql="SELECT count(*) as qtd from tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m')=date_format(curdate(), '%Y-%m') and contrato_id in (".$contratos.")";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dadosContratos = $stmt->fetch( PDO::FETCH_ASSOC );
    $count = $dadosContratos['qtd'];
    $count = ($count=='') ? '---' : $count;



    //echo $cntHoje." / ".$count;
    $cntHoje;
}


$table_concluido ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sum=0;
$contratos=$infoUserConfig['contrato_id'];
$sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tma']);

        $table_concluido.= "<tr>";
            $table_concluido.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
            $table_concluido.= "<td>". $dadosContratos[$z]['qtd']."</td>";
        $table_concluido.= "</tr>";
    }
}
$table_concluido.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;

?>


<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosConcluido').html(valor);
var table_concluido = '<?php echo $table_concluido; ?>';
$('#list-concluido').html(table_concluido);
</script>

