<style>
    #content-idx{
        display: none;
    }

</style>
 <!--<meta http-equiv="refresh" content="600">-->
<div id="load_conn">
    <center>
        <img src="img/loading.gif" alt="Carregando..." width="200"><br>
        CARREGANDO DASHBOARD...
    </center>

</div>

<div id="content-idx"></div>


<script type="text/javascript">
        function loadIdx(){
            $('#content-idx').show();
            $('#load_conn').hide();
            LoadIdxContent();
        }

        function LoadIdxContent(){
            $.post("staff/dash-fila.php", { idx: 0 }, function (valor) {
                $("#content-idx").html(valor);
            }).fail(function () {
                setTimeout(LoadIdxContent, 2000);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadIdx);
        } else {
            loadIdx();
        }
    </script>
