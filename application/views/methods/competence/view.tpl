<?php

$this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
$this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
$this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forms.css'), 'screen,print');

$this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.iframe-transport.min.js'));
$this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.fileupload.min.js'));
$this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.fileupload-ui.min.js'));
$this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.fileupload-fileremove.js'));

?>
    <hm-test
            context-helper="<?php echo $this->url(array('module' => $this->module, 'controller' => $this->controller, 'action' => 'context-helper', 'context-helper-action' => 'info'));?>"
            load="<?php echo $this->url(array('module' => $this->module, 'controller' => $this->controller, 'action' => 'load'));?>"
    ></hm-test>

<?php
/*
$this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
$this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
$containerId = $this->id('at-form');
?>
<div class="at-competence at-form">

    <div class="tests_header">
        <?php // @todo: рефакторить этот кусок unmanaged ?>
        <table  border="0" cellspacing="0" cellpadding="0" class="tests_main" style="width:100%;">
            <tr>
                <td class="header_first_td" align="left" valign="middle">
                    <?= $this->model['event']->name ?>
                </td>
                <td class="header_three_td" align="right" style="width: 200px">
                    <div style="display:none;"><?=_("Время не ограничено")?></div>
                    <div class="progress">
                        <div class="progress_load" id="progress_percent"></div>
                    </div>
                    <div class="tmc-at-progress-text" style='position:relative; top:2px; float:left;color:#fff;width: 200px;'>

                    </div>
                    <div class="progress_stop" style="float: right;">
                        <a href="<?= $this->resultsUrl ?>" onclick='return false;' title="<?= $this->escape(_('Закончить и перейти к результатам')) ?>" class="progress_stop"><img style=" width: 16px; height: 17px; border: none;" alt="" src="/images/content-modules/tests/break.gif"></a>
                    </div>
                    <div style='position:relative; top:2px; float:left;color:#fff'><?php echo _('Время не ограничено');?></div>
                </td>
            </tr>
        </table>
    </div>
    <div class="at-form-wrapper">
    <?= $this->questProgress($this->progress, array('target' => $containerId))?>

        <div class="at-form-header">
            <?php if (strlen($comment = $this->model['options']['competenceComment'])):?>
            <div class="at-form-comment">
                <?= $comment;?>
            </div>
            <?php else:?>
            <div style="height:15px;"></div>
            <?php endif;?>
            <div class="at-form-info">
                <h4><?= _("Оцениваемый пользователь") ?></h4>
                <?php if ($this->info && is_a($this->info['user'], 'HM_User_UserModel')): ?>
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
                    <?php foreach ($this->model['scaleValues'] as $value): ?>
                    <?php if ($value->value == HM_Scale_Value_ValueModel::VALUE_NA) continue;?>
                    <li><?php echo $value->value;?>: <?php echo $value->text;?><?php if ($value->description):?> - <?php echo $value->description;?><?php endif;?>
                        <?php endforeach;?>
                </ol>
            </div-->
        </div>
        <div class="at-form-body">
            <div id="<?= $containerId ?>" class="at-form-container">
                <?= $this->action('load', 'competence-multipage', 'event') ?>
            </div>
        </div>
    </div>

</div>
<? */
