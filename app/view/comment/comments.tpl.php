<div class='question-list'>

    <div class='sidebar'>
        <p class='votes'>
            <span class='number'><?=$comment->score?></span><br><span class='word'>röster</span>
        </p>
        <p class='answers'>
            <span class='number'><?=$amountOfAnswers?></span><br><span class='word'>svar</span>
        </p>
    </div>

    <div class='main'>

        <p class='title'>
            <a href='<?=$commentUrl?>/<?=$comment->id?>'><?=$comment->title?></a>
        </p>

        <p class='tag-row'>
            <?php foreach($tags as $tag): ?>
                <a href='<?=$taggedUrl?>/<?=$tag?>' class='tag'><?=$tag?></a>
            <?php endforeach; ?>
        </p>
        <p class='bottom-row'>
            <a href='<?=$userUrl?>/<?=$user['id']?>'><?=$user['name']?></a>, <span class='timestamp' title='<?=$timestamp?>'><?=$timestamp?></span>
        </p>

    </div>

</div>
