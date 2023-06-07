<?php


class HM_View_Infoblock_YieldBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'yield';
    protected $session;

    const TYPE_TRAININGS = 'trainings';
    const TYPE_TRAINEES  = 'trainees';
    const TYPE_COVERAGE  = 'coverage';

    const AREA_ALL = -1;

    public function yieldBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_yield');
		$this->_setDefaults();

        $all = new stdClass();
        $all->classifier_id = self::AREA_ALL;
        $all->name = _('Все регионы');
		//$areas = HM_Area_AreaModel::getAreas(true);


        $areas = array(HM_View_Infoblock_YieldBlock::AREA_ALL => $all->name); // + $areas;

		$this->view->area = $this->session->area;
		$this->view->areas = $areas;
		$this->view->type	= $this->session->type;
        $this->view->period = $this->session->period;
        $periods = $this->getService('Period')->fetchAll();

        foreach ($periods as $period) {
            if (date('Y', $period->starttime) > date('Y')) {
                unset($periods[$period]);
            }
        }

        $this->view->periods = $periods;

    	$content = $this->view->render('yieldBlock.tpl');
        
        return $this->render($content);
    }

    private function _setDefaults()
    {
		if (!isset($this->session->area)) {
			$this->session->area = self::AREA_ALL;
		}
		if (!isset($this->session->type)) {
			$this->session->type = self::TYPE_TRAININGS;
		}
        if (!isset($this->session->period)) {
            $this->session->period = date('Y');
        }
    }

    static function sortByName($area1, $area2)
    {
        return ($area1->name < $area2->name) ? -1 : 1;
    }
}