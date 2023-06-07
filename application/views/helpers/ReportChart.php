<?php
class HM_View_Helper_ReportChart extends HM_View_Helper_Abstract
{
    const TABLE_DISPLAY_NONE = 0;
    const TABLE_DISPLAY_INLINE = 1;
    const TABLE_DISPLAY_BLOCK = 2;

    protected $_chartId;
    protected $_chartType;
    protected $_chartData;
    protected $_chartTitle;
    protected $_chartHeight;
    protected $_chartMaxValue;
    
    protected $_multiGraph;
    protected $_showTable;
    protected $_footnote;
    
    static public $palette = array();

    public function setPalette($colors)
    {
        self::$palette = $colors;
    }

    public function reportChart($options)
    {
        if (!is_array($options['chartData']) || !count($options['chartData'])) return '';

        $this->_chartId = $this->view->chartId = $options['chartId'];
        $this->_chartType = $options['chartType'];
        $this->_chartData = $this->view->data = $options['chartData'];
        $this->_chartTitle = $this->view->title = isset($options['chartTitle']) ? $options['chartTitle'] : '';
        $this->_chartMaxValue = isset($options['chartMaxValue']) ? $options['chartMaxValue'] : 4;
        $this->_chartHeight = isset($options['chartHeight']) ? $options['chartHeight'] : 250;
        $this->_multiGraph = $this->view->multigraph = isset($options['multiGraph']) ? $options['multiGraph'] : true;
        $this->_showTable = $this->view->showtable = isset($options['showTable']) ? $options['showTable'] : self::TABLE_DISPLAY_BLOCK;
        $this->_footnote = $this->view->footnote = isset($options['footnote']) ? $options['footnote'] : false;

        $this->_addChart();
        return $this->view->render('report-chart.tpl');
    }

    protected function _addChart()
    {
		$baseUrl = Zend_Registry::get('config')->url->base;
		$this->view->headScript()->appendFile("{$baseUrl}js/lib/amcharts/swfobject.js");

		$loadingStr = _('Загрузка');
		$xmlSettings = $this->_getXmlSettings();
		$xmlData = $this->_getXmlData();

		$script = <<<E0D
			$(function(){
				{$this->_chartId}Chart = new SWFObject("{$baseUrl}js/lib/amcharts/{$this->_chartType}.swf", "{$this->_chartId}", "100%", {$this->_chartHeight}, "8", "#FFFFFF");
				{$this->_chartId}Chart.addVariable("chart_id", "{$this->_chartId}");
				{$this->_chartId}Chart.addVariable("path", "{$baseUrl}js/lib/amcharts/");
  				{$this->_chartId}Chart.addVariable("chart_settings", "{$xmlSettings}");
   				{$this->_chartId}Chart.addVariable("chart_data", "{$xmlData}");
				{$this->_chartId}Chart.addVariable("loading_settings", '{$loadingStr}');
				{$this->_chartId}Chart.addVariable("loading_data", '{$loadingStr}');
				{$this->_chartId}Chart.addParam("wmode", 'opaque');
				{$this->_chartId}Chart.write("{$this->_chartId}-container");
			});
E0D;
		$this->view->headScript()->appendScript($script);
    }

    protected function _getXmlSettings()
    {
        $view = new Zend_View();

        $view->setScriptPath(APPLICATION_PATH . "/views/helpers/views/report-chart/{$this->_chartType}/");
        $view->max = $this->_chartMaxValue;
        $xml = $view->render("settings.tpl");
        return self::_plainify($xml);
    }

    protected function _getXmlData()
    {
        $view = new Zend_View();
        $view->id = $this->_chartId;

        $chartData = ($this->_multiGraph) ? $this->_chartData: self::trimTitles($this->_chartData);

        // preserving keys
        $series = ($this->_multiGraph) ? array_slice($chartData, 0, 1, true) : array_shift($chartData);
        $data = ($this->_multiGraph) ? array_slice($chartData, 1, null, true) : array_pop($chartData);

        $view->series = $series;
        $view->data = $data;
        $view->colors = self::$palette;

        $view->setScriptPath(APPLICATION_PATH . "/views/helpers/views/report-chart/{$this->_chartType}/");
        $xml = $view->render("data.tpl");
        //if (!$this->_multiGraph) exit($xml);
        return self::_plainify($xml);
    }

    static protected function _plainify($xml)
    {
        return preg_replace('~\s*(<([^>]*)>[^<]*</\2>|<[^>]*>)\s*~','$1', str_replace(array('"', "\r", "\n"), "'", $xml));
    }

    static public function trimTitles($data)
    {
        $return = array();
        if (is_array($data)) {
            foreach ($data as $row) {
                $return[] = array_slice($row, 1, null, true);
            }
        }
        return $return;
    }
}