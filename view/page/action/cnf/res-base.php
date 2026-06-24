<?php include("cnf/session.php"); ?>



<center><h5>RESETAR BASE</h5></center>

<BR>
<center>
    <button class="btn btn-warning" id="btn_res">RESETAR BASE</button>
    <div id="result_base"></div>
</center>


<script>
    $('#btn_res').click(function () {
        btnRes();
    });

    function btnRes(){

        $("#result_base").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');

        $.post("staff/res_base.php",
        //{
            //action
        //},
        function (valor) {
            $("#result_base").html(valor);
        });

    }
</script>

