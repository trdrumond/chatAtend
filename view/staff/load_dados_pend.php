<?php
require_once __DIR__ . '/../cnf/session.php';

if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}
if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}
if (!isset($nivel_usu)) {
    $nivel_usu = (int)($infoUser['nivel_id'] ?? 0);
}
if (!isset($idu)) {
    $idu = (int)($infoUser['id_user'] ?? 0);
}
$infoPendAte = '';

//depurador($_POST);

    if($nivel_usu==4){
        $qeryPend = " and fila_id=".$_POST['fila_id']." and bko_resp='".$idu."' and data_hora_fim is null";
    } else
    if($nivel_usu==5){
        $qeryPend = " and ate_resp='".$idu."' and data_hora_fim is not null and data_hora_visualizacao is null";
    } else {
        $qeryPend = " and data_hora_visualizacao is null";
    }



    $sql_pend = "SELECT count(id_pend) as qtd FROM tbl_pend_info where situacao_id=3 $qeryPend";
    //echo "<br>".$sql_pend;
    $stmt = $PDO->prepare($sql_pend);
    $result = $stmt->execute();
    $infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );

    if($infoGeral['qtd']>0){

        if($nivel_usu==4 || $nivel_usu==5){
            $cntHoje = ' <span class="badge bg-light text-dark">' . $infoGeral['qtd'] . '</span>';
        } else {
            $cntHoje = $infoGeral['qtd'];
        }
    } else {

        if($nivel_usu==4 || $nivel_usu==5){
            $cntHoje = '';
        } else {
            $cntHoje = 0;
        }
    }





$table_pend ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sum=0;
$contratos=$infoUserConfig['contrato_id'];
$sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_pend) as qtd from tbl_pend_info where situacao_id=3 and fila_id=id_fila and data_hora_visualizacao is null) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tma']);

        $table_pend.= "<tr>";
            $table_pend.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
            $table_pend.= "<td>". $dadosContratos[$z]['qtd']."</td>";
        $table_pend.= "</tr>";
    }
}
$table_pend.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;


if($nivel_usu==5){
    $qeryPend = " and ate_resp='".$idu."' and data_hora_fim is not null and data_hora_visualizacao is null";

    $sql="SELECT id_pend, chat_id, (SELECT protocolo from tbl_chat_fila where id_chat_fila=chat_id) as protocolo FROM tbl_pend_info where situacao_id=3 $qeryPend";
    //echo "<br>".$sql;
    $stmt = $PDO_LOAD->prepare($sql);
    $result = $stmt->execute();
    $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
    $count=0;
    for($z=0;$z<count($dadosContratos);$z++){
        if($dadosContratos[$z]['id_pend']!=''){
            echo $dadosContratos[$z]['protocolo'];
            $infoPendAte .= " - " . $dadosContratos[$z]['protocolo'];
        }
    }
}





?>



<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosPend').html(valor);
$('#dadosPendMen').html(valor);
var table_pend = '<?php echo $table_pend; ?>';
$('#list-pend').html(table_pend);
var pend = '<?php echo $infoPendAte; ?>';
//console.log(pend);
$('#dadosPendAte').html(pend);
</script>

<!--
<script>
            Toastify({
                text: "Protocolo: XXXX com pendência resolvida!",
                duration: 5000,
                //destination: "https://github.com/apvarun/toastify-js",
                newWindow: true,
                close: true,
                gravity: "top", // `top` or `bottom`
                position: "left", // `left`, `center` or `right`
                stopOnFocus: true, // Prevents dismissing of toast on hover
                style: {
                    background: "linear-gradient(to right, #FF9999, #B20000)",
                },
                onClick: function(){} // Callback after click
                }).showToast();
    </script>
-->
<?php //include("../cnf/replace_msg.php");?>
