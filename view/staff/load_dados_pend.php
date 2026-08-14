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

$filaIdPost = (int) ($_POST['fila_id'] ?? 0);

    if($nivel_usu==4){
        $sql_pend = "SELECT count(id_pend) as qtd FROM tbl_pend_info where situacao_id=3 and fila_id=? and bko_resp=? and data_hora_fim is null";
        $stmt = $PDO->prepare($sql_pend);
        $result = $stmt->execute([$filaIdPost, $idu]);
    } else
    if($nivel_usu==5){
        $sql_pend = "SELECT count(id_pend) as qtd FROM tbl_pend_info where situacao_id=3 and ate_resp=? and data_hora_fim is not null and data_hora_visualizacao is null";
        $stmt = $PDO->prepare($sql_pend);
        $result = $stmt->execute([$idu]);
    } else {
        $sql_pend = "SELECT count(id_pend) as qtd FROM tbl_pend_info where situacao_id=3 and data_hora_visualizacao is null";
        $stmt = $PDO->prepare($sql_pend);
        $result = $stmt->execute();
    }
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
$cttBind = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
$sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_pend) as qtd from tbl_pend_info where situacao_id=3 and fila_id=id_fila and data_hora_visualizacao is null) as qtd from tbl_config_fila a where a.ativo=1 and a.contrato_id in ({$cttBind['ph']}) order by a.nome_fila";
//echo "<br>".$sql;
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute($cttBind['params']);
$dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
$count=0;
for($z=0;$z<count($dadosContratos);$z++){
    if($dadosContratos[$z]['id_fila']!=''){
        $ex = explode('.', $dadosContratos[$z]['tma']);

        $table_pend.= "<tr>";
            $table_pend.= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['nome_fila'], ENT_QUOTES, 'UTF-8') . "</td>";
            $table_pend.= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['qtd'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_pend.= "</tr>";
    }
}
$table_pend.='</tbody></table>';
//$count = ($count=='') ? '---' : $count;


if($nivel_usu==5){
    $sql="SELECT id_pend, chat_id, (SELECT protocolo from tbl_chat_fila where id_fila_chat=chat_id) as protocolo FROM tbl_pend_info where situacao_id=3 and ate_resp=? and data_hora_fim is not null and data_hora_visualizacao is null";
    $stmt = $PDO_LOAD->prepare($sql);
    $result = $stmt->execute([$idu]);
    $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
    $count=0;
    for($z=0;$z<count($dadosContratos);$z++){
        if($dadosContratos[$z]['id_pend']!=''){
            echo htmlspecialchars((string) $dadosContratos[$z]['protocolo'], ENT_QUOTES, 'UTF-8');
            $infoPendAte .= " - " . htmlspecialchars((string) $dadosContratos[$z]['protocolo'], ENT_QUOTES, 'UTF-8');
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
