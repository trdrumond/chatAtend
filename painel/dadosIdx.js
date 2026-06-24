function dadosIdx(resp_id, fila_id, contrato_id){
    var resp_id;
    var fila_id;
    var contrato_id;

    $.post("../view/staff/load_dados_dash_ind_painel.php",
        {
            resp_id, fila_id, contrato_id
        },
        function (valor) {
            $("#dadosDashInd").html(valor);
            //console.log(valor);
        }
    );

}




