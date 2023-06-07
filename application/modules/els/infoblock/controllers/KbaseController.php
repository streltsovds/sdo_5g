<?php
require_once APPLICATION_PATH . '/views/infoblocks/KbaseBlock.php'; // chart model wanted?

class Infoblock_KbaseController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Chart;

    const DEPTH_MONTHS = 12;

	protected $session;

	public function getDataAction()
	{
        $data = array();
		$this->session = new Zend_Session_Namespace('infoblock_kbase');
        if (($key = $this->_getParam('key')) && ($value = $this->_getParam('value'))) {
			$this->session->$key = $value;
			$this->view->$key = $value;
		}

        $service = Zend_Registry::get('serviceContainer')->getService('Session');
        $select = $service->getSelect();

        $legendX = _('Месяц');
        $legendY = _('Целевое покрытие, %');

        $date = new HM_Date();
        $date->sub(self::DEPTH_MONTHS, Zend_Date::MONTH);

        $resources = Zend_Registry::get('serviceContainer')->getService('Resource')->fetchAll(
            Zend_Registry::get('serviceContainer')->getService('Resource')->quoteInto(
                array(
                    '( status = ?  ' ,
                    //' OR status = ? ',
                    ' ) AND parent_id = ? AND ',
                    ' location = ?  AND ',
                    ' created > ? ',
                ),
                array(
                    HM_Resource_ResourceModel::STATUS_PUBLISHED,
                    //HM_Resource_ResourceModel::STATUS_STUDYONLY,
                    0,
                    HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL,
                    $date->get('Y-MM-dd'),
                )
            )
            , 'created' );


        foreach($resources as $resource) {
            $resourcesList[] = $resource;
        }

        $monthNames = HM_Date::getMonthes();

        $rCounter = count($resourcesList);
        $graphs =  array();
        $series = array();
        list($year, $month) = explode('-',date('Y-m', strtotime($resourcesList[0]->created)));

        if ($rCounter) {
            $counter = 0;
            $xid = 0;
            for ($iYear = $year; $iYear <= date('Y'); $iYear++) {
                $startMonth = 1;
                if ($counter == 0) {
                    $startMonth = $month;
                }
                for ($iMonth = $startMonth; $iMonth <= 12; $iMonth++) {
                    $iDate = $iYear * 100 + $iMonth;
                    for (; $counter < $rCounter; $counter++) {
                        $rDate = date('Ym', strtotime($resourcesList[$counter]->created));
                        if ($rDate > $iDate) {
                            //$counter++;
                            break;
                        }
                    }
                    $series[$xid] = $monthNames[$iMonth - 1] . ' ' . $iYear;
                    $graphs[$xid] = $counter;
                    $xid++;
                }
            }
        }

        //self::accumulate($graphs);

		$this->view->legendX = $legendX;
		$this->view->legendY = $legendY;
		$this->view->series = $series;
		$this->view->graphs = $graphs;

        $format = $this->getRequest()->getParam('format', '');

        if ($format == 'json') {
            $options = array(
                'legendEnabled' => 0,
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