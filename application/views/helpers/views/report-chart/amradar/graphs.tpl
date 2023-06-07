<?php foreach ($this->graphs as $graph => $value):?>
    <?php if ( $graph === 'title' ) continue; ?>
    var graph = new AmCharts.AmGraph();
    graph.title = "<?php echo $this->graphs[$graph]?>"
    graph.lineColor = "<?php echo $this->colors[$graph]?>"
    graph.fillAlphas = 0.1;
    graph.valueField = "<?php echo $graph?>";
    chart.addGraph(graph);
<?php endforeach;?>


