<?php
require_once __DIR__ . '/../cnf/session.php';

/** @var array<string, mixed> $infoUser */
/** @var array<string, mixed> $infoUserConfig */
/** @var int $idu */
/** @var int $nivel_usu */
/** @var string $infoPendAte */

if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}
if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}
if (!isset($idu)) {
    $idu = (int)($infoUser['id_user'] ?? 0);
}
$infoPendAte = '';

//depurador($_POST);
$nivel_usu = (int)($infoUser['nivel_id'] ?? 0);

//DADOS CONCLUÍDOS
if($infoUser['nivel_id']<5){

        if($infoUser['nivel_id']==4){
            $sql ="SELECT ativo from tbl_config_fila where id_fila=".$infoUser['fila_id'];
            //echo "<br>".$sql;
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $filaBko = $stmt->fetch( PDO::FETCH_ASSOC );
            if($filaBko['ativo']==1){
                $classFila = 'fila_in';
                $titleFila = 'Fila Ativa';
            } else {
                $classFila = 'fila_out';
                $titleFila = 'Fila Desativada';
            }
            ?>
<script>
$('#idx-Fila').removeClass();
$('#idx-Fila').addClass('<?=$classFila?>').attr({
    title: "<?=$titleFila?>"
});
</script>
<?php
        }

        $contratos=$infoUserConfig['contrato_id'];

        $sqlVer="SELECT filas from tbl_user_filas where user_id=".$infoUser['id_user'];
        //echo "<br>".$sqlVer;
        $stmt = $PDO->prepare($sqlVer);
        $result = $stmt->execute();
        $filas = $stmt->fetch( PDO::FETCH_ASSOC );

        $filasConfig = ($filas['filas']!='') ? $filas['filas'] : $infoUser['fila_id'];

        if($_POST['contrato_id']!=0){
            $qryContrato = " and contrato_id in (".$_POST['contrato_id'].")";
            if($nivel_usu==4){
                $qryBko = " and fila_id IN (".$filasConfig.")";
            } else {
                $qryBko = "";
            }

        } else {
            $qryContrato ='';
            $qryBko = "";
        }


        $sql="SELECT count(*) as qtd_concluido,
            (SELECT count(*) from tbl_chat_fila where status_fila=".ST_FILA_NA_FILA." $qryContrato $qryBko) as qtd_fila,
            (SELECT count(*) from tbl_chat_fila where status_fila=".ST_FILA_AGUARDANDO_ATENDIMENTO." $qryContrato $qryBko) as qtd_aguardando_atend,
            (SELECT count(*) from tbl_chat_fila where status_fila=".ST_FILA_EM_ATENDIMENTO." $qryContrato $qryBko) as qtd_atend,
            (SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' and date_format(hora_inicio, '%Y-%m-%d')=curdate()  $qryContrato $qryBko) as tma,
            (SELECT sec_to_time(avg(time_to_sec(te))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' and date_format(hora_inicio, '%Y-%m-%d')=curdate() $qryContrato) as tme
            from tbl_chat_fila where status_fila>=4 and hora_fim<>'' and date_format(hora_inicio, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') $qryContrato";

        //echo "<br>".$sql;
        //echo "<script>console.log(".$sql.");</script>";

        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $dadosHoje = $stmt->fetch( PDO::FETCH_ASSOC );
        //echo "<br>";
        //depurador($dadosHoje);


        $cntHojeConcluido = $dadosHoje['qtd_concluido'];
        $cntHojeConcluido = ($cntHojeConcluido=='') ? '---' : $cntHojeConcluido;
        if($nivel_usu<4){
            $table_concluido ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
            $sum=0;
            $contratos=$infoUserConfig['contrato_id'];

            $sql="SELECT id_fila, nome_fila, qtd, ativo FROM  ind_concluido";
            //echo "<br>".$sql;
            $stmt = $PDO_LOAD->prepare($sql);
            $result = $stmt->execute();
            $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $count=0;
            for($z=0;$z<count($dadosContratos);$z++){
                if($dadosContratos[$z]['id_fila']!='' && ($dadosContratos[$z]['ativo']==1 || $dadosContratos[$z]['qtd']>0)){
                    $ex = explode('.', $dadosContratos[$z]['tma']);

                    $table_concluido.= "<tr>";
                        $table_concluido.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
                        $table_concluido.= "<td>". $dadosContratos[$z]['qtd']."</td>";
                    $table_concluido.= "</tr>";
                }
            }
            $table_concluido.='</tbody></table>';
        }
    ?>


<script>
var valor = '<?php echo $cntHojeConcluido ?>';
$('#dadosConcluido').html(valor);
$('#ini_concluido').html(valor);
<?php if($nivel_usu<4){ ?>
var table_concluido = '<?php echo $table_concluido; ?>';
$('#list-concluido').html(table_concluido);
<?php } ?>
</script>

<?php

        $cntHojeFila = $dadosHoje['qtd_fila'];
        $cntHojeFila = ($cntHojeFila=='') ? '---' : $cntHojeFila;


        if($nivel_usu<4){
            $table_fila ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
            $sum=0;
            $contratos=$infoUserConfig['contrato_id'];
            $sql="SELECT id_fila, nome_fila, qtd, ativo FROM  ind_fila";
            //echo "<br>".$sql;
            $stmt = $PDO_LOAD->prepare($sql);
            $result = $stmt->execute();
            $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $count=0;
            for($z=0;$z<count($dadosContratos);$z++){
                if($dadosContratos[$z]['id_fila']!='' && ($dadosContratos[$z]['ativo']==1 || $dadosContratos[$z]['qtd']>0)){
                    $ex = explode('.', $dadosContratos[$z]['tma']);

                    $table_fila.= "<tr>";
                        $table_fila.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
                        $table_fila.= "<td>". $dadosContratos[$z]['qtd']."</td>";
                    $table_fila.= "</tr>";
                }
            }
            $table_fila.='</tbody></table>';
            //$count = ($count=='') ? '---' : $count;
        }

    ?>


<script>
var valor = '<?php echo $cntHojeFila ?>';
$('#dadosFila').html(valor);
$('#ini_aguardando').html(valor);
<?php if($nivel_usu<4){ ?>
var table_fila = '<?php echo $table_fila; ?>';
$('#list-fila').html(table_fila);
<?php } ?>
</script>

<?php

    //DADOS ATEND
    $cntHojeAtend = $dadosHoje['qtd_atend'];
    $cntHojeAtend = ($cntHojeAtend=='') ? '---' : $cntHojeAtend;

    if($nivel_usu<4){
        $table_atend ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
        $sum=0;
        $contratos=$infoUserConfig['contrato_id'];
        $sql="SELECT id_fila, nome_fila, qtd, ativo FROM  ind_atendimento";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_fila']!='' && ($dadosContratos[$z]['ativo']==1 || $dadosContratos[$z]['qtd']>0)){
                $ex = explode('.', $dadosContratos[$z]['tma']);

                $table_atend.= "<tr>";
                    $table_atend.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
                    $table_atend.= "<td>". $dadosContratos[$z]['qtd']."</td>";
                $table_atend.= "</tr>";
            }
        }
        $table_atend.='</tbody></table>';
    }

    ?>


<script>
var valor = '<?php echo $cntHojeAtend ?>';

$('#dadosAtend').html(valor);
$('#ini_atendimento').html(valor);
<?php if($nivel_usu<4){ ?>
var table_atend = '<?php echo $table_atend; ?>';
$('#list-atend').html(table_atend);
<?php } ?>
</script>

<?php
//echo $nivel_usu;
//echo "<br>Teste 1";
    if($nivel_usu<4){
        //echo "<br>Teste 2";
        $cntHojeTma = $dadosHoje['tma'];
        $ex = explode(".", $cntHojeTma);
        $cntHojeTma = $ex[0];
        $cntHojeTma = ($cntHojeTma=='') ? '--:--:--' : $cntHojeTma;

        //echo "<br>Teste 3";



        $table_tma ='<table class="table table-hover"><thead><th>FILA</th><th>QTD</th><th>TMA</th></thead><tbody>';
        $sum=0;
        $contratos=$infoUserConfig['contrato_id'];
        $sql="SELECT id_fila, nome_fila, tma, ativo, qtd FROM  ind_tma";
        //echo "<br>Teste 4";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        //depurador($dadosContratos);
        //echo "<br>".count($dadosContratos);
        //echo "<br>Teste 5";
        for($x=0; $x < count($dadosContratos); $x++){
            //echo "<br>".$dadosContratos[$x]['tma'];
            $ex = explode('.', $dadosContratos[$x]['tma']);
            //echo "<br>".$ex[0];
            //echo "<br>".$dadosContratos[$x]['ativo'];
            //echo "<br>".time_to_sec($ex[0]);
            if($dadosContratos[$x]['id_fila']!='' && ($dadosContratos[$x]['ativo']==1)){
                //echo "<br>Teste 5.1";
                //$ex = explode('.', $dadosContratos[$z]['tma']);

               $table_tma.= "<tr>";
                    $table_tma.= "<td>".$dadosContratos[$x]['nome_fila']."</td>";
                    $table_tma.= "<td>".$dadosContratos[$x]['qtd']."</td>";
                    $table_tma.= "<td>". $ex[0]."</td>";
                $table_tma.= "</tr>";
            }
        }

        $table_tma.='</tbody></table>';
        //echo "<br>Teste 6";

        ?>


<script>
var valor = '<?php echo $cntHojeTma ?>';
$('#dadosTma').html(valor);
$('#ini_tma').html(valor);
var table_tma = '<?php echo $table_tma; ?>';
$('#list-tma').html(table_tma);
</script>
<?php  } ?>

<?php

    $cntHojeTme = $dadosHoje['tme'];
    $ex = explode(".", $cntHojeTme);
    $cntHojeTme = $ex[0];
    $cntHojeTme = ($cntHojeTme=='') ? '--:--:--' : $cntHojeTme;


    if($nivel_usu<4){
        $table_tme ='<table class="table table-hover"><thead><th>FILA</th><th>QTD</th><th>TME</th></thead><tbody>';
        $sum=0;
        $contratos=$infoUserConfig['contrato_id'];
        $sql="SELECT id_fila, nome_fila, tme, ativo, qtd FROM  ind_tme";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            $ex = explode('.', $dadosContratos[$z]['tme']);
            if($dadosContratos[$z]['id_fila']!='' && ($dadosContratos[$z]['ativo']==1)){


                $table_tme.= "<tr>";
                    $table_tme.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
                    $table_tme.= "<td>".$dadosContratos[$z]['qtd']."</td>";
                    $table_tme.= "<td>". $ex[0]."</td>";
                $table_tme.= "</tr>";
            }
        }
        $table_tme.='</tbody></table>';
        //$count = ($count=='') ? '---' : $count;
    }

    ?>


<script>
var valor = '<?php echo $cntHojeTme ?>';
$('#dadosTme').html(valor);
$('#ini_tme').html(valor);
<?php if($nivel_usu<4){ ?>
var table_tme = '<?php echo $table_tme; ?>';
$('#list-tme').html(table_tme);
<?php } ?>
</script>


<?php

    //DADOS USUÁRIOS LOGADOS

    if($nivel_usu<5){
        if($_POST['contrato_id']==0){
            $contratos=$infoUserConfig['contrato_id'];
        } else {
            $contratos=$infoUser['contrato_id'];
        }

        if($infoUser['nivel_id']==4){
            $sql_fila = "and fila_id='".$infoUser['fila_id']."'";
            //$sql_fila = "";
        } else {
            $sql_fila="";
        }


        $sql = "SELECT user_id as id_us, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=id_us) as nome, (SELECT count(*) from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and user_id=id_us) as acao, (SELECT date_format(data_hora, '%H:%i:%s') from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and acao='Logout' and user_id=id_us order by data_hora desc limit 1) as logout, (SELECT date_format(data_hora, '%H:%i:%s') from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and acao<>'Logout' and user_id=id_us order by data_hora desc limit 1) as atend FROM tbl_log_diario where data_log=curdate() and nivel_id=4 and date_out is null $sql_fila";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoGeral = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $qtdOnLine = 0;
        for($x=0;$x<count($infoGeral);$x++){
            if($infoGeral[$x]['acao']>0){
                if(($infoGeral[$x]['logout']=='') || ($infoGeral[$x]['logout']!='' && $infoGeral[$x]['logout'] < $infoGeral[$x]['atend'])){
                    $qtdOnLine++;
                }

            }
        }
        echo '<script>$("#dadosOn").html('.$qtdOnLine.');</script>';
    }



    //DADOS PEND

    if($nivel_usu==4){
        $qeryPend = " and fila_id=".$_POST['fila_id']." and bko_resp='".$idu."' and data_hora_fim is null";
    } else
    if($nivel_usu==5){
        $qeryPend = " and ate_resp='".$idu."' and data_hora_fim is null";
    } else {
        $qeryPend = " and data_hora_fim is null";
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




    if($nivel_usu<4){
        $table_pend ='<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
        $sum=0;
        $contratos=$infoUserConfig['contrato_id'];
        $sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_pend) as qtd from tbl_pend_info where situacao_id=3 and fila_id=id_fila and data_hora_fim is null) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_fila']!=''){
                $table_pend.= "<tr>";
                    $table_pend.= "<td>".$dadosContratos[$z]['nome_fila']."</td>";
                    $table_pend.= "<td>". $dadosContratos[$z]['qtd']."</td>";
                $table_pend.= "</tr>";
            }
        }
        $table_pend.='</tbody></table>';
        //$count = ($count=='') ? '---' : $count;
    }

    if($nivel_usu==5){
        $qeryPend = " and ate_resp='".$idu."' and data_hora_fim is null";

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



        $qeryPend = " and ate_resp='".$idu."' and data_hora_fim is null";

        $sql="SELECT id_pend, chat_id, (SELECT protocolo from tbl_chat_fila where id_fila_chat=chat_id) as protocolo, (SELECT id_chat from tbl_chat_info where fila_chat_id=chat_id) as id_chat FROM tbl_pend_info where situacao_id=3 $qeryPend";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_pend']!=''){
                $dadosContratos[$z]['protocolo'];
                echo "<script>notPend('".$dadosContratos[$z]['protocolo']."', '".$dadosContratos[$z]['id_chat']."');</script>";
            }
        }
    }


    ?>



<script>
<?php if($nivel_usu<4){ ?>
var valor = '<?php echo $cntHoje ?>';
var sql = '<?php echo $sql_pend; ?>';
$('#dadosPend').html(valor);
$('#dadosPendMen').html(valor);
$('#ini_pendencias').html(valor);
var table_pend = '<?php echo $table_pend; ?>';
$('#list-pend').html(table_pend);
<?php } ?>
var pend = '<?php echo $infoPendAte; ?>';
$('#dadosPendAte').html(pend);
</script>


<?php } ?>

<?php
$day = (date('Y-m-d')<'2021-12-06') ? 1 : 5;


$sql = "SELECT format(avg(star), 1) as star from tbl_classificacao where ate=".$infoUser['id_user']." and star is not null and star<>''  and date_format(data_hora, '%Y-%m-%d') BETWEEN '0001-01-01' and date_sub(CURDATE(), INTERVAL $day DAY)";
//echo "<br>".$sql;

$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$star = $stmt->fetch( PDO::FETCH_ASSOC );

$star['star'] = (date('Y-m-d')<'2021-12-11' && $star['star'] < '2.5') ? ' -.- ' : $star['star'];
$star['star'] = ($star['star']=='') ? ' -.- ' : $star['star'];

//echo '<i class="fas fa-star" style="color: #D2D200"></i> '.$star['star'];
?>
<script>
$("#star").html('<?php echo '<i class="fas fa-star" style="color: #D2D200"></i> '.$star['star'];?>');
</script>
