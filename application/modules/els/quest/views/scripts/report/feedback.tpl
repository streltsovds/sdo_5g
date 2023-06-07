<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<div class="at-form-report">

<?php if ($this->print):?>
<h1><?php echo _('Сбор обратной связи по курсу');?></h1>
<?php endif;?>

<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->general);?>
    </div>
</div>

<?php foreach($this->clusters as $clusterId => $clusterData) : ?>
    <?php if (!$clusterData['questions']) continue; ?>
    <?php if ($clusterData['title']) : ?>
        <h1><?php echo $clusterData['title'];?></h1>
    <?php endif;?>
    <?php for ($i=0; $i<count($clusterData['questions']); $i++) : ?>
    <?
        $questionId = $clusterData['questions'][$i];
        $question = $this->feedback[$questionId];
    ?>

    <h2><?php echo $question['graphs'][$questionId]['title'];?></h2>
    <div class="clearfix">
        <?php $this->reportChartJS();
            if ($this->print){
                $showTable = HM_View_Helper_ReportChartJS::TABLE_DISPLAY_BLOCK;
            } else {
                $showTable = HM_View_Helper_ReportChartJS::TABLE_DISPLAY_INLINE;
            }
        ?>
        <?php echo $this->reportChartJS(
        array_values($question['data']),
        $question['graphs'],
        array(
            'id' => 'question'.$questionId,
            'type' => 'radar',
            'title'=> '',
            'maxValue' => $question['maxValue'],
            'height' => $this->print ? 350 : 600,
            'hideLegend' => true
        ),
        array(
            'dataTitle'=> _('Вариант ответа'),
            'showTable' => $showTable,
            'procentColumn' => true,
            'totalValue' => $question['totalValue']
        )
        ); ?>
    </div>
    <?php endfor;?>
<?php endforeach; ?>

</div>
<?php if (!$this->print):?>
<div>
    <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
</div>
<?php endif;?>

<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'quest', 'controller' => 'report', 'action' => 'feedback', 'print' => 1)),
)); ?>