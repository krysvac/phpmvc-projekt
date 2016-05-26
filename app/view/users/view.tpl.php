<div class='user-infobox'>

    <img src='<?=$user->gravatar?>'>

    <p class='reputation'>
        <span class='number'><?=$user->reputation?></span> poäng
    </p>
    <p>
        <i class="fa fa-envelope"></i><a href='mailto:<?=$user->email?>'> <?=$user->email?></a>
    </p>

    <?php if($user->location != null) : ?>
        <p>
            <i class="fa fa-map-marker"></i> <?=$user->location?>
        </p>
    <?php endif; ?>
    
    <?php if($profileUrl != null) : ?>
        <p>
            <a class='edit-profile' href='<?=$profileUrl?>'>Redigera profil</a>
        </p>
    <?php endif; ?>

</div>
