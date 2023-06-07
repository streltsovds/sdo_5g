<?php
    
    $this->headScript()->appendFile($this->serverUrl('/js/content-modules/quest.js') );
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
    $containerId = $this->id('at-form');
?>
<div class="at-competence at-form">
    <div class="tests_header">
        <?php // @todo: рефакторить этот кусок unmanaged ?>
        <table  border="0" cellspacing="0" cellpadding="0" class="tests_main">
            <tr>
                <td class="header_first_td" align="left" valign="middle"><?= $this->model['event']->name ?></td>
                <td class="header_three_td" align="right">
                    <?php if (!isset($this->singleScreen) || !$this->singleScreen): ?>
                    <div style="display:none;"><?=_("Время не ограничено")?></div>
                    <div class="progress">
                        <div class="progress_load" id="progress_percent"></div>
                    </div>
                    <div class="progress_stop">
                        <a href="<?= $this->url(array('action' => 'index'));?>" onclick='return confirm("<?=_('Вы действительно хотите прервать заполнение анкеты? Данные будут сохранены и в дальнейшем Вы сможете продолжить работу с анкетой.')?>")' title="<?= $this->escape(_('Прервать заполнение анкеты')) ?>" class="progress_stop"><img style=" width: 16px; height: 17px; border: none;" alt="" src="/images/content-modules/tests/break.gif"></a>
                    </div>
                    <div style='position:relative; top:2px; float:left;color:#fff'><?php echo _('Время не ограничено');?></div>
                    <?php endif;?>
                </td>
            </tr>
        </table>
    </div>
    <div class="at-form-wrapper">

        <div class="at-form-header">
            <div class="at-form-comment">
                <?= Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceComment', $this->info['session']->getOptionsModifier());?>
            </div>
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
            <!--div class="at-form-scale">
                <h4><?= $this->model['scale']->name?></h4>
                <ol>
                    <?php foreach ($this->model['scale']->scaleValues as $value): ?>
                    <?php if ($value->value == HM_Scale_Value_ValueModel::VALUE_NA) continue;?>
                    <li><?php echo $value->value;?>: <?php echo $value->text;?><?php if ($value->description):?> - <?php echo $value->description;?><?php endif;?>
                        <?php endforeach;?>
                </ol>
            </div-->
        </div>

        <div class="at-form-body">
            <div id="<?= $containerId ?>" class="at-form-container">
                <?php echo $this->partial('load.tpl', array(
                'itemId' => count($this->model['index']) ? array_shift(array_keys($this->model['index'])) : 1,
                'model' => $this->model,
                'results' => $this->results,
                'memoResults' => $this->memoResults,
                'navPanel' => $this->navPanel,
                'saveUrl' => $this->url(array('action' => 'save')),
                ));?>
            </div>
        </div>
    </div>
</div>