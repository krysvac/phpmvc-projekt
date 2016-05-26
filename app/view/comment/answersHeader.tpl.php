<div class='answers-header'>
    <?php if($amountOfAnswers > 0) : ?>
        <h3>Den här frågan har <?=$amountOfAnswers?> svar</h3>
        <p class='sorting-bar'>Sortera efter: <a href='<?=$sortByTime?>' class='<?=$sortByTimeClass?>'>tid</a> <a href='<?=$sortByScore?>' class='<?=$sortByScoreClass?>'>poäng</a></p>
    <?php else : ?>
        <h3>Den här frågan har inga svar än.</h3>
    <?php endif; ?>
</div>
