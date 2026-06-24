<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

if($_POST['pause']==0){
    $idx='dash-idx';
} else if($_POST['pause']==99){
    $idx='dash-out';
} else if($_POST['pause']==1){
    $idx='dash-idx';
} else {
    $idx='dash-pause';
}


$sql = "SELECT id, dem_id, date_in, resp_id, timediff(now(), date_in) as sla FROM tbl_tma_atend where dem_id=".$_POST['dem_id']." and date_out is null";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoAtend = $stmt->fetch( PDO::FETCH_ASSOC );
//echo "<BR>ETAPA 1";

$sql = "SELECT id_form_dados, dem_id, data_hora, resp_id FROM tbl_in_dados_".$_POST['form_id']."_".$_POST['contrato_id']." where dem_id=".$_POST['dem_id'];
//echo "<br><br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$info = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($info);
//echo "<BR>ETAPA 2";


$sqlAlterTmaAtend="UPDATE tbl_tma_atend SET";
$sqlAlterTmaAtend.=" date_out=now(), sla='".$infoAtend['sla']."'";
$sqlAlterTmaAtend.=" WHERE id='".$infoAtend['id']."'";
//echo "<br><br>".$sqlAlterDem;
$stmt = $PDO->prepare( $sqlAlterTmaAtend );
$resultDem = $stmt->execute();
//echo "<BR>ETAPA 3";

$sql = "SELECT sec_to_time(sum(time_to_sec(sla))) as sla FROM tbl_tma_atend where dem_id=".$_POST['dem_id'];
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );
$ex=explode('.', $infoGeral['sla']);
$infoGeral['sla']=$ex[0];
//echo "<br>".$infoGeral['sla'];
//echo "<BR>ETAPA 4";


//echo "<br><br>";
$sql = "SELECT nome_campo FROM tbl_forms_dados_input_campo where form_id=".$_POST['form_id'];
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$campo = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($campo);
//echo "<BR>ETAPA 5";

if($_POST['pause']==1){
    $sqlPend = "INSERT INTO tbl_pend_dados (dem_id, form_id, contrato_id, resp_id, motivo, tempo, obs) VALUES ('".$_POST['dem_id']."', '".$_POST['form_id']."', '".$_POST['contrato_id']."', '".$_SESSION['dados']['id_user']."', '".$_POST['motivo_pend']."', '".$_POST['tempo_pend']."', '".$_POST['obs_pend']."')";
    //echo "<br>".$sqlPend;
    $stmt = $PDO->prepare( $sqlPend );
    $resultPend = $stmt->execute();
    //echo "<BR>ETAPA 6";
}



$sqlAlterDados="UPDATE tbl_in_dados_".$_POST['form_id']."_".$_POST['contrato_id']." SET";
if($_POST['pause']!=1){
    $sqlAlterDados.=" data_hora_fim=now(), sla='".$infoGeral['sla']."'";
} else {
    $sqlAlterDados.=" data_hora_fim=null, sla=null";
}
for($x=0;$x<count($campo);$x++){ $sqlAlterDados.= ", ".$campo[$x]['nome_campo']."='".$_POST[$campo[$x]['nome_campo']]."'"; }
$sqlAlterDados.=" WHERE id_form_dados='".$info['id_form_dados']."'";
//echo "<br><br>".$sqlAlterDados;
//echo "<BR>ETAPA 7";



$stmt = $PDO->prepare( $sqlAlterDados );
$resultDados = $stmt->execute();


if($resultDados==1){
    //echo "<BR>ETAPA 8";
    $sqlAlterDem="UPDATE tbl_in_dem_".$_POST['form_id']."_".$_POST['contrato_id']." SET";
    if($_POST['pause']!=1){
        $sqlAlterDem.=" situacao_id=4, sla='".$infoGeral['sla']."'";
    } else {
        $sqlAlterDem.=" situacao_id=3, sla=null";
    }
    //$sqlAlterDem.=" situacao_id=4, sla='".$infoGeral['sla']."'";
    $sqlAlterDem.=" WHERE id_form_dem='".$_POST['dem_id']."'";
    //echo "<br><br>".$sqlAlterDem;
    $stmt = $PDO->prepare( $sqlAlterDem );
    $resultDem = $stmt->execute();
    if($resultDem==1){
        //echo "<BR>ETAPA 9";
        if(($_POST['pause']!=0)&&($_POST['pause']!=1)&&($_POST['pause']!=99)){
            $sqlPause = "INSERT INTO tbl_pause (user_id, hora_in, pause_id) VALUES ('".$_SESSION['dados']['id_user']."', now(), '".$_POST['pause']."')";
            //echo "<br>".$sqlPause;
            $stmt = $PDO->prepare( $sqlPause );
            $resultDem = $stmt->execute();
            //echo "<BR>ETAPA 9";
        }
        ?>

            <script>
                Swal.fire({
                    position: 'bottom-start',
                    icon: 'success',
                    title: 'Demanda finalizada com sucesso!',
                    showConfirmButton: false,
                    timer: 1500
                });

                <?php if(($_POST['pause']==99)){ ?>
                    setTimeout(function(){ logout('sair'); }, 2000);
                    <?php } else { ?>focus
                        setTimeout(function(){ document.location.reload(true); }, 2000);
                    <?php } ;?>






                function actionPage(action, sec){
                    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                    //dadosIdx(<?php echo $_SESSION['dados']['id_user']; ?>, <?php echo $_POST['form_id']; ?>, <?php echo $_POST['contrato_id']; ?>);
                    //console.log('A ação é: ' + action);
                    $.post("action.php",
                    {
                        action: action, sec: sec
                    },
                    function (valor) {
                        $("#action-page").html(valor);
                    });
                }

                function logout(action){
                    $.post("logout.php",
                    {
                        action: action
                    },
                    function (valor) {
                        $("#logout").html(valor);
                    });
                }



            </script>

        <?php
    }
}

?>
