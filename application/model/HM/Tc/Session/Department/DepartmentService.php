<?php
class HM_Tc_Session_Department_DepartmentService extends HM_Service_Abstract
{
    protected $_notWorkersCounter = array();

    public function getListSourceEmpty() // для генерации пустого грида, когда не хотим искать тк запрос слишком сложный
    {
        $select = $this->getSelect();
        $select->from(array('tcsd' => 'tc_session_departments'), array(
            'session_department_id' => new Zend_Db_Expr('1'),
            'cycle_id' => new Zend_Db_Expr('1'),
            'dzo_department' => new Zend_Db_Expr('1'),
            'level2_department' => new Zend_Db_Expr('1'),
            'department_id' => new Zend_Db_Expr('1'),
            'workflow_id' => new Zend_Db_Expr('1'),
            'tcsession' => new Zend_Db_Expr('1'),
            'cycle' => new Zend_Db_Expr('1'),
            'department' => new Zend_Db_Expr('1'),
            'parent_department' => new Zend_Db_Expr('1'),
            'fact_count' => new Zend_Db_Expr('1'),
            'plan_cost' => new Zend_Db_Expr('1'),
            'fact_cost' => new Zend_Db_Expr('1'),
            'summ' => new Zend_Db_Expr('1'),
            'state' => new Zend_Db_Expr('1'),
            'sop_status' => new Zend_Db_Expr('1')
        ))->where('session_department_id=-77777') ;
        return $select;
    }


    public function getListSource($options)
    {
        $default = array(
            'sessionId'   => 0,
            'departmentId'  => 0,
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();
        $select->from(array('tcsd' => 'tc_session_departments'), array(
            'session_department_id' => 'tcsd.session_department_id',
            'cycle_id' => 'tcs.cycle_id',
            'dzo_department' => 'dzo.name',
            'level2_department' => 'level2.name',
            'department_id' => 'tcsd.department_id',
            'workflow_id' => 'tcsd.session_department_id',
            'tcsession' => 'tcs.session_id',
            'cycle' => 'c.name',
            'department' => 'sd.name',
            'department_manager_user_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT pm.mid)'),
            'fact_count' => new Zend_Db_Expr('COUNT(distinct tca.application_id)'),
            'state' => 'sp.current_state',
            'summ' => new Zend_Db_Expr('SUM(tca.price)'),
            'sop_status' => 'sp.status'
        ));
        $select->joinLeft(
            array('sd' => 'structure_of_organ'),
            'tcsd.department_id = sd.soid',
            array()
        );
        $select->joinLeft(
            array('tca' => 'tc_applications'),
            $this->quoteInto('tcsd.session_department_id = tca.session_department_id AND tca.status in (?)', array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)),
            array()
        );
        $select->joinLeft(
            array('spm' => 'structure_of_organ'),
            'spm.owner_soid = sd.soid AND spm.is_manager = '.HM_Orgstructure_OrgstructureModel::MANAGER,
            array()
        );
        $select->joinLeft(
            array('pm' => 'People'),
            'spm.mid = pm.MID',
            array()
        );
        $select->joinInner(
            array('tcs' => 'tc_sessions'),
            'tcsd.session_id = tcs.session_id',
            array()
        );
        $select->joinLeft(
            array('c' => 'cycles'),
            'c.cycle_id = tcs.cycle_id',
            array()
        );
        $select->joinLeft(
            array('sp' => 'state_of_process'),
            'tcsd.session_department_id = sp.item_id AND sp.process_type='.HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
            array()
        );
        $select->joinLeft(
            array('s' => 'subjects'),
            's.subid = tca.subject_id',
            array()
        );

        //считаем рабов в подразделениях (не считаем РАБОЧИХ)
        $subSelect = $this->getSelect();
        $subSelect->from(
            array('so' => 'structure_of_organ'),
            array(
                'soid' => 'so.soid',
                'owner_soid' => 'so.owner_soid',
                'owner_soid2' => 'so2.owner_soid',
                'mid' => 'so.mid',
                'is_manager' => 'so.is_manager'))
            ->joinLeft(
                array('so2' => 'structure_of_organ'),
                'so2.soid = so.owner_soid',
                array())
            ->joinLeft(
                array('atp' => 'at_profiles'),
                'atp.profile_id=so.profile_id',
                array())
            ->where('atp.category_id IS NULL')
//            ->where('so.mid > 0')
            ->where('so.blocked = 0')
            ->where('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION);
            //->where('(so.owner_soid=' . $position->owner_soid . ' AND so.is_manager=0) OR (so2.owner_soid=' . $position->owner_soid . ' AND so.is_manager=1)');
        $select->joinLeft(
            array('nc' => $subSelect),
            '(nc.owner_soid = tcsd.department_id AND nc.is_manager = 0) OR (nc.owner_soid2 = tcsd.department_id AND nc.is_manager = 1)',
            array()
        );

        /*$select->joinLeft(array('sdfp' => 'session_department_fact_price'),
            'sdfp.session_department_id = tcsd.session_department_id',
            array()
        );*/


        $select->joinLeft(array('level2' => 'structure_of_organ'),
            'level2.blocked=0 and level2.type=0 and level2.level=1 and level2.lft<=sd.lft AND level2.rgt>=sd.rgt',
            array()
        );

        $select->joinLeft(array('dzo' => 'structure_of_organ'),
            'dzo.soid=level2.owner_soid and (dzo.owner_soid=0) AND (dzo.blocked=0) AND (dzo.type=0)',
            array()
        );


        $select->group(array(
            'tcsd.session_department_id',
            'tcsd.department_id',
            'sd.name',
            'dzo.name',
            'level2.name',
            //'sdfp.fact_price',
            'tcs.status',
            'tcs.cycle_id',
            'tcs.session_id',
            'c.cycle_id',
            'c.name',
            'sp.current_state',
            'sp.status'
        ));

// правильнее всё же показывать пустые, т.к. сгенерированных может и не быть, но инициативное может и быть
//            ->having('COUNT(distinct tca.application_id) > 0');

        if ($options['sessionId']) {
            $select->where($this->quoteInto(
                    'tcsd.session_id = ?', $options['sessionId'])
            );
        }
        if ($options['departmentId']) {
            $select->where($this->quoteInto(
                    'tcsd.department_id IN (?)', $options['departmentId'])
            );
        }
        return $select;
    }

    public function getListSourceQuarter($options)
    {
        $default = array(
            'sessionQuarterId'   => 0,
            'departmentId'  => 0,
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();
        $select->from(array('tcsd' => 'tc_session_departments'), array(
            'session_department_id' => 'tcsd.session_department_id',
            'cycle_id' => 'tcsq.cycle_id',
            'dzo_department' => 'dzo.name',
            'level2_department' => 'level2.name',
            'department_id' => 'tcsd.department_id',
            'workflow_id' => 'tcsd.session_department_id',
            'tcsessionquarter' => 'tcsq.session_quarter_id',
            'cycle' => 'c.name',
            'department' => 'sd.name',
            'department_manager_user_ids' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT pm.mid)'),
            'fact_count' => new Zend_Db_Expr('COUNT(distinct tca.application_id)'),
            'summ' => new Zend_Db_Expr('SUM(tca.price)'),
            'state' => 'sp.current_state',
            'sop_status' => 'sp.status'
        ));
        $select->joinLeft(
            array('sd'=>'structure_of_organ'),
            'tcsd.department_id = sd.soid',
            array()
        );
        $select->joinLeft(
            array('tca'=>'tc_applications'),
            $this->quoteInto('tcsd.session_department_id = tca.session_department_id AND tca.status in (?)', array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)),
            array()
        );
        $select->joinLeft(
            array('spm' => 'structure_of_organ'),
            'spm.owner_soid = sd.soid AND spm.is_manager = '.HM_Orgstructure_OrgstructureModel::MANAGER,
            array()
        );
        $select->joinLeft(
            array('pm' => 'People'),
            'spm.mid = pm.MID',
            array()
        );

        $select->joinInner(
            array('tcsq'=>'tc_sessions_quarter'),
            'tcsd.session_quarter_id = tcsq.session_quarter_id',
            array()
        );
        $select->joinLeft(
            array('c' => 'cycles'),
            'c.cycle_id = tcsq.cycle_id',
            array()
        );
        $select->joinLeft(
            array('sp'=>'state_of_process'),
            'tcsd.session_department_id = sp.item_id AND sp.process_type='.HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
            array()
        );

        //считаем рабов в подразделениях (не считаем РАБОЧИХ)
        $subSelect = $this->getSelect();
        $subSelect->from(
            array('so' => 'structure_of_organ'),
            array(
                'soid' => 'so.soid',
                'owner_soid' => 'so.owner_soid',
                'owner_soid2' => 'so2.owner_soid',
                'mid' => 'so.mid',
                'is_manager' => 'so.is_manager'))
            ->joinLeft(
                array('so2' => 'structure_of_organ'),
                'so2.soid = so.owner_soid',
                array())
            ->joinLeft(
                array('atp' => 'at_profiles'),
                'atp.profile_id=so.profile_id',
                array())
            ->where('atp.category_id IS NULL')
//            ->where('so.mid > 0')
            ->where('so.blocked = 0')
            ->where('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION);
        //->where('(so.owner_soid=' . $position->owner_soid . ' AND so.is_manager=0) OR (so2.owner_soid=' . $position->owner_soid . ' AND so.is_manager=1)');
        $select->joinLeft(
            array('nc' => $subSelect),
            '(nc.owner_soid = tcsd.department_id AND nc.is_manager = 0) OR (nc.owner_soid2 = tcsd.department_id AND nc.is_manager = 1)',
            array()
        );
        $select->joinLeft(array('sdfp' => 'session_department_fact_price'),
            'sdfp.session_department_id = tcsd.session_department_id',
            array()
        );


        $select->joinLeft(array('level2' => 'structure_of_organ'),
            'level2.blocked=0 and level2.type=0 and level2.level=1 and level2.lft<=sd.lft AND level2.rgt>=sd.rgt',
            array()
        );

        $select->joinLeft(array('dzo' => 'structure_of_organ'),
            'dzo.soid=level2.owner_soid and (dzo.owner_soid=0) AND (dzo.blocked=0) AND (dzo.type=0)',
            array()
        );


        $select->group(array(
            'tcsd.session_department_id',
            'tcsd.department_id',
            'sd.name',
            'dzo.name',
            'level2.name',
            'sdfp.fact_price',
            'tcsq.status',
            'tcsq.cycle_id',
            'tcsq.session_quarter_id',
            'c.cycle_id',
            'c.name',
            'sp.current_state',
            'sp.status'

        ));

        if ($options['sessionQuarterId']) {
            $select->where($this->quoteInto(
                'tcsd.session_quarter_id = ?', $options['sessionQuarterId'])
            );
        }
        if ($options['departmentId']) {
            $select->where($this->quoteInto(
                'tcsd.department_id IN (?)', $options['departmentId'])
            );
        }
        return $select;
    }

    public function getScListSource($options)
    {
        $default = array(
            'sessionId' => 0,
            'departmentId' => 0,
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();
        $select->from(array('tcsd' => 'tc_session_departments'), array(
            'session_department_id' => 'tcsd.session_department_id',
            'cycle_id' => 'tcs.cycle_id',
           // 'dzo_department' => 'dzo.name',
            'department_id' => 'tcsd.department_id',
            'tcsession' => 'tcs.session_id',
            'cycle' => 'c.name',
            'department' => 'sd.name',
            'parent_department' => 'spd.name',
            'primary_count' => new Zend_Db_Expr('ac.primary_count'),
            'training_count' => new Zend_Db_Expr('ac.training_count'),
        ));
        $select->joinLeft(
            array('tcda' => 'tc_department_applications'),
            'tcda.department_id = tcsd.department_id AND tcda.session_id = tcsd.session_id',
            array()
        );
        $select->joinLeft(
            array('sd' => 'structure_of_organ'),
            'tcsd.department_id = sd.soid',
            array()
        );
        $select->joinLeft(
            array('spd' => 'structure_of_organ'),
            'spd.soid = sd.owner_soid',
            array()
        );
        $select->joinInner(
            array('tcs' => 'tc_sessions'),
            'tcsd.session_id = tcs.session_id',
            array()
        );
        $select->joinLeft(
            array('c' => 'cycles'),
            'c.cycle_id = tcs.cycle_id',
            array()
        );
        $select->group(array(
            'tcsd.session_department_id',
            'tcsd.department_id',
            'sd.name',
            'spd.name',
            'tcs.cycle_id',
            'tcs.session_id',
            'c.name',
            'ac.primary_count',
            'ac.training_count'
            //'dzo.name',
            //'pls.name',
        ));
        //subselect Для количества заявок и слушателей
        $subSelect = $this->getSelect();
        $subSelect->from(array('da' => 'tc_department_applications'), array(
            'session_department_id' => 'da.session_department_id',
            'primary_count' => new Zend_Db_Expr('COUNT(distinct CASE WHEN (a.category = '.HM_Tc_Application_ApplicationModel::SC_CATEGORY_PRIMARY.') THEN a.application_id END)'),
            'training_count' => new Zend_Db_Expr('COUNT(distinct CASE WHEN (a.category = '.HM_Tc_Application_ApplicationModel::SC_CATEGORY_ADDITION.' OR a.category = '.HM_Tc_Application_ApplicationModel::SC_CATEGORY_RECOMENDED.') THEN a.application_id END)'),
        ));
        $subSelect->joinLeft(array('a'=> 'tc_applications'),
            $this->quoteInto(
                'a.department_application_id = da.department_application_id AND a.status IN (?) ',
                array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)
            ),
            array()
        );

        if ($options['sessionId']) {
            $subSelect->where($this->quoteInto(
                'da.session_id = ?', $options['sessionId'])
            );
        }

        $subSelect->group(array(
            'da.session_department_id'
        ));
        /*$subSelect->joinLeft(array('ta'=> 'tc_applications'),
            $this->quoteInto('ta.department_application_id = da.department_application_id AND ta.category IN (?)', array(
                HM_Tc_Application_ApplicationModel::SC_CATEGORY_ADDITION,
                HM_Tc_Application_ApplicationModel::SC_CATEGORY_RECOMENDED,
            )),
            array()
        );*/

        $select->joinLeft(array('ac' => $subSelect),
            'ac.session_department_id = tcda.session_department_id',
            array()
        );
        if ($options['sessionId']) {
            $select->where($this->quoteInto(
                'tcsd.session_id = ?', $options['sessionId'])
            );
        }
        if ($options['departmentId']) {
            $select->where($this->quoteInto(
                'tcsd.department_id IN (?)', $options['departmentId'])
            );
        }
        return $select;
    }

    public function startProcesses($sessionId)
    {
        $sessionDepartments = $this->fetchAll(
            $this->quoteInto('session_id = ?', $sessionId)
        );
        if ($sessionDepartments && count($sessionDepartments)) {
            foreach ($sessionDepartments as $sessionDepartment) {
                $this->getService('Process')->startProcess($sessionDepartment, array(
                    'HM_Tc_Session_Department_State_Open' => array(
                        'session_id' => $sessionDepartment->session_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                    'HM_Tc_Session_Department_State_AgreementStandart' => array(
                        'session_id' => $sessionDepartment->session_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                    'HM_Tc_Session_Department_State_AssignmentCost' => array(
                        'session_id' => $sessionDepartment->session_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                    'HM_Tc_Session_Department_State_Agreement' => array(
                        'session_id' => $sessionDepartment->session_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                ));
            }
        }
    }

    public function startQuarterProcesses($sessionQuarterId)
    {
        $sessionDepartments = $this->fetchAll(
            $this->quoteInto('session_quarter_id = ?', $sessionQuarterId)
        );
        if ($sessionDepartments && count($sessionDepartments)) {
            foreach ($sessionDepartments as $sessionDepartment) {
                $this->getService('Process')->startProcess($sessionDepartment, array(
                    'HM_Tc_SessionQuarter_Department_State_Open' => array(
                        'session_quarter_id' => $sessionDepartment->session_quarter_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                    'HM_Tc_SessionQuarter_Department_State_Agreement' => array(
                        'session_quarter_id' => $sessionDepartment->session_quarter_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                    'HM_Tc_SessionQuarter_Department_State_Complete' => array(
                        'session_quarter_id' => $sessionDepartment->session_quarter_id,
                        'department_id' => $sessionDepartment->department_id,
                        'session_department_id' => $sessionDepartment->session_department_id
                    ),
                ));
            }
        }
    }



    public function changeState($sessionDepartmentId, $state)
    {
        /** @var HM_Process_ProcessService $processService*/
        $processService = $this->getService('Process');
        $result = false;

        $sessionDepartment = $this->getOne($this->find($sessionDepartmentId));
        if ($sessionDepartment) {
            switch ($state) {
                case HM_State_Abstract::STATE_STATUS_CONTINUING:
                    $result = $processService->goToNextState($sessionDepartment);
                    //$currentState = $processService->getCurrentState($sessionDepartment);
                    break;
                case HM_State_Abstract::STATE_STATUS_FAILED:
                    $result = $processService->goToFail($sessionDepartment);
                    break;
                case HM_State_Abstract::STATE_STATUS_ROLLBACK:
                    $result = $processService->goToPrevState($sessionDepartment);
                    break;
                default:
                    // something wrong..
                    return false;
                    break;
            }
        }
        return $result;
    }

    public function getDepartmentName($departmentId)
    {
        $name = '';
        $department = $this->getOne($this->getService('Orgstructure')->find($departmentId));
        if ($department){
            $name = $department->name;
        }
        return $name;
    }

    public function getProcessStates($onlyNames = false)
    {
        $process = $this->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT);
        $states = array(
            'classes' => array(),
            'names' => array()
        );
        if ($process){
            foreach ($process->states['state'] as $key => $state) {
                $states['classes'][$key] = $state['class'];
                $states['names'][$key] = $state['name'];
            }
        }
        return ($onlyNames) ? $states['names'] : $states;
    }

    public function getAdditionApplicationsCounter($sessionDepartmentIds)
    {
        if (!count($sessionDepartmentIds)) {
            $apps = array();
        } else {
            $apps = $this->getService('TcApplication')->fetchAll($this->getService('TcApplication')->quoteInto(
                array('session_department_id in (?)', 'AND status in (?)', 'AND category in (?)'),
                array(
                    $sessionDepartmentIds,
                    array(HM_Tc_Application_ApplicationModel::STATUS_ACTIVE, HM_Tc_Application_ApplicationModel::STATUS_COMPLETE),
                    array(HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION, HM_Tc_Application_ApplicationModel::CATEGORY_RECOMENDED))));

        }


        $departments = array();
        foreach ($apps as $row) {
            $sdId = $row->session_department_id;
            if (!isset($departments[$sdId])) {
                $departments[$sdId] = 1;
            } else {
                $departments[$sdId]++;
            }
        }

        if (is_array($sessionDepartmentIds)) {
            return $departments;
        }
        return isset($departments[$sessionDepartmentIds]) ? $departments[$sessionDepartmentIds] : 0;
    }

    public function getNotWorkersCounter($sessionDepartmentId)
    {
        if (!isset($this->_notWorkersCounter[$sessionDepartmentId])) {
            $orgService = $this->getService('Orgstructure');
            $slaves = array();
            $select = $orgService->getSelect();
            $select->from(
                    array('so' => 'structure_of_organ'),
                    array('so.soid'))
                ->joinLeft(
                    array('so2' => 'structure_of_organ'),
                    'so2.soid = so.owner_soid',
                    array())
                ->joinLeft(array('tcsd' => 'tc_session_departments'),
                    '(so.owner_soid=tcsd.department_id AND so.is_manager=0) OR (so2.owner_soid=tcsd.department_id AND so.is_manager=1)',
                    array())
                //->where('so.mid>0')
                ->where('tcsd.session_department_id=?', $sessionDepartmentId)
                ->where('so.blocked=0')
                ->where('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION);

            $slavesResult = $select->query()->fetchAll();
            foreach ($slavesResult as $slave) {
                $slaves[] = $slave['soid'];
            }

            if(!$slaves) {
                return 0;
            }

            $notWorkers = $orgService->getSelect();
            $notWorkers->from(
                array('so' => 'structure_of_organ'),
                array(
                    'so.soid'
                ))
                ->joinLeft(
                    array('atp' => 'at_profiles'),
                    'atp.profile_id=so.profile_id',
                    array())
                ->where('so.soid in (?)', $slaves)
                ->where('atp.category_id IS NULL')
                ->group(array('so.soid'));
            $notWorkersRows = $notWorkers->query()->fetchAll();

            $this->_notWorkersCounter[$sessionDepartmentId] = count($notWorkersRows);
        }

        return $this->_notWorkersCounter[$sessionDepartmentId];
    }


    // возвращает ID консолидированной заявки по soid одного из нижних подразделений
    public function getSessionDepartmentByDescendant($session, $descendantDepartmentId)
    {
        $sessionDepartments = $session->departments->asArrayOfObjects();
        $sessionDepartmentIds = $session->departments->getList('department_id', 'session_department_id');
        $descendantDepartmentTree = $this->getService('Orgstructure')->getAllOwnersInTree($descendantDepartmentId);

        $intersect = array_intersect(array_keys($sessionDepartmentIds), $descendantDepartmentTree);
        if (count($intersect)) {
            $departmentId = array_shift($intersect);
            $sessionDepartmentId = $sessionDepartmentIds[$departmentId];
            return $sessionDepartments[$sessionDepartmentId];
        }

        return false;
    }

}