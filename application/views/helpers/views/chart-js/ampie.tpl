<script src="/js/lib/amcharts_3.21.12/amcharts/pie.js" type="text/javascript"></script>
<?php $this->inlineScript()->captureStart(); ?>
$(function(){
var chartData = <?php echo json_encode($this->data); ?>;
var chart = new AmCharts.AmPieChart();
chart.valueField = "count";
chart.titleField = "title";
<?php if($this->options['colors']): ?>
chart.colors = <?php echo json_encode(array_values($this->options['colors'])); ?>;
<?php endif ?>
<?php if($this->options['radius']): ?>
chart.radius = <?php echo $this->options['radius']?>;
<?php endif ?>
<?php if($this->options['labelRadius']): ?>
chart.labelRadius = <?php echo $this->options['labelRadius']?>;
<?php endif ?>
<?php if($this->options['labelText']): ?>
chart.labelText = '<?php echo $this->options['labelText']?>';
<?php endif ?>
chart.angle = 15;
chart.depth3D = 5;
chart.marginTop = 2;
chart.startRadius = '300%';
chart.showZeroSlices = true;
chart.dataProvider = chartData;


chart.write("<?php echo $this->options['id']?>-chart-container");
});
<?php $this->inlineScript()->captureEnd(); ?>