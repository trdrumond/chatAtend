//let qtdMax=3;

function selAbaCom(indice, id){
    var indice;
    var spans = $('.tab');
    var qtd_span = $('.tab').length;
    //console.log(indice);
    //console.log(id);
    //console.log(qtd_span);



    for (var i = 0; i <= qtd_span; i++){
        //var div = '#div-' + i;
        var title = '#title-' + i;

        //$(div).removeClass();
        //$(div).addClass('div');
        //if(i==indice){ $(div).addClass('show'); } else { $(div).addClass('sec'); }

        $(title).addClass('tab');
        if(i==indice){
            $(title).addClass('active-tab');
            loadCom(indice, id);
            $(title).removeClass('blink_me');
            //console.log('indice script: ' + indice);
        } else {
            $(title).removeClass('active-tab');
        }
    }




    //loadTxt();
}

function selAbaComList(indice, id){
    var indice;
    var spans = $('.tab');
    var qtd_span = $('.tab').length;

    for (var i = 0; i <= qtd_span; i++){
        //var div = '#div-' + i;
        var title = '#title-' + i;

        $(title).addClass('tab');
        if(i==indice){
            $(title).addClass('active-tab');
            loadComHist(indice, id);
            $(title).removeClass('blink_me');
            //console.log('indice script: ' + indice);
        } else {
            $(title).removeClass('active-tab');
        }
    }




    //loadTxt();
}


function fechaAbaCom(indice){
    var title = '#title-' + indice;
    var div = '#div-' + indice;
    var spans = $('.tab');

    $(title).remove();
    $(div).remove();

    var qtd_span = spans.length;
    //console.log('qtd abas: ' + qtd_span);

    if(indice == 1){
        indSel = 2;
    } else {
        indSel = 1;
    }

    qtd_span = spans.length;
    //console.log('qtd abas: ' + qtd_span);

    if(qtd_span == 1){
        //addAba();
        location.reload();
    } else {
        selAbaCom(indSel);
    }
    if(qtd_span < qtdMax){
        console.log('qtd menor que maximo');
        $("#btn-add-tab").attr('disabled', false);
    }


}




function newMessageAbaCom(indice){
        var name_title = '#title-' + indice;
        if($(name_title).hasClass('active-tab') == false){
            $(name_title).addClass('blink_me');
        }

}
