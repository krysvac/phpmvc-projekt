<div class='answer' id='<?=$answerId?>'>

    <div class='sidebar'>
        <p>
            <a href='<?=$voteUrl?>/<?=$answerId?>/up'><i class="fa fa-caret-up" title="Rösta upp"></i></a>
        </p>
        <p class='score'>
            <?=$score?>
        </p>
        <p>
            <a href='<?=$voteUrl?>/<?=$answerId?>/down'><i class="fa fa-caret-down" title="Rösta ner"></i></a>
        </p>
        <p>
            <a href='<?=$commentUrl?>/<?=$answerId?>'><i class="fa fa-comment" title="Kommentera"></i></a>
        </p>
        <p>
            <?php if($editUrl != null) : ?>
                <a href='<?=$editUrl?>/<?=$answerId?>'><i class="fa fa-pencil" title='Redigera'></i></a>
            <?php endif; ?>
        </p>
    </div>

    <div class='answer-content'>
        <?php if (isset($acceptUrl)) : ?>
            <a href='<?=$acceptUrl?>/<?=$answerId?>'><i class="fa fa-check accepted <?=$accepted?>"></i></a>
        <?php else : ?>
            <i class="fa fa-check accepted <?=$accepted?>"></i>
        <?php endif; ?>
        
        <?=$answerContent?>
    </div>

    <div class='bottom-row'>
        <div class='question-info'>
            <img src='<?=$user["gravatar"]?>' alt='<?=$user["name"]?>' title='<?=$user["name"]?>'>
            <span class='author-info'>
                <span class='timestamp' title='<?=$timestamp?>'><?=$timestamp?></span>
                <a href='<?=$userUrl?>/<?=$user['id']?>'><?=$user['name']?></a> (<?=$user['reputation']?>)
            </span>
        </div>
    </div>
</div>
