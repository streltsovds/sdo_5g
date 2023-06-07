<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<div class="at-form-report">

<?php if ($this->print):?>
<h1><?php echo _('Отчет о результатах оценочной сессии');?></h1>
<?php endif;?>

<?php if ($this->status != HM_At_Session_SessionModel::STATE_CLOSED): ?>
<div class="attention"><?php echo _('ВНИМАНИЕ! Оценочная сессия еще не окончена, результаты предварительные');?></div>
<?php endif;?>

<div class="report-summary">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['session']);?>
    </div>
    <div class="right-block">
        <?php echo $this->reportList($this->lists['stats']);?>
    </div>
</div>

<div  style="clear: both"></div>

<?php $i = 1;?>
<?php if (count($this->competenceProfiles)): ?>
<?php foreach($this->competenceProfiles as $profile): ?>
<h2><?php echo sprintf(_('Профиль %s'), sprintf('&laquo;%s&raquo;', $profile->name));?></h2>

<?php if (in_array(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, array_keys($this->methods[$profile->profile_id]))): ?>
<h3><?php echo _('Результаты оценки компетенций методом 360 град.')?></h3>
<?php echo $this->reportTable(
    $this->tables['competence_profile_' . $profile->profile_id],
    sprintf(_('Таблица %d. Профиль %s'), $i++, sprintf('&laquo;%s&raquo;', $profile->name))
);?>

<?php if(1): // что-то здесь не то, надо разобраться?>
<h3><?php echo _('Выводы');?></h3>
<p><?php echo _('Пользователи, набравшие набольшее количество баллов');?>:</p>
<?php echo $this->reportList($this->lists['competence_top']);?>

<p><?php echo _('Пользователи, набравшие наименьшее количество баллов');?>:</p>
<?php echo $this->reportList($this->lists['competence_bottom']);?>
<?php endif;?>
<?php endif;?>

<?php if (in_array(HM_At_Evaluation_EvaluationModel::TYPE_RATING, array_keys($this->methods[$profile->profile_id]))): ?>
<h3><?php echo _('Результаты оценки компетенций методом парных сравнений')?></h3>
<?php foreach($this->departments[$profile->profile_id] as $soid => $departmentName): ?>
<?php echo $this->reportTable(
    $this->tables['rating_profile_' . $profile->profile_id . '_' . $soid],
    sprintf(_('Таблица %d. %s; %s'), $i++, $departmentName, $profile->name)
);?>
<?php endforeach;?>
<?php endif;?>

<?php endforeach;?>
<?php endif;?>

</div>
<?php if (!$this->print):?>
<div>
    <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
</div>
<?php endif;?>

<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'session', 'controller' => 'report', 'action' => 'index', 'print' => 1)),
)); ?>