

<div id="indice"><?php include("content/indice/indice-".$_GET['sec'].".php"); ?></div>
<div id="content">
    <div id="action-page" <?php if(!empty($userPermiss['chat'])){ echo 'class="content-7"';} else { echo 'class="content-10"';}?> >
        <?php include("action.php"); ?>
    </div>
    <?php if(!empty($userPermiss['chat'])){ ?>
        <div id="chat" class="content-3-chat">
            <?php include("content/chat.php"); ?>
        </div>
    <?php } ?>

</div>

