<?php
trait HM_Controller_Action_Trait_Chart
{
	protected $chartType;

	public function initChart()
	{
        $this->chartType = $this->getRequest()->getControllerName();

        $title = $this->chartType . '_' . date('Y-m-d_H-i');
		$this->_helper->ContextSwitch()->addContext(
            'csv',
            array(
                'suffix' => 'csv',
                'headers' => array(
                	'Content-Type' => 'text/csv',
	                'Content-Disposition' => "attachment; filename=\"{$title}.csv\"",
            	),
            )
        );
        header('Pragma: cache'); // интеллигентныый способ не работает.(

		$this->_helper->ContextSwitch()->addActionContext('get-settings', 'xml')->initContext();
		$this->_helper->ContextSwitch()->addActionContext('get-data', array('xml', 'csv'))->initContext();
	}

	public function getSettingsAction()
	{
	}

	protected function jsonResponse($series, $graphs, $options = array())
    {
        $dataJson   =
        $graphsJson = array();
        if ($this->isAjaxRequest()) {
            if (isset($options['graphsType']) && $options['graphsType'] == 'pie') {
                foreach ($series as $key => $serie) {
                    $dataJson[] = array('category' => $key, 'column-1' => $serie);
                }
            } else {
                foreach ($series as $key => $serie) {
                    $arr = array('title' => $serie);
                    foreach ($graphs as $graphKey => $graph) {
                        $arr[$graphKey] = $graphs[$graphKey][$key];
                        if (!isset($graphsJson[$graphKey])) $graphsJson[$graphKey] = array('legend' => '', 'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
                    }
                    $dataJson[] = $arr;
                }
            }
        }

        // в getScriptPath() не передается ничего, что выбрасывает ошибку, странно что тут всё раньше
        // не валилось. Плюс все равно JSON формируется руками ниже. Хотя идея с 'settings.json' неплоха
        //$json = $this->view->getScriptPath() . $this->getRequest()->getControllerName() . DIRECTORY_SEPARATOR . 'settings.json';
        //$options['settings'] = json_decode(file_get_contents($json), TRUE);

        echo json_encode(array(
            'data' => $dataJson,
            'graphs' => $graphsJson,
            'options'=> array(
                'legendEnabled' => isset($options['legendEnabled']) ? $options['legendEnabled'] : false,
                'axisX'         => isset($options['legendX']) ? $options['legendX'] : '',
                'theme'         => isset($options['theme']) ? $options['theme'] : 'light',
                'axisY'         => isset($options['legendY']) ? $options['legendY'] : '',
                'graphsType'    => isset($options['graphsType']) ? $options['graphsType'] : 'column',
                'balloonText'   => isset($options['balloonText']) ? $options['balloonText'] : '',
                'angle'   => isset($options['angle']) ? $options['angle'] : '',
                'depth3D'   => isset($options['depth3D']) ? $options['depth3D'] : '',
                'innerRadius'   => isset($options['innerRadius']) ? $options['innerRadius'] : '',
                'settings'      => isset($options['settings']) ? $options['settings'] : array(),
                'labelRadius'   => isset($options['labelRadius']) ? $options['labelRadius'] : '',
                'radius'        => isset($options['radius']) ? $options['radius'] : '',
                'labelText'     => isset($options['labelText']) ? $options['labelText'] : '',
                'addClassNames'     => isset($options['addClassNames']) ? $options['addClassNames'] : 'false',
                'balloon'     => isset($options['balloon']) ? $options['balloon'] : array(),
                'legend'     => isset($options['legend']) ? $options['legend'] : array()
            )
        ));
        exit();
    }

    protected function getData($series, $graphs, $options = array()) {
        $dataJson   = array();
        if ($this->isAjaxRequest()) {
            if (isset($options['graphsType']) && $options['graphsType'] == 'pie') {
                foreach ($series as $key => $serie) {
                    $dataJson[] = array('category' => $key, 'column-1' => $serie);
                }
            } else {
                foreach ($series as $key => $serie) {
                    $arr = array('title' => $serie);
                    foreach ($graphs as $graphKey => $graph) {
                        $arr[$graphKey] = $graphs[$graphKey][$key];
                    }
                    $dataJson[] = $arr;
                }
            }
        }

        return $dataJson;
    }
}
