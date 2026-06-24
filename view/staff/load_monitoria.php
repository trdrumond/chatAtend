<style>
    .titulo_mon {
        background-color: #FFFFFF;
        width: 100%;
        padding-top: 10px;
        padding-bottom: 10px;
        margin-bottom: 10px;
        height: 40px;
    }

    .titulo_mon > .titulo {
        width: 85%; float: left;
        background-color: #FFFFFF;
    }
    .titulo_mon > .close_mon {
        width: 15%; float: left;
        background-color: #FFFFFF;
    }
</style>

<div class="titulo_mon">
    <div class="titulo">
        <h5>Monitoria</h5>
    </div>
    <div class="close_mon">
        <button type="button" class="btn-close" onclick="closeMon(<?=$_POST['id_chat']; ?>)"></button>
    </div>
</div>

<div>



<?php
include("../cnf/session.php");
include("../cnf/func_input.php");

//depurador($_POST);
//depurador($infoUser);

$sql = "SELECT * from tbl_in_mon_".$_POST['fila']."_".$_POST['contrato']." where chat_id=".$_POST['id_chat'];
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dadosMon = $stmt->fetch( PDO::FETCH_ASSOC );

$sql = "SELECT count(campo_id) as qtd from tbl_forms_mon_input_campo_cnf where fila_id=".$_POST['fila']." and ativo=1 and qualif=1";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$countAva = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($countAva);

if($dadosMon==''){

        if($infoUser['nivel_id']>2){
            echo "<br><br><h4>Monitoria ainda não foi realizada, aguarde!</h4>";
        } else {
            $sql = "SELECT a.campo_id as id_campo, a.fila_id, b.nome_fila, a.input_id, c.nome_input, c.tipo_input, d.desc_campo, d.nome_campo, a.ativo, a.date_time, a.ordem, a.obg FROM tbl_forms_mon_input_campo_cnf a, tbl_config_fila b, tbl_forms_mon_input c, tbl_forms_mon_input_campo d where b.id_fila=".$_POST['fila']." and b.id_fila=a.fila_id and a.ativo=1 and a.fila_id=b.id_fila and a.input_id=c.id_input and a.campo_id=d.id_campo order by ordem asc";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $campoConfig = $stmt->fetchAll( PDO::FETCH_ASSOC );
            //depurador($campoConfig);
            //echo "<br>".count($campoConfig);

            for($num=0;$num<count($campoConfig);$num++){

                if(($campoConfig[$num]['tipo_input']=='checkbox')){

                        $stmt = $PDO->prepare( "SELECT id_option, desc_option, referencia, value_option from tbl_forms_mon_input_option where referencia='opcao_chk_1_mon' and campo_id=".$campoConfig[$num]['id_campo'] );
                        $result = $stmt->execute();
                        $option_1 = $stmt->fetch( PDO::FETCH_ASSOC );
                        //depurador($option_1);

                        $stmt = $PDO->prepare( "SELECT id_option, desc_option, referencia, value_option from tbl_forms_mon_input_option where referencia='opcao_chk_2_mon' and campo_id=".$campoConfig[$num]['id_campo'] );
                        $result = $stmt->execute();
                        $option_2 = $stmt->fetch( PDO::FETCH_ASSOC );
                        //depurador($option_2);


                    inputCheckboxMon($campoConfig[$num]['desc_campo'], $campoConfig[$num]['nome_campo'], $option_1['desc_option'], $option_1['id_option'], $option_1['value_option'], $option_2['desc_option'], $option_2['id_option'], $option_2['value_option'], $campoConfig[$num]['obg'], $_POST['id_chat']);
                } else

                if($campoConfig[$num]['tipo_input']=='select'){
                    $sql="SELECT desc_option, value_option from tbl_forms_mon_input_option where campo_id=".$campoConfig[$num]['id_campo']." and ativo=1 order by desc_option";
                    //echo "<br>".$sql;
                    $stmt = $PDO->prepare( $sql );
                    $result = $stmt->execute();
                    $options = $stmt->fetchAll( PDO::FETCH_ASSOC );
                    //depurador($options);

                    inputSelectMon($campoConfig[$num]['desc_campo'], $campoConfig[$num]['nome_campo'], $options, $campoConfig[$num]['obg'], $_POST['id_chat']);
                }

                else{
                    inputTextMon($campoConfig[$num]['desc_campo'], $campoConfig[$num]['nome_campo'], $campoConfig[$num]['tipo_input'], $campoConfig[$num]['obg'], $_POST['id_chat']);

                }


            }
            ?>
                <div>
                    <button class="btn btn-success" id="save_mon_<?=$_POST['id_chat'];?>" type="button">Salvar Monitoria</button>
                </div>
                <div id="save_feed"></div>

                <script>
                    $('#save_mon_<?=$_POST['id_chat'];?>').click(function(){
                                    var feed = '#save_feed';
                                    var fila_id = '<?=$_POST['fila']; ?>';
                                    var contrato_id = '<?=$_POST['contrato']; ?>';
                                    var chat_id = '<?=$_POST['id_chat']; ?>';
                                    var resp_mon = '<?=$infoUser['id_user']; ?>';
                                    <?php
                                        $sql = "SELECT nome_campo, input_id, (SELECT tipo_input from tbl_forms_mon_input where id_input=input_id) as tipo_input FROM tbl_forms_mon_input_campo where fila_id=".$_POST['fila'];
                                        //echo "<br>".$sql;
                                        $stmt = $PDO->prepare($sql);
                                        $result = $stmt->execute();
                                        $campoScript = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                        if(count($campoScript)>0){
                                            for($num=0;$num<count($campoScript);$num++){
                                                if($campoScript[$num]['tipo_input']!='checkbox'){
                                                    echo 'var '.$campoScript[$num]['nome_campo'].'= $("#'.$campoScript[$num]['nome_campo'].'_'.$_POST['id_chat'].'").val();'."\n";
                                                } else {
                                                    echo 'var '.$campoScript[$num]['nome_campo'].'= $("input:radio[name='.$campoScript[$num]['nome_campo'].'_'.$_POST['id_chat'].']:checked").val();'."\n";
                                                }

                                            }
                                        }
                                    ?>
                                    //console.log(assunto);

                                    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></center>');
                                    <?php
                                        if(count($campoScript)>0){
                                            for($num=0;$num<count($campoScript);$num++){
                                                echo "console.log(".$campoScript[$num]['nome_campo'].");";
                                            }
                                        }
                                    ?>
                                    $.post("staff/save_mon.php",
                                    {
                                        <?php

                                            if(count($campoScript)>0){
                                                for($num=0;$num<count($campoScript);$num++){
                                                    echo $campoScript[$num]['nome_campo'].',';
                                                }
                                            }

                                        ?>
                                        fila_id, contrato_id, chat_id, resp_mon

                                    },
                                    function (valor) {
                                        $(feed).html(valor);
                                    });


                                });
                </script>
            <?php
        }


} else {

    ?>
    <div id="result">
        <?php
            if($dadosMon['avaliacao']!='' && $countAva['qtd'] > 0){
                echo "Pontuação: ".$dadosMon['avaliacao'];
            }
        ?>
    </div>

    <?php
    $sql = "SELECT a.campo_id as id_campo, a.fila_id, b.nome_fila, a.input_id, c.nome_input, c.tipo_input, d.desc_campo, d.nome_campo, a.ativo, a.qualif, a.date_time, a.ordem, a.obg FROM tbl_forms_mon_input_campo_cnf a, tbl_config_fila b, tbl_forms_mon_input c, tbl_forms_mon_input_campo d where b.id_fila=".$_POST['fila']." and b.id_fila=a.fila_id and a.ativo=1 and a.fila_id=b.id_fila and a.input_id=c.id_input and a.campo_id=d.id_campo order by ordem asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $campoConfig = $stmt->fetchAll( PDO::FETCH_ASSOC );


    for($quest=0;$quest < count($campoConfig);$quest++){
        $campoPt = "pt_".$campoConfig[$quest]['nome_campo'];
        //echo "<br>".$dadosMon[$campoPt];

        if($dadosMon[$campoPt]!=''){
            $span = '<span class="badge bg-success">'.$dadosMon[$campoPt].'</span>';
        } else {
            $span='';
        }

        $dadosMon[$campoConfig[$quest]['nome_campo']] = str_replace("_", " ", $dadosMon[$campoConfig[$quest]['nome_campo']]);

    ?>
    <style>
        .div_question {
            background-color: #FFFFFF;
            width: 100%;
        }
        .question {
            width: 100%;
            background-color: #EEEEEE;
            padding-left: 5px;
            font-weight: bold;
            text-align: left;
        }
        .resposta {
            width: 100%;
            padding-left: 10px;
            text-align: left;
        }
        #result {
            text-align: center;
            font-size: 18px;
        }
    </style>
    <div>
        <div class="div_question">
            <div class="question"><?=$campoConfig[$quest]['desc_campo'];?></div>
            <div class="resposta"><?=$span?> <?=ucwords(strtolower($dadosMon[$campoConfig[$quest]['nome_campo']]));?></div>
        </div>
    </div>


    <?php
    }
}
?>
</div>
