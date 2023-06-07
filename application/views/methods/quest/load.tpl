<?php if (!is_null($this->itemId)) :?>
<?php $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.checkbox.js')); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/test.css')); ?>
<form method="POST" action="<?= $this->escape($this->saveUrl) ?>">
    <input type="hidden" name="finalize" value="0">
    <input type="hidden" name="stop"     value="0">
    <input type="hidden" name="timestop" value="0">
<input type="hidden" name="item_id" value="<?= $this->itemId?>">
<?php foreach ($this->model['index'][$this->itemId] as $questionId): ?>
<div class="quest-question quest-question-<?php echo $this->model['questions'][$questionId]->type;?>">
    <?php if ($this->model['quest']->mode_self_test ): ?>
        <?php if ($this->model['questions'][$questionId]->justification !== 'http://') :?>
            <a class="quest-question-justification" href="#" style="cursor: default; color: black;">
                <span><?php echo $this->model['questions'][$questionId]->justification;?></span>
            </a>
            <?php endif;?>        <?php endif;?>
    <?php echo $this->question(
            $this->model['questions'][$questionId],
            $this->results[$this->itemId][$questionId],
            array(
                'show_free_variant' => $this->model['quest']->show_free_variant,
                'number' => $this->model['numbers'][$questionId],
                'comment' => $this->comments[$this->itemId][$questionId],
            ));

    ?>
    </div>
<?php endforeach;?>

<?php  $this->inlineScript()->captureStart(); ?>
    $("input[type='radio']").checkbox({cls:'jquery-radio-checkbox'});
    $('input:checkbox:not([safari])').checkbox();
    $('input[safari]:checkbox').checkbox({cls:'jquery-safari-checkbox'});
<?php $this->inlineScript()->captureEnd(); ?>
<div class="at-form-navpanel">
    <?php if ($this->model['quest']->mode_self_test ): ?>
        <a href="<?= $this->navPanel['test'] ?>" data-item-id="<?=$this->navPanel['test']?>" title="<?= $this->escape(_('Проверить')) ?>" class="at-form-button at-form-submit at-form-self-test"><?= _('Проверить') ?></a>
    <?php endif;?>

    <?php // если всего одна страница теста/опроса/формы - только выйти и финализировать ?>
    <?php // доступность самих кнопок - через Multipage и его потомки ?>

    <?php if (!isset($this->model['clusters']) || (count($this->model['clusters']) <= 1)): ?>
        <?php if ($this->navPanel['stop']) :?>
            <a href="<?= $this->navPanel['stop'] ?>" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-submit at-form-stop"><?= _('Выйти') ?></a>
        <?php endif;?>
        <?php if ($this->navPanel['finalize']) :?>
            <a href="<?= $this->navPanel['finalize'] ?>" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты и выйти')) ?>" class="at-form-button at-form-submit at-form-finalize"><?= _('Готово') ?></a>
        <?php endif;?>
    <?php else: ?>

        <?php // если страниц много ?>
        <?php if ($this->navPanel['prevId']):?>

            <?php // доступность кнопки назад зависит от свойств теста ?>
            <?php if ($this->model['quest']->mode_test_page ): ?>
                <a href="<?= $this->navPanel['prev'] ?>" data-item-id="<?=$this->navPanel['prevId']?>" title="<?= $this->escape(_('Вернуться к предыдущей странице')) ?>" class="at-form-button at-form-submit at-form-return"><?= _('Назад') ?></a>
            <?php endif;?>
        <?php endif;?>

        <?php // если не последняя страница  ?>
        <?php if ($this->navPanel['nextId']) :?>
            <a href="<?= $this->navPanel['next'] ?>" data-item-id="<?=$this->navPanel['nextId']?>" title="<?= $this->escape(_('Перейти к следующей странице')) ?>" class="at-form-button at-form-submit at-form-advance"><?= _('Вперёд') ?></a>
        <?php else: ?>
            <?php // если последняя страница - к результатам ?>
            <a href="<?= $this->navPanel['finalize'] ?>" title="<?= $this->escape(_('Закончить и перейти к результатам')) ?>" class="at-form-button at-form-submit at-form-complete at-form-advance"><?= _('Закончить') ?></a>
        <?php endif;?>
    <?php endif;?>

</div>
</form>
<?php endif; ?>

<?php $this->inlineScript()->captureStart(); ?>

$(document).delegate('.at-form a.at-form-finalize', 'mousedown', function (event) {
    var target;
    if (target = $(this).attr('target')) {
        $(this)
        .data('target', target)
            .removeAttr('target');
    }
});

$(document).delegate('.at-form a.at-form-finalize', 'click', function (event) {
    var $target = $(this)
        , message;

    event.preventDefault();

    elsHelpers.alert(<?= HM_Json::encodeErrorSkip(_('Выполняя данную операцию, Вы подтвержаете что форма заполнена корректно и дальнейшему изменению не подлежит.
    Необходимо заполнить оставшиеся поля, либо прервать заполнение анкеты.')) ?>).done(function () {
        top.location.href = $target.attr('href');
    });
});

$(document).delegate('.at-form a.at-form-stop', 'mousedown', function (event) {
    var target;
        if (target = $(this).attr('target')) {
            $(this)
            .data('target', target)
            .removeAttr('target');
    }
});

$(document).delegate('.at-form a.at-form-stop', 'click', function (event) {
    var $target = $(this)
    , message;

    event.preventDefault();

        elsHelpers.confirm(<?= HM_Json::encodeErrorSkip(_('Выполняя данную операцию, Вы НЕ заканчиваете заполнение анкеты и оставляете возможность для её дальнейшего изменения. Продолжить?')) ?>).done(function () {
            top.location.href = $target.attr('href');
        })
});

$(document).ready(function(){
    $('#button-print').click(function(){

        var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'index', 'action' => 'print', 'session_event_id' => $this->questId));?>';

        var name = 'print-results';

        var options = [ 'location=no', 'menubar=no', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',');
        window.open(url, name, options);
    });

    // убираем ссылку на материал если нету кнопки 'Проверить'
    if (!$('a.at-form-self-test').length) {
        $('.quest-question-justification').remove();
    }

    // подсвечиваем правильность ответов при нажатии кнопки 'Проверить'
    $(document).delegate('a.at-form-self-test', 'click',
        function (event) {
            event.preventDefault()

            // инпуты с очками за ответ
            var inputEls = $('.quest-question .quest-item-value .quest-variant')
            inputEls.each(function (i, el) {
                var el = $(el)
                var rowEl = el.parent().parent()
                if (el.attr('checked')) {
                    var shade = el.attr('data-color')
                    rowEl.css('border-bottom', '2px solid ' + shade)
                } else {
                    rowEl.css('border-bottom', '')
                }
            })
        }
    )
});
<?php $this->inlineScript()->captureEnd(); ?>