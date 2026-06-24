<?php
require_once __DIR__ . '/../../cnf/session.php';

/** @var array<string, mixed> $infoUser */
/** @var string $titulo_app */
/** @var int|string $tmpDash */
/** @var string $men */

//$infoUser['nome'];
//depurador($infoUser);

$infoUser['fila_id'] = ($infoUser['fila_id'] == '' || $infoUser['fila_id'] === null) ? 0 : (int) $infoUser['fila_id'];
$infoUser['contrato_id'] = ($infoUser['contrato_id'] == '' || $infoUser['contrato_id'] === null) ? 0 : (int) $infoUser['contrato_id'];
$infoUser['id_user'] = (int) $infoUser['id_user'];
//echo "<br>".$infoUser['fila_id'];
?>


<script type="text/javascript" src="js/dadosIdx.js"></script>

<script>
loaddadosIdx();

function loaddadosIdx() {
    //console.log('Atualiza informações indice bko');
    dadosIdx(<?= (int) $infoUser['id_user'] ?>, <?= (int) $infoUser['fila_id'] ?>, <?= (int) $infoUser['contrato_id'] ?>);
}
<?php if($infoUser['nivel_id']==4 || $infoUser['nivel_id']==5){ ?>

if (window.__dashIdxInterval) {
    clearInterval(window.__dashIdxInterval);
}
window.__dashIdxInterval = setInterval(loaddadosIdx, <?php echo (int)$tmpDash; ?>);

<?php } ?>
</script>
<?php if ($infoUser['nivel_id'] == 4) { ?>
<style>
.fila_in { color: #16a34a; }
.fila_out { color: #FA5252; }
</style>
<?php } ?>


<div id="indice-idx-men">
    <div class="alert-msg" role="alert">
        <?php echo $men; ?> de trabalho para você, <?=rtrim(ucwords(strtolower($infoUser['nome'])))?>!
        <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
    </div>
</div>
<?php
    if($infoUser['nivel_id']==4){
        if($infoUser['fila_status']==1){
            $classFila = 'fila_in';
            $titleFila = 'Fila Ativa';
        } else {
            $classFila = 'fila_out';
            $titleFila = 'Fila Desativada';
        }

?>
<div id="indice-idx-fila">
    <div class="content-2-line">
        <div id="feed_fila_sel"></div>
        <div id="idx-Fila" class="<?=$classFila?>" title="<?=$titleFila?>"><i class="fas fa-list-alt fa-3x"></i></div>
    </div>
    <div class="content-7-line">
        <div class="input-container">
            <select name="fila_sel" id="fila_sel">
                <?php
                    $sql = "SELECT id_fila, nome_fila FROM tbl_config_fila WHERE contrato_id = :contrato_id ORDER BY nome_fila";
                    $stmt = $PDO->prepare($sql);
                    $stmt->bindValue(':contrato_id', (int)$infoUser['contrato_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if($infoUser['fila_id']==$fila['id_fila']){$sel="selected";} else {$sel="";}
                        echo '<option value="'.$fila['id_fila'].'" '.$sel.'>'.$fila['nome_fila'].'</option>';
                    }
                ?>
            </select>
        </div>
    </div>


    <script>
    $("#fila_sel").off('change.idxFila').on('change.idxFila', function() {
        var fila = $('#fila_sel').val();
        var user = <?=$infoUser['id_user']?>;
        saveNewFila(user, fila);
    });


    function saveNewFila(user, fila) {
        //$("#feed_fila_sel").html('Carregando...');
        //console.log("Contrato escolhido: "+contrato);
        $.post("staff/save_new_fila.php", {
                user,
                fila
            },
            function(valor) {
                $("#feed_fila_sel").html(valor);
                //console.log(valor);
            });
    }
    </script>
</div>
<?php } ?>

<div id="indice-idx">
    <div id="dadosDashInd"></div>


    <?php if($infoUser['nivel_id']<5){ ?>
    <div class="bloco_info">
        <div class="til">Backoffice</div>
        <div id="dadosOn" class="inf info1">---</div>
    </div>

    <div class="bloco_info pointer" <?php if($infoUser['nivel_id']<=2){ ?> onclick="$('#fundo_mod_fila').fadeIn()"
        <?php } ?>>
        <div class="til">Em Fila</div>
        <div id="dadosFila" class="inf info2">---</div>
    </div>
    <div class="bloco_info pointer" <?php if($infoUser['nivel_id']<=2){ ?> onclick="$('#fundo_mod_atend').fadeIn()"
        <?php } ?>>
        <div class="til">Em Atend.</div>
        <div id="dadosAtend" class="inf info3">---</div>
    </div>

    <?php if($infoUser['nivel_id']<4){ ?>
    <div class="bloco_info pointer" <?php if($infoUser['nivel_id']<=2){ ?> onclick="$('#fundo_mod_concluido').fadeIn()"
        <?php } ?>>
        <div class="til">Concluído</div>
        <div id="dadosConcluido" class="inf info4">---</div>
    </div>

    <div class="bloco_info pointer" <?php if($infoUser['nivel_id']<=2){ ?> onclick="$('#fundo_mod_pend').fadeIn()"
        <?php } ?>>
        <div class="til">Pendência</div>
        <div id="dadosPend" class="inf info7">---</div>
    </div>

    <div class="bloco_info pointer" <?php if($infoUser['nivel_id']<=2){ ?> onclick="$('#fundo_mod_tma').fadeIn()"
        <?php } ?>>
        <div class="til">TMA</div>
        <div id="dadosTma" class="inf info5">--:--:--</div>
    </div>
    <?php } ?>

    <div class="bloco_info pointer" <?php if($infoUser['nivel_id']<=2){ ?> onclick="$('#fundo_mod_tme').fadeIn()"
        <?php } ?>>
        <div class="til">TME</div>
        <div id="dadosTme" class="inf info6">--:--:--</div>
    </div>
    <?php } ?>
    <!--
    <div class="bloco_info">
        <div id="statusServer" class="inf info0"><div id="sinal_server" class="signal status_neutro"></div></div>
    </div>
    -->
</div>

<?php if($infoUser['nivel_id']<=2){ ?>
<div id="fundo_mod_fila" class="gw-modal-fundo">
    <div id="mod_fila" class="gw-modal-small">
        <h5>Informações de Fila</h5><br />
        <div class="row" id="list-fila">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>

<script>
$("#fundo_mod_fila, .close").click(function() {
    $("#fundo_mod_fila").hide();
});
$("#mod_fila").click(function(e) {
    e.stopPropagation();
});
</script>

<div id="fundo_mod_atend" class="gw-modal-fundo">
    <div id="mod_atend" class="gw-modal-small">
        <h5>Informações de Chats Concluídos</h5><br />
        <div class="row" id="list-atend">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>

<script>
$("#fundo_mod_atend, .close").click(function() {
    $("#fundo_mod_atend").hide();
});
$("#mod_atend").click(function(e) {
    e.stopPropagation();
});
</script>

<div id="fundo_mod_concluido" class="gw-modal-fundo">
    <div id="mod_concluido" class="gw-modal-small">
        <h5>Informações de Chats Concluídos</h5><br />
        <div class="row" id="list-concluido">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>

<script>
$("#fundo_mod_concluido, .close").click(function() {
    $("#fundo_mod_concluido").hide();
});
$("#mod_concluido").click(function(e) {
    e.stopPropagation();
});
</script>

<div id="fundo_mod_pend" class="gw-modal-fundo">
    <div id="mod_pend" class="gw-modal-small">
        <h5>Informações de Chats com Pendência</h5><br />
        <div class="row" id="list-pend">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>

<script>
$("#fundo_mod_pend, .close").click(function() {
    $("#fundo_mod_pend").hide();
});
$("#mod_pend").click(function(e) {
    e.stopPropagation();
});
</script>

<div id="fundo_mod_tme" class="gw-modal-fundo">
    <div id="mod_tme" class="gw-modal-small">
        <h5>Informações de TME</h5><br />
        <div class="row" id="list-tme">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>

<script>
$("#fundo_mod_tme, .close").click(function() {
    $("#fundo_mod_tme").hide();
});
$("#mod_tme").click(function(e) {
    e.stopPropagation();
});
</script>

<div id="fundo_mod_tma" class="gw-modal-fundo">
    <div id="mod_tma" class="gw-modal-small">
        <h5>Informações de TMA</h5><br />
        <div class="row" id="list-tma">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>

<script>
$("#fundo_mod_tma, .close").click(function() {
    $("#fundo_mod_tma").hide();
});
$("#mod_tma").click(function(e) {
    e.stopPropagation();
});
</script>

<?php } ?>

<?php if($infoUser['nivel_id']==5){ ?>
<div id="dadosPendAte"></div>

<?php } ?>

<script>
function notPend(protocolo, id_chat) {
    Toastify({
        text: "Protocolo: " + protocolo + " com pendência resolvida!",
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
        onClick: function() {
            abreDetail(id_chat)
        } // Callback after click
    }).showToast();
}

function abreDetail(id) {
    //console.log(id);
    $('#fundo_detail').fadeIn();
    $.post("staff/load_hist_pend.php", {
            id
        },
        function(valor) {
            //$('#info_detail').show('slow');
            $('#div_detail').html(
                '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
            setTimeout(function() {
                $('#div_detail').html(valor);
            }, 500);


        });
}
</script>
<div id="fundo_detail" class="gw-modal-fundo">
    <div id="mod_detail" class="gw-modal-large">
        <h5>Informações do Chat</h5><br />
        <div class="row" id="div_detail">
        </div>
        <div class="close"><span>&times;</span></div>
    </div>
</div>


<script>
$("#fundo_detail, .close").click(function() {
    $("#fundo_detail").hide();
    $("#div_detail").html('');
});
$("#mod_detail").click(function(e) {
    e.stopPropagation();
});
</script>
