<script src="/js/lib/amcharts_3.21.12/amcharts/serial.js" type="text/javascript"></script>
<?php $this->inlineScript()->captureStart(); ?>
$(function(){

	var chart;
	var chartData = <?php echo html_entity_decode(json_encode($this->data));?>;

	AmCharts.ready(function () {
        // SERIAL CHART
        chart = new AmCharts.AmSerialChart();
        chart.dataProvider = chartData;
        chart.categoryField = "title";
        chart.fontSize = 14;
        
        // TITLE
        //chart.addTitle('<?php echo html_entity_decode($this->options['title']);?>', 15);
        
        // AXES
        var categoryAxis = chart.categoryAxis;
        //categoryAxis.labelRotation = 20;
        categoryAxis.fontSize = 10;
        categoryAxis.color = "#565051";
        categoryAxis.gridPosition = "start";
        
        // VALUE AXIS
        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.axisAlpha = 0.15;
        <?php if (Zend_Registry::get('view')->getRequest()->getActionName() != 'competence'): ?>
            valueAxis.minimum = 0;
        <?php else: ?>
            valueAxis.minimum = <?php echo $this->options['minValue']?>;
        <?php endif; ?>
        valueAxis.maximum = <?php echo $this->options['maxValue']?>;
        valueAxis.dashLength = 3;
        chart.addValueAxis(valueAxis);
        
        // GRAPH
        <?php if (count($this->graphs)):?>
        <?php foreach ($this->graphs as $key => $graph):?>
            var graph = new AmCharts.AmGraph();
            graph.title = "<?php echo $graph['legend']?>"
            graph.valueField = "<?php echo $key?>";
            graph.type = "column";
            graph.lineColor = "<?php echo $graph['color']?>"
            graph.lineAlpha = 0;
            graph.fillAlphas = 0.8;
            chart.addGraph(graph);
        <?php endforeach;?>
        
            // LEGEND
            var legend = new AmCharts.AmLegend();
            legend.borderAlpha = 0.2;
            legend.horizontalGap = 10;
            legend.autoMargins = false;
            legend.marginLeft = 20;
            legend.marginRight = 20;
            legend.marginBottom = 20;
            <?php if (Zend_Registry::get('view')->getRequest()->getActionName() != 'competence'): ?>
            chart.addLegend(legend);
            <?php endif; ?>
        <?php else: ?>
            var graph = new AmCharts.AmGraph();
            graph.valueField = 'value';
            graph.type = "column";
            graph.colorField = "color";
            graph.lineAlpha = 0;
            graph.fillAlphas = 0.8;
            chart.addGraph(graph);         
        <?php endif; ?>
        
        // WRITE
        chart.write("<?php echo $this->options['id']?>-chart-container");
    });
});
<?php $this->inlineScript()->captureEnd(); ?>