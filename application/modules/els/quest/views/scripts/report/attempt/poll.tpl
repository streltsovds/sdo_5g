<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<div class="at-form-report">

<?php if ($this->print):?>
<h1><?php echo _('Отчет о тестировании');?></h1>
<?php endif;?>

<h2><?php echo _('Общие сведения');?></h2>
<div>
    <v-row> 
        <v-col>
            <?php echo $this->reportList($this->lists['general-test']);?>
        </v-col>    
        <v-col>
            <?php echo $this->reportList($this->lists['general-context']);?>
        </v-col>    
    <v-row/>
</div>


<!-- <div class="report-summary clearfix">
    <div class="left-block">
        
    </div>
    <div class="right-block">
        
    </div>
</div> -->

<?php if (count($this->questions)): ?>
<h2><?php echo _('Подробные результаты');?></h2>
<?php foreach ($this->questions as $question):?>
    <div class="tmc-report-question">
        <h3><?php echo ++$i . '. ' . $question->shorttext;?></h3>
        <div class="clearfix">
            <?php echo $this->reportList($this->lists['question-' . $question->question_id], HM_View_Helper_ReportList::CLASS_COLORED_QUESTION);?>
        </div>
    </div>

<?php endforeach;?>
<?php endif; ?>
</div>

<?php if (!$this->print):?>
<!-- <div>
    <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
</div> -->
<?php endif;?>

<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'quest', 'controller' => 'report', 'action' => 'attempt', 'print' => 1)),
)); ?>