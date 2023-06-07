<?php
class HM_Tc_Session_SessionService extends HM_Service_Abstract
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

       $select1 = clone $select;
       $select1->from(
            array('tc_applications'),
            array('session_id', 'price','status','session_quarter_id', 'impersonal'=>new Zend_Db_Expr('0'))
        );
       $select2 = clone $select;
       $select2->from(
            array('tc_applications_impersonal'),
            array('session_id', 'price'=>new Zend_Db_Expr('(price*quantity)'),'status','session_quarter_id', 'impersonal'=>new Zend_Db_Expr('1'))
        );
        $subSelectUnion = clone $select;
        $subSelectUnion->union(array($select1, $select2), Zend_Db_Select::SQL_UNION_ALL);

        $select->from(array('tcs' => 'tc_sessions'), array(
            'session_id'  => 'tcs.session_id',
            'workflow_id' => 'tcs.session_id',
            'name'        => 'tcs.name',
            'cycleid'     => 'c.cycle_id',
            'cycle'       => 'c.name',
            'summ'        => new Zend_Db_Expr('SUM(tca.price * (CASE WHEN tca.status=0 THEN 0 ELSE 1 END))'),
            'status'      => 'tcs.status',
            'responsible_id' => 'tcs.responsible_id',
            'responsible' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(rp.LastName, ' ') , rp.FirstName), ' '), rp.Patronymic)"),
        ));
        $select->joinLeft(
            array('tca' => $subSelectUnion),
            'tcs.session_id = tca.session_id  AND tca.session_quarter_id IS NULL',
            array()
        );
        $select->joinLeft(
            array('c' => 'cycles'),
            'c.cycle_id = tcs.cycle_id',
            array()
        );
        $select->joinLeft(
            array('rp' => 'People'),
            'rp.MID = tcs.responsible_id',
            array()
        );

        $select->where('tcs.type=?', $options['type']);

        $select->group(array(
                'tcs.session_id',
                'tcs.name',
                'c.cycle_id',
                'c.name',
                'tcs.status',
                'tcs.responsible_id',
                'rp.FirstName',
                'rp.LastName',
                'rp.Patronymic'
            )
        );


// сейчас показываем руководитлям все сессии;
// когда появятся сессии из других дочерних обществ, это условие надо будет реанимировать

        /*
        if ($options['departmentId']) {

            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
                $select->joinLeft(
                    array('tcsd2' => 'tc_session_departments'),
                    'tcsd2.session_id = tcs.session_id',
                    array(
                        'tcsession_1_mydep' => new Zend_Db_Expr('COUNT(DISTINCT tcsd2.department_id) - COUNT(DISTINCT sop2.state_of_process_id)'),
                        'tcsession_2_mydep' => new Zend_Db_Expr("SUM(CASE WHEN
                                                    sop2.current_state='HM_Tc_Session_Department_State_Open' THEN 1 ELSE 0 END)"),
                        'tcsession_3_mydep' => new Zend_Db_Expr("SUM(CASE WHEN sop2.current_state IN (
                                                      'HM_Tc_Session_Department_State_AgreementStandart',
                                                    'HM_Tc_Session_Department_State_AssignmentCost',
                                                    'HM_Tc_Session_Department_State_Agreement') THEN 1 ELSE 0 END)"),
                        'tcsession_4_mydep' => new Zend_Db_Expr("SUM(CASE WHEN sop2.current_state='HM_Tc_Session_Department_State_Complete' THEN 1 ELSE 0 END)"),
                    ))
                    ->joinLeft(
                        array('sop2' => 'state_of_process'),
                        'sop2.item_id=tcsd2.session_department_id and sop2.process_type=' . HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
                        array())
                    ->where(
                        $this->quoteInto(
                            'tcsd2.department_id IN (?)',
                            $options['departmentId']
                        )
                    );
            } else {
                $select->joinInner(
                    array('tcsd2' => 'tc_session_departments'),
                    'tcsd2.session_id = tcs.session_id',
                    array())
                    ->where(
                        $this->quoteInto(
                            'tcsd2.department_id IN (?)',
                            $options['departmentId']
                        )
                    );
            }
        }
        */

        $a = (String) $select;
        return $select;
    }

    public function getPastSource($options)
    {
        $default = array();

        $options = array_merge($default, $options);

        $select = $this->getSelect();

        $select->from(array('tcs' => 'tc_sessions'), array(
            'session_id'  => 'tcs.session_id',
            'name'        => 'tcs.name',
            'course'      => 's.name',
            'type'        => 'tca.category',
            'price'       => 'tca.price',
            'end'         => 'g.end',
            'certificate' => 'g.certificate_id',
            'year'        => 'c.year',
        ));

        $select->joinLeft(
            array('tca' => 'tc_applications'),
            'tcs.session_id = tca.session_id',
            array()
        );

        $select->joinLeft(
            array('s' => 'subjects'),
            's.subid = tca.subject_id',
            array()
        );

        $select->joinLeft(
            array('g' => 'graduated'),
            'g.application_id = tca.application_id',
            array()
        );

        $select->joinLeft(
            array('c' => 'cycles'),
            'tcs.cycle_id = c.cycle_id',
            array()
        );

        $select->where('tca.user_id=?', $options['employee']);

        $select->group(array(
                'tcs.session_id',
                'tcs.name',
                's.name',
                'tca.category',
                'tca.price',
                'g.end',
                'g.certificate_id',
                'c.year'
            )
        );

        return $select;
    }

    public function delete($sessionId)
    {
        $this->getService('TcApplication')->deleteBy('session_id='.$sessionId);
        $this->getService('TcSessionDepartment')->deleteBy('session_id='.$sessionId);

        parent::delete($sessionId);
    }

    public function insert($data, $createApplications = true)
    {
        $orgstructureService = $this->getService('Orgstructure');

        $condition = array(
            'level = ?' => 2, // какой ужосс
            'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
            'blocked = ?' => 0,
        );

        // ВНИМАНИЕ!!!
        // убрать этот хардкод когда подтянутся другие ПСК
        $config = Zend_Registry::get('config');
        if ($soid = $config->gsp->integration->sources->soid) {
            $root = $orgstructureService->findOne($soid);
            if ($root) {
                $condition['lft > ?'] = $root->lft;
                $condition['rgt < ?'] = $root->rgt;
            }
        }

        $collection = $orgstructureService->fetchAll($condition);

        if (!count($collection)) {
            return false;
        }

        $allDepartments = $collection->getList('soid');
        $data['checked_items'] = serialize($allDepartments);

        $session = parent::insert($data);
        if ($session) {
            $dataSoid = array(
                'session_id' => $session->session_id,
                'department_id' => 0
            );


            foreach ($allDepartments as $item) {
                $dataSoid['department_id'] = $item;
                $this->getService('TcSessionDepartment')->insert($dataSoid);
            }
        }

        /********* init Process ********/

        // это очень важное место
        // здесь задаются все параметры, которые можно будет использовать внутри Action
        // почему-то надо задавать отдельно для каждого шага..

        switch ($session->type) {
            case HM_Tc_Session_SessionModel::TYPE_TC:
                $this->getService('Process')->startProcess($session, array(
                    'HM_Tc_Session_State_Open' => array(
                        'session_id' => $session->session_id,
                    ),
                    'HM_Tc_Session_State_Publish' => array(
                        'session_id' => $session->session_id,
                    ),
                    'HM_Tc_Session_State_Analysis' => array(
                        'session_id' => $session->session_id,
                    ),
                    'HM_Tc_Session_State_Agreement' => array(
                        'session_id' => $session->session_id,
                    ),
                ));

                //Создаем заявки на обязательное и рекомендованое обучение
                if ($createApplications) {
                    $this->createApplications($session->session_id);
                }
                break;
        }

        return $session;
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

    public function createApplications($sessionId)
    {
        $applicationsService = $this->getService('TcApplication');
        $session = $this->getOne($this->fetchAllDependence(
                array('Cycle', 'Department'),
                $this->quoteInto('session_id = ?', $sessionId))
        );

        //Если заявки по сессии уже созданы - выход
        $created = $applicationsService->countAll('session_id=' . $session->session_id);
        if ($created) {
            return 0;
        }

        $this->createRequiredApplications($session);
        $this->createRequiredApplicationsByCertificates($session);
        $this->createRecomendedApplications($session);
    }

    public function createRecomendedApplications($session)
    {
        //выбираем оргструктуру с профилями и компетенциями/квалификациями
        $soidSelect = $this->getSelect();
        $soidSelect->from(
            array('so' => 'structure_of_organ'),
            array(
                'user_id'        => 'p.MID',
                'soid'           => 'so.soid',
                'department'     => new Zend_Db_Expr("CASE WHEN so.is_manager>0 THEN so2.owner_soid ELSE so.owner_soid END"),
                'sotype'         => 'so.type',
                'is_manager'     => 'so.is_manager',
                'profile_id'     => 'so.profile_id',
                'criterion_id'   => 'apcv.criterion_id',
                'criterion_type' => 'apcv.criterion_type',
                'importance'     => 'apcv.importance',
                'value'          => 'apcv.value',
                'required'       => new Zend_db_Expr("COUNT(app.application_id)"),
            ))
            ->joinInner(
                array('so2'    => 'structure_of_organ'),
                'so.owner_soid = so2.soid',
                array())
            ->joinInner(
                array('p'    => 'People'),
                'so.mid = p.MID',
                array())
            ->joinInner(
                array('ap' => 'at_profiles'),
                'so.profile_id = ap.profile_id',
                array())
            ->joinInner(
                array('apcv' => 'at_profile_criterion_values'),
                'so.profile_id = apcv.profile_id',
                array())
            ->joinLeft(
                array('app' => 'tc_applications'),
                'app.user_id=p.MID AND app.criterion_id=apcv.criterion_id
                AND app.criterion_type=apcv.criterion_type
                AND app.session_id=' . $session->session_id,
                array())
            ->joinLeft(array('a' => 'absence'),
                "a.user_id = so.mid",
                array())
            ->where('p.blocked = 0 OR p.blocked IS NULL')
            ->where('apcv.value IS NOT NULL')
            ->where($this->quoteInto(array(
                        '(a.absence_begin is NULL) OR (a.absence_end is NULL) OR (a.absence_begin>?)',
                        ' OR (a.absence_end<?)'
                    ), array(
                        $session->cycle->current()->begin_date,
                        $session->cycle->current()->end_date)))
            ->group(array(
                'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',
                'so.soid', 'so.name', 'so.type', 'so.is_manager', 'so.owner_soid', 'so2.owner_soid', 'so.profile_id',
                'ap.profile_id', 'apcv.criterion_id', 'apcv.criterion_type', 'apcv.value', 'apcv.importance',
            ));

        $people = $soidSelect->query()->fetchAll();
        if (!$people) {
            return;
        }

        $users             = array();
        $profileCriterions = array();
        foreach ($people as $person) {

            if ($person['required']) {
                continue;
            }
/*            
            if (($person['criterion_type'] == HM_At_Criterion_CriterionModel::TYPE_CORPORATE) && ($person['importance'] != HM_At_Criterion_CriterionModel::IMPORTANCE_MATTERS)) {
                continue;
            }
*/            
            if (!$profileCriterions[$person['profile_id']]) {
                $profileCriterions[$person['profile_id']] = array();
            }
            $profileCriterions[$person['profile_id']][$person['criterion_type']. "_" . $person['criterion_id']] = $person['value'] ? $person['value'] : 0;
            $users[$person['user_id']] = $person;
        }

        $userAtValues = $gradUsers = array();
        if (count($users)) {
            //выбираем текущие значения компетнций для пользователей из оценочных сессий
            $atValuesSelect = $this->getSelect();
            $atValuesSelect->from(
                array('s' => 'at_sessions'),
                array(
                    's.end_date',
                    'su.user_id',
                    'sucv.criterion_id',
                    'sucv.criterion_type',
                    'sucv.value',
                ))
                ->joinInner(
                    array('su' => 'at_session_users'),
                    's.session_id=su.session_id',
                    array())
                ->joinInner(
                    array('sucv' => 'at_session_user_criterion_values'),
                    'su.session_user_id=sucv.session_user_id',
                    array())
                ->where('su.user_id in(?)', array_keys($users))
                ->order('s.end_date ASC');

            $atValues = $atValuesSelect->query()->fetchAll();
            foreach ($atValues as $atValue) {
                if (empty($userAtValues[$atValue['user_id']])) {
                    $userAtValues[$atValue['user_id']] = array();
                }
                $userAtValues[$atValue['user_id']][$atValue['criterion_type'] . "_" . $atValue['criterion_id']] = $atValue['value'];
            }

            //выбираем список пройденных нашими пользователями курсов
            $graduatedSelect = $this->getSelect();
            $graduatedSelect->from(
                array('g' => 'Graduated'),
                array(
                    'user_id'   => 'g.MID',
                    'criterion' => new Zend_Db_Expr("CONCAT(s.criterion_type, CONCAT('_', s.criterion_id))"),
                    'graduated' => new Zend_Db_Expr("COUNT(s.subid)")))
                ->joinInner(
                    array('s' => 'subjects'),
                    's.subid=g.CID',
                    array())
                ->where('g.MID in(?)', array_keys($users))
                ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
                ->group(array('g.MID', 's.criterion_type', 's.criterion_id'));
            $gradData  = $graduatedSelect->query()->fetchAll();
            foreach ($gradData as $graduated) {
                if (!$gradUsers[$graduated['user_id']]) {
                    $gradUsers[$graduated['user_id']] = array();
                }
                $gradUsers[$graduated['user_id']][$graduated['criterion']] = $graduated['graduated'];
            }
        }

        $recomended      = array();
        $recomendedWhere = array();
        foreach ($users as $userId => $userData) {
            foreach ($profileCriterions[$userData['profile_id']] as $mixedKey => $goodValue) {
                list($crType, $crId) = explode('_', $mixedKey);

                if ((($crType == HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL) &&
                        (empty($gradUsers[$userId]) || empty($gradUsers[$userId][$mixedKey]))) ||
                    (!isset($userAtValues[$userId][$mixedKey]) || ($userAtValues[$userId][$mixedKey] < $goodValue))) {

                    if (empty($recomended[$userId])) {
                        $recomended[$userId] = array();
                    }
                    $recomended[$userId][] = $mixedKey;
                    $recomendedWhere[] = preg_replace('/(\d*)_(\d*)/', '(sub.criterion_type=\\1 AND sub.criterion_id=\\2)', $mixedKey);
                }
            }
        }

        $recomendedWhere = implode(' OR ', array_unique($recomendedWhere));
        if (!$recomended) {
            return 0;
        }

        //Саб-селект, для подсчета рейтинга по курсам
        $ratingSelect = $this->getService('Subject')->getSelect();
        $ratingSelect->from(
            array('s' => 'subjects'),
            array(
                'base_id'      => 's.base_id',
                'rating'       => 's.rating'
            ))
            ->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->group(array('s.base_id', 's.rating'));

        //курсы подходящие по компетенциям
        $subSelectCources = $this->getSelect();
        $subSelectCources->from(
            array('sub' => 'subjects'),
            array(
                'subid'          => 'sub.subid',
                'name'           => 'sub.name',
                'provider_id'    => 'sub.provider_id',
                'criterion_id'   => 'sub.criterion_id',
                'criterion_type' => 'sub.criterion_type',
                'price'          => 'sub.price',
                'rating'         => 'sub.rating'
            ))
            ->joinLeft(
                array('rt' => $ratingSelect),
                'rt.base_id=sub.subid',
                array())
            ->where('sub.status != 0')
            ->where('sub.category = ' . HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_ADDITION)
            ->where('sub.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->where('sub.base != ? OR sub.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->where($recomendedWhere)
            ->group(array('sub.subid', 'sub.name', 'sub.provider_id', 'sub.criterion_id', 'sub.criterion_type', 'sub.price', 'sub.rating'))
            ->order('sub.price');
        $courses = $subSelectCources->query()->fetchAll();

        //курсы к пройденым юзерами сессиям, чтобы не повторяться
        $subSelectGraduated = $this->getSelect();
        $subSelectGraduated->from(
            array('gr' => 'graduated'),
            array(
                'sub.base_id',
                'gr.MID'
            ))
            ->joinInner(
                array('sub' => 'subjects'),
                'gr.CID=sub.subid',
                array())
            ->where($recomendedWhere)
            ->where('gr.MID in (?)', array_keys($users))
            ->group(array('sub.base_id', 'sub.criterion_id', 'sub.criterion_type', 'gr.MID'));
        $graduated = $subSelectGraduated->query()->fetchAll();
        $gradUsers = array();
        foreach ($graduated as $grad) {
            if (!isset($gradUsers[$grad['MID']])) {
                $gradUsers[$grad['MID']] = array();
            }
            $gradUsers[$grad['MID']][] = $grad['base_id'];
        }

        $applicationsService = $this->getService('TcApplication');
        $sessionDepartments  = $session->departments->getList('department_id', 'session_department_id');

        $orgstructureService = $this->getService('Orgstructure');
        foreach ($sessionDepartments as $sessionDepartmentKey => $sessionDepartment) {
            if ($sessionDepartmentKey != HM_Orgstructure_OrgstructureModel::HEAD_SOID) {
                $subDepartments = $orgstructureService->getDescendants(
                    $sessionDepartmentKey,
                    false,
                    HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
                );

                foreach ($subDepartments as $subDepartment) {
                    $sessionDepartments[$subDepartment] = $sessionDepartment;
                }
            }
        }
        $sessionDepartments[0] = $sessionDepartments[HM_Orgstructure_OrgstructureModel::HEAD_SOID];

        foreach ($recomended as $userId => $recomendedCouses) {

            foreach ($recomendedCouses as $mixedKey) {

                $criterion     = explode('_', $mixedKey);
                $criterionType = $criterion[0];
                $criterionId   = $criterion[1];
                if (!$criterionId) {
                    continue;
                }

                $subjectId      = 0;
                $providerId     = 0;

                //ищем подходящий курс
                //приоритет выбора: бесплатный/платный -> наивысший рейтинг
                if ($courses) {
                    $ratedCources   = array();

                    foreach ($courses as $course) {
                        if (($course['criterion_id'] != $criterionId) || ($course['criterion_type'] != $criterionType)) {
                            continue;
                        }

                        if (is_array($gradUsers[$userId]) && in_array($course['subid'], $gradUsers[$userId])) {
                            continue;
                        }

                        $strKey = empty($course['price']) ? '1' : '0';

                        if (empty($ratedCources[$strKey])) {
                            $ratedCources[$strKey] = array();
                        }
                        if (empty($ratedCources[$strKey][$course['rating']])) {
                            $ratedCources[$strKey][$course['rating']] = array();
                        }
                        $ratedCources[$strKey][$course['rating']][] = $course;
                    }

                    if ($ratedCources) {
                        krsort($ratedCources);

                        $bestPlaced = array_shift($ratedCources);
//                        krsort($bestRated);
                        $bestRated = array_shift($bestPlaced);

                        $selectedCource = array_shift($bestRated);

                        $subjectId      = $selectedCource['subid'];
                        $providerId     = $selectedCource['provider_id'];
                    }

                }
                if (!$subjectId) {
                    continue;
                }

                if (!$sessionDepartments[$users[$userId]['department']]) {
                    continue;
                }

                $subject = $this->getService('Subject')->getOne($this->getService('Subject')->fetchAll(
                    array('subid  = ?' => $subjectId))
                );
                $insert = array(
                    'session_id'            => $session->session_id,
                    'department_id'         => $users[$userId]['department'],
                    'session_department_id' => $sessionDepartments[$users[$userId]['department']],
                    'user_id'               => $users[$userId]['user_id'],
                    'position_id'           => $users[$userId]['soid'],
                    'criterion_id'          => $criterionId,
                    'criterion_type'        => $criterionType,
                    'subject_id'            => $subjectId,
                    'provider_id'           => $providerId,
                    'category'              => HM_Tc_Application_ApplicationModel::CATEGORY_RECOMENDED,
                    'created'               => date('Y-m-d'),
                    'cost_item' => HM_Tc_Application_ApplicationModel::DEFAULT_COST_ITEM,
                    'status'                => HM_Tc_Application_ApplicationModel::STATUS_INACTIVE,
                    'price'                 => $subject->price,
                );
                $applicationsService->insert($insert);
            }
        }
    }

    public function requiredApplicationsListSource($session)
    {
        $select = $this->getSelect();
        $subSelectGraduated = clone $select;
        $subSelectGraduated->from(
            array('gr' => 'graduated'),
            array(
                'lastcourse' => new Zend_Db_Expr('MAX(gr.end)'),
                'sub.criterion_id',
                'gr.MID'
            ))
            ->joinInner(
                array('sub' => 'subjects'),
                'gr.CID=sub.subid',
                array())
            ->where('sub.criterion_type='.HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST)
            ->group(array('sub.criterion_id', 'gr.MID'));

        $subSelectCources = clone $select;
        $subSelectCources->from(
            array('sub' => 'subjects'),
            array(
                'subid'        => 'sub.subid',
                'name'         => 'sub.name',
                'primary_type' => 'sub.primary_type',
                'criterion_id' => 'sub.criterion_id',
                'price'        => 'sub.price'
            ))
            ->where('sub.status != 0')
            ->where('sub.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->where('sub.category = ?', HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY)
            ->where('sub.base != ? OR sub.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->where('sub.criterion_type='.HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST);

        $select->from(
            array('so' => 'structure_of_organ'),
            array(
                'user_id'       => 'p.MID',
                'fio'           => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'position'      => 'so.name',
                'soid'          => 'so.soid',
                'department'    => new Zend_Db_Expr("CASE WHEN so.is_manager>0 THEN so2.owner_soid ELSE so.owner_soid END"),
                'sotype'        => 'so.type',
                'is_manager'    => 'so.is_manager',
                'profile_id'    => 'ap.profile_id',
                'profile'       => 'ap.name',
                'criterion_id'  => 'apcv.criterion_id',
                'criterion_type' => 'apcv.criterion_type',
                'criterion'     => 'act.name',
                'act.validity',
                'grad.lastcourse',
                'courses'          => new Zend_Db_Expr("GROUP_CONCAT(ft.subid)")
            ))
            ->joinInner(
                array('so2'    => 'structure_of_organ'),
                'so.owner_soid = so2.soid',
                array())
            ->joinInner(
                array('p'    => 'People'),
                'so.mid = p.MID',
                array())
            ->joinInner(
                array('ap' => 'at_profiles'),
                'so.profile_id = ap.profile_id',
                array())
            ->joinInner(
                array('apcv' => 'at_profile_criterion_values'),
                'so.profile_id = apcv.profile_id',
                array())
            ->joinInner(
                array('act' => 'at_criteria_test'),
                'act.criterion_id = apcv.criterion_id',
                array())
            ->joinLeft(
                array('grad' => $subSelectGraduated),
                'grad.criterion_id=apcv.criterion_id AND grad.MID=p.mid',
                array())
            ->joinLeft(
                array('ft' => $subSelectCources),
                'ft.criterion_id=apcv.criterion_id',
                array())
            ->joinLeft(array('a' => 'absence'),
                "a.user_id = so.mid",
                array())
            ->where($this->quoteInto(array(
                '(a.absence_begin is NULL) OR (a.absence_end is NULL) OR (a.absence_begin>?)',
                ' OR (a.absence_end<?)'
                ), array(
                $session->cycle->current()->begin_date,
                $session->cycle->current()->end_date)))
            ->where('p.blocked = 0 OR p.blocked IS NULL')
            ->where('apcv.criterion_type=' . HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL)
            ->where('act.required = 1')
            ->where('act.employee_type = ' . HM_At_Criterion_Test_TestModel::EMPLOYEE_TYPE_EMPLOYEE)
            ->where('grad.lastcourse is NULL OR act.validity > 0')
            ->where('so.blocked   = 0')
            ->group(array(
                'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',
                'so.soid', 'so.name', 'so.type', 'so.is_manager', 'so.owner_soid', 'so2.owner_soid',
                'ap.profile_id', 'ap.name', 'apcv.criterion_id', 'apcv.criterion_type',
                'act.name', 'act.validity', 'grad.lastcourse'
            ));

        //Фильтр по департаментам привязаным к сессии
        if ($session) {
            $departmentIds = $session->departments->getList('department_id', 'department_id');
            $structureWhere = '';
            $structures = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
                'soid in (?)' , $departmentIds
            ));
            foreach ($structures as $structure) {
                $structureWhere = ($structureWhere ? $structureWhere . ' OR ' : '') .
                    "((so.lft > " . $structure->lft . ") AND (so.rgt < " . $structure->rgt . "))";
            }
            if (!$structureWhere) {
                $structureWhere = '1=0';
            }
            $select->where($structureWhere);
        }

        return $select;
    }

    /*
     * Создаем заявки на очное обучение
     * Метод должен запускаться в БП сессии
     * Возвращает колличество созданых заявок
     */
    public function createRequiredApplications($session)
    {
        $applicationsService = $this->getService('TcApplication');

        $select       = $this->requiredApplicationsListSource($session);

        $applications = $select->query()->fetchAll();

        //Если обязательного обучения не найдено - выход
        if(!$applications) {
            return 0;
        }

        $sessionDepartments = $session->departments->getList('department_id', 'session_department_id');

        $orgstructureService = $this->getService('Orgstructure');
        foreach ($sessionDepartments as $sessionDepartmentKey => $sessionDepartment) {
            if ($sessionDepartmentKey != HM_Orgstructure_OrgstructureModel::HEAD_SOID) {
                $subDepartments = $orgstructureService->getDescendants(
                    $sessionDepartmentKey,
                    false,
                    HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
                );

                foreach ($subDepartments as $subDepartment) {
                    $sessionDepartments[$subDepartment] = $sessionDepartment;
                }
            }
        }
        $sessionDepartments[0] = $sessionDepartments[HM_Orgstructure_OrgstructureModel::HEAD_SOID];


        $firstPeriod = date('Y-m-01', strtotime($session->cycle ? $session->cycle->current()->begin_date : date('Y-m-d')));

        //кеш всех курсов подходящих к заявкам, сортировка по цене
        $coursesIds = array();
        foreach ($applications as $application) {
            $coursesIds[] = $application['courses'];
        }
        $coursesIds = implode(',',$coursesIds);
        $coursesIds = explode(',',$coursesIds);
        $coursesIds = array_unique($coursesIds);
        $coursesIds = array_filter($coursesIds);
        $coursesCache = array();
        if ($coursesIds) {
            $coursesCache = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $coursesIds), 'price');
        }

        foreach ($applications as $application) {// Идут в порядке увеличения цены

            $subjectId  = 0;
            $providerId = 0;

            //выбираем подходящий курс
            $coursesIds     = array_filter(array_unique(explode(',', $application['courses'])));
            foreach($coursesCache as $course) {
                if (in_array($course->subid, $coursesIds)) {
                    $subjectId  = $course->subid;
                    $providerId = $course->provider_id;
                    break;
                }
            }

            if (!$sessionDepartments[$application['department']]) {
                continue;
            }

            $expire = '';
            $period = $firstPeriod;
            if (!empty($application['lastcourse'])) {
                $dateLC = new DateTime($application['lastcourse']);
                $dateLC->add(new DateInterval('P'.$application['validity'].'M'));
                $expire = $dateLC->format('Y-m-d');
                if ($expire > $session->cycle->current()->end_date) {
                    continue;
                }

                //#18601
                $period = $dateLC->sub(new DateInterval('P2M'))->format('Y-m-01');
                if ($period < $firstPeriod) {
                    $period = $firstPeriod;
                }
            }

            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->fetchAll(
                array('subid  = ?' => $subjectId))
            );

            $insert = array(
                'session_id'            => $session->session_id,
                'department_id'         => $application['department'],
                'session_department_id' => $sessionDepartments[$application['department']],
                'user_id'               => $application['user_id'],
                'position_id'           => $application['soid'],
                'criterion_id'          => $application['criterion_id'],
                'criterion_type'        => $application['criterion_type'],
                'subject_id'            => $subjectId,
                'provider_id'           => $providerId,
                'period'                => $period,
                'category'              => HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED,
                'created'               => date('Y-m-d'),
                'expire'                => $expire,
                'primary_type'          => empty($application['lastcourse']) ? HM_Tc_Subject_SubjectModel::FULLTIME_PRIMARY_PRIMARY : HM_Tc_Subject_SubjectModel::FULLTIME_PRIMARY_SECONDARY,
                'cost_item'             => HM_Tc_Application_ApplicationModel::DEFAULT_COST_ITEM,
                'status'                => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                'price'                 => $subject->price
           );
            $applicationsService->insert($insert);
        }

        return count($applications);
    }

    /*
     * Альтернативный способ выявления потребности в очном обучении
     * На основе даннных о прошлых обучениях
     */
    public function createRequiredApplicationsByCertificates($session)
    {
        $cnt = 0;
        $applicationsService = $this->getService('TcApplication');

        $certificates = $this->getService('Certificates')->fetchAllDependenceJoinInner(
            'Subject',
            $this->getService('Certificates')->quoteInto(
                array(
                    ' self.enddate > ? AND ',
                    ' self.enddate < ? AND ',
                    ' Subject.category = ? '
                ),
                array(
                    $session->cycle->current()->begin_date,
                    $session->cycle->current()->end_date,
                    HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY
                )
            )
        );

        //Если обязательного обучения не найдено - выход
        if (!count($certificates)) {
            return 0;
        }

        $sessionDepartments = $session->departments->getList('department_id', 'session_department_id');

        $orgstructureService = $this->getService('Orgstructure');
        foreach ($sessionDepartments as $sessionDepartmentKey => $sessionDepartment) {
            if ($sessionDepartmentKey != HM_Orgstructure_OrgstructureModel::HEAD_SOID) {
                $subDepartments = $orgstructureService->getDescendants(
                    $sessionDepartmentKey,
                    false,
                    HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
                );

                foreach ($subDepartments as $subDepartment) {
                    $sessionDepartments[$subDepartment] = $sessionDepartment;
                }
            }
        }
        $sessionDepartments[0] = $sessionDepartments[HM_Orgstructure_OrgstructureModel::HEAD_SOID];


        $subjectIds = $certificates->getList('subject_id');

        $subjectsCache = $this->getService('Subject')->fetchAllDependence(
            array('BaseSubject', 'TcProvider'),
            array('subid IN (?)' => $subjectIds),
            'price'
        )->asArrayOfObjects();

        // Если сертификат привязан к сессии, меняем её на курс, на основании которого она создана.
        foreach ($subjectsCache as $key => $subject) {
            if ($subject->baseSubject) $subjectsCache[$key] = $subject->baseSubject->current();
        }

        $userIds = $certificates->getList('user_id');
        $usersCache = $this->getService('User')->fetchAllDependence(array('Position'), array('MID IN (?)' => $userIds))->asArrayOfObjects();

        $firstPeriod = date('Y-m-01', strtotime($session->cycle ? $session->cycle->current()->begin_date : date('Y-m-d')));

        foreach ($certificates as $certificate) {

            if (isset($subjectsCache[$certificate->subject_id])) {

                $subject = $subjectsCache[$certificate->subject_id];
                if (count($subject->baseSubject)) {
                    $subject = $subject->baseSubject->current();
                }

                $provider = count($subject->tcProvider) ? $subject->tcProvider->current() : false;

                $user = $usersCache[$certificate->user_id];
                if ($subject && count($user->positions)) {

                    $position = $user->positions->current();
                    $departmentId = $position->owner_soid;

                    $expireDate = new DateTime($certificate->enddate);
                    // за 2 месяца до дэдлайна, с 1-го числа
                    $periodDate = clone $expireDate;
                    $period = $periodDate->sub(new DateInterval('P2M'))->format('Y-m-01');
                    if ($period < $firstPeriod) {
                        $period = $firstPeriod;
                    }

                    $insert = array(
                        'session_id'            => $session->session_id,
                        'department_id'         => $departmentId,
                        'session_department_id' => $sessionDepartments[$departmentId],
                        'user_id'               => $user->MID,
                        'position_id'           => $position->soid,
                        'criterion_id'          => 0,
                        'criterion_type'        => 0,
                        'subject_id'            => $subject->subid,
                        'provider_id'           => $provider ? $provider->provider_id : 0,
                        'period'                => $period,
                        'category'              => HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED,
                        'created'               => date('Y-m-d'),
                        'expire'                => $expireDate->format('Y-m-d'),
                        'primary_type'          => HM_Tc_Subject_SubjectModel::FULLTIME_PRIMARY_SECONDARY,
                        'cost_item'             => HM_Tc_Application_ApplicationModel::DEFAULT_COST_ITEM,
                        'status'                => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                        'price'                 => $subject->price
                    );
                    $applicationsService->insert($insert);
                    $cnt++;
                }
            }
        }

        return $cnt;
    }

    public function isApplicable($session)
    {
        if ($session) {
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
                    return is_a($state, 'HM_Tc_Session_Department_State_Open');
                }
            }
        }

        return false;
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
                return HM_Tc_Session_SessionModel::STATE_CLOSED;
            }
        }

        if ($isSuper) {
            $superDepartmentId   = $this->getService('Orgstructure')->getResponsibleDepartments();
            if ($departmentId && ($superDepartmentId != $departmentId)) {
                return HM_Tc_Session_SessionModel::STATE_PENDING;
            }
            $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                $this->quoteInto(array('session_id =? ',' AND department_id = ?'),
                    array($sessionId, $superDepartmentId)));
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $departmentIds      = $this->getService('Orgstructure')->getResponsibleDepartments();
            if (!$departmentIds) {
                if ($departmentId) {
                    $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                        $this->quoteInto(array('session_id =? ',' AND department_id = ?'),
                            array($sessionId, $departmentId)));
                } else {
                    $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                        $this->quoteInto(array('session_id =? '), array($sessionId)));
                }
            } else {
                if ($departmentId) {
                    if (!in_array($departmentId, $departmentIds)) {
                        return HM_Tc_Session_SessionModel::STATE_PENDING;
                    } else {
                        $departmentIds = array($departmentId);
                    }
                }
                $sessionDepartments = $this->getService('TcSessionDepartment')->fetchAll(
                    $this->quoteInto(array('session_id =? ',' AND department_id in (?)'),
                        array($sessionId, $departmentIds)));
            }
        } else {
            if ($departmentId) {
                $sessionDepartment = $this->getService('TcSessionDepartment')->getOne(
                    $this->getService('TcSessionDepartment')->fetchAll(
                    $this->quoteInto(array('session_id =? ',' AND department_id=?'),
                        array($sessionId, $departmentId))));
                $state = $this->getService('Process')->getCurrentState($sessionDepartment);
                if (!$state) {
                    return HM_Tc_Session_SessionModel::STATE_PENDING;
                }
                if ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) {
                    if ($departmentId) {
                        return HM_Tc_Session_SessionModel::STATE_CANCELED;
                    }
                }
            }

            return HM_Tc_Session_SessionModel::STATE_ACTUAL;
        }

        //вырожденный случай: нет подразделения в этой сессии
        if (!$sessionDepartments) {
            return HM_Tc_Session_SessionModel::STATE_PENDING;
        }

        foreach ($sessionDepartments as $sessionDepartment) {
            $state = $this->getService('Process')->getCurrentState($sessionDepartment);

            if (!$state) {
                return HM_Tc_Session_SessionModel::STATE_PENDING;
            }

            if ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) {
                if ($departmentId) {
                    return HM_Tc_Session_SessionModel::STATE_CANCELED;
                }
            } elseif ($state instanceof HM_Tc_Session_Department_State_Open ||
                (!$isSuper && (
                        ($state instanceof HM_Tc_Session_Department_State_Agreement) ||
                        ($state instanceof HM_Tc_Session_Department_State_AssignmentCost) ||
                        ($state instanceof HM_Tc_Session_Department_State_AgreementStandart)))) {
                return HM_Tc_Session_SessionModel::STATE_ACTUAL;
            }
        }

        return HM_Tc_Session_SessionModel::STATE_CLOSED;

    }

    public function getActiveDepartments($sessionId)
    {
        $select = $this->getSelect();
        $select->from(
            array('tcsd' => 'tc_session_departments'),
            array(
                'tcsd.session_department_id',
            ))
            ->joinInner(
                array('sop' => 'state_of_process'),
                'sop.item_id=tcsd.session_department_id and sop.process_type=' . HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
                array())
            ->where("sop.current_state <>'HM_Tc_Session_Department_State_Complete'")
            ->where("sop.status<>?", HM_Process_Abstract::PROCESS_STATUS_FAILED)
            ->where('tcsd.session_id=?', $sessionId);

        $data   = $select->query()->fetchAll();
        $result = array();
        foreach ($data as $row) {
            $result[] = $row['session_department_id'];
        }

        return $result;
    }

    public function getCorporateSource($sessions, $depIds = array())
    {
        $cycleIds = array(0);
        $departmentIds = array(0);
        foreach ($sessions as $session) {
            $cycleIds[]      = $session->cycle_id;
        }

        if ($depIds) {
            $departmentIds = array_intersect($departmentIds, $depIds);
        }

        $cycleWhere      = $this->quoteInto('corp.cycle_id in (?)', array_unique($cycleIds));
        $departmentWhere = $this->quoteInto('so.soid in (?)', array_unique($departmentIds));

        $select = $this->getSelect();

        $subSelectCity = clone $select;
        $subSelectCity->from(
            array('cl' => 'classifiers_links'),
            array(
                'cl.classifier_id',
                'cl.item_id',
                'cl.type',
                'c.name'
            ))
            ->joinInner(
                array('c' => 'classifiers'),
                $this->getService('Classifier')->quoteInto(
                    'c.classifier_id = cl.classifier_id AND c.type = ?',
                    HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES
                ),
                array()
            );

        $selectOrg = clone $select;
        $selectOrg->from(array('tccp' => 'tc_corporate_learning'), array(
            'corporate_learning_id' => 'tccp.corporate_learning_id',
            'corporate_name'        => 'tccp.name',
            'manager_name'          => 'tccp.manager_name',
            'meeting_type'          => 'tccp.meeting_type',
            'cycle_id'              => 'tccp.cycle_id',
            'month'                 => 'tccp.month',
            'type'                  => new Zend_Db_Expr(HM_Tc_CorporateLearning_CorporateLearningModel::CORPORATE_TYPE_ORGANIZER),
            'cost'                  => 'tccp.cost_for_organizer',
            'soid'                  => 'tccp.organizer_id',
        ));

        $selectPart = clone $select;
        $selectPart->from(array('tccp' => 'tc_corporate_learning'), array(
            'corporate_learning_id' => 'tccp.corporate_learning_id',
            'corporate_name'        => 'tccp.name',
            'manager_name'          => 'tccp.manager_name',
            'meeting_type'          => 'tccp.meeting_type',
            'cycle_id'              => 'tccp.cycle_id',
            'month'                 => 'tccp.month',
            'type'                  => new Zend_Db_Expr(HM_Tc_CorporateLearning_CorporateLearningModel::CORPORATE_TYPE_PARICIPANT),
            'cost'                  => 'p.cost',
            'soid'                  => 'p.participant_id',
        ))
            ->joinLeft(
                array('p' => 'tc_corporate_learning_participant'),
                'tccp.corporate_learning_id = p.corporate_learning_id',
                array())
            ->group(array(
                'tccp.corporate_learning_id', 'tccp.name', 'tccp.manager_name',
                'tccp.month', 'tccp.cycle_id', 'tccp.meeting_type',
                'p.participant_id','p.cost',
            ));

        $subSelectCorporate = clone $select;
        $subSelectCorporate->union(array($selectOrg, $selectPart), Zend_Db_Select::SQL_UNION);

        $select->from(
            array('so' => 'structure_of_organ'),
            array(
                'department_id' => 'so.soid',
                'department'    => 'so.name',
                'corp.corporate_learning_id',
                'corp.corporate_name',
                'corp.manager_name',
                'corp.meeting_type',
                'corp.cycle_id',
                'corp.month',
                'corp.type',
                'corp.cost'
            ))
            ->joinInner(
                array('corp' => $subSelectCorporate),
                'so.soid=corp.soid',
                array())
            ->where($cycleWhere)
            ->where($departmentWhere);


        return $select;
    }

    public  function checkPlanningDepartment($planningDepartment, $soids)
    {
        $planningDepartment = is_array($planningDepartment) && $planningDepartment[0]
            ? $planningDepartment[0] : 0;
        $soids = is_array($soids) ? $soids : explode(',',$soids);

        if (!$planningDepartment) {
            return _('Не выбран уровень планирования');
        }

        $departments = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
            'soid in (?)', array_merge(array($planningDepartment), $soids)
        ));

        $pdLft = 0;
        $pdRgt = 0;
        $items = array();

        foreach ($departments as $department) {
            if ($department->soid == $planningDepartment) {
                $pdLft = $department->lft;
                $pdRgt = $department->rgt;
            } else {
                $items[$department->soid] = array(
                    'lft' => $department->lft,
                    'rgt' => $department->rgt);
            }
        }

        foreach ($items as $item) {
            if (($item['lft'] < $pdLft) || ($item['rgt'] > $pdRgt)) {
                return _('Уровень планирования включает не все выбраные подразделения');
            }
        }

        return false;
    }


    public function getYearList() {
        $years = array();
        for($i = -1; $i <= 3; $i++) {
            $year = (int) date('Y') + $i;
            $years[$year] = $year;
        }
        return $years;
    }
}