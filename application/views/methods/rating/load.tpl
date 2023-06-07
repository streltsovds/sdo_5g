<?php if ($this->itemId) :?>
<?php $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.checkbox.js')); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/test.css')); ?>
<form method="POST" action="<?= $this->escape($this->saveUrl) ?>" id="<?php echo $pairCompairId ?>">
<input type="hidden" name="finalize" value="0">
<input type="hidden" name="item_id" value="<?= $this->itemId?>">
<?php foreach ($this->model['index'][$this->itemId] as $criterionId): ?>
<h2><?php echo sprintf(_('У кого из пользователей компетенция "%s" более развита?'), $this->model['criteria'][$criterionId]->name)?></h2>

<?php if (count($this->model['criteria'][$criterionId]->indicators)) :?>
<?php if (max(array_map('strlen', $this->model['criteria'][$criterionId]->indicators->getList('indicator_id', 'description_positive')))) : // если оно везде не заполнено - не показываем?>
<div class="at-form-info">
<h4><?php echo _('Компетенция у пользователя развита если')?>:</h4>
<ul>
<?php foreach ($this->model['criteria'][$criterionId]->indicators as $indicator):?>
<li><?php echo $indicator->description_positive?></li>
<?php endforeach;?>
</ul>
</div>
<div class="at-form-info">
<h4><?php echo _('Компетенция нуждается в развитии если')?>:</h4>
<ul>
<?php foreach ($this->model['criteria'][$criterionId]->indicators as $indicator):?>
<li><?php echo $indicator->description_negative?></li>
<?php endforeach;?>
</ul>
</div>
<?php endif;?>
<?php endif;?>
<script>

    $(function () {

        var rating = new hm.QuestRating('#hm-rating-pair-compare-<?php echo $criterionId ?> .quest-item-row');

    });
</script>
<table id="hm-rating-pair-compare-<?php echo $criterionId ?>">
    <tbody><?php foreach ($this->model['pairs'] as $pair): ?>
    	<?php if (!isset($this->model['users'][$pair->first_user_id]) || !isset($this->model['users'][$pair->second_user_id])) continue;?>
    	<?php $id = "results-{$criterionId}-{$pair->session_pair_id}";?>
    	<?php $name = "results[{$criterionId}][{$pair->session_pair_id}]";?>
        <tr class="quest-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
            <td class="title hm-quest-raiting-item-left" data-hm_people_id="<?php echo $pair->first_user_id?>">
                <label for="<?php echo $id;?>_left" class="hm-quest-raiting-item-label">
                    <div class="hm-quest-raiting-item-checkbox">
                        <input type="radio" class="quest-answer" id="<?php echo $id?>_left" name="<?php echo $name?>" value="<?php echo $pair->first_user_id?>" <?php if (isset($this->results[$this->itemId][$criterionId][$pair->session_pair_id]) && ($this->results[$this->itemId][$criterionId][$pair->session_pair_id] == $pair->first_user_id)) :?>checked<?php endif;?>>
                    </div>
                    <div class="hm-quest-raiting-item-user">
                        <img src="<?php echo Zend_Registry::get('config')->url->base . $this->model['users'][$pair->first_user_id]->getPhoto()?>" />
                        <div class="hm-quest-raiting-item-user-fio">
                            <?php echo $this->model['users'][$pair->first_user_id]->getName()?>
                        </div>
                        <div class="hm-quest-raiting-item-user-position"><?php echo count($this->model['users'][$pair->first_user_id]->positions) ? $this->model['users'][$pair->first_user_id]->positions->current()->name : '';?></div>
                    </div>
                </label>
            </td>
            <td class="title hm-quest-raiting-item-right" data-hm_people_id="<?php echo $pair->second_user_id?>">
                <label for="<?php echo $id;?>_right" class="hm-quest-raiting-item-label">
                    <div class="hm-quest-raiting-item-checkbox">
                        <input type="radio" class="quest-answer" id="<?php echo $id;?>_right" name="<?php echo $name?>" value="<?php echo $pair->second_user_id?>" <?php if (isset($this->results[$this->itemId][$criterionId][$pair->session_pair_id]) && ($this->results[$this->itemId][$criterionId][$pair->session_pair_id] == $pair->second_user_id)) :?>checked<?php endif;?>>
                    </div>
                    <div class="hm-quest-raiting-item-user">
                        <img src="<?php echo Zend_Registry::get('config')->url->base . $this->model['users'][$pair->second_user_id]->getPhoto()?>" />
                        <div class="hm-quest-raiting-item-user-fio">
                            <?php echo $this->model['users'][$pair->second_user_id]->getName()?>
                        </div>
                        <div class="hm-quest-raiting-item-user-position"><?php echo count($this->model['users'][$pair->second_user_id]->positions) ? $this->model['users'][$pair->second_user_id]->positions->current()->name : '';?></div>
                    </div>
                </label>
            </td>
        </tr>
    <?php endforeach;?></tbody>
</table>
<?php endforeach;?>
<?php  $this->inlineScript()->captureStart(); ?>
$("input[type='radio']").checkbox({cls:'jquery-radio-checkbox'});
<?php $this->inlineScript()->captureEnd(); ?>
<div class="at-form-navpanel">
    <?php if (!isset($this->model['clusters']) || (count($this->model['clusters']) < 1)): ?>
        <?php if ($this->navPanel['stop']) :?>
        <a href="<?= $this->navPanel['stop'] ?>" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-submit at-form-stop"><?= _('Выйти') ?></a>
        <?php endif;?>
        <?php if ($this->navPanel['finalize']) :?>
        <a href="<?= $this->navPanel['finalize'] ?>" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты и выйти')) ?>" class="at-form-button at-form-submit at-form-finalize"><?= _('Готово') ?></a>
        <?php endif;?>
    <?php else: ?>
        <?php if ($this->navPanel['prevId']) :?>
        <a href="<?= $this->navPanel['prev'] ?>" data-item-id="<?=$this->navPanel['prevId']?>" title="<?= $this->escape(_('Вернуться к предыдущей странице')) ?>" class="at-form-button at-form-submit at-form-return"><?= _('Назад') ?></a>
        <?php else:?>
        <a href="#" class="at-form-button at-form-submit at-form-return ui-state-disabled"><?= _('Назад') ?></a>
        <?php endif;?>
        <?php if ($this->navPanel['nextId']) :?>
        <a href="<?= $this->navPanel['next'] ?>" data-item-id="<?=$this->navPanel['nextId']?>" title="<?= $this->escape(_('Перейти к следующей странице')) ?>" class="at-form-button at-form-submit at-form-advance"><?= _('Вперёд') ?></a>
        <?php else: ?>
        <a href="<?= $this->resultsUrl ?>" title="<?= $this->escape(_('Закончить и перейти к результатам')) ?>" class="at-form-button at-form-submit at-form-complete at-form-advance"><?= _('Закончить') ?></a>
        <?php endif;?>
    <?php endif;?>
</div>
</form>
<?php endif; ?>
<?php 
        $js = "
            yepnope({
                test: Modernizr.canvas,
                nope: ['/js/lib/jquery/excanvas.compiled.js'],
                complete: function () {
                    yepnope({
                        test: $.fn.bt,
                        nope: [
                            '/css/jquery-ui/jquery.ui.tooltip.css',
                            '/js/lib/jquery/jquery.hoverIntent.minified.js',
                            '/js/lib/jquery/jquery.ui.tooltip.js'
                        ],
                        complete: function () {
                            _.delay(function () {
                                jQuery(function ($) {
                                    $('.tooltip').bt({killTitle: false});
                                });
                            }, 100);
                        }
                    });
                }
            });

        ";
        $this->inlineScript(Zend_View_Helper_HeadScript::SCRIPT)->offsetSetScript("tooltip_decorator", $js);
?>