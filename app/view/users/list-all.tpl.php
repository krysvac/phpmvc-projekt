<h1>
    <?=$title?>
</h1>

<?php foreach ($users as $user) : ?>
    <div class='user-info'>
        
        <a href='<?=$profile?>/<?=$user->id?>'>
            <img src='<?=$user->gravatar?>'>
        </a>

        <p class='name'>
            <a href='<?=$profile?>/<?=$user->id?>'>
                <?=$user->name?>
            </a>
        </p>
        
        <p class='location'><?=$user->location?></p>
        <p class='reputation'><?=$user->reputation?></p>
        
    </div>
<?php endforeach; ?>
