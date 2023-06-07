<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<div class="at-form-report">

<?php if ($this->print):?>
<h1><?php echo _('Отчет об анкетировании');?></h1>
<?php endif;?>

<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['general-test']);?>
    </div>
    <div class="right-block">
        <?php echo $this->reportList($this->lists['general-context']);?>
    </div>
</div>

<?php if (count($this->questions)): ?>
<h2><?php echo _('Результаты');?></h2>
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