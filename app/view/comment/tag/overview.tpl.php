<h1><?=$title?></h1>
<div class='tags-container'>
    <?php foreach ($tags as $tag => $amount) : ?>
        <div class='tag-overview'>
            <p>
                <a href='<?=$url?>/<?=$tag?>' class='tag'><?=$tag?></a>× <?=$amount?>
            </p>
        </div>
    <?php endforeach; ?>
</div>
