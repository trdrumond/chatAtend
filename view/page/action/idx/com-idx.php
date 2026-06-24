<?php
include("cnf/session.php");
require_once __DIR__ . '/../cnf/_cnf_ui.php';

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */
?>
<script src="js/tabs-com.js?<?= time() ?>"></script>

<!-- <script type="text/javascript" src='chat/assets/js/script_com_msg.js?<?= time() ?>' defer></script> -->

<!-- <script type="text/javascript" src='chat/assets/js/script_group.js?<?= time() ?>' defer></script> -->
<style>
#bloco {
    margin-top: 5px;
    width: 99%;
    float: left;
    height: 460px;
}

#menu-com {
    float: left;
    width: 20%;
    height: 455px;
    overflow: scroll;
}

#content-com {
    float: left;
    width: 79%;
    height: 445px;
    margin-left: 10px;
    background-color: #FFFFFF;
}

#menu-names {
    height: 340px;
}

.tab-pesq {
    background-color: #FFFFFF;
    border-bottom: 1px solid #AAAAAA;
    font-size: 16px;
    height: 35px;
    text-align: left;
    padding-top: 3px;
    padding-bottom: 3px;
    padding-left: 2px;
}

.tab {
    background-color: #FFFFFF;
    border-left: 5px solid #FFFFFF;
    border-bottom: 1px solid #AAAAAA;
    font-size: 16px;
    height: 35px;
    text-align: left;
    padding-top: 3px;
    padding-bottom: 3px;
    padding-left: 2px;
    cursor: pointer;
}

.tab:hover {
    border-left: 5px solid #CCCCCC;
}

.active-tab {
    background-color: #FFFFFF;
    color: #000000;
    border-left: 5px solid #ff6d6d;

}

.blink_me {
    animation: blinker 2s linear infinite;
}

.group {
    background-color: #DDDDDD;
}

@keyframes blinker {
    50% {
        opacity: 0.9;
        background-color: #F4D6D5;
    }
}
</style>

<div id="bloco">

    <div id="menu-com">
        <?php if($infoUser['new_conv']==1){ ?>
        <button type="button" class="btn btn-secondary bol" data-bs-toggle="modal" data-bs-target="#new_com"
            title="Nova conversa"><i class="fas fa-plus"></i></button>
        <?php } ?>
        <?php if($infoUser['nivel_id']<2 && $infoUser['men_massa']==1){ ?>
        <button type="button" class="btn btn-secondary bol" data-bs-toggle="modal" data-bs-target="#msg_massa"
            title="Mensagem em Massa"><i class="fas fa-comments"></i></button>
        <?php } ?>
        <?php if($infoUser['nivel_id']<1 && $infoUser['grupos']==1){ ?>
        <button type="button" class="btn btn-secondary bol" data-bs-toggle="modal" data-bs-target="#new_group"
            title="Novo Grupo"><i class="fab fa-rocketchat"></i></button>
        <?php } ?>
        <?php if($infoUser['nivel_id']<1){ ?>
        <button type="button" class="btn btn-secondary bol" id="com-idx-list" title="Histórico de Comunicação"><i
                class="fas fa-list"></i></button>

        <?php } ?>
        <div class="st-field input-container" style="margin-top: 15px;">
            <label class="st-label" for="filtro-nome-com">Pesquisar</label>
            <input id="filtro-nome-com" class="input" type="text" />
        </div>
        <div id="menu-names">
        </div>


    </div>

    <div id="content-com">
        <br><br>
        <center><img src="img/chat_transp.fw.png" alt="Carregando..." width="300"></center>

    </div>

    <script>
    //loadCom(1);

    loadComList();

    function loadComList(indice, com) {
        var user = '<?=$infoUser['id_user']?>';

        if (typeof indice !== 'undefined') {
            var indice = indice;
        } else {
            var indice = '';
        }
        if (typeof com !== 'undefined') {
            var com = com;
        } else {
            var com = '';
        }
        //console.log('load: ' + indice + ' - ' + com)

        //$('#menu-names').html('<center><img src="img/loading.gif" alt="Carregando..." width="200"><br>CARREGANDO CONVERSA...</center>');
        if (conn.readyState === 1) {
            $.post("staff/load_com_list.php", {
                    user,
                    indice,
                    com
                },
                function(valor) {
                    $("#menu-names").html(valor);
                });
        } else {
            setTimeout(function() {
                loadCom(id);
            }, 1000);
        }
    }

    function loadCom(indice, id_com) {
        //console.log('Teste 1.2.1');
        //console.log(conn);
        $('#content-com').html(
            '<center><img src="img/loading.gif" alt="Carregando..." width="200"><br>CARREGANDO MENSAGENS...</center>'
            );
        if (conn.readyState === 1) {
            $.post("staff/load_com.php", {
                    indice,
                    id_com
                },
                function(valor) {
                    $("#content-com").html(valor);
                });
        } else {
            setTimeout(function() {
                loadCom(indice, id);
            }, 1000);
        }
    }

    $("#com-idx-list").click(function() {
        actionPage('com-idx-list', 'idx');
    });

    function actionPage(action, sec) {

        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');
        //console.log('A ação é: ' + action);

        $.post("action.php", {
                action: action,
                sec: sec
            },
            function(valor) {
                $("#action-page").html(valor);
            });

    }

    $(document).ready(function() {
        $('#filtro-nome-com').keyup(function() {
            var nomeFiltro = $(this).val().toLowerCase();
            $("#menu-names .tab").css("display", "block");
            $("#menu-names .tab").each(function() {
                var conteudoCelula = $(this).text();
                var corresponde = conteudoCelula.toLowerCase().indexOf(nomeFiltro) >= 0;
                $(this).css('display', corresponde ? '' : 'none');
            });
        });
    });
    </script>

</div>

<?php
$nivelOptsCom = '<option value="">Perfil</option>';
$sql = "SELECT id_nivel, nome_nivel, icon from tbl_nivel where id_nivel<>0 order by id_nivel asc";
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dadosNivelCom = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dadosNivelCom); $x++) {
    $nivelOptsCom .= '<option value="' . $dadosNivelCom[$x]['id_nivel'] . '">' . $dadosNivelCom[$x]['nome_nivel'] . '</option>';
}

$qryColCom = ($infoUser['nivel_id'] != 0) ? ' and contrato_id=' . $infoUser['contrato_id'] : '';
$colOptsCom = '<option value="">Colaborador</option>';
$sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_col from tbl_user where id_user<>" . $infoUser['id_user'] . " $qryColCom and nivel_id<>0 order by nome_col asc";
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dadosColCom = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dadosColCom); $x++) {
    $colOptsCom .= '<option value="' . $dadosColCom[$x]['id_user'] . '">' . $dadosColCom[$x]['nome_col'] . '</option>';
}
?>
<!-- NOVA CONVERSA -->
<?php cnf_modal_shell_open('new_com', 'Nova Conversa'); ?>
<div class="st-form-section cnf-form-section">
    <div class="st-form-grid st-form-grid--1">
        <?php
        cnf_field_select('nivel', 'Perfil', $nivelOptsCom);
        cnf_field_select('col', 'Colaborador', $colOptsCom);
        ?>
    </div>
</div>
</div>
<div class="modal-footer cnf-modal-footer">
    <div id="save_feed_cad" class="cnf-feed"></div>
    <button type="button" id="close" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
    <button type="button" id="save" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar</button>
</div>
</div></div></div>

<?php
$nivelOptsMassa = '<option value="">Perfil</option>';
$sql = "SELECT id_nivel, nome_nivel, icon from tbl_nivel where id_nivel<>0 order by id_nivel asc";
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dadosNivelMassa = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dadosNivelMassa); $x++) {
    $nivelOptsMassa .= '<option value="' . $dadosNivelMassa[$x]['id_nivel'] . '">' . $dadosNivelMassa[$x]['nome_nivel'] . '</option>';
}

$qryColMassa = ($infoUser['nivel_id'] != 0) ? ' and contrato_id=' . $infoUser['contrato_id'] : '';
$colOptsMassa = '';
$sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_col from tbl_user where id_user<>" . $infoUser['id_user'] . " $qryColMassa and nivel_id<>0 order by nome_col asc";
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dadosColMassa = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dadosColMassa); $x++) {
    $colOptsMassa .= '<option value="' . $dadosColMassa[$x]['id_user'] . '">' . $dadosColMassa[$x]['nome_col'] . '</option>';
}
$msgMassaSuffix = time();
$msgMassaId = 'message_com_massa_' . $msgMassaSuffix;
?>
<!-- MENSAGEM EM MASSA -->
<?php cnf_modal_shell_open('msg_massa', 'Mensagem em Massa'); ?>
<div class="st-form-section cnf-form-section">
    <div class="st-form-grid st-form-grid--1">
        <?php cnf_field_select('nivel_massa', 'Perfil', $nivelOptsMassa); ?>
        <div class="st-field input-container">
            <label class="st-label" for="col_massa">Colaboradores</label>
            <select id="col_massa" name="col_massa[]" multiple class="form-control" size="10">
                <?= $colOptsMassa ?>
            </select>
        </div>
        <input type="hidden" name="name_com_massa" id="name_com_massa" value="<?= ucwords(strtolower($infoUser['nome_completo'])) ?>" />
        <input type="hidden" name="id_user_remetente_com_massa" id="id_user_remetente_com_massa" value="<?= $infoUser['id_user'] ?>" />
        <input type="hidden" name="img_com_massa" id="img_com_massa" value="<?= $infoUser['img_perfil'] ?>" />
        <?php cnf_field_textarea($msgMassaId, 'Mensagem', ['rows' => 3, 'full' => true]); ?>
    </div>
</div>
</div>
<div class="modal-footer cnf-modal-footer">
    <div id="send_feed_massa" class="cnf-feed"></div>
    <button type="button" id="close_massa" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
    <button type="button" id="send_massa" class="btn btn-solvetask" title="Enviar"><i class="far fa-paper-plane"></i> Enviar</button>
</div>
</div></div></div>

<?php
$qryGroup = ($infoUser['nivel_id'] != 0) ? ' and contrato_id=' . $infoUser['contrato_id'] : '';
$sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_col, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel from tbl_user where id_user<>" . $infoUser['id_user'] . " $qryGroup order by nivel asc, nome_col asc";
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dadosGroup = $stmt->fetchAll(PDO::FETCH_ASSOC);
$colOptsGroup = '';
for ($x = 0; $x < count($dadosGroup); $x++) {
    $colOptsGroup .= '<option value="' . $dadosGroup[$x]['id_user'] . '">' . $dadosGroup[$x]['nivel'] . ' - ' . $dadosGroup[$x]['nome_col'] . '</option>';
}
?>
<!-- NOVO GRUPO -->
<?php cnf_modal_shell_open('new_group', 'Novo Grupo'); ?>
<div class="st-form-section cnf-form-section">
    <div class="st-form-grid st-form-grid--1">
        <?php cnf_field_input('nome_grupo', 'Nome do Grupo', ['required' => true]); ?>
        <div class="st-field input-container">
            <label class="st-label" for="col_group">Participantes</label>
            <select id="col_group" name="col_group[]" multiple class="form-control" size="15">
                <?= $colOptsGroup ?>
            </select>
        </div>
    </div>
</div>
</div>
<div class="modal-footer cnf-modal-footer">
    <div id="save_feed_group" class="cnf-feed"></div>
    <button type="button" id="close_group" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
    <button type="button" id="save_group" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar</button>
</div>
</div></div></div>

<script>
$(document).ready(function() {
    $("#nivel").change(function() {
        var nivel = $('#nivel').val();
        //console.log(contrato);
        loadCol(nivel);
    });

    $("#nivel_massa").change(function() {
        var nivel = $('#nivel_massa').val();
        //console.log(contrato);
        loadColMassa(nivel);
    });

    $("#save").click(function() {
        //console.log('Clicou no botão');
        var col = $('#col').val();
        saveRegistro(col);
    });

    $("#send_massa").click(function() {
        //console.log('Clicou no botão');
        var col = $('#col_massa').val();
        var nome = $('#name_com_massa').val();
        var rem = $('#id_user_remetente_com_massa').val();
        var img = $('#img_com_massa').val();
        var msg = $('#<?= $msgMassaId ?>').val();

        sendMassa(col, nome, rem, img, msg);
        $('#<?= $msgMassaId ?>').val('');
        $('#col_massa').val('');

    });

    $("#save_group").click(function() {
        //console.log('Clicou no botão');
        var col = $('#col_group').val();
        var nome_grupo = $('#nome_grupo').val();

        sendGroup(col, nome_grupo);
        //$('#col_group').val('');

    });


    function saveRegistro(col) {
        $('#close').click();
        $("#save_feed_cad").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
            );
        $.post("staff/save_new_chat.php", {
                col
            },
            function(valor) {
                $("#save_feed_cad").html(valor);
            });

    }

    function sendMassa(col, nome, rem, img, msg) {

        $('#close_massa').click();
        $("#send_feed_massa").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
            );
        $.post("staff/send_msg_massa.php", {
                col,
                nome,
                rem,
                img,
                msg
            },
            function(valor) {
                $("#send_feed_massa").html(valor);
                loadComList();
            });
    }

    function sendGroup(col, nome_grupo) {

        //$('#close_group').click();
        $("#save_feed_group").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
            );
        $.post("staff/save_new_grupo.php", {
                col,
                nome_grupo
            },
            function(valor) {
                $("#save_feed_group").html(valor);
                loadComList();
            });
    }

    function loadCol(nivel) {
        $("#col").html('Carregando...');
        //console.log("Contrato escolhido: "+contrato);
        $.post("staff/load_col.php", {
                nivel
            },
            function(valor) {
                $("#col").html(valor);
            });
    }

    function loadColMassa(nivel) {
        $("#col_massa").html('Carregando...');
        //console.log("Contrato escolhido: "+contrato);
        $.post("staff/load_col.php", {
                nivel
            },
            function(valor) {
                $("#col_massa").html(valor);
            });
    }

    setTimeout(function() {
        message_box_massa_<?= $msgMassaSuffix ?>();
    }, 0);


    function message_box_massa_<?= $msgMassaSuffix ?>() {
        stTinyMceApply('#<?= $msgMassaId ?>', {
            menubar: false,
            height: 100,
            branding: false,
            promotion: false,
            toolbar: '',
            invalid_elements: "div,span,a,nav,code,h1,h2,h3,h4,h5,script,style,tr,table,td,javascript",
            content_style: 'body { font-size:12px }'
        });
    }


});
</script>
