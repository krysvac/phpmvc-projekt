<div class='lists'>
    
    <h4 class='background-red'>Frågor ställda</h4>
    <ul>
        <?php foreach ($questions as $q) : ?>
            <li>
                <a href='<?=$commentUrl?>/<?=$q->id?>'><?=$q->title?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h4 class='background-orange'>Frågor svarade</h4>
    <ul>
        <?php foreach ($answers as $a) : ?>
            <li>
                <a href='<?=$commentUrl?>/<?=$a->parent?>#<?=$a->id?>'><?=$a->parentTitle?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h4 class='background-green'>Kommentarer postade</h4>
    <ul>
        <?php foreach ($comments as $c) : ?>
            <li>
                <a href='<?=$commentUrl?>/<?=$c->topParent?>#<?=$c->id?>'><?=$c->contentShort?></a>
            </li>
        <?php endforeach; ?>
    </ul>

</div>
