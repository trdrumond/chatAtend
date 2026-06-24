//let qtdMax=3;

function selAba(indice){
    indice = parseInt(indice, 10) || 1;
    var spans = $('.tab');
    var qtd_span = window.qtdMax || 1;

    for (var i = 1; i <= qtd_span; i++){
        var div = '#div-' + i;
        var title = '#title-' + i;
        $(div).removeClass();
        $(div).addClass('div');
        if(i==indice){ $(div).addClass('show'); } else { $(div).addClass('sec'); }

        $(title).addClass('tab');
        if(i==indice){
            $(title).addClass('active-tab');
            $(title).removeClass('blink_me');
        } else {
            $(title).removeClass('active-tab');
        }
    }

    window.stBkoIndiceAtivo = indice;

    if(qtd_span < (window.qtdMax || 1)){
        console.log('qtd menor que maximo');
        $("#btn-add-tab").attr('disabled', false);
    }

    //loadTxt();
}

function addAba(){
    $("#btn-add-tab").attr('disabled', true);
    setTimeout(function() { $("#btn-add-tab").attr('disabled', false); }, 10000);
    var spans = $('.tab');
    var qtd_span = spans.length;
    if(qtd_span < (window.qtdMax || 1)){
        var verificacao = '';
        for(var i = 1; i <= (window.qtdMax || 1); i++){
            var ver = document.getElementById('title-'+i);
            if(!ver && !verificacao){
                verificacao = i;
            }
        }

        var element = verificacao;
        var name_title = 'title-' + element;
        var name_div = 'div-' + element;
        var area_bloco = '#bloco-bko';
        var area_bloco = document.getElementById('bloco-bko');
        var div_bloco = document.createElement('span');
        div_bloco.setAttribute('id', name_title);
        div_bloco.setAttribute('class', 'tab');
        div_bloco.setAttribute("onclick","selAba("+ element +");");
        div_bloco.textContent = 'Aguardando...';

        area_bloco.appendChild(div_bloco);

        var area_principal = '#principal';
        var area_principal = document.getElementById('principal');
        var div_principal = document.createElement('div');
        var div_close = document.createElement('div');
        div_principal.setAttribute('class', 'div');
        //div_principal.textContent = 'TESTE ';
        div_principal.setAttribute('id', name_div);
        //div_close.setAttribute('class', 'close-chat');
        //div_close.textContent = 'X';
        //div_close.setAttribute("onclick","fechaAba("+ element +");");

        area_principal.appendChild(div_principal);
        div_principal.appendChild(div_close);

        //$("#btn-add-tab").attr('disabled', true);
        selAba(element);
        setTimeout(function() {
            if (typeof window.loadFilaBko === 'function') {
                window.loadFilaBko(element);
            }
        }, 100);


    } else {
        Swal.fire('Limite maximo chats atingido');
    }
}

function fechaAba(indice){
    indice = parseInt(indice, 10) || 1;
    if (typeof window.stBkoCloseTab === 'function') {
        window.stBkoCloseTab(indice);
    }
    var title = '#title-' + indice;
    var div = '#div-' + indice;

    $(title).remove();
    $(div).remove();

    var qtd_span = $('.tab').length;

    if(indice == 1){
        indSel = 2;
    } else {
        indSel = 1;
    }

    if(qtd_span === 0){
        if ($('#content-bko').length && typeof window.stBkoReturnToQueue === 'function') {
            window.stBkoReturnToQueue(1);
        } else if ($('#content-bko').length && typeof window.stBkoStartEspera === 'function') {
            window.stBkoStartEspera(1, true);
        } else {
            location.reload();
        }
    } else {
        selAba(indSel);
    }
    if(qtd_span < (window.qtdMax || 1)){
        //console.log('qtd menor que maximo');
        $("#btn-add-tab").attr('disabled', false);
    }


}

function newMessageAba(indice){
        var name_title = '#title-' + indice;
        if($(name_title).hasClass('active-tab') == false){
            $(name_title).addClass('blink_me');
        }

}
