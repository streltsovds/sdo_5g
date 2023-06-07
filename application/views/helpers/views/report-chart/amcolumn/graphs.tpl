<?php foreach ($this->legend as $graph => $value):?>
    <?php if ( $graph === 'title' ) continue; ?>
    var graph = new AmCharts.AmGraph();
    graph.title = "<?php echo $this->legend[$graph]?>"
    graph.valueField = "<?php echo $graph?>";
    graph.type = "column";
    graph.lineColor = "<?php echo $this->palette[$graph]?>"
    graph.lineAlpha = 0;
    graph.fillAlphas = 0.8;
    chart.addGraph(graph);
<?php endforeach;?>



