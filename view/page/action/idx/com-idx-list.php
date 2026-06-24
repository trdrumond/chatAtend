<?php
include("cnf/session.php");
require_once __DIR__ . '/../cnf/_cnf_ui.php';
?>
<script src="js/tabs-com.js?<?= time() ?>"></script>
<!-- <script type="text/javascript" src='chat/assets/js/script_com.js?<?= time() ?>' defer></script> -->
<style>
    #bloco {
        margin-top: 5px;
        width: 99%;
        float: left;
        height: 450px;
    }

    #menu-com-hist {
        float: left;
        width: 25%;
        max-height: 445px;
        overflow: scroll;

    }

    #content-com-hist {
        float: left;
        width: 74%;
        min-height: 445px;
        margin-left: 10px;
        background-color: #FFFFFF;
    }

    #menu-names-hist {
        min-height: 415px;
        overflow: auto;
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

    <div id="menu-com-hist">
        <div class="st-field input-container" style="margin-top: 15px;">
            <label class="st-label" for="filtro-nome">Pesquisar</label>
            <input id="filtro-nome" class="input" type="text" />
        </div>

        <div id="menu-names-hist">
            <?php include("staff/load_com_list_hist.php"); ?>
        </div>
    </div>

    <div id="content-com-hist">
        <br><br>
        <center><img src="img/chat_transp.fw.png" alt="Carregando..." width="300"></center>

    </div>

    <script>

        function loadComHist(indice, id_com){
            //console.log('Teste 1.2.1');
            $('#content-com-hist').html('<center><img src="img/loading.gif" alt="Carregando..." width="200"><br>CARREGANDO MENSAGENS...</center>');
            if( conn.readyState === 1){
                $.post("staff/load_com_hist.php",
                {
                    indice, id_com
                },
                function (valor) {
                    $("#content-com-hist").html(valor);
                });
            } else {
                setTimeout(function(){ loadComHist(indice, id);},  1000);
            }
        }
    </script>

</div>


<script>
    $(document).ready(function () {
        $('#filtro-nome').keyup(function() {
            var nomeFiltro = $(this).val().toLowerCase();
            $("#menu-names-hist .tab").css("display", "block");
            $("#menu-names-hist .tab").each(function(){
                var conteudoCelula = $(this).text();
                var corresponde = conteudoCelula.toLowerCase().indexOf(nomeFiltro) >= 0;
                $(this).css('display', corresponde ? '' : 'none');
            });
        });
    });

</script>
