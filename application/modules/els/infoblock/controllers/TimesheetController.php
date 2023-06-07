<?php

class Infoblock_TimesheetController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Chart;


    public function init()
    {
        parent::init();
        header("Pragma: cache");

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
    }

    public function loadAction()
    {
        
        $currentUser = $this->getService('User')->getCurrentUserId();
        $currentDate = date('Y-m-d');
        $timesheets = $this->getService('Timesheet')->fetchAllDependence(
            array('ActionType'),
            array(
                ' user_id = ? ' => $currentUser,
                ' action_date = ? ' => $currentDate
            )
        );
        $data = array();

        foreach ($timesheets as $timesheet) {
            if (!$timesheet->action_type->current()) continue;
            $data[] = array(
                'typeId' => $timesheet->action_type->current()->classifier_id,
                'type' => $timesheet->action_type->current()->name,
                'description' => $timesheet->description,
                'time' => array(
                    'from' => date('H:i', strtotime($timesheet->begin_time)),
                    'to' => date('H:i', strtotime($timesheet->end_time)),
                ),
            );
        }

        $actionTypes = Zend_Registry::get('serviceContainer')->getService('Classifier')->fetchAll(
            array('type = ?' => HM_Classifier_Type_TypeModel::BUILTIN_TYPE_ACTION_TYPES)
        );


        $this->view->assign(array(
            'items' => $data,
            'action_types' => $actionTypes->asArray(),
            'chart_data' => $this->getChartData()
        ));
    }

    public function saveAction()
    {

        // @TODO: Сделать вставку не из одного элемента
        $params = $this->getJsonParams()[0];

        if (!$params['action_type'] || (empty($params['begin_time']) && empty($params['end_time']))) {
            $this->sendAsJsonViaAjax(array(
                'success' => false
            ));
        }
        $data = array();
        $data = $params;
        $data['action_date'] = date('Y-m-d');
        $data['user_id']     = $this->getService('User')->getCurrentUserId();

        $timesheet = $this->getService('Timesheet')->insert($data);
        $success = $timesheet ? true : false;

        $this->sendAsJsonViaAjax(array(
            'success' => $success
        ));
    }

    public function deleteAction()
    {
        $params = $this->getJsonParams();

        if (!$params['action_type']) {
            $this->sendAsJsonViaAjax(array(
                'success' => false
            ));
        }
        $type = $this->getService('Classifier')->fetchAll(array(
            'name = ?' => $params['action_type']
        ));
        if (count($type)) $type = $type->current();
        $data = array();
        $data['user_id = ?']     = $this->getService('User')->getCurrentUserId();
        $data['action_type = ?'] = $type->getValue('classifier_id');
        $data['description = ?'] = $params['description'];
        $data['begin_time = ?']  = $params['begin_time'];
        $data['end_time = ?']    = $params['end_time'];

        $timesheet = $this->getService('Timesheet')->fetchAll($data);
        if (count($timesheet)) {
            $id = $timesheet->current();
            $this->getService('Timesheet')->delete($id->timesheet_id);

            $this->sendAsJsonViaAjax(array(
                'success' => true
            ));
        }

        $this->sendAsJsonViaAjax(array(
            'success' => false
        ));
    }

    private function getChartData()
    {
        $currentUser = $this->getService('User')->getCurrentUserId();
        $service = $this->getService('Timesheet');
        $select = $service->getSelect();
        $data = array();

        $select->from(array('ts' => 'timesheets'), array(
                'name'    => 'cl.name',
                'begin_time' => 'ts.begin_time',
                'end_time' => 'ts.end_time'
            )
        )
            ->joinLeft(array('cl' => 'classifiers'), "cl.classifier_id = ts.action_type", array())
            ->where(new Zend_Db_Expr($service->quoteInto(array("ts.user_id = ?"), array($currentUser))))
            ->where(new Zend_Db_Expr($service->quoteInto(array("ts.action_date = ?"), array(date('Y-m-d')))))
            ->group(array('cl.name', 'ts.begin_time', 'ts.end_time'))
            ->order(new Zend_Db_Expr("ts.begin_time ASC")
            );

        if ($rowset = $select->query()->fetchAll()) {
            $names = array();
            $value = 0;
            foreach ($rowset as $row) {
                if (in_array($row['name'], $names)) {
                    $value += (int) $this->timeDiff($row['end_time'], $row['begin_time']);
                } else {
                    $names[] = $row['name'];
                    $value = (int) $this->timeDiff($row['end_time'], $row['begin_time']);
                }
                $data[$row['name']] = $value;
            }
        }
        $ret = array();
        foreach ($data as $key => $value) {
            $ret[] = array(
                'type' => $key,
                'value' => $value
            );
        }
        return $ret;
    }

    public function getDataAction()
    {
        $currentUser = $this->getService('User')->getCurrentUserId();
        $service = $this->getService('Timesheet');
        $select = $service->getSelect();
        $data = array();

        $select->from(array('ts' => 'timesheets'), array(
                'name'    => 'cl.name',
                'begin_time' => 'ts.begin_time',
                'end_time' => 'ts.end_time'
            )
        )
            ->joinLeft(array('cl' => 'classifiers'), "cl.classifier_id = ts.action_type", array())
            ->where(new Zend_Db_Expr($service->quoteInto(array("ts.user_id = ?"), array($currentUser))))
            ->where(new Zend_Db_Expr($service->quoteInto(array("ts.action_date = ?"), array(date('Y-m-d')))))
            ->group(array('cl.name', 'ts.begin_time', 'ts.end_time'))
            ->order(new Zend_Db_Expr("ts.begin_time ASC")
        );

        if ($rowset = $select->query()->fetchAll()) {
            $names = array();
            $value = 0;
            foreach ($rowset as $row) {
                if (in_array($row['name'], $names)) {
                    $value += $this->timeDiff($row['end_time'], $row['begin_time']);
                } else {
                    $names[] = $row['name'];
                    $value = (int) $this->timeDiff($row['end_time'], $row['begin_time']);
                }
                $data[$row['name']] = $value;
            }
        }
        $this->view->data = $data;

        $series = $data;

        $format = $this->getRequest()->getParam('format', '');

        if ($format == 'json') {
            $options = array(
                'legendEnabled' => 0,
                'theme' => 'SGK',
                'balloonText' => '<dl><dt>[[title]]</dt><dd>[[percents]]%</dd></dl>',
                'graphsType' => 'pie',
                'axisX' => '',
                'axisY' => '',
                'radius' => '40%',
                'angle' => 0,
                'depth3D' => 0,
                'innerRadius' => '20%',
                'labelRadius' => '-65%',
                'labelText' => '[[percents]]%',
                'addClassNames' => 'true',
                'balloon' => array(
                    'color' => '#fff',
                    'cornerRadius' => 5,
                    'fixedPosition' => false,
                    'drop' => false,
                    'fontSize' => 12,
                    'shadowAlpha' => 0,
                    'shadowColor' => '#333',
                    'showBullet' => false
                ),
                'legend' => array(
                    'enabled' => true,
                    'equalWidths' => false,
                    'markerSize' => 20,
                    'rollOverColor' => '#FFFFFF',
                    'valueText' => '',
                    'valueWidth' => 1
                )
            );

            $allGraphs = array(
//            'limits' => $limits,
                'profile' => $data,
            );

            $this->jsonResponse($series, $allGraphs, $options);
        }
    }

    private function timeDiff($end, $begin)
    {
        list($endHours, $endMinutes) = explode(':', $end);
        list($beginHours, $beginMinutes) = explode(':', $begin);
        $hoursDiff   = $endHours - $beginHours;
        $minutesDiff = $endMinutes - $beginMinutes;
        return $minutesDiff + $hoursDiff * 60;
    }
}