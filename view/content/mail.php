<style>
    #menu-mail {
        width: 310px;
    }
    .drop-menu-mail {
        width: 100%;
        height: 50px;
        float: left;
        text-align: left;
        line-height: 50px;
    }

    .mail {
      height: 50px !important;
    }

    .btn-group > span {
      font-size: 8px;
      height: 25%;
    }



</style>
<div class="btn-group">
  <span class="badge rounded-pill bg-danger">1</span><i class="far fa-envelope" data-bs-toggle="dropdown" aria-expanded="false"></i>
  <ul class="dropdown-menu" id="menu-mail">
    <li class="drop-menu-mail"><a class="dropdown-item mail" href="#"><i class="fas fa-envelope fa-2x"></i> Action</a></li>
    <li class="drop-menu-mail"><a class="dropdown-item mail" href="#"><i class="far fa-envelope-open fa-2x"></i> Another action</a></li>
    <li class="drop-menu-mail"><a class="dropdown-item mail" href="#"><i class="far fa-envelope-open fa-2x"></i> Something else here</a></li>
  </ul>
</div>


