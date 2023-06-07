<?php


class HM_View_Infoblock_ActivitydevBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'activitydev';
    protected $session;

    const TYPE_TIMES 	= 'times';
    const TYPE_DATES	= 'dates';

    public $periodSet = array(
    	HM_Date::PERIOD_WEEK_CURRENT,
    	HM_Date::PERIOD_WEEK_PREVIOUS,
    	HM_Date::PERIOD_MONTH_CURRENT,
    	HM_Date::PERIOD_MONTH_PREVIOUS,
    	HM_Date::PERIOD_4WEEKS_RELATIVE,
    	HM_Date::PERIOD_TODAY,
    );

    public function activitydevBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_activitydev');
		$this->_setDefaults();

        $this->view->periodSet = $this->periodSet;

		$this->view->period = $this->session->period;
		$this->view->type	= $this->session->type;

        $this->view->activityDistributions = [
            [
                'value' => 'times',
                'text' => _('в течение суток')
            ],
            [
                'value' => 'dates',
                'text' => _('в течение недели')
            ]
        ];

        $periods = [];
        $periodsSets = HM_Date::pluralFormsPeriods($this->periodSet);
        foreach($periodsSets as $key => $period) {
            $periods[] = [
                'value' => $key,
                'text' => $period
            ];
        }
        $this->view->periods = $periods;

        $this->view->export_url = $this->view->url(array(
            'module' => 'infoblock',
            'controller' => 'activitydev',
            'action' => 'get-data',
            'format' => 'csv',
        ));

    	$content = $this->view->render('activitydevBlock.tpl');
        
        return $this->render($content);
    }

    private function _setDefaults()
    {
		if (!isset($this->session->period)) {
			$this->session->period = HM_Date::PERIOD_WEEK_CURRENT;
		}
		if (!isset($this->session->type)) {
			$this->session->type = 'times';
		}
    }
}