<?php
require_once APPLICATION_PATH . '/views/infoblocks/LeasingBlock.php'; // chart model wanted?

class Infoblock_LeasingController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Chart;

	protected $session;

	public function init()
    {
        parent::init();
        $this->initChart();
    }

	public function getDataAction()
	{
        $sessionsData = $hddData = array();

		$config = Zend_Registry::get('config');
		$this->session = new Zend_Session_Namespace('infoblock_leasing');
        if (($key = $this->_getParam('key')) && ($value = $this->_getParam('value'))) {
			$this->session->$key = $value;
			$this->view->$key = $value;
		}

		$begin = new HM_Date($this->session->period->begin);
		$realEnd = HM_Date::now(); // будущую статистику не показываем.)

        $service = Zend_Registry::get('serviceContainer')->getService('Load');
        $select = $service->getSelect();
        $select->from(array('l' => 'load'))
		    ->where(new Zend_Db_Expr($service->quoteInto(array("load_date BETWEEN ? ", "AND ?"), array($begin->toString(HM_Date::SQL), $realEnd->toString(HM_Date::SQL)))));

        $refreshBegin = clone $begin;
        if ($rowset = $select->query()->fetchAll()) {

            $lastRecordDate = new HM_Date($rowset[count($rowset)-1]['load_date']);
            $lastRecordDate->add(1, HM_Date::DAY); // нужно добавить 1 день, т.к. данные за сегодня мы пока еще не хотим рефрешить (ещё не вечер)
            $refreshBegin = $lastRecordDate;

            foreach ($rowset as $row) {
                if ($row['sessions']) $sessionsData[$row['load_date']] = $row['sessions'];
                if ($row['hdd']) $hddData[$row['load_date']] = $row['hdd'];
            }
        }

        if (!isset($lastRecordDate) || ($lastRecordDate->get() < strtotime(date('Y-m-d')))) {
            list($newSessionsData, $newHddData) = $service->refresh((object)array(
                'begin' => $refreshBegin->toString(HM_Date::SQL),
                'end' => $realEnd->toString(HM_Date::SQL),
            ));
            $sessionsData = array_merge($sessionsData, $newSessionsData);
            $hddData = array_merge($hddData, $newHddData);
        }

//        $duration = ceil(($realEnd->toString(HM_Date::TIMESTAMP) - $begin->toString(HM_Date::TIMESTAMP)) / 86400);

        $graphs = $series = $days = $limits = array();
        $iterator = clone $begin;
        while ($iterator->get() < $realEnd->get()) {
            $days[$iterator->toString('Y-MM-dd')] = $iterator->toString('dd.MM.YY');
            $iterator->add(1, HM_Date::DAY);
        }

        switch ($this->session->type) {

			case HM_View_Infoblock_LeasingBlock::TYPE_SESSIONS:

				$legendX = _('Максимальное количество подключений в этот день');
				$legendY = _('День');
                $limitValue = $config->leasing->limit->sessions;

                foreach ($days as $date => $value) {
                    if (isset($sessionsData[$date])) {
                        $graphs[$date] = $sessionsData[$date];
                        $series[$date] = $value;
                        $limits[$date] = $limitValue;
                    }
                }
                break;

			case HM_View_Infoblock_LeasingBlock::TYPE_HDD:

                $legendX = _('Объём дискового пространства, Гб');
				$legendY = _('День');
                $limitValue = round($config->leasing->limit->hdd / 1048576, 2); // Гб

                foreach ($days as $date => $value) {
                    if (isset($hddData[$date])) {
                        $graphs[$date] = round($hddData[$date] / 1048576, 2); // Гб
                        $series[$date] = $value;
                        $limits[$date] = $limitValue;
                    }
                }
				break;
			default:
				break;
		}

		$this->view->legendX = $legendX;
		$this->view->legendY = $legendY;
		$this->view->series = $series;
		$this->view->graphs = $graphs;
	    $this->view->limitValue = $limitValue;
	    $this->view->type = $this->session->type;

        $format = $this->getRequest()->getParam('format', '');

        if ($format == 'json') {
            $options = array(
                'legendEnabled' => 0,
                'axisX' => $legendX,
                'axisY' => $legendY,
                'graphsType' => 'line',
                'balloonText' => $legendX . ': [[value]]',
            );

            $allGraphs = array(
                'limits' => $limits,
                'profile' => $graphs,
            );

            $this->jsonResponse($series, $allGraphs, $options);
        }
	}
}