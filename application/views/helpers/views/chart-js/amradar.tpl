<script src="/js/lib/amcharts_3.21.12/amcharts/radar.js" type="text/javascript"></script>
<?php $this->inlineScript()->captureStart(); ?>

$(function(){
    var chart;
    var chartData = <?php echo json_encode($this->data); ?>;

    AmCharts.ready(function () {
        // RADAR CHART
        chart = new AmCharts.AmRadarChart();
        //chart.sequencedAnimation = false;
        chart.dataProvider = chartData;
        chart.categoryField = "title";
        //chart.startDuration = 0;
    
        // TITLE
        //chart.addTitle("<?php echo $this->options['title']; ?>", 15);
    
        // VALUE AXIS
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.gridType = "circles";
        valueAxis.fillAlpha = 0.05;
        valueAxis.fillColor = "#000000"
        valueAxis.axisAlpha = 0.2;
        valueAxis.gridAlpha = 0;
        valueAxis.fontWeight = "bold"
        valueAxis.minimum = 0;
        valueAxis.maximum = <?php echo $this->options['maxValue']; ?>;
        chart.addValueAxis(valueAxis);
    
        // GRAPH
        <?php foreach ($this->graphs as $key => $graph):?>
        <?php if ($key === 'title') continue; ?>
            var graph = new AmCharts.AmGraph();
            graph.title = "<?php echo $graph['legend']; ?>"
            graph.lineColor = "<?php echo $graph['color']?>"
            graph.fillAlphas = 0.1;
            graph.valueField = "<?php echo $key; ?>";
            chart.addGraph(graph);
        <?php endforeach;?>
    
        // LEGEND
        if (!'<?=$this->options['hideLegend'];?>') {
        var legend = new AmCharts.AmLegend();
        legend.borderAlpha = 0.2;
        legend.horizontalGap = 10;
        legend.autoMargins = false;
        legend.align = "center";
        //legend.marginLeft = 20;
        //legend.marginRight = 20;
        legend.marginBottom = 20;
        chart.addLegend(legend);
        }
    
        // WRITE
        chart.write("<?php echo $this->options['id']?>-chart-container");
    });
});
<?php $this->inlineScript()->captureEnd(); ?>