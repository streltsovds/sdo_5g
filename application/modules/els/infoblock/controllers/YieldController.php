<?php
require_once APPLICATION_PATH . '/views/infoblocks/YieldBlock.php'; // chart model wanted?

class Infoblock_YieldController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Chart;

	protected $session;

	public function getDataAction()
	{
        $data = array();
		$this->session = new Zend_Session_Namespace('infoblock_yield');
        if (($key = $this->_getParam('key')) && ($value = $this->_getParam('value'))) {
			$this->session->$key = $value;
			$this->view->$key = $value;
		}

        $service = Zend_Registry::get('serviceContainer')->getService('Session');
        $select = $service->getSelect();

		switch ($this->session->type) {

			case HM_View_Infoblock_YieldBlock::TYPE_TRAININGS:

				$legendX = _('Месяц');
				$legendY = _('Количество проведенных учебных сессий с начала года');
		        $select->from(array('s' => 'subjects'), array(
    	              'month' => new Zend_Db_Expr('MONTH(s.begin)'),
    	              'value' => new Zend_Db_Expr('COUNT(DISTINCT s.subid)'),
    		        ))
		        	->where('YEAR(s.begin) = ?', HM_Date::now()->get('Y'))
                    ->group('MONTH(s.begin)')
		        	->order(new Zend_Db_Expr('MONTH(s.begin)'));
				break;

			case HM_View_Infoblock_YieldBlock::TYPE_TRAINEES:

				$legendX = _('Месяц');
				$legendY = _('Количество прошедших обучение с начала года');
		        $select->from(array('g' => 'graduated'), array(
    	              'month' => new Zend_Db_Expr('MONTH(g.begin)'),
    	              'value' => new Zend_Db_Expr('COUNT(g.SID)'),
    		        ))
    		        ->join(array('p' => 'People'), 'g.MID = p.MID', array())
    		        ->join(array('s' => 'subjects'), 's.subid = g.CID', array())
		        	->where('YEAR(g.begin) = ?', HM_Date::now()->get('Y'))
		        	->group('MONTH(g.begin)')
		        	->order(new Zend_Db_Expr('MONTH(g.begin)'));

				break;

            case HM_View_Infoblock_YieldBlock::TYPE_COVERAGE:

                // TODO Fix it

                $areas = HM_Area_AreaModel::getAreas();

                $classifier = $this->getService('Classifier')->getOne(
                    $this->getService('Classifier')->fetchAll(
                        array(
                            'type' => HM_Classifier_ClassifierService::TYPE_AREA,
                            'name' => $areas[$area] ? $areas[$area] : ''
                        )
                    )
                );

                if($classifier){
                    $area = $classifier->classifier_id;
                }

               $options = ($area != HM_View_Infoblock_YieldBlock::AREA_ALL)? array('areas'=>$area, 'enableAreaRecursiveSearch' => true) : array();

                $users    = $this->getService('ProgrammRule')->getRulesUsersId($options);
                //$subjects = $this->getService('ProgrammRule')->getRulesSubjectsId();
                //$plan     = $this->getService('ProgrammRule')->getYearPlan($options);

                $year = $this->session->period;
                $plan = $this->getService('Plan')->getOne($this->getService('Plan')->fetchAll($this->quoteInto('period=?',$this->getService('Plan')->getStartPeriodDate($year))));

                $legendX = _('Месяц');
                $legendY = _('Целевое покрытие, %');
                $select->from(array('g' => 'graduated'), array(
                    'month' => new Zend_Db_Expr('MONTH(g.begin)'),
                    'value' => ($plan && $plan->plan_value > 0)? new Zend_Db_Expr('ROUND((COUNT(g.SID)/' . $plan->plan_value . ')*100)') : new Zend_Db_Expr('0'),
                    //'value' => new Zend_Db_Expr('COUNT(g.SID)'),
                ))
                    ->join(array('p' => 'People'), 'g.MID = p.MID', array())
                    ->join(array('s' => 'subjects'), 's.subid = g.CID', array())
                    ->where('g.begin >= ?', $this->getService('Plan')->getStartPeriodDate($year,true))
                    ->where('g.end <= ?', $this->getService('Plan')->getEndPeriodDate($year,true))
                    ->where('g.MID IN (?)', count($users)? $users : 0 )
                    //->where('g.CID IN (?)', count($subjects)? $subjects : 0)
                    ->group('MONTH(g.begin)')
                    ->order(new Zend_Db_Expr('MONTH(g.begin)'));

               /* Этот кусок не нужен так как выборка $users и так производится с учетом Геоданных с учетом иерархии
               if ($area != HM_View_Infoblock_YieldBlock::AREA_ALL) {
                    $select
                        ->join(array('str' => 'structure_of_organ'),'str.mid=p.MID',array())
                        ->join(array('cl' => 'classifiers_links'), 'cl.item_id = str.soid', array())
                        ->where('cl.classifier_id = ?', $area);
                }*/

                break;

			default:
				break;
		}
//		exit($select->__toString());

        $series = array_slice(HM_Date::getMonthes(), 0, HM_Date::now()->get('M'));
        array_unshift($series, ''); //
        $series[count($series) - 1] = _('Сегодня');

		$graphs = array_fill(0, count($series), 0);

        if ($rowset = $select->query()->fetchAll()) {
        	foreach ($rowset as $row) {
        		if (isset($graphs[$row['month']])) {
        			$graphs[$row['month']] = $row['value'];
        		}
        	}
        }

        self::accumulate($graphs);

		$this->view->legendX = $legendX;
		$this->view->legendY = $legendY;
		$this->view->series = $series;
		$this->view->graphs = $graphs;
	    $this->view->type = $this->session->type;

        $format = $this->getRequest()->getParam('format', '');

        if ($format == 'json') {
            $options = array(
                'legendEnabled' => 0,
                'axisX' => $legendX,
                'axisY' => $legendY,
                'graphsType' => 'line',
                'balloonText' => $legendY . ': [[value]]',
            );

            $allGraphs = array(
                'profile' => $graphs,
            );

            $this->jsonResponse($series, $allGraphs, $options);
        }

	}

	static public function accumulate(&$array)
	{
        $result = array();
        for ($i = count($array) - 1; $i >= 0; $i--) {
            $sum = 0;
            for ($j = 0; $j <= $i; $j++) {
                $sum += $array[$j];
            }
            $result[] = $sum;
        }
        $array = array_reverse($result);
        return true;
	}
}