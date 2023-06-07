<script defer src="/js/new-amcharts-ajax.js"></script>
<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function() {
    var chartData   = <?php echo json_encode($this->data);?>;
    var chartGraphs = <?php echo json_encode($this->graphs);?>;
    var container   = "analytics-chart-container";
    var options     = {"legendEnabled":1,"graphsType":"column"}
    onResponse(chartData, chartGraphs, container, options);
    $("#user_analytics_form :input").change(function(){
        $( "div#analytics-chart-container" ).html("");
        loadData("", "analytics-chart-container");
    });
});
<?php $this->inlineScript()->captureEnd(); ?>
