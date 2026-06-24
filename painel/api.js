function dadosPainel(){
    api = 1;
    $.post("../view/staff/load_painel.php",
        function (valor) {
            $("#dadosPainel").html(valor);
        }
    );
}




