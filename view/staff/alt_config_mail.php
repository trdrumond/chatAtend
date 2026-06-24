<?php
include("../cnf/session.php");

//var_dump($_POST);



?>

<div id="feed_fil">
<!--
<center>
    <button type="button" id="save_mail" class="btn btn-success"><i class="fas fa-paper-plane fa-5x"></i></button>
</center>
-->
</div>



<script>
$(document).ready(function() {

    filUser(<?php echo $_POST['id']; ?>);

    $("#save_mail").click(function() {
        var id = <?php echo $_POST['id']; ?>;
        filUser(id);
    });


    function filUser(id) {
        console.log('Chegou na função');
        $("#feed_fil").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
        );

        $.post("staff/envio_mail_cad.php", {

                id
            },
            function(valor) {
                $("#feed_fil").html(valor);
            });
    }

});
</script>
