<?php
include("../cnf/conn.php");

//depurador($_POST);
$infoUser['nivel_id']=0;
$nivel_usu = $infoUser['nivel_id'];
$contratoIdPost = (int) ($_POST['contrato_id'] ?? 0);
$filaIdPost = (int) ($_POST['fila_id'] ?? 0);

//DADOS CONCLUÍDOS
if($infoUser['nivel_id']<5){


        $dashPainelParams = [];
        $qryContrato = '';
        $qryBko = '';
        if($contratoIdPost != 0){
            $cttBind = stSqlInBind([$contratoIdPost]);
            $qryContrato = ' and contrato_id in (' . $cttBind['ph'] . ')';
            if($nivel_usu==4 && $filaIdPost > 0){
                $qryBko = ' and fila_id=?';
                $dashPainelParams = array_merge(
                    $cttBind['ids'], [$filaIdPost],
                    $cttBind['ids'], [$filaIdPost],
                    $cttBind['ids'], [$filaIdPost],
                    $cttBind['ids'],
                    $cttBind['ids']
                );
            } else {
                $dashPainelParams = array_merge(
                    $cttBind['ids'],
                    $cttBind['ids'],
                    $cttBind['ids'],
                    $cttBind['ids'],
                    $cttBind['ids']
                );
            }
        }


        $sql="SELECT count(*) as qtd_concluido,
            (SELECT count(*) from tbl_chat_fila where status_fila=1 $qryContrato $qryBko) as qtd_fila,
            (SELECT count(*) from tbl_chat_fila where status_fila=2 $qryContrato $qryBko) as qtd_atend,
            (SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' and date_format(hora_inicio, '%Y-%m-%d')=curdate()  $qryContrato $qryBko) as tma,
            (SELECT sec_to_time(avg(time_to_sec(te))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' and date_format(hora_inicio, '%Y-%m-%d')=curdate() $qryContrato) as tme
            from tbl_chat_fila where status_fila>=4 and hora_fim<>'' and date_format(hora_inicio, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') $qryContrato";

        //echo "<br>".$sql;
        //echo "<script>console.log(".$sql.");</script>";

        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute($dashPainelParams);
        $dadosHoje = $stmt->fetch( PDO::FETCH_ASSOC );
        //echo "<br>";
        //var_dump($dadosHoje);


        $cntHojeConcluido = $dadosHoje['qtd_concluido'];
        $cntHojeConcluido = ($cntHojeConcluido=='') ? '---' : $cntHojeConcluido;

            $sum=0;
            $sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila>=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
            //echo "<br>".$sql;
            $stmt = $PDO_LOAD->prepare($sql);
            $result = $stmt->execute();
            $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $count=0;
            for($z=0;$z<count($dadosContratos);$z++){
                if($dadosContratos[$z]['id_fila']!=''){
                    $ex = explode('.', $dadosContratos[$z]['tma']);

                    echo "<script>$('#dadosConcluido_".$dadosContratos[$z]['id_fila']."').html(".$dadosContratos[$z]['qtd'].");</script>";
                }
            }


    ?>


<script>
var valor = '<?php echo $cntHojeConcluido ?>';
$('#dadosConcluido').html(valor);
<?php if($nivel_usu<4){ ?>
var table_concluido = '<?php echo $table_concluido; ?>';
$('#list-concluido').html(table_concluido);
<?php } ?>
</script>

<?php

        $cntHojeFila = $dadosHoje['qtd_fila'];
        $cntHojeFila = ($cntHojeFila=='') ? '---' : $cntHojeFila;


            $sum=0;
            $sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=1 and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
            //echo "<br>".$sql;
            $stmt = $PDO_LOAD->prepare($sql);
            $result = $stmt->execute();
            $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $count=0;
            for($z=0;$z<count($dadosContratos);$z++){
                if($dadosContratos[$z]['id_fila']!=''){
                    $ex = explode('.', $dadosContratos[$z]['tma']);
                    echo "<script>$('#dadosFila_".$dadosContratos[$z]['id_fila']."').html(".$dadosContratos[$z]['qtd'].");</script>";
                }
            }

    ?>


<script>
var valor = '<?php echo $cntHojeFila ?>';
$('#dadosFila').html(valor);
<?php if($nivel_usu<4){ ?>
var table_fila = '<?php echo $table_fila; ?>';
$('#list-fila').html(table_fila);
<?php } ?>
</script>

<?php

    //DADOS ATEND
    $cntHojeAtend = $dadosHoje['qtd_atend'];
    $cntHojeAtend = ($cntHojeAtend=='') ? '---' : $cntHojeAtend;

        $sum=0;
        $sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=2 and date_format(hora_inicio, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_fila']!=''){
                $ex = explode('.', $dadosContratos[$z]['tma']);
                echo "<script>$('#dadosAtend_".$dadosContratos[$z]['id_fila']."').html(".$dadosContratos[$z]['qtd'].");</script>";
            }
    }

    ?>


<script>
var valor = '<?php echo $cntHojeAtend ?>';
$('#dadosAtend').html(valor);
<?php if($nivel_usu<4){ ?>
var table_atend = '<?php echo $table_atend; ?>';
$('#list-atend').html(table_atend);
<?php } ?>
</script>

<?php
    if($nivel_usu<4){
        $cntHojeTma = $dadosHoje['tma'];
        $ex = explode(".", $cntHojeTma);
        $cntHojeTma = $ex[0];
        $cntHojeTma = ($cntHojeTma=='') ? '--:--:--' : $cntHojeTma;



        $sum=0;
        $sql="SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(ta))) as tma from tbl_chat_fila where ta is not null and status_fila>=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as tma from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_fila']!=''){
                $ex = explode('.', $dadosContratos[$z]['tma']);
                if($dadosContratos[$z]['tma']==''){
                    $ex[0]='--:--:--';
                }

                echo "<script>$('#dadosTma_".$dadosContratos[$z]['id_fila']."').html('".$ex[0]."');</script>";
            }
        }

        ?>


<script>
var valor = '<?php echo $cntHojeTma ?>';
$('#dadosTma').html(valor);
var table_tma = '<?php echo $table_tma; ?>';
$('#list-tma').html(table_tma);
</script>

<?php
    }

    $cntHojeTme = $dadosHoje['tme'];
    $ex = explode(".", $cntHojeTme);
    $cntHojeTme = $ex[0];
    $cntHojeTme = ($cntHojeTme=='') ? '--:--:--' : $cntHojeTme;


        $sum=0;
        $sql="SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(te))) as tme from tbl_chat_fila where te is not null and status_fila>=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as tme from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        //depurador($dadosContratos);
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_fila']!=''){
                $ex = explode('.', $dadosContratos[$z]['tme']);
                if($dadosContratos[$z]['tme']==''){
                    $ex[0]='--:--:--';
                }

                echo "<script>$('#dadosTme_".$dadosContratos[$z]['id_fila']."').html('".$ex[0]."');</script>";
            }
        }

    ?>


<script>
var valor = '<?php echo $cntHojeTme ?>';
$('#dadosTme').html(valor);
<?php if($nivel_usu<4){ ?>
var table_tme = '<?php echo $table_tme; ?>';
$('#list-tme').html(table_tme);
<?php } ?>
</script>


<?php

    //DADOS USUÁRIOS LOGADOS

        if($contratoIdPost == 0){
        } else {
        }



        $sql = "SELECT user_id as id_us, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=id_us) as nome, (SELECT count(*) from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and user_id=id_us) as acao, (SELECT date_format(data_hora, '%H:%i:%s') from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and acao='Logout' and user_id=id_us order by data_hora desc limit 1) as logout, (SELECT date_format(data_hora, '%H:%i:%s') from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and acao<>'Logout' and user_id=id_us order by data_hora desc limit 1) as atend FROM tbl_log_diario where data_log=curdate() and nivel_id=4 and date_out is null";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoGeral = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $qtdOnLine = 0;
        //echo "<br>";
        for($x=0;$x<count($infoGeral);$x++){
            if($infoGeral[$x]['acao']>0){
                if(($infoGeral[$x]['logout']=='') || ($infoGeral[$x]['logout']!='' && $infoGeral[$x]['logout'] < $infoGeral[$x]['atend'])){

                    $qtdOnLine++;
                }
            }
        }
        echo '<script>$("#dadosOn").html('.$qtdOnLine.');</script>';

        $sql="SELECT id_fila from tbl_config_fila where ativo=1";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        //depurador($dadosContratos);
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            $filaIdLoop = (int) $dadosContratos[$z]['id_fila'];

            $sql = "SELECT user_id as id_us, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=id_us) as nome, (SELECT count(*) from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and user_id=id_us) as acao, (SELECT date_format(data_hora, '%H:%i:%s') from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and acao='Logout' and user_id=id_us order by data_hora desc limit 1) as logout, (SELECT date_format(data_hora, '%H:%i:%s') from tbl_log_atendimento where date_format(data_hora, '%y-%m-%d')=curdate() and acao<>'Logout' and user_id=id_us order by data_hora desc limit 1) as atend FROM tbl_log_diario where data_log=curdate() and nivel_id=4 and date_out is null and fila_id=?";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute([$filaIdLoop]);
            $infoGeralFila = $stmt->fetchAll( PDO::FETCH_ASSOC );
            //depurador($infoGeralFila);
            $qtdOnLineFila = 0;
            for($x=0;$x<count($infoGeralFila);$x++){
                if($infoGeralFila[$x]['acao']>0){
                    if(($infoGeralFila[$x]['logout']=='') || ($infoGeralFila[$x]['logout']!='' && $infoGeralFila[$x]['logout'] < $infoGeralFila[$x]['atend'])){
                        $qtdOnLineFila++;
                    }

                }

            }
            echo '<script>$("#dadosOn_'.$dadosContratos[$z]['id_fila'].'").html('.$qtdOnLineFila.');</script>';

        }



    //DADOS PEND


    $qeryPend = " and data_hora_visualizacao is not null";



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




        $sum=0;
        $sql="SELECT a.id_fila, a.nome_fila, (SELECT count(id_pend) as qtd from tbl_pend_info where situacao_id<>7 and fila_id=id_fila and data_hora_visualizacao is not null) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
        //echo "<br>".$sql;
        $stmt = $PDO_LOAD->prepare($sql);
        $result = $stmt->execute();
        $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $count=0;
        for($z=0;$z<count($dadosContratos);$z++){
            if($dadosContratos[$z]['id_fila']!=''){
                $ex = explode('.', $dadosContratos[$z]['tma']);

                echo "<script>$('#dadosPend_".$dadosContratos[$z]['id_fila']."').html(".$dadosContratos[$z]['qtd'].");</script>";
            }
        }

    ?>



<script>
var valor = '<?php echo $cntHoje ?>';
$('#dadosPend').html(valor);
var table_pend = '<?php echo $table_pend; ?>';
$('#list-pend').html(table_pend);
var pend = '<?php echo $infoPendAte; ?>';
$('#dadosPendAte').html(pend);
</script>

<?php } ?>