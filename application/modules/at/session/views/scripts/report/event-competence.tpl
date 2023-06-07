<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<div class="at-form-report">

<?php if ($this->print):?>
<h1><?php echo _('Отчет о заполнении анкеты');?></h1>
<?php endif;?>

<?php if ($this->status != HM_At_Session_Event_EventModel::STATUS_COMPLETED): ?>
<div class="attention"><?php echo _('ВНИМАНИЕ! Заполнение анкеты не закончено, результаты предварительные');?></div>
<?php endif;?>

<h2><?php echo _('Общая информация');?></h2>
<div class="report-summary clearfix">
    <div class="left-block">
        <h3><?php echo _('Оценочная сессия');?></h3>
        <?php echo $this->reportList($this->lists['general']);?>
    </div>
    <div class="clearfix"></div>
    <div class="left-block">
        <h3><?php echo _('Участник');?></h3>
        <?php echo $this->reportList($this->lists['general-user']);?>
    </div>
    <div class="right-block">
        <h3><?php echo _('Респондент');?></h3>
        <?php echo $this->reportList($this->lists['general-respondent']);?>
    </div>
</div>

<h2 class="clearfix"><?php echo _('Оценка компетенций');?></h2>

<div class="clearfix">
<?php $this->reportChartJS();?>
<?php echo $this->reportChartJS(
    $this->charts['criteria']['data'], 
    $this->charts['criteria']['graphs'], 
    array(
        'id' => 'criteria',
        'type' => 'radar',
        'title'=> _('Диаграмма 1. Результаты оценки компетенций'),
        'maxValue' => $this->scaleMaxValue,
        'height' => $this->print ? 350 : 600,
    ),
    array(
        'dataTitle'=> _('Компетенция'),
        'showTable' => HM_View_Helper_ReportChartJS::TABLE_DISPLAY_BLOCK,
    )                
);  ?>
</div>
<?php if (count($this->competenceCriteria)): ?>
<h2><?php echo _('Подробные результаты по компетенциям');?></h2>
<?php foreach($this->competenceCriteria as $criterionId => $criterionName): ?>
<?php if (!count($this->lists['criterion_' . $criterionId])) continue;?>
<div class="clearfix">
<h3><?php echo $criterionName;?></h3>
<?php echo $this->reportList($this->lists['criterion_' . $criterionId]);?>
</div>
<?php endforeach;?>
<?php else:?>
<p><?php echo _('Оценка индикаторов не применяется');?></p>
<?php endif;?>

<?php if (count($this->competenceMemos)): ?>
<h2><?php echo _('Дополнительная информация');?></h2>
<?php foreach($this->competenceMemos as $memoId => $memoName): ?>
<div class="clearfix">
<?php echo $this->reportText($this->texts['memo_' . $memoId], $memoName);?>
</div>
<?php endforeach;?>
<p></p>
<?php endif;?>

<h2><?php echo _('Выводы');?></h2>
<h3><?php echo _('Cильные стороны пользователя');?></h3>
<?php echo $this->reportList($this->lists['top']);?>

<h3><?php echo _('Компетенции, набравшие наименьшее количество баллов');?></h3>
<?php echo $this->reportList($this->lists['bottom']);?>


    <?php if ($this->atMemoResults):?>
    <h3><?php echo _('Общее впечатление о пользователе');?></h3>

    <table class="report-list report-list-normal">
        <tbody>
            <?php foreach($this->atMemoResults as $key => $value): ?>
                <tr>
                    <td class="report-list-key"><?php echo $key;?></td>
                    <td class="report-list-value"><?php echo $value;?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

</div>


<?php if (!$this->print):?>
<div>
    <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
</div>
<?php endif;?>

<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'session', 'controller' => 'report', 'action' => 'event', 'print' => 1)),
)); ?>
