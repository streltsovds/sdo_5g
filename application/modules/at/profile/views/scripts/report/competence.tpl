
<style>
    div.report-chart-table {
        max-width: 100% !important;
        width: 100% !important;
    }

    div.report-chart-graph {
        max-width: 100% !important;
        width: 100% !important;
    }
    
    div#competence-chart-container {
        width: 100% !important;
    }

    div#competence-chart-container div{
        width: 100% !important;
    }

    div#competence-chart-container div svg{
        width: 100% !important;
    }
</style>

<div class="at-form-report">
<div class="clearfix">
<?php //echo $this->reportChart(array(
//    'chartId' => 'competence',
//    'chartType' => 'bar',
//    'chartData' => $this->charts['competence'],
//    'chartTitle'=> _('Диаграмма 1. Требования к уровню развития компетенций (профиль успешности)'),
//    'chartMaxValue' => $this->scaleMaxValue,
//    'chartHeight' => 400,
//    'multiGraph' => false,
//));?>
<?php echo $this->reportChartJS(
    $this->charts['competence']['data'],
    $this->charts['competence']['graphs'],
    array(
        'id' => 'competence',
        'type' => 'bar',
        'maxValue' => $this->scaleMaxValue,
        'minValue' => $this->scaleMinValue,
        //'dataValue' => 'value',
        'dataLabel' => 'title',
        'height' => 400,
        'width' => 1000,
//        'title' => _('Диаграмма 1. Требования к уровню развития компетенций (профиль успешности)'),
    ),
    array(
        'showTable' => 2,
        'dataTitle' => _('Компетенция'),
    )
);
?>
</div>
</div>