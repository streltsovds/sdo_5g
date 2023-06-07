<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<div class="at-form-report">

<?php if ($this->print):?>
<h1><?php echo _('Отчет о психологическом тестировании');?></h1>
<?php endif;?>

<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['general-test']);?>
    </div>
    <div class="right-block">
        <?php echo $this->reportList($this->lists['general-context']);?>
    </div>
</div>

<?php if (isset($this->lists['clusters'])): ?>
<h2><?php echo _('Результаты по темам');?></h2>
<div class="clearfix">
    <?php echo $this->reportList($this->lists['clusters'], HM_View_Helper_ReportList::CLASS_BRIEF);?>
</div>
<?php endif;?>

<?php if (isset($this->lists['categories'])): ?>
<h2><?php echo _('Результаты по показателям психологического опроса');?></h2>
<div class="clearfix">
    <?php echo $this->reportList($this->lists['categories']);?>
</div>
<?php endif;?>

<?php if (count($this->questions)): ?>
<h2><?php echo _('Подробные результаты');?></h2>
<?php foreach ($this->questions as $question):?>
    <h3><?php echo ++$i . '. ' . $question->shorttext;?></h3>
    <div class="clearfix">
        <?php echo $this->reportList($this->lists['question-' . $question->question_id], HM_View_Helper_ReportList::CLASS_BRIEF);?>
    </div>

<?php endforeach;?>
<?php endif; ?>
</div>

<?php if (!$this->print):?>
<div>
    <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
</div>
<?php endif;?>

<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'quest', 'controller' => 'report', 'action' => 'attempt', 'print' => 1)),
)); ?>