<div class='comment' id='<?=$id?>'>

    <a href='<?=$voteUrl?>/<?=$comment->id?>/up'><i class="fa fa-arrow-up"></i></a> <?=$comment->score?> 
    <a href='<?=$voteUrl?>/<?=$comment->id?>/down'><i class="fa fa-arrow-down"></i></a>
    <p>
        <?=$comment->content?>
    </p> — 
    <p>
        <a href='<?=$userUrl?>/<?=$user['id']?>'><?=$user["name"]?></a>, <span class='timestamp' title='<?=$timestamp?>'><?=$timestamp?></span>
        
        <?php if($editUrl != null) : ?>
            <a href='<?=$editUrl?>/<?=$comment->id?>'>[edit]</a>
        <?php endif; ?>
    </p>

</div>
