<?php
class HM_Tc_SessionQuarter_SessionQuarterService extends HM_Service_Abstract
{

    public function getListSource($options)
    {
        $default = array(
            'providerId'    => 0,
            'departmentId'  => 0,
            'type'          => HM_Tc_Session_SessionModel::TYPE_TC
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();
        $select->from(array('tcsq' => 'tc_sessions_quarter'), array(
            'session_quarter_id'  => 'tcsq.session_quarter_id',
            'workflow_id' => 'tcsq.session_quarter_id',
            'name'        => 'tcsq.name',
            'cycleid'     => 'c.cycle_id',
            'cycle'       => 'c.name',
            //'norm'        => new Zend_Db_Expr('1.0*SUM(sdfp.fact_price)*COUNT(DISTINCT tcsd.department_id)/COUNT(tcsd.department_id)'),
            'summ'        => new Zend_Db_Expr('SUM(tca.price * (CASE WHEN tca.status=0 THEN 0 ELSE 1 END))'),
            'status'      => 'tcsq.status',
        ));

        $select->joinLeft(
            array('tca' => 'tc_applications'),
            'tcsq.session_quarter_id = tca.session_quarter_id AND tca.session_id IS NULL',
            array()
        );
        $select->joinLeft(
            array('c' => 'cycles'),
            'c.cycle_id = tcsq.cycle_id',
            array()
        );

//        $select->joinLeft(
//            array('sop' => 'state_of_process'),
//            'sop.item_id=tcsd.session_department_id and sop.process_type=' . HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
//            array()
//        );

        $select->where('tcsq.type=?', $options['type']);

        $select->group(array(
                'tcsq.session_quarter_id',
                'tcsq.name',
                'c.cycle_id',
                'c.name',
                'tcsq.norm',
                'tcsq.status',
            )
        );

// сейчас показываем руководитлям все сессии;
// когда появятся сессии из других дочерних обществ, это условие надо будет реанимировать
//        if ($options['departmentId']) {
//
//            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
//                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
//                $select->joinLeft(
//                    array('tcsd2' => 'tc_session_departments'),
//                    'tcsd2.session_quarter_id = tcsq.session_quarter_id',
//                    array())
//                    ->joinLeft(
//                        array('sop2' => 'state_of_process'),
//                        'sop2.item_id=tcsd2.session_department_id and sop2.process_type=' . HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
//                        array())
//                    ->where(
//                        $this->quoteInto(
//                            'tcsd2.department_id IN (?)',
//                            $options['departmentId']
//                        )
//                    );
//            } else {
//                $select->joinInner(
//                    array('tcsd2' => 'tc_session_departments'),
//                    'tcsd2.session_quarter_id = tcsq.session_quarter_id',
//                    array())
//                    ->where(
//                        $this->quoteInto(
//                            'tcsd2.department_id IN (?)',
//                            $options['departmentId']
//                        )
//                    );
//            }
//        }

        return $select;
    }

    public function getStudentsListSource($options)
    {
        $default = array(
            'sessionQuarterId' => 0,
        );

        $options = array_merge($default, $options);


        $select = $this->getSelect();
        $select->from(array('tca' => 'tc_applications'), array(
            'application_id' => 'tca.application_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//            'position_full' => new Zend_Db_Expr("CONCAT(so.name, CONCAT( '/', CONCAT(so2.name, CONCAT('/', so3.name))))"),
            'MID' => 'p.MID',
            'department'      => new Zend_Db_Expr("CASE WHEN so.is_manager>0 THEN so3.name ELSE so2.name END"),
            'position_id'      => 'so.soid',
            'is_manager'      => 'so.is_manager',
            'position'      => 'so.name',
            'subject_name'      => 'sss.name',
            'subjectId'      => 'sss.subid',
            'comment'      => 'ss.comment',
            'price'      => 'tca.price',
            'period'      => 'tca.period',
            'study_status' =>  new Zend_Db_Expr("
                CASE WHEN g.SID>0 THEN ".HM_Tc_Application_ApplicationModel::STUDY_STATUS_COMPLETE." ELSE (
                    CASE WHEN (ss.SID>0 AND SS.CID != tca.subject_id)
                        THEN ".HM_Tc_Application_ApplicationModel::STUDY_STATUS_SESSION." 
                        ELSE ".HM_Tc_Application_ApplicationModel::STUDY_STATUS_NONE." 
                    END
                ) END"),
            'is_absent' => new Zend_Db_Expr("
                CASE WHEN a.user_id IS NULL
                    THEN 0
                    ELSE 1
                END"),
            'has_student' => new Zend_Db_Expr("
                CASE WHEN ss.MID IS NULL
                    THEN 0
                    ELSE 1
                END"),
            //'notified' => new Zend_Db_Expr("CASE WHEN ss.notified=1 THEN 1 ELSE 0 END"),
        ));


        $select->joinInner(
            array('p' => 'People'),
            'tca.user_id = p.MID',
            array()
        )
        ->joinLeft(
            array('so' => 'structure_of_organ'),
            'so.MID = p.MID AND so.soid = tca.position_id',
            array())
        ->joinLeft(
            array('so2' => 'structure_of_organ'),
            'so.owner_soid = so2.soid',
            array())
        ->joinLeft(
            array('so3' => 'structure_of_organ'),
            'so2.owner_soid = so3.soid',
            array())
        ->joinLeft(
            array('g' => 'graduated'),
            'g.application_id = tca.application_id',
            array())
        ->joinLeft(
            array('ss' => 'Students'),
            'ss.application_id = tca.application_id',
            array())
        ->joinLeft(
            array('sss' => 'subjects'),
            'ss.CID = sss.subid or g.CID = sss.subid',
            array())
        ->joinLeft(array('a' => 'absence'),
            'a.user_id = p.MID and
                (datediff(day, sss.end, a.absence_begin) <= 0) and
                (datediff(day, sss.begin, a.absence_end) >= 0) and
                sss.period = ' . HM_Subject_SubjectModel::PERIOD_DATES,
            array()
        );

        $select
            ->where('tca.session_quarter_id=?', $options['sessionQuarterId'])
            ->where('tca.status=?', HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)
        ;

        $sessionQuarter = $this->getOne($this->find($options['sessionQuarterId']));
        $state = $this->getService('Process')->getCurrentState($sessionQuarter);

        if($state instanceof HM_Tc_SessionQuarter_State_Open
            || $state instanceof HM_Tc_SessionQuarter_State_Publish
        ) {
            $select->where(new Zend_Db_Expr("1 = 0", null));
        }

        $select->group(array(
                'tca.application_id',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'p.MID',
                'so.soid',
                'so.is_manager',
                'so.name',
                'so2.name',
                'so3.name',
                'tca.period',
                'tca.status',
                'tca.subject_id',
                'g.SID',
                'ss.SID',
                'ss.CID',
                'sss.subid',
                'sss.name',
                'ss.notified',
                'sss.base',
                'tca.price',
                'ss.comment',
                'a.user_id',
                'ss.MID'
            )
        );

        return $select;
    }


    public function delete($sessionId)
    {
        $this->getService('TcApplication')->deleteBy('session_quarter_id='.$sessionId);
        $this->getService('TcSessionDepartment')->deleteBy('session_quarter_id='.$sessionId);

        parent::delete($sessionId);
    }

    public function insert($data)
    {
        $sessionQuarter = parent::insert($data);

        if ($sessionQuarter && isset($data['session_id'])) {

            $tcDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                array(
                    'session_id = ?' => $data['session_id']
                )
            );

            $cycle = $this->getService('Cycle')->fetchOne(array('cycle_id = ?' => $data['cycle_id']));

            $year = $cycle->year;
            $periods = array();
            switch ($cycle->quarter) {
                case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_1:
                    $periods[] = $year.'-01-01';
                    $periods[] = $year.'-02-01';
                    $periods[] = $year.'-03-01';
                    break;
                case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_2:
                    $periods[] = $year.'-04-01';
                    $periods[] = $year.'-05-01';
                    $periods[] = $year.'-06-01';
                    break;
                case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_3:
                    $periods[] = $year.'-07-01';
                    $periods[] = $year.'-08-01';
                    $periods[] = $year.'-09-01';
                    break;
                case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_4:
                    $periods[] = $year.'-10-01';
                    $periods[] = $year.'-11-01';
                    $periods[] = $year.'-12-01';
                    break;
            }

            $hasApplicationsDepartmentId = array();

            // copy Departments
            foreach ($tcDepartments as $tcDepartment) {
                $departmentData = $tcDepartment->getData();
                $departmentData['session_quarter_id'] = $sessionQuarter->session_quarter_id;
                $sdid = $departmentData['session_department_id'];


                unset($departmentData['session_id']);
                unset($departmentData['session_department_id']);
                unset($departmentData['status']);
                $inserted = $this->getService('TcSessionDepartment')->insert($departmentData);

                $hasApplicationsDepartmentId[$sdid] = $inserted->session_department_id;
            }

            $tcApplications = $this->getService('TcApplication')->fetchAll(
                $this->getService('TcApplication')->quoteInto(
                    array(
                        'session_id = ?',
                        ' AND status = ?',
                        ' AND period IN (?)',
                    ),
                    array(
                        $data['session_id'],
                        HM_Tc_Application_ApplicationModel::STATUS_COMPLETE,
                        $periods
                    )
                )
            );

            // copy Applications
            foreach ($tcApplications as $tcApplication) {
                $applicationData = $tcApplication->getData();
                $applicationData['session_quarter_id'] = $sessionQuarter->session_quarter_id;
                $applicationData['session_department_id'] = $hasApplicationsDepartmentId[$applicationData['session_department_id']];
                $applicationData['status'] = HM_Tc_Application_ApplicationModel::STATUS_ACTIVE;

                unset($applicationData['session_id']);
                unset($applicationData['application_id']);
                $this->getService('TcApplication')->insert($applicationData);
            }

            $tcApplicationsImpersonal = $this->getService('TcApplicationImpersonal')->fetchAll(
                $this->getService('TcApplicationImpersonal')->quoteInto(
                    array(
                        'session_id = ?',
                        ' AND status = ?',
                        ' AND period IN (?)',
                    ),
                    array(
                        $data['session_id'],
                        HM_Tc_Application_ApplicationModel::STATUS_COMPLETE,
                        $periods
                    )
                )
            );

            // copy Impersonal Applications
            foreach ($tcApplicationsImpersonal as $tcApplication) {
                $applicationData = $tcApplication->getData();

                // #27730
                if ($applicationData['cost_item'] == HM_Tc_Application_ApplicationModel::PROFCOM_COST_ITEM) {
                    $this->createProject($tcApplication);
                    continue;
                }
                // end of #27730

                // #27817
                if ($applicationData['cost_item'] == HM_Tc_Application_ApplicationModel::CULTURE_COST_ITEM) {
                    $this->createProject($tcApplication);
                    continue;
                }
                // end of #27817

                $applicationData['session_quarter_id'] = $sessionQuarter->session_quarter_id;
                $applicationData['session_department_id'] = $hasApplicationsDepartmentId[$applicationData['session_department_id']];
                $applicationData['status'] = HM_Tc_Application_ApplicationModel::STATUS_ACTIVE;

                unset($applicationData['session_id']);
                unset($applicationData['application_impersonal_id']);
                unset($applicationData['quantity']);
                $applicationData['user_id'] = 0;

                for ($i = 1; $i <= $tcApplication->quantity; $i++) {
                    $this->getService('TcApplication')->insert($applicationData);
                }
            }
        }

        /********* init Process ********/

        // это очень важное место
        // здесь задаются все параметры, которые можно будет использовать внутри Action
        // почему-то надо задавать отдельно для каждого шага..


        $this->getService('Process')->startProcess($sessionQuarter, array(
            'HM_Tc_SessionQuarter_State_Open' => array(
                'session_quarter_id' => $sessionQuarter->session_quarter_id,
                'session_id' => $data['session_id']
            ),
            'HM_Tc_SessionQuarter_State_Publish' => array(
                'session_quarter_id' => $sessionQuarter->session_quarter_id,
                'session_id' => $data['session_id']
            ),
            'HM_Tc_SessionQuarter_State_Analysis' => array(
                'session_quarter_id' => $sessionQuarter->session_quarter_id,
                'session_id' => $data['session_id']
            ),
            'HM_Tc_SessionQuarter_State_Agreement' => array(
                'session_quarter_id' => $sessionQuarter->session_quarter_id,
                'session_id' => $data['session_id']
            ),
        ));


        return $sessionQuarter;
    }

    protected function createProject($tcApplication) {
        $description =
            '<p>' . _('Количество участников')  .': ' . $tcApplication->quantity . '<br><br>' .
            _('Стоимость (на 1 человека, руб)') .': ' . $tcApplication->price    . '</p>';

        $this->getService('Project')->insert(
            array(
                'name'        => $tcApplication->event_name,
                'description' => $description,
                'period'      => HM_Project_ProjectModel::PERIOD_FREE,
                'base_color'  => $this->getService('Project')->generateColor(),
            )
        );
    }

    public function changeState($sessionId, $state)
    {
        $session = $this->getOne($this->find($sessionId));
        /** @var HM_Process_ProcessService $processService */
        $processService = $this->getService('Process');
        switch ($state) {
            case HM_State_Abstract::STATE_STATUS_CONTINUING:
                $result = $processService->goToNextState($session);
                break;
            case HM_State_Abstract::STATE_STATUS_FAILED:
                $result = $processService->goToFail($session);
                break;
            case HM_State_Abstract::STATE_STATUS_ROLLBACK:
                $result = $processService->goToPrevState($session);
                if($result && $session->status == HM_Tc_Session_SessionModel::FINISHED) {
                    $session->status = HM_Tc_Session_SessionModel::GOING;
                    $this->update($session->getValues());
                }
        }
        return $result;
    }

    // очень нехороший метод
    // я ничего не понимаю что там написано
    public function applicationsStatus($sessionId, $departmentId = false)
    {
        $isSuper = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR);
        $session = $this->getOne($this->find($sessionId));
        if ($session) {
            /** @var HM_Process_ProcessService $processService */
            $processService = $this->getService('Process');
            $process = $processService->initProcess($session);
            $process = $session->getProcess();
            if (in_array($process->getStatus(), array(
                    HM_Process_Abstract::PROCESS_STATUS_COMPLETE,
                    HM_Process_Abstract::PROCESS_STATUS_FAILED)
            )
            ) {
                return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_CLOSED;
            }
        }

        if ($isSuper) {
            $superDepartmentId   = $this->getService('Orgstructure')->getResponsibleDepartments();
            if ($departmentId && ($superDepartmentId != $departmentId)) {
                return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_PENDING;
            }
            $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                $this->quoteInto(array('session_quarter_id =? ',' AND department_id = ?'),
                    array($sessionId, $superDepartmentId)));
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $departmentIds      = $this->getService('Orgstructure')->getResponsibleDepartments();
            if (!$departmentIds) {
                if ($departmentId) {
                    $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                        $this->quoteInto(array('session_quarter_id =? ',' AND department_id = ?'),
                            array($sessionId, $departmentId)));
                } else {
                    $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                        $this->quoteInto(array('session_quarter_id =? '), array($sessionId)));
                }
            } else {
                if ($departmentId) {
                    if (!in_array($departmentId, $departmentIds)) {
                        return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_PENDING;
                    } else {
                        $departmentIds = array($departmentId);
                    }
                }
                $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                    $this->quoteInto(array('session_quarter_id =? ',' AND department_id in (?)'),
                        array($sessionId, $departmentIds)));
            }
        } else {
            if ($departmentId) {
                $sessionDepartment = $this->getService('TcSessionDepartment')->getOne(
                    $this->getService('TcSessionDepartment')->fetchAll(
                        $this->quoteInto(array('session_quarter_id =? ',' AND department_id=?'),
                            array($sessionId, $departmentId))));
                $state = $this->getService('Process')->getCurrentState($sessionDepartment);
                if (!$state) {
                    return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_PENDING;
                }
                if ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) {
                    if ($departmentId) {
                        return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_CANCELED;
                    }
                }
            }

            return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_ACTUAL;
        }

        //вырожденный случай: нет подразделения в этой сессии
        if (!$sessionDepartments) {
            return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_PENDING;
        }

        foreach ($sessionDepartments as $sessionDepartment) {
            $state = $this->getService('Process')->getCurrentState($sessionDepartment);

            if (!$state) {
                return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_PENDING;
            }

            if ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) {
                if ($departmentId) {
                    return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_CANCELED;
                }
            } elseif ($state instanceof HM_Tc_Session_Department_State_Open ||
                (!$isSuper && (
                        ($state instanceof HM_Tc_Session_Department_State_Agreement) ||
                        ($state instanceof HM_Tc_Session_Department_State_AssignmentCost) ||
                        ($state instanceof HM_Tc_Session_Department_State_AgreementStandart)))) {
                return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_ACTUAL;
            }
        }

        return HM_Tc_SessionQuarter_SessionQuarterModel::STATE_CLOSED;

    }

    // почти целиком скопировано из Tc_Session
    public function isApplicable($session)
    {
        // если сессия закончилась - в ней ничего менять нельзя
        $this->getService('Process')->initProcess($session);
        if (in_array($session->getProcess()->getStatus(), array(
            HM_Process_Abstract::PROCESS_STATUS_COMPLETE,
            HM_Process_Abstract::PROCESS_STATUS_FAILED,
        ))) {
            return false;
        }

        // менеджеру можно всегда
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            return true;
        }

        // супервайзеру - только на определенном этапе БП консолидированной заявки
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))){

            $sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant(
                $session,
                $this->getService('Orgstructure')->getResponsibleDepartments()
            );

            if ($sessionDepartment) {
                $state = $this->getService('Process')->getCurrentState($sessionDepartment);
                return is_a($state, 'HM_Tc_SessionQuarter_Department_State_Open');
            }
        }

        return false;
    }



    public function getQuarterList() {
        return array(
            HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_1 => _('Первый квартал'),
            HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_2 => _('Второй квартал'),
            HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_3 => _('Третий квартал'),
            HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_4 => _('Четвертый квартал'),
        );
    }
}