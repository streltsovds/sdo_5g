<?php if ($this->itemId) :?>
    <?php $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.checkbox.js')); ?>
    <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/test.css')); ?>
    <form method="POST" action="<?= $this->escape($this->saveUrl) ?>">
        <!--h3><?= $this->escape($this->model['clusters'][$this->itemId]->name); ?></h3-->
        <input type="hidden" name="finalize" value="0">
        <input type="hidden" name="item_id" value="<?= $this->itemId?>"><br>
        <table class="criteria-table">
            <thead><tr>
                <th>
                    <?php if (!$this->model['options']['competenceUseIndicatorsReversive']): // если нет хитрых реверсивных  индикаторов и можно показать название?>
                    <?php if ($this->model['options']['competenceUseIndicatorsDescriptions']): // если нужно показать 2 столбца с проявлениями индикаторов?>
                    <?php echo _('Негативное проявление');?></th>
                <?php else: ?>
                    <?php echo ($this->model['options']['competenceUseIndicators']) ? _('Индикатор') : _('Компетенция'); // если проявления индикаторов не заполнены - показываем только название?>
                <?php endif; ?>
                <?php endif; ?>
                </th>
                <?php foreach ($this->model['scaleValues'] as $value): ?>
                    <th><?php echo $this->model['options']['competenceUseIndicators'] ? (!empty($value->text) ? $value->text : $value->value) : (!empty($value->text) ? $value->text : $value->value);?></th>
                <?php endforeach; ?>
                <?php if ($this->model['options']['competenceUseIndicatorsDescriptions']): ?>
                    <?php if (!$this->model['options']['competenceUseIndicatorsReversive']): // если нет хитрых реверсивных  индикаторов и можно показать название?>
                        <th><?php echo _('Позитивное проявление');?></th>
                    <?php else: ?>
                        <th>&nbsp;</th>
                    <?php endif; ?>
                <?php endif; ?>
            </tr></thead>
            <?php if (is_array($this->model['index'][$this->itemId])):?>
                <tbody><?php foreach ($this->model['index'][$this->itemId] as $criterionId => $indicators): ?>
                    <?php if (!$this->model['options']['competenceUseIndicators']): ?>
                        <tr class="quest-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
                            <td class="title"><?php echo $this->model['criteria'][$criterionId]->name;?></td>
                            <?php foreach ($this->model['scaleValues'] as $value): ?>
                                <td class="value">
                                    <input type="radio" class="quest-answer" name="results[<?php echo $criterionId;?>]" value="<?php echo $value->value_id;?>" <?php if (isset($this->results[$this->itemId][$criterionId]) && ($this->results[$this->itemId][$criterionId] == $value->value_id)) :?>checked<?php endif;?>>
                                    <?php if ($this->model['options']['competenceUseScaleValues'] && strlen($this->model['criteria'][$criterionId]->scaleValues[$value->value_id])): ?>
                                        <div class="criterion-description">
                                            <?php echo $this->model['criteria'][$criterionId]->scaleValues[$value->value_id]?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach;?>
                        </tr>
                    <?php else: ?>

                        <?php if(count($this->model['index'][$this->itemId][$criterionId])) : ?>
                            <!--tr class="quest-item-head-row">
            <td class="title" colspan="100%"><b><?php echo $this->model['criteria'][$criterionId]->name;?></b></td>
        </tr-->
                        <?php endif; ?>

                        <tr class="quest-item-rowgroup <?= $this->cycle(array('odd', 'even'))->next() ?>">
                            <td><?php echo $this->model['criteria'][$criterionId]->name;?></td>
                            <?php foreach ($this->model['scaleValues'] as $value): ?>
                                <td>&nbsp;</td>
                            <?php endforeach; ?>
                            <?php if ($this->model['options']['competenceUseIndicatorsDescriptions']): ?>
                                <td>&nbsp;</td>
                            <?php endif; ?>
                        </tr>
                        <?php foreach ($this->model['index'][$this->itemId][$criterionId] as $indicatorId): ?>
                            <tr class="quest-item-row quest-item-rowgroup-item <?= $this->cycle(array('odd', 'even'))->next() ?>">
                                <td class="title">
                                    <?php echo ($this->model['options']['competenceUseIndicatorsDescriptions']) ?
                                        (
                                        ($this->model['indicators'][$indicatorId]->reverse && $this->model['options']['competenceUseIndicatorsReversive']) ?
                                            $this->model['indicators'][$indicatorId]->description_positive :
                                            $this->model['indicators'][$indicatorId]->description_negative
                                        ) :
                                        $this->model['indicators'][$indicatorId]->name_questionnaire;
                                    ?></td>
                                <?php $values = $this->model['scaleValues'];?>
                                <?php //if ($this->model['indicators'][$indicatorId]->reverse && $this->model['options']['competenceUseIndicatorsReversive']) $values = array_reverse($values)?>
                                <?php foreach ($values as $value): ?>
                                    <?php $radioId = sprintf('radio_%s_%s_%s', $this->itemId, $indicatorId, $value->value_id);?>
                                    <td class="quest-item-value">
                                        <label for="<?php echo $radioId;?>">
                                            <div>
                                                <input id="<?php echo $radioId;?>" type="radio" class="quest-answer" name="results[<?php echo $indicatorId;?>]" value="<?php echo $value->value_id;?>" <?php if (isset($this->results[$this->itemId][$indicatorId]) && ($this->results[$this->itemId][$indicatorId] == $value->value_id)) :?>checked<?php endif;?>>
                                                <?php if ($this->model['options']['competenceUseIndicatorsScaleValues'] && count($this->model['indicators'][$indicatorId]->scaleValuesQuestionnaire) && strlen($this->model['indicators'][$indicatorId]->scaleValuesQuestionnaire[$value->value_id])): ?>
                                                    <div class="criterion-description indicator-description">
                                                        <?php echo $this->model['indicators'][$indicatorId]->scaleValuesQuestionnaire[$value->value_id]?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </td>
                                <?php endforeach;?>
                                <?php if (
                                    ($this->model['indicators'][$indicatorId]->doubt) &&
                                    ($this->model['evaluationType']->relation_type != HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF) /*&&
			            ($this->model['evaluationType']->relation_type != HM_At_Evaluation_EvaluationModel::RELATION_TYPE_CHILDREN)*/
                                ): ?>
                                    <?php $radioId = sprintf('radio_%s_%s_%s', $this->itemId, $indicatorId, 'doubt');?>
                                    <td class="quest-item-value">
                                        <label for="<?php echo $radioId;?>">
                                            <div>
                                                <input type="radio" class="quest-answer" name="results[<?php echo $indicatorId;?>]" value="-1" <?php if (isset($this->results[$this->itemId][$indicatorId]) && ($this->results[$this->itemId][$indicatorId] == -1)) :?>checked<?php endif;?>>
                                                <div class="criterion-description indicator-description" style="min-width: 70px;">
                                                    <?php echo _('Не могу оценить') ?>
                                                </div>
                                            </div>
                                        </label>
                                    </td>
                                <?php else: ?>
                                    <!--td class="quest-item-value">
                                    </td-->
                                <?php endif; ?>
                                <?php if ($this->model['options']['competenceUseIndicatorsDescriptions']): ?>
                                    <td><?php echo
                                        ($this->model['indicators'][$indicatorId]->reverse && $this->model['options']['competenceUseIndicatorsReversive']) ?
                                            $this->model['indicators'][$indicatorId]->description_negative :
                                            $this->model['indicators'][$indicatorId]->description_positive;
                                        ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach;?>
                    <?php endif;?>
                <?php endforeach;?></tbody>
            <?php endif; // if is_array?>
        </table>
        <?php if (count($this->model['memos'])): ?>
            <div class="memos-container">
                <?php foreach ($this->model['memos'] as $memo): ?>
                    <div class="textarea-fields clearfix">
                        <label for="<?php echo $this->escape($memo->evaluation_memo_id); ?>"><?php echo $memo->name; ?></label>
                        <div class="textarea-wrapper"><textarea name="memos[<?php echo $memo->evaluation_memo_id;?>]"><?echo !empty($this->memoResults[$memo->evaluation_memo_id]) ? $this->memoResults[$memo->evaluation_memo_id] : '';?></textarea></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif;?>
        <?php  $this->inlineScript()->captureStart(); ?>
        $("input[type='radio']").checkbox({cls:'jquery-radio-checkbox'});
        $('input:checkbox:not([safari])').checkbox();
        $('input[safari]:checkbox').checkbox({cls:'jquery-safari-checkbox'});
        <?php $this->inlineScript()->captureEnd(); ?>
        <div class="at-form-navpanel">

            <?php // если всего одна страница теста/опроса/формы - только выйти и финализировать ?>
            <?php // доступность самих кнопок - через Multipage и его потомки ?>

            <?php if (!isset($this->model['clusters']) || (count($this->model['clusters']) <= 1)): ?>
                <?php if ($this->navPanel['stop']) :?>
                    <a href="<?= $this->navPanel['stop'] ?>" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-submit at-form-stop"><?= _('Выйти') ?></a>
                <?php endif;?>
                <?php if ($this->navPanel['finalize']) :?>
                    <a href="<?= $this->navPanel['finalize'] ?>" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты и выйти')) ?>" class="at-form-button at-form-submit at-form-advance"><?= _('Закончить') ?></a>
                <?php endif;?>
            <?php else: ?>

                <?php // если страниц много ?>
                <?php if ($this->navPanel['prevId']) :?>
                    <a href="<?= $this->navPanel['prev'] ?>" data-item-id="<?=$this->navPanel['prevId']?>" title="<?= $this->escape(_('Вернуться к предыдущей странице')) ?>" class="at-form-button at-form-submit at-form-return"><?= _('Назад') ?></a>
                <?php else:?>
                    <a href="#" class="at-form-button at-form-submit at-form-return ui-state-disabled"><?= _('Назад') ?></a>
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