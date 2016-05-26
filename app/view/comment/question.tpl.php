<div class='question' id='<?=$questionId?>'>
    <h2 class='question-title'><?=$questionTitle?></h2>

    <div class='sidebar'>
        <p>
            <a href='<?=$voteUrl?>/<?=$questionId?>/up'><i class="fa fa-caret-up" title="Rösta upp"></i></a>
        </p>
        <p class='score'>
            <?=$score?>
        </p>
        <p>
            <a href='<?=$voteUrl?>/<?=$questionId?>/down'><i class="fa fa-caret-down" title="Rösta ner"></i></a>
        </p>
        <p>
            <a href='<?=$commentUrl?>/<?=$questionId?>'><i class="fa fa-comment" title="Kommentera"></i></a>
        </p>
        <p>
            <?php if (isset($editUrl) && $editUrl != null) : ?>
                <a href='<?=$editUrl?>/<?=$questionId?>'><i class="fa fa-pencil" title='Redigera'></i></a>
            <?php endif; ?>
        </p>
    </div>

    <div class='question-content'>

        <?=$question?>
        <p>
            <?php foreach($tags as $tag): ?>
                <a href='<?=$taggedUrl?>/<?=$tag?>' class='tag'><?=$tag?></a>
            <?php endforeach; ?>
        </p>
        <div class='question-info'>
            <img src='<?=$user["gravatar"]?>' alt='<?=$user["name"]?>' title='<?=$user["name"]?>'>
            <span class='author-info'>
            <span class='timestamp' title='<?=$timestamp?>'><?=$timestamp?></span>
            <a href='<?=$userUrl?>/<?=$user['id']?>'><?=$user['name']?></a> (<?=$user['reputation']?>)</span>
        </div>

    </div>
</div>
