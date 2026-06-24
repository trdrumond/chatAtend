function dadosApi(user, data_de, data_ate){
    if(user != ''){
        $.post("../view/staff/load_api.php",
            {
                user, data_de, data_ate
            },
            function (valor) {
                $("#dadosApi").html(valor);
            }
        );
    } else {
        alert('Você deve inserir um usuário válido!');
    }


}




