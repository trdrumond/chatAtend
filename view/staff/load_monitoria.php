<?php
$filaId = (int) ($_POST['fila'] ?? 0);
$contratoId = (int) ($_POST['contrato'] ?? 0);
$idChat = (int) ($_POST['id_chat'] ?? 0);
?>
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
        <button type="button" class="btn-close" onclick="closeMon(<?=$idChat; ?>)"></button>
    </div>
</div>

<div>



<?php
include("../cnf/session.php");
include("../cnf/func_input.php");

if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    echo '<p class="text-danger">Contrato não autorizado.</p>';
    return;
}

//depurador($_POST);
//depurador($infoUser);

$tableMon = 'tbl_in_mon_' . $filaId . '_' . $contratoId;
if (!preg_match('/^tbl_in_mon_\d+_\d+$/', $tableMon)) {
    return;
}

$sql = "SELECT * from {$tableMon} where chat_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$idChat]);
$dadosMon = $stmt->fetch( PDO::FETCH_ASSOC );

$sql = "SELECT count(campo_id) as qtd from tbl_forms_mon_input_campo_cnf where fila_id=? and ativo=1 and qualif=1";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$filaId]);
$countAva = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($countAva);

if($dadosMon==''){

        if($infoUser['nivel_id']>2){
            echo "<br><br><h4>Monitoria ainda não foi realizada, aguarde!</h4>";
        } else {
            $sql = "SELECT a.campo_id as id_campo, a.fila_id, b.nome_fila, a.input_id, c.nome_input, c.tipo_input, d.desc_campo, d.nome_campo, a.ativo, a.date_time, a.ordem, a.obg FROM tbl_forms_mon_input_campo_cnf a, tbl_config_fila b, tbl_forms_mon_input c, tbl_forms_mon_input_campo d where b.id_fila=? and b.id_fila=a.fila_id and a.ativo=1 and a.fila_id=b.id_fila and a.input_id=c.id_input and a.campo_id=d.id_campo order by ordem asc";
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute([$filaId]);
            $campoConfig = $stmt->fetchAll( PDO::FETCH_ASSOC );
            //depurador($campoConfig);
            //echo "<br>".count($campoConfig);

            for($num=0;$num<count($campoConfig);$num++){

                if(($campoConfig[$num]['tipo_input']=='checkbox')){

                        $stmt = $PDO->prepare( "SELECT id_option, desc_option, referencia, value_option from tbl_forms_mon_input_option where referencia='opcao_chk_1_mon' and campo_id=?" );
                        $result = $stmt->execute([$campoConfig[$num]['id_campo']]);
                        $option_1 = $stmt->fetch( PDO::FETCH_ASSOC );
                        //depurador($option_1);

                        $stmt = $PDO->prepare( "SELECT id_option, desc_option, referencia, value_option from tbl_forms_mon_input_option where referencia='opcao_chk_2_mon' and campo_id=?" );
                        $result = $stmt->execute([$campoConfig[$num]['id_campo']]);
                        $option_2 = $stmt->fetch( PDO::FETCH_ASSOC );
                        //depurador($option_2);


                    inputCheckboxMon($campoConfig[$num]['desc_campo'], $campoConfig[$num]['nome_campo'], $option_1['desc_option'], $option_1['id_option'], $option_1['value_option'], $option_2['desc_option'], $option_2['id_option'], $option_2['value_option'], $campoConfig[$num]['obg'], $idChat);
                } else

                if($campoConfig[$num]['tipo_input']=='select'){
                    $sql="SELECT desc_option, value_option from tbl_forms_mon_input_option where campo_id=? and ativo=1 order by desc_option";
                    $stmt = $PDO->prepare( $sql );
                    $result = $stmt->execute([$campoConfig[$num]['id_campo']]);
                    $options = $stmt->fetchAll( PDO::FETCH_ASSOC );
                    //depurador($options);

                    inputSelectMon($campoConfig[$num]['desc_campo'], $campoConfig[$num]['nome_campo'], $options, $campoConfig[$num]['obg'], $idChat);
                }

                else{
                    inputTextMon($campoConfig[$num]['desc_campo'], $campoConfig[$num]['nome_campo'], $campoConfig[$num]['tipo_input'], $campoConfig[$num]['obg'], $idChat);

                }


            }
            ?>
                <div>
                    <button class="btn btn-success" id="save_mon_<?=$idChat;?>" type="button">Salvar Monitoria</button>
                </div>
                <div id="save_feed"></div>

                <script>
                    $('#save_mon_<?=$idChat;?>').click(function(){
                                    var feed = '#save_feed';
                                    var fila_id = '<?=$filaId; ?>';
                                    var contrato_id = '<?=$contratoId; ?>';
                                    var chat_id = '<?=$idChat; ?>';
                                    var resp_mon = '<?=$infoUser['id_user']; ?>';
                                    <?php
                                        $sql = "SELECT nome_campo, input_id, (SELECT tipo_input from tbl_forms_mon_input where id_input=input_id) as tipo_input FROM tbl_forms_mon_input_campo where fila_id=?";
                                        $stmt = $PDO->prepare($sql);
                                        $result = $stmt->execute([$filaId]);
                                        $campoScript = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                        if(count($campoScript)>0){
                                            for($num=0;$num<count($campoScript);$num++){
                                                $nc = (string) ($campoScript[$num]['nome_campo'] ?? '');
                                                if (!preg_match('/^[a-zA-Z0-9_]+$/', $nc)) {
                                                    continue;
                                                }
                                                if($campoScript[$num]['tipo_input']!='checkbox'){
                                                    echo 'var '.$nc.'= $("#'.$nc.'_'.$idChat.'").val();'."\n";
                                                } else {
                                                    echo 'var '.$nc.'= $("input:radio[name='.$nc.'_'.$idChat.']:checked").val();'."\n";
                                                }

                                            }
                                        }
                                    ?>
                                    //console.log(assunto);

                                    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></center>');
                                    $.post("staff/save_mon.php",
                                    {
                                        <?php

                                            if(count($campoScript)>0){
                                                for($num=0;$num<count($campoScript);$num++){
                                                    $nc = (string) ($campoScript[$num]['nome_campo'] ?? '');
                                                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nc)) {
                                                        continue;
                                                    }
                                                    echo $nc.',';
                                                }
                                            }

                                        ?>
                                        fila_id, contrato_id, chat_id, resp_mon

                                    },
                                    function (valor) {
                                        $(feed).html(typeof stSafeChatHtml === 'function' ? stSafeChatHtml(valor) : valor);
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
    $sql = "SELECT a.campo_id as id_campo, a.fila_id, b.nome_fila, a.input_id, c.nome_input, c.tipo_input, d.desc_campo, d.nome_campo, a.ativo, a.qualif, a.date_time, a.ordem, a.obg FROM tbl_forms_mon_input_campo_cnf a, tbl_config_fila b, tbl_forms_mon_input c, tbl_forms_mon_input_campo d where b.id_fila=? and b.id_fila=a.fila_id and a.ativo=1 and a.fila_id=b.id_fila and a.input_id=c.id_input and a.campo_id=d.id_campo order by ordem asc";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$filaId]);
    $campoConfig = $stmt->fetchAll( PDO::FETCH_ASSOC );


    for($quest=0;$quest < count($campoConfig);$quest++){
        $campoPt = "pt_".$campoConfig[$quest]['nome_campo'];
        //echo "<br>".$dadosMon[$campoPt];

        if($dadosMon[$campoPt]!=''){
            $span = '<span class="badge bg-success">'.stHtml($dadosMon[$campoPt]).'</span>';
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
            <div class="question"><?= stHtml($campoConfig[$quest]['desc_campo']); ?></div>
            <div class="resposta"><?= $span ?> <?= stHtml(ucwords(strtolower((string) $dadosMon[$campoConfig[$quest]['nome_campo']]))); ?></div>
        </div>
    </div>


    <?php
    }
}
?>
</div>
