<?php


class HM_View_Infoblock_ClaimsBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'claims';
    protected $session;

    public $periodSet = array(
    	HM_Date::PERIOD_WEEK_CURRENT,
    	HM_Date::PERIOD_MONTH_CURRENT,
    	HM_Date::PERIOD_YEAR_CURRENT,
    );

    public function claimsBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_claims');
		if (!isset($this->session->period)) {
			$this->session->period = HM_Date::PERIOD_MONTH_CURRENT; //default
		}

        $periods = [];
        $periodsSets = HM_Date::pluralFormsPeriods($this->periodSet);
        foreach($periodsSets as $key => $period) {
            $periods[] = [ 'value' => $key, 'text' => $period ];
        }
        $this->view->periods = $periods;

        $this->view->period = $this->session->period;
        $this->view->exportUrl = $this->view->url(array(
            'module' => 'infoblock',
            'controller' => 'claims',
            'action' => 'get-data',
            'format' => 'csv',
            'period' => $this->session->period
        ));

    	$content = $this->view->render('claimsBlock.tpl');
        
        return $this->render($content);
    }
}