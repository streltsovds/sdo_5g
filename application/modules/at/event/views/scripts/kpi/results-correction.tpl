<div class="at-competence at-form">
    <div class="at-form-comment">
        <?php  echo sprintf(_('В системе установлено ограничение на количество макимальных результатов по одному критерию среди всех пользователей подразделения (не более %d%%). Пожалуйста, выберите, у кого из перечисленных пользователей следует <u>снизить</u> результат по критерию.'), $this->maxResultLimit);?>
    </div>
    <div class="at-form-container">
        <form method="POST" action="<?= $this->escape($this->url(array('action' => 'results-correction'))) ?>">
            <?php foreach ($this->resultsWitValue as $criterionId => $users): ?>
            <h3><?php echo sprintf(_('Снизить оценку за "%s"'), $this->criteria[$criterionId]);?></h3>
            <table>
                <?php foreach ($users as $sessionUserId => $user): ?>
                <tr class="quiz-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
                    <td class="value" width="50px"><input type="radio" class="quiz-answer" name="corrections[<?php echo $criterionId;?>]" value="<?php echo $sessionUserId;?>"></td>
                    <td class="title"><?php echo $user->getName();?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endforeach; ?>
            <div class="at-form-navpanel">
                <a href="<?= $this->url(array('action' => 'finalize')); ?>" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты и выйти')) ?>" class="at-form-button at-form-submit at-form-finalize"><?= _('Готово') ?></a>
            </div>
        </form>
    </div>
</div>
<?php $this->inlineScript()->captureStart(); ?>

$(".ui-dialog-titlebar-close").css('display', 'none');

$("input[type='radio']").checkbox({cls:'jquery-radio-checkbox'});

$('.at-form-navpanel a.at-form-button').each(function () {
$(this).button({ disabled: $(this).hasClass('ui-state-disabled') });
});
$(document).ready(function(){



var criterias = <?php echo count($this->resultsWitValue);?>;

$('.at-form-navpanel a.at-form-button').hide();
$('.at-form-container form').find('input[type="radio"]').on('change', function() {
var values = $('.at-form-container form').find('input[name^=corrections]').serializeArray();
if (criterias == values.length) {
$('.at-form-navpanel a.at-form-button').show();
}
});



/*
function disableLink(e) {
e.preventDefault();
return false;
}
$('.at-form-navpanel a.at-form-button').unbind('click', disableLink);
$('.at-form-navpanel a.at-form-button').bind('click', disableLink);

*/


});


<?php $this->inlineScript()->captureEnd(); ?>