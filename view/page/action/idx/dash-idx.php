<?php include("cnf/session.php");
include('cnf/rotina_pendencia.php');

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" crossorigin="anonymous"></script>

<script type="text/javascript">

    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
        $.post("action.php",
        {
            action: action, sec: sec
        },
        function (valor) {
            $("#action-page").html(valor);
        });
    }

</script>




<div id="fila">

    <h1>Verificando Demandas</h1>
    <?php
        //$sql="SELECT id_form, nome_forms, contrato_id, dem_id, ativo from tbl_forms_dados where contrato_id=".$infoUser['contrato_id']." and ativo=1 order by nome_forms";
        //echo "<br>".$sql;
        //$stmt = $PDO->prepare($sql);
        //$result = $stmt->execute();
        //$serv = $stmt->fetch( PDO::FETCH_ASSOC );

        $sqlVer="SELECT user_id, pause_id, hora_in from tbl_pause where hora_out is null and date_format(hora_in, '%Y-%m-%d')=curdate() and user_id=".$_SESSION['dados']['id_user'];
        //echo "<br>".$sqlVer;
        $stmt = $PDO->prepare($sqlVer);
        $result = $stmt->execute();
        $ver = $stmt->fetch( PDO::FETCH_ASSOC );
        if($ver['user_id']!=''){
            //echo "<br>ETAPA 1";
            echo "<script>setTimeout(function(){ actionPage('dash-pause', 'idx'); }, 0);</script>";
        } else {
            //echo "<br>ETAPA 2";

            $sql="SELECT dem_id from tbl_tma_atend where resp_id=".$idu." and date_out is null";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $form_tma = $stmt->fetch( PDO::FETCH_ASSOC );

            if($form_tma['dem_id']!=''){
                //echo "<br>ETAPA 3";
                echo "<script>setTimeout(function(){ actionPage('dash-trt', 'idx'); }, 2000);</script>";
            } else {
                //echo "<br>ETAPA 4";
                $sql="SELECT id_form_dem, situacao_id from tbl_in_dem_".$infoUser['id_form']."_".$infoUser['id_contrato']." where (situacao_id=1 AND resp_id is null) or (situacao_id=2 AND resp_id=".$idu.")   order by situacao_id desc, id_form_dem asc limit 1";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare($sql);
                $result = $stmt->execute();
                $formDem = $stmt->fetch( PDO::FETCH_ASSOC );
                //depurador($formDem);
                if($formDem['id_form_dem']!=''){
                    //echo "<br>ETAPA 5";
                    echo "<script>setTimeout(function(){ actionPage('dash-trt', 'idx'); }, 2000);</script>";
                } else {
                    //echo "<br>ETAPA 6";
                    echo "<script>setTimeout(function(){ actionPage('dash-idx', 'idx'); }, 5000);</script>";
                }

            }
            // else {
            //    echo "<script>setTimeout(function(){ actionPage('dash-idx', 'idx'); }, 5000);</script>";
            //}
        }

    ?>

</div>

<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>

<script>
    //dadosIdx(<?php echo $_SESSION['dados']['id_user']; ?>, <?php echo $infoUser['id_form']; ?>, <?php echo $infoUser['id_contrato']; ?>);
</script>
<script type="text/javascript" src="js/load.js"></script>
