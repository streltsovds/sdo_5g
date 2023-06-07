<?php
class HM_Load_LoadService extends HM_Service_Abstract
{
    const PRE_LIMIT_PERCENT = 90;

    public function refresh($period)
    {
        $data = $sessionsData = $hddData = array();

        // sessions

        $subSelect = $this->getSelect();
        $subSelect->from(array('h24' => new Zend_Db_Expr('hours24')), array(
                'start' => new Zend_Db_Expr('s.start'),
                'value' => new Zend_Db_Expr("COUNT(h24.h)"),
            )
        )
        ->join(array('s' => 'sessions'), 'h24.h >= HOUR(s.start) AND h24.h <= HOUR(s.stop)', array())
        ->where(new Zend_Db_Expr($this->quoteInto(array("(s.course_id=0 OR s.course_id IS NULL) AND s.start BETWEEN ? ", "AND ?"), array($period->begin, $period->end))))
        ->group(array(new Zend_Db_Expr('s.start'), new Zend_Db_Expr('h24.h')))
        ;//->order(new Zend_Db_Expr('h24.h'));

        $select = $this->getSelect();
        $select->from(array('hours' => $subSelect), array(
            'start' => 'start',
            'maxValue' => new Zend_Db_Expr("MAX(hours.value)"),
        ))
        ->group('start')
        ->order('start');

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $data[] = array(
                    'load_date' => $row['start'],
                    'sessions' => $row['maxValue']
                );
                $sessionsData[$row['start']] = $row['maxValue'];
            }
        }

        // hdd

        if ($output = system('du --max-depth=1 ../')) {
            $output = explode(' ', $output);
            if ($output[0]) {
                $today = date('Y-m-d');
                if (!isset($data[$today])) $data[$today] = array('load_date' => $today);
                $hddData[$today] =  $data[$today]['hdd'] = $output[0];
            }
        }

        foreach ($data as $item) {
            $this->insert($item);
        }

        $this->_checkExceed($sessionsData, $hddData);

        return array($sessionsData, $hddData);
    }

    protected function _checkExceed($sessionsData, $hddData)
    {
        /*$sessionsData['2016-06-10 15:30:33'] = 45.6;
        $hddData['2018-12-25'] = 5342880;*/

        $config = Zend_Registry::get('config');

        if (!isset($config->leasing->notify) || ($config->leasing->notify != 1)) return true;

        $messenger = $this->getService('Messenger');
        $preLimitCoeff = self::PRE_LIMIT_PERCENT / 100;

        if (isset($config->leasing->limit->sessions)) {

            $sessionLimit = $config->leasing->limit->sessions;
            $sessionPreLimit = $sessionLimit * $preLimitCoeff;

            foreach ($sessionsData as $date => $value) {

                if ($value > $sessionLimit or $value > $sessionPreLimit) {

                    if($value >= $sessionLimit) {
                        $sessionWarningTemplate = HM_Messenger::TEMPLATE_LEASING_EXCEED_SESSIONS;
                    }
                    else {
                        $sessionWarningTemplate = HM_Messenger::TEMPLATE_LEASING_PRE_EXCEED_SESSIONS;
                    }


                    $date = new HM_Date($date);
                    $messenger->setOptions(
                        $sessionWarningTemplate,
                        array(
                            'sessions_plan' => $sessionLimit,
                            'sessions_fact' => $value,
                            'date' => $date->toString('dd.MM.yyyy'),
                            'time' => $date->toString('H:m:s'),
                            'url' => Zend_Registry::get('view')->getHelper('ServerUrl')->getHost()
                        )
                    );

                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::SYSTEM_USER_ID);
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::TECH_SUPPORT_USER_ID);
                    break;
                }
            }
        }

        if (isset($config->leasing->limit->hdd)) {
            $hddLimit = $config->leasing->limit->hdd;
            $hddPreLimit = $hddLimit * $preLimitCoeff;

            foreach ($hddData as $date => $value) {
                if ($value >  $hddLimit or $value > $hddPreLimit) {
                    if($value >= $hddLimit) {
                        $hddWarningTemplate = HM_Messenger::TEMPLATE_LEASING_EXCEED_HDD;
                    }
                    else {
                        $hddWarningTemplate = HM_Messenger::TEMPLATE_LEASING_PRE_EXCEED_HDD;
                    }

                    $date = new HM_Date($date);
                    $messenger->setOptions(
                        $hddWarningTemplate,
                        array(
                            'hdd_plan' => number_format($hddLimit / 1048576, 2),
                            'hdd_fact' => number_format($value / 1048576, 2),
                            'date' => $date->toString('dd.MM.yyyy'),
                            'time' => date('H:i:s'),
                            'url' => Zend_Registry::get('view')->getHelper('ServerUrl')->getHost(),

                        )
                    );

                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::SYSTEM_USER_ID);
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::TECH_SUPPORT_USER_ID);
                    break;
                }
            }
        }
    }
}