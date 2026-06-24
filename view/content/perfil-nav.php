<style>
    #menu-perfil-nav {
        width: 310px;
    }

    #perfil-nav > .span-menu {
        width: 90px;
        height: 90px;
        margin: 5px;
        float: left;
        text-align: center;
    }

</style>
<div class="btn-group">
  <i class="fas fa-ellipsis-h" data-bs-toggle="dropdown" aria-expanded="false"></i>

  <ul class="dropdown-menu" id="menu-perfil-nav">
    <?php include("content/menu-".$_GET['sec'].".php"); ?>
  </ul>
</div>
