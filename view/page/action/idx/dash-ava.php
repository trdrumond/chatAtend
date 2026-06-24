<?php
include("cnf/session.php");
include('cnf/rotina_pendencia.php');
require_once __DIR__ . '/../cnf/_cnf_ui.php';

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */
?>



<style>

    .left {
        float: left;
    }

    #perfil_user {
        background-color: #EEEEEE;
        border-radius: 10px;
        margin-left: 5px;
        margin-top: 5px;
        padding: 10px;
        width: 15%;
        min-height: 150px;
        height: 390px;
        float: left;
    }

    .label-perfil {
        font-size: 10px;
        color: #B7202F;
    }

    .info-perfil {
        font-size: 12px;
        padding-left: 10px;

    }

    #div_ope {
        /* background-color: #FFAAAA; */
        border-radius: 10px;
        margin-left: 5px;
        margin-top: 5px;
        width: 84%;
        min-width: 300px;
        height: 390px;
        float: left;
    }

    #img-perfil-user {
        margin: auto;
        width: 100%;
    }

    #perfil-img {
        margin: auto;
        text-align: center;
    }

    #bloco_central {
        margin-top: 20px;
        margin-left: auto;
        margin-right: auto;
        width: 30%;
        height: 200px;
        border: solid 1px #B7202F;
        border-radius: 10px;
    }






</style>

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

<?php
    $stm = $PDO->query("SELECT protocolo, data_hora from tbl_chat_fila where status_fila=1 and ate_resp=".$infoUser['id_user']);
    $infoFila = $stm->fetch(PDO::FETCH_ASSOC);
    if($infoFila['protocolo']!=''){
        echo '<div id="load_gif"><img src="img/loading.gif" alt="Verificando fila..." width="200"></div>';
        echo "<script>setTimeout(function(){ actionPage('chat-fila', 'idx'); }, 1000);</script>";
    } else {

?>


    <div id="dashboard">
        <?php
                $sqlVer="SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, timediff(now(), data_hora) as te from tbl_chat_fila where (status_fila=".ST_FILA_NA_FILA." or ".stFilaSqlChamarSolicitante().") and ate_resp=".$_SESSION['dados']['id_user'];
                //echo "<br>".$sqlVer;
                $stmt = $PDO->prepare($sqlVer);
                $result = $stmt->execute();
                $infFila = $stmt->fetch( PDO::FETCH_ASSOC );
                //var_dump($infFila);

                if($infFila){
                    if($infFila['status_fila']==ST_FILA_NA_FILA){
                        echo "<script>setTimeout(function(){ actionPage('chat-fila', 'idx'); }, 1000);</script>";
                    } else if(stFilaDeveChamarSolicitante((int)$infFila['status_fila'])){
                        echo "<script>setTimeout(function(){ actionPage('chat-ate', 'idx'); }, 1000);</script>";
                    }
                } else {



        ?>
            <div id="div_ope">
                <div id="bloco_central">
                    <div class="st-form cnf-form">
                        <div class="st-form-grid st-form-grid--1">
                            <div id="div_fila" class="st-field input-container" style="display: none;">
                                <label class="st-label" for="fila">Fila</label>
                                <select name="fila" id="fila">
                                    <option value="">Selecione uma fila?</option>
                                    <?php
                                        $sql="SELECT id_fila, nome_fila, ativo from tbl_config_fila where ativo=1 and contrato_id in (". $infoUser['contrato_id'].") order by nome_fila asc";
                                        $stmt = $PDO->prepare($sql);
                                        $stmt->execute();
                                        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        for($x=0;$x<count($dados);$x++){
                                            echo '<option value="'.$dados[$x]['id_fila'].'">'.$dados[$x]['nome_fila'].'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div id="div_assunto" class="st-field input-container" style="display: none;">
                                <label class="st-label" for="assunto">Assunto</label>
                                <select name="assunto" id="assunto">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="div_btn" class="content-10-line" style="display: none;">
                        <center>
                            <button type="button" id="call" class="btn btn-danger"><i class="fas fa-comment-dots"></i> Chamar</button>
                            <br>
                            <div id="feed_call"></div>
                        </center>

                    </div>
                </div>
            </div>

            <?php } ?>

    </div>

    <script>
        $('#div_fila').show(500);


        $(document).ready(function () {
            $("#call").click(function(){
                var fila = $('#fila').val();
                var assunto = $('#assunto').val();
                if(assunto==''){
                    menAlert();
                } else {
                    call(fila, assunto);
                }

            });

            $("#fila").change(function(){
                var fila = $('#fila').val();
                //console.log('A fila é: ' + fila);
                if(fila!=''){
                    $('#div_assunto').show(500);
                    loadAss(fila);
                } else {
                    $('#div_assunto').hide(500);
                    $('#div_btn').hide(500);
                }

            });

            $("#assunto").change(function(){
                var assunto = $('#assunto').val();
                //console.log('O assunto é: ' + assunto);
                if(assunto!=''){
                    $('#div_btn').show(500);
                } else {
                    $('#div_btn').hide(500);
                }

            });



            function call(fila, assunto){
                $("#feed_call").html('<br><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                $.post("staff/save_call.php",
                {
                    fila, assunto
                },
                function (valor) {
                    $("#feed_call").html(valor);
                });
            }

            function loadAss(fila){
                //console.log(fila);
                $("#assunto").html('<option>Carregando assuntos...</option>');
                $.post("staff/load_ass.php",
                {
                    fila
                },
                function (valor) {
                    $("#assunto").html(valor);
                });
            }

            function menAlert(){
                $("#feed_call").html('<br><div style="color: red">Você deve informar o assunto que você quer falar!</div>');
            }

        });
    </script>

    <?php } ?>

    <script type="text/javascript" src="js/load.js"></script>
