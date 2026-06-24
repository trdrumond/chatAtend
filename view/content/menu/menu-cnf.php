<?php
include("../cnf/session.php");
if (!isset($menu_cnf) || !is_array($menu_cnf)) {
    $menu_cnf = array_fill(0, 11, '0');
}
?>
<?php if ($menu_cnf[0] == '1') { ?>
<span class="span-menu" id="cad-usu-cnf"><i class="far fa-user"></i> Usuários</span>
<?php } ?>
<?php if ($menu_cnf[1] == '1') { ?>
<span class="span-menu" id="cad-reg-cnf"><i class="fas fa-sitemap"></i> Regional</span>
<?php } ?>
<?php if ($menu_cnf[2] == '1') { ?>
<span class="span-menu" id="cad-emp-cnf"><i class="fas fa-building"></i> Empresa</span>
<?php } ?>
<?php if ($menu_cnf[3] == '1') { ?>
<span class="span-menu" id="cad-age-cnf"><i class="far fa-building"></i> Agência</span>
<?php } ?>
<?php if ($menu_cnf[4] == '1') { ?>
<span class="span-menu" id="cad-ass-cnf"><i class="fas fa-tasks"></i> Assuntos</span>
<?php } ?>
<?php if ($menu_cnf[5] == '1') { ?>
<span class="span-menu" id="cad-pri-cnf"><i class="fas fa-layer-group"></i> Prioridades</span>
<?php } ?>
<?php if ($menu_cnf[6] == '1') { ?>
<span class="span-menu" id="cad-faq-cnf"><i class="fas fa-question-circle"></i> FAQ</span>
<?php } ?>
<?php if ($menu_cnf[7] == '1') { ?>
<span class="span-menu" id="cad-men-cnf"><i class="fas fa-comment-dots"></i> Mensagem</span>
<?php } ?>
<?php if ($menu_cnf[8] == '1') { ?>
<span class="span-menu" id="log-acess-cnf"><i class="fas fa-clipboard-list"></i> Log Acesso</span>
<?php } ?>
<?php if ($menu_cnf[9] == '1') { ?>
<span class="span-menu" id="cad-fil-cnf"><i class="fas fa-tasks"></i> Filas</span>
<?php } ?>
<?php if ($menu_cnf[10] == '1') { ?>
<span class="span-menu" id="cad-ctt-cnf"><i class="fas fa-file-alt"></i> Contrato</span>
<?php } ?>
<?php if ((int) ($_SESSION['dados']['nivel_id'] ?? 99) <= 1) { ?>
<span class="span-menu" id="cnf-ia-cnf"><i class="fas fa-robot"></i> Config. IA</span>
<?php } ?>

<span class="span-menu" id="sair"><i class="fas fa-door-open"></i> Sair</span>
<div id="logout"></div>
