<?php
class HM_Tc_Session_Department_Application_ApplicationService extends HM_Service_Abstract
{

    const PRIMARY_APPLICATION_CHECK_YEAR = 3;

    public function getStudyMonth($date)
    {
        $result = '';
        if($date) {
            $date = new HM_Date($date);
            $result = month_name($date->toString("MM")).' '.$date->toString('y');
        }
        return $result;
    }

    public function createApplications($sessionId) {

        $session = $this->getOne($this->getService('ScSession')->fetchAllDependence(
            array('Cycle', 'Department'),
            $this->quoteInto('session_id = ?', $sessionId))
        );

        $this->createPrimaryApplications($session);
        $this->createTrainingApplications($session);
    }

    public function createPrimaryApplications($session)
    {
        $departmentIds = $session->departments->getList('session_department_id', 'department_id');

        $studyCenterId = $this->getService('ScManager')->getStudyCenter();

        foreach ($departmentIds as $sessionDepartmentId => $departmentId) {
            //id профилей которые привязаны к должностям в этом подразделении
            $profileIds = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
                array('owner_soid = ? ', ' AND type = ? ', ' AND blocked = ?', ' AND profile_id > 0'),
                array($departmentId, HM_Orgstructure_OrgstructureModel::TYPE_POSITION, 0)
            ))->getList('soid', 'profile_id');
            $profileIds = array_unique($profileIds);
            if(!count($profileIds)) {
                continue;
            }
            $select = $this->getSelect();
            $select->from(array('s' => 'subjects'),array(
                'subject_id' => 's.subid',
                'city_id' => 's.city',
                'profile_id' => 'atpcv.profile_id',
                'criterion_id' => 's.criterion_id',
                'criterion_type' => 's.criterion_type',
            ))
            ->joinInner(
                array('atpcv' => 'at_profile_criterion_values'),
                's.criterion_type = atpcv.criterion_type AND s.criterion_id = atpcv.criterion_id',
                array()
            )
            ->joinLeft(
                array('tcps' => 'tc_providers_subjects'),
                's.subid = tcps.subject_id',
                array()
            )
            ->where(
                $this->quoteInto(
                    array(
                        'atpcv.profile_id IN (?) ',
                        ' AND s.provider_type = ? ',
                        ' AND s.base != ? ',
                        ' AND (tcps.provider_id = ? OR tcps.provider_id IS NULL)',
                        ' AND s.category = ?'
                    ),
                    array(
                        $profileIds,
                        HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER,
                        HM_Tc_Subject_StudyCenter_SubjectModel::BASETYPE_SESSION,
                        $studyCenterId,
                        HM_Tc_Subject_StudyCenter_SubjectModel::FULLTIME_CATEGORY_PRIMARY
                    )
                )
            );
           // pr($select->assemble());die;
            $result = $select->query()->fetchAll();

            foreach($result as $item) {

                //TODO тутачки надо добавить рассчет количества создаваемых заявок на первичку, по месяцам
                $monthsApplications = $this->countApplicationsPerMonths($departmentId, $item['profile_id']);
                if(!count($monthsApplications)) {
                    continue;
                }

                foreach($monthsApplications as $key => $count) {
                    $studyDate = $this->getStudyDate($session, $key);
                    $studyMonth = $studyDate->toString(HM_Date::SQL_DATE);
                    $departmentApplication = $this->checkDepartmentApplication(
                        $session->session_id,
                        $departmentId,
                        $sessionDepartmentId,
                        $item['profile_id'],
                        $item['subject_id'],
                        $studyMonth
                    );

                    $insert = array(
                        'session_id' => $session->session_id,
                        'department_id' => $departmentApplication->department_id,
                        'department_application_id' => $departmentApplication->department_application_id,
                        'session_department_id' => $departmentApplication->session_department_id,
                        'user_id' => 0,
                        'position_id' => 0,
                        'criterion_id' => $item['criterion_id'],
                        'criterion_type' => $item['criterion_type'],
                        'subject_id' => $item['subject_id'],
                        'provider_id' => $session->provider_id,
                        'category' => HM_Tc_Application_ApplicationModel::SC_CATEGORY_PRIMARY,
                        'created' => date('Y-m-d'),
                        'status' => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                    );

                    for($i = 0; $i < $count; $i++) {
                        $this->getService('TcApplication')->insert($insert);
                    }
                }
            }
        }
    }

    public function getListSource($options)
    {
        $default = array(
            'sessionId'   => 0,
            'category'  => 0, //категория курса
            'applicationStatus' => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE
        );
        $options = array_merge($default, $options);


        $selectIn = $this->getSelect();
        $selectIn->from(array('tca2' => 'tc_applications'), array(
            'session_id' => 'tca2.session_id',
            'study_month' => 'tcda2.study_month',
            'app_count' => new Zend_Db_Expr("COUNT(tca2.application_Id)")
        ));
        $selectIn->joinInner(
            array('tcda2' => 'tc_department_applications'),
            $this->quoteInto(
                array(
                    'tca2.department_application_id = tcda2.department_application_id AND tca2.status = ?'
                ),
                array(
                    $options['applicationStatus']
                )
            ),
            array()
        );
        $selectIn->group(array('tca2.session_id', 'tcda2.study_month'));

        $select = $this->getSelect();

        $select->from(array('tcda' => 'tc_department_applications'), array(
            'tcda.department_application_id',
            'department_name' => 'so.name',
            'parent_department_name' => 'pso.name',
            'subject_name' => 's.name',
            'programm_name' => 'p.name',
            'study_month' => 'tcda.study_month',
            'category' => 's.category',
            'application_category' => 'tca.category',
            'criterion_name' => 'cr.name',
            'students_count' => new Zend_Db_Expr("COUNT(tca.application_id)"),
            'students_id' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT tca.user_id)"),
            'profile_name' => 'atp.name',
            'is_offsite' => 'tcda.is_offsite',
            'city' => 'cl.classifier_id',
            'provider_id' => 'tcp.provider_id',
            'pass_by' => 'tcp.pass_by',
            'tap_count' => 'tap_count.app_count',

        ))->joinLeft(
            array('s' => 'subjects'),
            's.subid = tcda.subject_id',
            array()
        )->joinLeft(
            array('so' => 'structure_of_organ'),
            'so.soid = tcda.department_id',
            array()
        )->joinLeft(
            array('pso' => 'structure_of_organ'),
            'pso.soid = so.owner_soid',
            array()
        )->joinLeft(
            array('tca' => 'tc_applications'),
            $this->quoteInto(
                array(
                    'tca.department_application_id = tcda.department_application_id AND tca.status = ?'
                ),
                array(
                    $options['applicationStatus']
                )
            ),
            array()
        )->joinLeft(
            array('pe' => 'programm_events'),
            $this->quoteInto('pe.item_id = s.subid AND pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT),
            array()
        )->joinLeft(
            array('p' => 'programm'),
            $this->quoteInto('p.programm_id = pe.programm_id AND p.programm_type = ?', HM_Programm_ProgrammModel::TYPE_ELEARNING),
            array()
        )->joinLeft(
            array('atp' => 'at_profiles'),
            'atp.profile_id = tcda.profile_id',
            array()
        )->joinLeft(
            array('cl' => 'classifiers_links'),
            $this->quoteInto(
                'tcda.department_application_id = cl.item_id AND cl.type = ?',
                HM_Classifier_Link_LinkModel::TYPE_TC_DEPARTMENT_APPLICATION
            ),
            array()
        )->joinLeft(array('cr' => 'criteria'),
            'cr.criterion_id = s.criterion_id AND cr.criterion_type = s.criterion_type',
            array()
        )->joinLeft(array('tcs' => 'tc_sessions'),
            'tcs.session_id = tcda.session_id',
            array()
        )->joinLeft(array('tcp' => 'tc_providers'),
                'tcp.provider_id = tcs.provider_id',
                array()
        )->joinLeft(array('tap_count' => $selectIn),
                'tap_count.session_id = tcda.session_id AND tcda.study_month = tap_count.study_month',
                array()
        );

        $select->group(array(
            'tcda.department_application_id',
            'so.name',
            's.name',
            'p.name',
            'tcda.study_month',
            's.category',
            'tca.category',
            'cr.name',
            'atp.name',
            'tcda.is_offsite',
            'tcda.city_id',
            'cl.classifier_id',
            'tcp.provider_id',
            'tcp.pass_by',
            'tap_count.app_count',
            'pso.name'
        ));


        if ($options['sessionId']) {
            $select->where($this->quoteInto(
                'tcda.session_id = ?', $options['sessionId'])
            );
        }
        if ($options['category']) {
            $select->where($this->quoteInto(
                's.category = ?', $options['category'])
            );
        }
        $select->having("COUNT(tca.application_id) > 0");

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $department = $this->getService('Orgstructure')->getResponsibleDepartments();
            $select->where(
                    $this->quoteInto(
                        'tcda.department_id IN (?)',
                        $department
                    )
                );
        }

        return $select;
    }

    public function createTrainingApplications($session)
    {
        //выбираем оргструктуру с профилями и компетенциями/квалификациями
        $soidSelect = $this->getSelect();
        $soidSelect->from(
            array('so' => 'structure_of_organ'),
            array(
                'user_id'        => 'p.MID',
                'soid'           => 'so.soid',
                'department'     => new Zend_Db_Expr("so.owner_soid"),
                'sotype'         => 'so.type',
                'is_manager'     => 'so.is_manager',
                'profile_id'     => 'so.profile_id',
                'criterion_id'   => 'apcv.criterion_id',
                'criterion_type' => 'apcv.criterion_type',
                'importance'     => 'apcv.importance',
                'value'          => 'apcv.value',
                //'required'       => new Zend_db_Expr("COUNT(app.application_id)"),
                'city'           => new Zend_db_Expr("GROUP_CONCAT(c.classifier_id)"),
            ))
            ->joinInner(
                array('so2'    => 'structure_of_organ'),
                'so.owner_soid = so2.soid',
                array())
            ->joinInner(
                array('p'    => 'People'),
                'so.mid = p.MID',
                array())
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'cl.item_id = so.soid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
                array())
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array())
            ->joinInner(
                array('ap' => 'at_profiles'),
                'so.profile_id = ap.profile_id',
                array())
            ->joinLeft(
                array('apcv' => 'at_profile_criterion_values'),
                'so.profile_id = apcv.profile_id',
                array())
            /*->joinLeft(
                array('aet' => 'at_evaluation_type'),
                'aet.profile_id = so.profile_id AND aet.method = '. HM_At_Evaluation_EvaluationModel::TYPE_RATING,
                array())*/

            /*->joinLeft(
                array('app' => 'tc_applications'),
                'app.user_id=p.MID AND app.criterion_id=apcv.criterion_id
                AND app.criterion_type=apcv.criterion_type
                AND app.session_id=' . $session->session_id,
                array())*/
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

        //Фильтр по департаментам привязаным к сессии
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
        $soidSelect->where($structureWhere);
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

            if (($person['criterion_type'] == HM_At_Criterion_CriterionModel::TYPE_CORPORATE) && ($person['importance'] != HM_At_Criterion_CriterionModel::IMPORTANCE_MATTERS)) {
                continue;
            }

            if (!$profileCriterions[$person['profile_id']]) {
                $profileCriterions[$person['profile_id']] = array();
            }
            $profileCriterions[$person['profile_id']][$person['criterion_type']. "_" . $person['criterion_id']] = $person['value'] ? $person['value'] : 0;
            $users[$person['user_id']] = $person;
        }

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
        $userAtValues = array();
        foreach ($atValues as $atValue) {
            if (empty($userAtValues[$atValue['user_id']])) {
                $userAtValues[$atValue['user_id']] = array();
            }
            $userAtValues[$atValue['user_id']][$atValue['criterion_type'] . "_" . $atValue['criterion_id']] = $atValue['value'];
        }

        // выбираем парное сравнение
        $ratingValuesSelect = $this->getSelect();
        $ratingValuesSelect->from(array('s' => 'at_sessions'),
            array(
                's.end_date',
                'su.user_id',
                'sucv.criterion_id',
                'criterion_type' => new Zend_Db_Expr(HM_At_Criterion_CriterionModel::TYPE_CORPORATE),
                'value'=>'sucv.ratio',
            ))
            ->joinInner(
                array('su' => 'at_session_users'),
                's.session_id=su.session_id',
                array())
            ->joinInner(
                array('sucv' => 'at_session_pair_ratings'),
                'su.session_user_id=sucv.session_user_id',
                array())
            ->where('su.user_id in(?)', array_keys($users))
            ->order('s.end_date ASC');

        $ratingValues = $ratingValuesSelect->query()->fetchAll();
        foreach ($ratingValues as $ratingValue) {
            if($ratingValue['value'] >= HM_At_Session_Pair_Rating_RatingModel::RATIO_THRESHOLD_LO)
            {
                continue;
            }
            if (empty($userAtValues[$ratingValue['user_id']])) {
                $userAtValues[$ratingValue['user_id']] = array();
            }
            $userAtValues[$ratingValue['user_id']][$ratingValue['criterion_type'] . "_" . $ratingValue['criterion_id']] = $ratingValue['value'];
        }

        //выбираем список пройденных нашими пользователями курсов
        /*$graduatedSelect = $this->getSelect();
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
        $gradUsers = array();
        foreach ($gradData as $graduated) {
            if (!$gradUsers[$graduated['user_id']]) {
                $gradUsers[$graduated['user_id']] = array();
            }
            $gradUsers[$graduated['user_id']][$graduated['criterion']] = $graduated['graduated'];
        }*/

        $recomended      = array();
        $recomendedWhere = array();
        foreach ($users as $userId => $userData) {
            foreach ($profileCriterions[$userData['profile_id']] as $mixedKey => $goodValue) {
                list($crType, $crId) = explode('_', $mixedKey);

                if (/*(($crType == HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL)) &&*/
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
        /*$ratingSelect = $this->getService('Subject')->getSelect();
        $ratingSelect->from(
            array('s' => 'subjects'),
            array(
                'base_id'      => 's.base_id',
                'rating'       => new Zend_Db_Expr('COUNT(gr.mid) * AVG(gr.effectivity) * AVG(sv.value)')
            ))
            ->joinInner(
                array('gr' => 'graduated'),
                'gr.CID = s.subid',
                array())
            ->joinLeft(
                array('f' => 'tc_feedbacks'),
                'gr.CID = f.subject_id AND gr.MID=f.user_id',
                array())
            ->joinLeft(
                array('sv' => 'scale_values'),
                'sv.value_id = f.mark',
                array())
            ->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->group(array('s.base_id'));*/

        //курсы подходящие по компетенциям из УЦ специалиста
        $studyCenterId = $this->getService('ScManager')->getStudyCenter();
        $subSelectCources = $this->getSelect();
        $subSelectCources->from(
            array('sub' => 'subjects'),
            array(
                'subid'          => 'sub.subid',
                'name'           => 'sub.name',
                'provider_id'    => 'sub.provider_id',
                'criterion_id'   => 'sub.criterion_id',
                'criterion_type' => 'sub.criterion_type',
                'city'           => 'c.classifier_id',
                // 'price'          => 'sub.price',
                //'rating'         => new Zend_Db_Expr('AVG(rt.rating)')
            ))
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'cl.item_id = sub.subid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                array())
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array())
            ->joinLeft(
                array('tcps' => 'tc_providers_subjects'),
                'tcps.subject_id = sub.subid',
                array())
            ->where('sub.status != 0')
            ->where('sub.category = ' . HM_Tc_Subject_StudyCenter_SubjectModel::FULLTIME_CATEGORY_TRAINING)
            ->where('sub.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->where('sub.base != ? OR sub.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->where('sub.provider_type = ?', HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER)
            ->where('tcps.provider_id = ? OR tcps.provider_id IS NULL', $studyCenterId)
            ->where($recomendedWhere)
            ->group(array('sub.subid', 'sub.name', 'sub.provider_id', 'sub.criterion_id', 'sub.criterion_type', 'c.classifier_id', /*'sub.price'*/));
        //->order('sub.price');
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

        foreach ($recomended as $userId => $recomendedCouses) {
            $userCities = explode(',', $users[$userId]['city']);
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
                // тупо берем курс покрывающий компетенцию/квалификацию

                if ($courses) {
                    //$ratedCources   = array();

                    foreach ($courses as $course) {
                        if (($course['criterion_id'] != $criterionId) || ($course['criterion_type'] != $criterionType)) {
                            continue;
                        }
                        $subjectId      = $course['subid'];
                        /*if (is_array($gradUsers[$userId]) && in_array($course['subid'], $gradUsers[$userId])) {
                            continue;
                        }

                        $strKey = (in_array($course['city'], $userCities) ? '1' : '0')
                            . (empty($course['price']) ? '1' : '0');

                        if (empty($ratedCources[$strKey])) {
                            $ratedCources[$strKey] = array();
                        }
                        if (empty($ratedCources[$strKey][$course['rating']])) {
                            $ratedCources[$strKey][$course['rating']] = array();
                        }
                        $ratedCources[$strKey][$course['rating']][] = $course;*/
                    }

                    /*if ($ratedCources) {
                        krsort($ratedCources);
                        $bestPlaced = array_shift($ratedCources);
                        krsort($bestRated);
                        $bestRated = array_shift($bestPlaced);

                        $selectedCource = array_shift($bestRated);

                        $subjectId      = $selectedCource['subid'];
                        $providerId     = $selectedCource['provider_id'];
                    }*/

                }
                if (!$subjectId) {
                    continue;
                }

                if (!$sessionDepartments[$users[$userId]['department']]) {
                    continue;
                }

                // TODO раскидывание заявок по месяцам в зависимости от загруженности центра и пропускной способности
                $studyDate = $this->getStudyDate($session, 1);
                $studyMonth = $studyDate->toString(HM_Date::SQL_DATE);
                $departmentApplication = $this->checkDepartmentApplication(
                    $session->session_id,
                    $users[$userId]['department'],
                    $sessionDepartments[$users[$userId]['department']],
                    $users[$userId]['profile_id'],
                    $subjectId,
                    $studyMonth
                );

                $insert = array(
                    'session_id'            => $session->session_id,
                    'department_id'         => $users[$userId]['department'],
                    'session_department_id' => $sessionDepartments[$users[$userId]['department']],
                    'department_application_id' => $departmentApplication->department_application_id,
                    'user_id'               => $users[$userId]['user_id'],
                    'position_id'           => $users[$userId]['soid'],
                    'criterion_id'          => $criterionId,
                    'criterion_type'        => $criterionType,
                    'subject_id'            => $subjectId,
                    'provider_id'           => $session->provider_id,
                    'category'              => HM_Tc_Application_ApplicationModel::SC_CATEGORY_RECOMENDED,
                    'created'               => date('Y-m-d'),
                    'status'                => HM_Tc_Application_ApplicationModel::STATUS_INACTIVE,
                );

                $applicationsService->insert($insert);
            }
        }
    }

    /**
     * проверяем существует ли консолидированная заявка на этот курс для этого профиля
     *  1 консолидированная заявка на курс + профиль + месяц
     */
    public function checkDepartmentApplication($sessionId, $departmentId, $sessionDepartmentId, $profileId, $subjectId, $studyMonth, $cityClassifierId = null)
    {
        static $departmentApplications = null;
        if ($departmentApplications === null) {
            $departmentApplicationsCollection = $this->fetchAllDependence('TcApplication',
                $this->quoteInto('session_id = ?', $sessionId)
            );
            foreach ($departmentApplicationsCollection as $departmentApplicationItem)
            {
                if(!isset($departmentApplications[$departmentApplicationItem->department_id])) {
                    $departmentApplications[$departmentApplicationItem->department_id] = array();
                }
                if(!isset($departmentApplications[$departmentApplicationItem->department_id][$departmentApplicationItem->profile_id])) {
                    $departmentApplications[$departmentApplicationItem->department_id][$departmentApplicationItem->profile_id] = array();
                }
                if(!isset($departmentApplications[$departmentApplicationItem->department_id][$departmentApplicationItem->profile_id][$departmentApplicationItem->subject_id])) {
                    $departmentApplications[$departmentApplicationItem->department_id][$departmentApplicationItem->profile_id][$departmentApplicationItem->subject_id] = array();
                }
                $departmentApplications[$departmentApplicationItem->department_id][$departmentApplicationItem->profile_id][$departmentApplicationItem->subject_id][$departmentApplicationItem->study_month] = $departmentApplicationItem;
            }
        }

        if(!isset($departmentApplications[$departmentId][$profileId][$subjectId][$studyMonth])) {

            if (!$cityClassifierId) {
                $studyCenterId = $this->getService('ScManager')->getStudyCenter();
                $studyCenterCityClassifier = $this->getOne($this->getService('Classifier')->fetchAllJoinInner(
                    'ClassifierLink',
                    $this->quoteInto(
                        array(
                            'ClassifierLink.item_id = ? ',
                            ' AND self.type = ?',
                            ' AND ClassifierLink.type = ?'
                        ),
                        array(
                            $studyCenterId,
                            HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                            HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER
                        )
                    )
                ));
                $cityClassifierId = $studyCenterCityClassifier->classifier_id;
            }
            $values = array(
                'department_id' => $departmentId,
                'session_id' => $sessionId,
                'session_department_id' => $sessionDepartmentId,
                'subject_id' => $subjectId,
                'profile_id' => $profileId,
                'study_month' => date('Y-m-01', strtotime($studyMonth)),
                'is_offsite' => 0,
            );
            $departmentApplication = $this->insert($values);

            $departmentApplications[$departmentApplication->department_id][$departmentApplication->profile_id][$departmentApplication->subject_id][$departmentApplication->study_month] = $departmentApplication;
        }

        $resultDepartmentApplication = $departmentApplications[$departmentId][$profileId][$subjectId][$studyMonth];
        if($cityClassifierId) {
            $this->getService('ClassifierLink')->setClassifiers(
                $resultDepartmentApplication->department_application_id,
                HM_Classifier_Link_LinkModel::TYPE_TC_DEPARTMENT_APPLICATION,
                array($cityClassifierId)
            );
        }
        return $resultDepartmentApplication;
    }

    public function delete($id)
    {
        // при удалении консолидированной заявки сносим все персональные
        $this->getService('TcApplication')->deleteBy(
            $this->quoteInto('department_application_id = ?', $id)
        );

        return parent::delete($id);
    }

    protected function countApplicationsPerMonths($departmentId, $profileId)
    {
        static $applicationsPerMonths = array();
        if(!isset($applicationsPerMonths[$departmentId])) {
            $descendants = $this->getService('Orgstructure')->getDescendants($departmentId,true,HM_Orgstructure_OrgstructureModel::TYPE_POSITION);
            $select = $this->getSelect();
            $select->from(array('sohh' => 'structure_of_organ_hired_history'),array(
                'profile_id' => 'sohh.profile_id',
                'month' => new Zend_Db_Expr("MONTH(sohh.position_date)"),
                'count' => new Zend_Db_Expr("COUNT(sohh.history_id)")
            ));
            $now = new HM_Date();
            $minYear = new HM_Date();
            $minYear->subYear(self::PRIMARY_APPLICATION_CHECK_YEAR);
            $select->where('YEAR(sohh.position_date) >= ?', $minYear->toString('y'));
            $select->where('YEAR(sohh.position_date) < ?', $now->toString('y'));
            $select->where('position_id IN (?)', $descendants);
            $select->group(array('MONTH(sohh.position_date)', 'profile_id'));
            $result = $select->query()->fetchAll();
            $applicationsPerMonths[$departmentId] = array();
            foreach ($result as $item) {
                if(!$applicationsPerMonths[$departmentId][$item['profile_id']]) {
                    $applicationsPerMonths[$departmentId][$item['profile_id']] = array();
                }
                $applicationsPerMonths[$departmentId][$item['profile_id']][$item['month']] = ceil($item['count']/self::PRIMARY_APPLICATION_CHECK_YEAR);
            }
        }
        return $applicationsPerMonths[$departmentId][$profileId];
    }

    protected function getStudyDate($session, $studyMonth)
    {
        $studyDate = new HM_Date($session->cycle->current()->begin_date);
        $studyDate->setDay(01);
        $studyDate->setMonth($studyMonth);

        return $studyDate;
    }


}