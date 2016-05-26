<h3>Aktivitet</h3>
<?php if($count > 0) : ?>
    <p>Antal bidrag: <?=$count?></p>
    <ul class="fa-ul">
        <li>
            <i class="fa-li fa fa-sign-in"></i> Gick med: <span title='<?=$user->created?>'><?=$user->created?></span>
        </li>
        <li>
            <i class="fa-li fa fa-question"></i> Frågor: <?=$questions?>
        </li>
        <li>
            <i class="fa-li fa fa-reply"></i> Svar: <?=$answers?>
        </li>
        <li>
            <i class="fa-li fa fa-comments-o"></i> Kommentarer: <?=$comments?>
        </li>
    </ul>
<?php else : ?>
    <p>
        <?=$user->name?> har inte ställt några frågor än!
    </p>
<?php endif; ?>


