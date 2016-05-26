<h1><?=$title?></h1>

<?php if (isset($url)) : ?>
    <p>
        <a href="<?=$url?>">Gå tillbaka</a>
    </p>
<?php endif; ?>

<?=$content?>

<?php if (isset($links)) : ?>
    <ul>
        <?php foreach ($links as $link) : ?>
            <li>
                <a href="<?=$link['href']?>"><?=$link['text']?>
                </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
