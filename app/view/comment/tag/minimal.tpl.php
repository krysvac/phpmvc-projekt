<div class='front-page-box'>
    <h3 class='background-orange'><?=$title?></h3>
    <?php foreach($tags as $tag => $amount): ?>
        <p>
            <a href='<?=$url?>/<?=$tag?>'><?=ucfirst($tag)?> (<?=$amount?>)</a>
        </p>
    <?php endforeach; ?>
</div>
