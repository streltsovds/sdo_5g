<?php $settings = $this->model['quest']->getSettings(); ?>
<?php if ($settings->comments): ?>
<div class="at-form-comment"><?php echo $settings->comments; ?></div>
<?php endif;?>

<?php if ($this->itemId) :?>
<?php $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.checkbox.js')); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/test.css')); ?>
<form method="POST" action="<?= $this->escape($this->saveUrl) ?>">
    <input type="hidden" name="finalize" value="0">
    <input type="hidden" name="stop"     value="0">
<input type="hidden" name="item_id" value="<?= $this->itemId?>">
<?php foreach ($this->model['index'][$this->itemId] as $questionId): ?>
<div class="quest-question quest-question-<?php echo $this->model['questions'][$questionId]->type;?>">
<a name="q<?php echo $this->model['questions'][$questionId]->question_id; ?>"></a>
<?php echo $this->question(
        $this->model['questions'][$questionId], 
        $this->results[$this->itemId][$questionId], 
        array(
            'show_free_variant' => $this->model['quest']->show_free_variant,
            'number' => $this->model['numbers'][$questionId],
            'comment' => $this->comment[$this->itemId][$questionId],
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
    <?php if (!isset($this->model['clusters']) || (count($this->model['clusters']) < 2)): ?>
        <?php if ($this->navPanel['nextId']) :?>
        <a href="<?= $this->navPanel['next'] ?>" data-item-id="<?=$this->navPanel['nextId']?>" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты')) ?>" class="at-form-button at-form-submit at-form-finalize"><?= _('Готово') ?></a>
        <?php endif;?>
    <?php else: ?>
        <?php if ($this->navPanel['prevId']) :?>
            <a href="<?= $this->navPanel['prev'] ?>" data-item-id="<?=$this->navPanel['prevId']?>" title="<?= $this->escape(_('Вернуться к предыдущей странице')) ?>" class="at-form-button at-form-submit at-form-return"><?= _('Назад') ?></a>
        <?php elseif ($this->navPanel['nextId']) : ?>
          <a href="#" class="at-form-button at-form-submit at-form-return ui-state-disabled"><?= _('Назад') ?></a>
        <?php endif;?>
        <?php if ($this->navPanel['nextId']) :?>
            <a href="<?= $this->navPanel['next'] ?>" data-item-id="<?=$this->navPanel['nextId']?>" title="<?= $this->escape(_('Перейти к следующей странице')) ?>" class="at-form-button at-form-submit at-form-advance"><?= _('Вперёд') ?></a>
        <?php else: ?>
            <?php if ($this->navPanel['stop']) :?>
            <a href="<?= $this->navPanel['stop'] ?>" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-submit at-form-stop"><?= _('Выйти') ?></a>
            <?php endif;?>
            <?php if ($this->navPanel['finalize']) :?>
            <a href="<?= $this->navPanel['finalize'] ?>" title="<?= $this->escape(_('Закончить и перейти к результатам')) ?>" class="at-form-button at-form-submit at-form-finalize"><?= _('Готово') ?></a>
            <?php endif;?>
        <!--a href="<?= $this->resultsUrl ?>" title="<?= $this->escape(_('Закончить и перейти к результатам')) ?>" class="at-form-button at-form-submit at-form-complete at-form-advance"><?= _('Закончить') ?></a-->
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
    });

<?php $this->inlineScript()->captureEnd(); ?>