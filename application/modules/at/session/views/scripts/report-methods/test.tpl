<h2><?php echo _('Профессиональное тестирование');?></h2>
<div class="clearfix">
<?php $this->reportChartJS();?>
<?php echo $this->reportChartJS(
    $this->charts['criteria_test']['data'], 
    $this->charts['criteria_test']['graphs'], 
    array(
        'id' => 'criteria_test',
        'type' => 'apexbar',        
        'dataLabel' => 'title',
        'colors' => ['#C24E5F','#4b8c3e'],
        'title'=> sprintf(_('Диаграмма %s. Объединенные результаты оценки квалификаций'), ++$i),
        'maxValue' => 100,
        'height' => 300,
    ),
    array(
        'dataTitle'=> _('Квалификация'),
        'showTable' => HM_View_Helper_ReportChartJS::TABLE_DISPLAY_BLOCK,
    )
);?>
</div>
<div class="pagebreak"></div>