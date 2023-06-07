<?php
    
    $this->headScript()->appendFile($this->serverUrl('/js/content-modules/quest.js') );
    $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.checkbox.js'));
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/test.css'));
    $containerId = $this->id('at-form');
?>
<div class="at-competence at-form">
    <div class="tests_header">
    <?php // @todo: рефакторить этот кусок unmanaged ?>
    <table  border="0" cellspacing="0" cellpadding="0" class="tests_main" style="width: 100%;">
    	<tr>
    		<td class="header_first_td" align="left" valign="middle"><?= $this->model['event']->name ?></td>
    	</tr>
    </table>
    </div>
    <div class="at-form-wrapper">

        <div class="at-form-header">
            <?php if ($kpiComment = trim(Zend_Registry::get('serviceContainer')->getService('Option')->getOption('kpiСomment', $this->info['session']->getOptionsModifier()))):?>
            <div class="at-form-comment">
                <?= $kpiComment;?>
            </div>
            <?php endif;?>
            <div class="at-form-info">
                <h4><?= _("Оцениваемый пользователь") ?></h4>
                <?php if ($this->info): ?>
                <ul>
                    <li><?php echo $this->cardLink('/user/list/view/user_id/' . $this->info['user']->MID); // @todo: baseUrl не работает ?><?php echo $this->escape($this->info['user']->getName())?></li>
                    <li><?php echo _('Подразделение')?>: <?php echo $this->info['department']->name;?></li>
                    <li><?php echo _('Должность')?>: <?php echo $this->info['position']->name;?></li>
                    <li><?php echo _('Профиль должности')?>: <?php echo $this->info['profile']->name;?></li>
                </ul>
                <?php endif;?>
            </div>
        </div>

        <div class="at-form-body">
            <div id="<?= $containerId ?>" class="at-form-container">

                <form method="POST" action="<?= $this->escape($this->url(array('action' => 'save'))) ?>">
                <input type="hidden" name="finalize" value="0">
                <input type="hidden" name="item_id" value="<?= $this->itemId?>">
                <?php if (count($this->model['kpis'])): ?>
                <br>
                <h3><?= _('Оценка показателей эффективности') ?></h3>
                <table>
                    <thead><tr>
                        <th style="text-align: left;"><?php echo _('Показатель эффективности');?></th>
                        <!--<th><?php //echo _('Вес');?></th>-->
                        <th style="text-align: left;"><?php echo _('Плановое значение');?></th>
                        <th style="text-align: left;"><?php echo _('Фактическое значение');?></th>
                        <th style="text-align: left;"><?php echo _('Комментарий');?></th>
                    </tr></thead>
                    <tbody><?php foreach ($this->model['kpis'] as $cluster => $kpis): ?>
                        <?php if ($cluster != HM_At_Kpi_Cluster_ClusterModel::NONCLUSTERED):?>
                        <tr class="quest-item-rowgroup <?= $this->cycle(array('odd', 'even'))->next() ?>">
                            <td colspan="99"><?php echo $cluster;?></td>
                        </tr>
                        <?php endif;?>
                        <?php foreach ($kpis as $kpi): ?>
                        <tr class="quest-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
                            <td class="title"><?php echo $kpi['name'];?></td>
                            <!--<td class="title"><?php //echo $kpi['weight'];?></td>-->
                            <td class="title"><?php echo $kpi['value_plan'];?>&nbsp;<?php echo $kpi['unit'];?></td>
                            <td class="title" style="white-space: nowrap;">
                                <?php if($kpi['value_type'] == HM_At_Kpi_User_UserModel::TYPE_QUANTITATIVE):?>
                                    <input type="text"
                                           name="kpis[<?php echo $kpi['user_kpi_id'];?>]"
                                           value="<?php echo $kpi['value_fact'];?>"
                                    >&nbsp;<?php echo $kpi['unit'];?>
                                <?php else:?>
                                    <select name="kpis[<?php echo $kpi['user_kpi_id'];?>]">
                                        <?php foreach(HM_At_Kpi_User_UserModel::getQualitiveValues() as $key => $value):?>
                                            <option value='<?=$key?>' <?php echo ($kpi['value_fact'] == $key) ? 'selected' : '';?>><?=$value?></option>
                                        <?php endforeach;?>
                                    </select>
                                <?php endif;?>
                            </td>
                            <td class="title">
                                <?php /*if ($this->model['evaluation']->relation_type == HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF): */?>
                                <textarea name="comments[<?php echo $kpi['user_kpi_id'];?>]" style="width: 100%; height: 50px;"><?php echo $kpi['comments']?></textarea>
                                <?php /*else:?>
                                <?php echo $kpi['comments']?>
                                <?php endif;*/?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    <?php endforeach;?></tbody>
                </table>
                <?php else: ?>
                <p><?php echo _('Показатели эффективности на данный период не заданы');?></p>
                <?php endif;?>
                <?php if ($this->model['options']['kpiUseCriteria'] && ($this->model['evaluation']->relation_type != HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF)): ?>
                <h3 style="padding-top: 30px;"><?= _('Оценка способа достижения') ?></h3>
                <table class="criteria-table criteria-kpi-table">
                    <thead><tr>
                        <th>
                            <?php echo _('Критерий');?>
                        </th>
                        <?php if (count($this->model['scale']->scaleValues)):?>
                            <?php foreach ($this->model['scale']->scaleValues as $value): ?>
                                <th><?php echo !empty($value->text) ? $value->text : $value->value;?></th>
                            <?php endforeach;?>
                        <?php endif;?>
                    </tr></thead>
                    <?php if (count($this->model['criteria'])): ?>
                    <tbody><?php foreach ($this->model['criteria'] as $criterion): ?>
                        <tr class="quest-item-row <?= $this->cycle(array('odd', 'even'))->next() ?>">
                            <td class="title"><?php echo $criterion->name;?></td>
                            <?php if (count($this->model['scale']->scaleValues)):?>
                                <?php foreach ($this->model['scale']->scaleValues as $value): ?>
                                    <td class="value">
                                        <input type="radio" class="quest-answer" name="results[<?php echo $criterion->criterion_id;?>]" value="<?php echo $value->value_id;?>" <?php if (isset($this->results[$criterion->criterion_id]) && ($this->results[$criterion->criterion_id] == $value->value_id)) :?>checked<?php endif;?>>
                                        <?php if ($this->model['options']['kpiUseScaleValues'] && strlen($this->model['criteria'][$criterion->criterion_id]->scaleValues[$value->value_id])): ?>
                                            <div class="tooltip-description" style="display: none;">
                                                <p class="hint"><?php echo $this->model['criteria'][$criterion->criterion_id]->scaleValues[$value->value_id]?></p></div>
                                            <span class="tooltip"></span>
                                        <?php endif; ?>
                                        </td>
                                <?php endforeach;?>
                            <?php endif;?>
                        </tr>
                    <?php endforeach;?></tbody>
                    <?php endif;?>
                </table>
                <?php  $this->inlineScript()->captureStart(); ?>
                $("input[type='radio']").checkbox({cls:'jquery-radio-checkbox'});
                <?php $this->inlineScript()->captureEnd(); ?>
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
                <?php endif; // if kpiUseCriteria?>
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
                <div class="at-form-navpanel">
                        <?php if ($this->navPanel['stop']) :?>
                        <a href="<?= $this->navPanel['stop'] ?>" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-submit at-form-stop"><?= _('Выйти') ?></a>
                        <?php endif;?>
                        <?php if ($this->navPanel['finalize']) :?>
                        <a href="<?= $this->navPanel['finalize'] ?>" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты и выйти')) ?>" class="at-form-button at-form-submit at-form-finalize"><?= _('Готово') ?></a>
                        <?php endif;?>
                </div>
                </form>

            </div>
        </div>
    </div>
</div>