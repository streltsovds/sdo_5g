<?php
class HM_Tc_Application_ApplicationService extends HM_Service_Abstract
{
    public function getListSource($sessionId = false, $additionalFields = array())
    {
        $arrayFields = array(
            'ap.application_id',
            'ap.user_id',
            'ap.session_department_id',
            'fio'           => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//            'position_full' => new Zend_Db_Expr("CONCAT(so.name, CONCAT( '/', CONCAT(so2.name, CONCAT('/', so3.name))))"),
            'position'      => 'so.name',
            'position_id' => 'so.soid',
            'is_manager' => 'so.is_manager',
            'department'      => 'so2.name', //new Zend_Db_Expr("CASE WHEN so.is_manager>0 THEN so3.name ELSE so2.name END"),
            'manager_id' => new Zend_Db_Expr('GROUP_CONCAT(som.mid)'),
            'user_city'     => new Zend_Db_Expr("GROUP_CONCAT(c.classifier_id)"),
            'criterion'     => new Zend_Db_Expr("CASE WHEN (ap.criterion_type = " . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION. ") THEN atc.name ELSE CASE WHEN (ap.criterion_type = " .HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST. ") THEN atct.name ELSE '' END END"),
            'ap.expire',
            'ap.period',
            'ap.status',
            'ap.subject_id',
            'subject_name'  => 's.name',
            'subject_city'  => new Zend_Db_Expr("GROUP_CONCAT(c2.classifier_id)"),
            's.longtime',
            's.price');

        $arrayGroupBy =array(
            'ap.application_id', 'ap.user_id', 'ap.subject_id', 'ap.status', 'ap.session_department_id',
            'ap.period', 'expire', 'ap.criterion_id', 'ap.criterion_type',
            'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',
            'so.soid', 'so.name', 'so.is_manager', 'so2.soid', 'so2.name', 'so3.soid', 'so3.name', 's.name', 's.longtime','s.price',
            'atc.name', 'atct.name');

        if (!empty($additionalFields)) {
            $arrayFields  = array_merge($arrayFields, $additionalFields);
            $arrayGroupBy = array_merge($arrayGroupBy, array_values($additionalFields));
        }


        $select = $this->getService('TcApplication')->getSelect();
        $select->from(
            array('ap' => 'tc_applications'),
            $arrayFields)
//            ->joinInner(
            ->joinLeft(
                array('p' => 'People'),
                'ap.user_id = p.MID',
                array())
//            ->joinInner(
            ->joinLeft(
                array('so' => 'structure_of_organ'),
                'so.MID = p.MID AND so.soid = ap.position_id',
                array())
//            ->joinInner(
            ->joinLeft(
                array('so2' => 'structure_of_organ'),
                'so.owner_soid = so2.soid',
                array())
            ->joinLeft(
                array('so3' => 'structure_of_organ'),
                'so2.owner_soid = so3.soid',
                array())
            ->joinLeft(
                array('som' => 'structure_of_organ'),
                'som.owner_soid = so2.soid AND som.is_manager ='.HM_Orgstructure_OrgstructureModel::MANAGER,
                array()
            )
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'cl.item_id = so.soid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
            ->joinLeft(
                array('cl2' => 'classifiers_links'),
                'cl2.item_id = ap.subject_id AND cl2.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                array()
            )
            ->joinLeft(
                array('c2' => 'classifiers'),
                'cl2.classifier_id = c2.classifier_id AND c2.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
            ->joinLeft(
                array('atc' => 'at_criteria'),
                'ap.criterion_id = atc.criterion_id AND ap.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION,
                array()
            )
            ->joinLeft(
                array('atct' => 'at_criteria_test'),
                'ap.criterion_id = atct.criterion_id AND ap.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST,
                array()
            )

            ->joinLeft(
                array('s' => 'subjects'),
                'ap.subject_id = s.subid',
                array())
// есть еще tc_applications на основе истории обучения (certificates)
// у них нет никаких критериёв
//            ->where('(atct.criterion_id is NOT NULL) OR (atc.criterion_id is NOT NULL)')
            ->group($arrayGroupBy);

        if ($sessionId) {
            $select->where('ap.session_id in (?)', $sessionId);
        }

        //Ограничение доступа для руководителя подразделения (ROLE_SUPERVISOR)
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $department = $this->getService('Orgstructure')->getResponsibleDepartment();
            if ($department) {
                $select
                    ->where('so.lft > ?', $department->lft)
                    ->where('so.rgt < ?', $department->rgt);
            }
        }

        return $select;
    }

    public function getClaimantQuarterListSource($options, $allWithHandled = false, $costItemToSelect = true)
    {
        $default = array(
            'sessionQuarterId'    => 0,
            'departmentId'        => 0,
            'sessionDepartmentId' => 0,
            'userIds'             => false,
            'status'              => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();

        $fields = array(
            'application_id' => 'tca.application_id',
            'session_quarter_id2' => 'tca.session_quarter_id',
            'subjectId' => 'tca.subject_id',
            'subject_status' => 's.status',
            'provider_status' => 'tcp.status',
            'MID'   => 'tca.user_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//            'position_full' => new Zend_Db_Expr("CONCAT(sop.name, CONCAT( '/', CONCAT(sod.name, CONCAT('/', sod2.name))))"),
            'position_id' => 'sop.soid',
            'is_manager' => 'sop.is_manager',
            'position' => 'sop.name',
            'department_name' => "sod.name",
            'manager_id' => new Zend_Db_Expr('GROUP_CONCAT(som.mid)'),
            'user_city' => new Zend_Db_Expr('GROUP_CONCAT(c1.classifier_id)'),
            'subject' => 's.name',
            'subject_city' => new Zend_Db_Expr('GROUP_CONCAT(c2.classifier_id)'),
            'provider_id' => 'tcp.provider_id',
            'provider_name' => 'tcp.name',
            'price' => 'tca.price',
            'format' => 's.format',
            'period' => 'tca.period',
            'longtime' => 's.longtime',
            'department_goal' => 'tca.department_goal',
            'education_goal' => 'tca.education_goal',
            'category' => 'tca.category',
            //'manager_fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pm.LastName, ' ') , pm.FirstName), ' '), pm.Patronymic)"),
            'cost_item' => 'tca.cost_item',
            'event_name' => 'tca.event_name',
            'application_status' => 'tca.status',
            'initiator' => 'tca.initiator',
            'payment_type' => 'tca.payment_type'
        );


        $select->from(array('tca'=>'tc_applications'),
            $fields
        );


        $select->joinLeft(
            array('sop' => 'structure_of_organ'),
            'sop.soid = tca.position_id AND sop.type = 1',
            array()
        );
        $select->joinLeft(
            array('p' => 'People'),
            'p.MID = tca.user_id',
            array()
        );

        $select->joinLeft(
            array('s' => 'subjects'),
            's.subid = tca.subject_id',
            array()
        );
        $select->joinLeft(
            array('tcp' => 'tc_providers'),
            'tcp.provider_id = s.provider_id',//tca
            array()
        );

        $select->joinLeft(
            array('sod' => 'structure_of_organ'),
            'sod.soid = tca.department_id',
            array()
        );
        $select->joinLeft(
            array('sod2' => 'structure_of_organ'),
            'sod2.soid = sod.owner_soid',
            array()
        );
        $select->joinLeft(
            array('som' => 'structure_of_organ'),
            'som.owner_soid = sod.soid AND som.is_manager ='.HM_Orgstructure_OrgstructureModel::MANAGER,
            array()
        );
        $select->joinLeft(
            array('cl1' => 'classifiers_links'),
            'cl1.item_id = tca.position_id AND cl1.type ='. HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
            array());
        $select->joinLeft(
            array('c1' => 'classifiers'),
            'cl1.classifier_id = c1.classifier_id AND c1.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
            array());

        $select->joinLeft(
            array('cl2' => 'classifiers_links'),
            'cl2.item_id = s.subid AND cl2.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array());
        $select->joinLeft(
            array('c2' => 'classifiers'),
            'cl2.classifier_id = c2.classifier_id AND c2.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
            array());

        if ($allWithHandled) {
            $select->joinLeft(
                array('st' => 'Students'),
                'st.application_id = tca.application_id',
                array());
        } else {
            // только необработанные - т.е. по которым еще не назначено обучение
            $select->joinLeft(
                array('st' => 'Students'),
                'st.application_id = tca.application_id',
                array())
            ->where('st.application_id IS NULL');
        }

        if ($costItemToSelect) $select->where('tca.cost_item != ? ', HM_Tc_Application_ApplicationModel::PROFCOM_COST_ITEM);


        $select->group(array(
                'tca.application_id',
                'tca.session_quarter_id',
                'tca.subject_id',
                's.status',
                'tcp.status',
                'tca.user_id',
                'sop.soid',
                'sop.is_manager',
                'sop.name',
                'sop.is_manager',
                's.name',
                's.format',
                'tcp.provider_id',
                'tcp.name',
                'tca.period',
                's.longtime',
                'tca.department_goal',
                'tca.education_goal',
                'tca.category',
                'sod.name',
                'sod2.name',
                'tca.cost_item',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'tca.event_name',
                'tca.price',
                'tca.status',
                'tca.initiator',
                'tca.payment_type',
            )
        );

        $select->where($this->quoteInto('tca.status in (?)', $options['status']));
        if ($options['sessionDepartmentId']) {
            if ($options['userIds']) {
                $select->where($this->quoteInto(
                    array('tca.session_department_id = ? AND (tca.user_id=0', ' OR p.MID in (?))'),
                    array($options['sessionDepartmentId'], $options['userIds'])));
            } else {
                $select->where($this->quoteInto('tca.session_department_id = ?', $options['sessionDepartmentId']));
            }
        } else {
            if ($options['sessionQuarterId']) {
                $select->where($this->quoteInto('tca.session_quarter_id in (?)', $options['sessionQuarterId']));
            }
            if ($options['departmentId']) {
                $select->where($this->quoteInto('tca.department_id in (?)', $options['departmentId']));
            }
        }

        return $select;
    }


    // для страницы "все персональные заявки"
    public function getClaimantListSource($options)
    {
        $default = array(
            'sessionId'           => 0,
            'departmentId'        => 0,
            'sessionDepartmentId' => 0,
            'userIds'             => false,
            'status'              => array(
                HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                HM_Tc_Application_ApplicationModel::STATUS_COMPLETE,
            )
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();

        $fields = array(
            'application_id' => 'tca.application_id',
            'subject_id' => 'tca.subject_id',
            'subject_status' => 's.status',
            'provider_status' => 'tcp.status',
            'MID'   => 'tca.user_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//            'position_full' => new Zend_Db_Expr("CONCAT(sop.name, CONCAT( '/', CONCAT(sod.name, CONCAT('/', son.name))))"),
            'department_name' => "sod.name", //"sod3.name",
            'subject' => 's.name',
            'provider_id' => 'tcp.provider_id',
            'provider_name' => 'tcp.name',
            'price' => 'tca.price',
            'period' => 'tca.period',
            'department' => 'son.name',
            'category' => 'tca.category',
            'cost_item' => 'tca.cost_item',
            'event_name' => 'tca.event_name',
            'initiator' => 'tca.initiator',
            'payment_type' => 'tca.payment_type',
            'previous_period' => new Zend_Db_Expr(0),
        );

        $select->from(array('tca'=>'tc_applications'),
            $fields
        );

        $select->joinLeft(
            array('sop' => 'structure_of_organ'),
            'sop.soid = tca.position_id AND sop.type = 1',
            array(
                'position_id' => 'soid',
                'is_manager' => 'is_manager'
            )
        );
        $select->joinLeft(
            array('p' => 'People'),
            'p.MID = tca.user_id',
            array()
        );

        $select->joinLeft(
            array('s' => 'subjects'),
            's.subid = tca.subject_id',
            array()
        );
        $select->joinLeft(
            array('tcp' => 'tc_providers'),
            'tcp.provider_id = tca.provider_id',//tca
            array()
        );

        $select->joinLeft(
            array('sod' => 'structure_of_organ'),
            'sod.soid = tca.department_id',
            array(
                'position' => 'sop.name',
            )
        );
        $select->joinLeft(
            array('sod2' => 'structure_of_organ'),
            'sod2.soid = sod.owner_soid',
            array()
        );
        $select->joinLeft(
            array('sod3' => 'structure_of_organ'),
            'sop.owner_soid = sod3.soid',
            array()
        );
        $select->joinLeft(
            array('som' => 'structure_of_organ'),
            'som.owner_soid = sod.soid AND som.is_manager ='.HM_Orgstructure_OrgstructureModel::MANAGER,
            array()
        );
        $select->joinLeft(
            array('tcsd' => 'tc_session_departments'),
            'tca.session_department_id = tcsd.session_department_id',
            array());
        $select->joinLeft(
            array('son' => 'structure_of_organ'),
            'son.soid = tcsd.department_id',
            array()
        );
        $select->joinLeft(
            array('c' => 'cycles'),
            'son.soid = tcsd.department_id',
            array()
        );


        $select->group(array(
                'tca.application_id',
                'tca.session_id',
                'tca.subject_id',
                's.status',
                'tcp.status',
                'tca.user_id',
                'sop.name',
                'sop.soid',
                'sop.is_manager',
                's.name',
                'tca.price',
                's.format',
                'tcp.provider_id',
                'tcp.name',
                'tca.period',
                's.longtime',
                'tca.department_goal',
                'tca.education_goal',
                'tca.category',
                'tca.event_name',
                'sod.name',
                'sod2.name',
                'sod3.name',
                'tca.cost_item',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'son.name',
                'tca.initiator',
                'tca.payment_type',
            )
        );

        $select->where($this->quoteInto('tca.status in (?)', $options['status']));

        if ($options['sessionDepartmentId']) {
            if ($options['userIds']) {
                $select->where($this->quoteInto(
                    array('tca.session_department_id = ? AND (tca.user_id=0', ' OR p.MID in (?))'),
                    array($options['sessionDepartmentId'], $options['userIds'])));
            } else {
                $select->where($this->quoteInto('tca.session_department_id = ?', $options['sessionDepartmentId']));
            }
        } else {
            if ($options['sessionId']) {
                $select->where($this->quoteInto('tca.session_id in (?)', $options['sessionId']));
            }
            if ($options['departmentId']) {
                $select->where($this->quoteInto('tca.department_id in (?)', $options['departmentId']));
            }
        }

        if ($options['department']) {
            $select
                ->where('sop.lft > ?', $options['department']->lft)
                ->where('sop.rgt < ?', $options['department']->rgt);
        }
        $s = $select->__toString();

        return $select;
    }

    public function delete($applicationId, $sendMessage = true) {
        $application = $this->getOne($this->find($applicationId));
        $result = false;
        if ($application) {
            switch ($application->category) {
//                case HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED:
//                    break;
                case HM_Tc_Application_ApplicationModel::CATEGORY_RECOMENDED:
                    $data = $application->getValues();
                    $data['status'] = 0;
                    $result = $this->update($data, false);
                    if($sendMessage){
                        $this->claimantChangeMessage($data, HM_Messenger::TEMPLATE_PERSONAL_CLAIMANT_DELETE);
                    }
                    break;
                default:
                    $data = $application->getValues();
                    if($sendMessage){
                        $this->claimantChangeMessage($data, HM_Messenger::TEMPLATE_PERSONAL_CLAIMANT_DELETE);
                    }
                    $result = parent::delete($applicationId);
            }
        }
        return $result;
    }
    
    public function update($data, $sendMessage = true)
    {
        parent::update($data);
        $application = $this->getOne($this->find($data['application_id']));
        $data = $application->getValues();
        if($sendMessage){
            $this->claimantChangeMessage($data, HM_Messenger::TEMPLATE_PERSONAL_CLAIMANT_EDIT);
        }
        return true;
    }
    
    public function claimantChangeMessage($data, $template)
    {
        try {
            
            $subjectService = $this->getService('Subject');
            $subject = $subjectService->fetchAll(array('subid = ?' => $data['subject_id']))->current();

            $userService = $this->getService('User');
            /** @var HM_User_UserModel $user */
            $user = $userService->fetchAll(array('MID = ?' => $data['user_id']))->current();

            $orgstructureService = $this->getService('Orgstructure');
            $sessionDepartment = $this->getService('TcSessionDepartment')->find($data['session_department_id'])->current();
            $sessionDepartmentManager = $orgstructureService->fetchAll(array(
                'owner_soid = ?' => $sessionDepartment->department_id,
                'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                'is_manager = ?' => HM_Orgstructure_OrgstructureModel::MANAGER
            ))->current();
            
            if($sessionDepartmentManager->mid != ''){
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    $template,
                    array(
                        'user_name'    => $user->MID ? $user->getName() : '',
                        'subject_name' => $subject->name
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $sessionDepartmentManager->mid);
            }
        } catch (Exception $exc) {
            Zend_Registry::get('log_system')->debug(var_export($exc, true));
        }
    }

    /**
     * @param mixed $departmentIds
     * @return Zend_Db_Table_Select
     */
    public function getExpireRequiredList($departmentIds = false)
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

        $select->from(
            array('so' => 'structure_of_organ'),
            array(
                'user_id'         => 'p.MID',
                'fio'             => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//                'position'        => 'so.name',
//                'soid'            => 'so.soid',
//                'department'      => new Zend_Db_Expr("CASE WHEN so.is_manager>0 THEN so2.owner_soid ELSE so.owner_soid END"),
                'department_name' => new Zend_Db_Expr("CASE WHEN so.is_manager>0 THEN so3.name ELSE so2.name END"),
//                'sotype'          => 'so.type',
//                'is_manager'      => 'so.is_manager',
//                'profile_id'      => 'ap.profile_id',
//                'profile'         => 'ap.name',
//                'criterion_id'    => 'apcv.criterion_id',
//                'criterion_type'  => 'apcv.criterion_type',
                'criterion'       => 'act.name',
                'validity'        => 'act.validity',
                'lastcourse'      => 'grad.lastcourse',
                'expire'          => 'app.expire',
                //'expire'          => new Zend_Db_Expr("CASE WHEN act.validity=0 THEN 0 ELSE CASE WHEN grad.lastcourse is NULL THEN 1 ELSE DATE_ADD(month, act.validity, grad.lastcourse) END END"),
                'period'          => 'app.period',
                'subid'           => 's.subid',
                'subject_name'    => 's.name',
                'providerid'      => 'tcp.provider_id',
                'provider'        => 'tcp.name',
            ))
            ->joinInner(
                array('so2'    => 'structure_of_organ'),
                'so.owner_soid = so2.soid',
                array())
            ->joinLeft(
                array('so3'    => 'structure_of_organ'),
                'so2.owner_soid = so3.soid',
                array())
            ->joinInner(
                array('p'    => 'People'),
                'so.mid = p.MID',
                array())
            ->joinInner(
                array('cl' => 'classifiers_links'),
                'cl.item_id = so.soid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
                array()
            )
            ->joinInner(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
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
                array('app' => 'tc_applications'),
                'act.criterion_id=app.criterion_id AND app.user_id=p.mid AND (grad.lastcourse is NULL OR app.period>grad.lastcourse)',
                array())
            ->joinLeft(
                array('s' => 'subjects'),
                's.subid=app.subject_id',
                array())
            ->joinLeft(
                array('tcp' => 'tc_providers'),
                's.provider_id=tcp.provider_id',
                array())
            ->where('p.blocked = 0 OR p.blocked IS NULL')
            ->where('apcv.criterion_type=' . HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL)
            ->where('act.required = 1')
            ->where('act.employee_type = ' . HM_At_Criterion_Test_TestModel::EMPLOYEE_TYPE_EMPLOYEE)
            ->where('grad.lastcourse is NULL OR act.validity > 0')
            ->where('so.blocked   = 0')
            ->group(array(
                'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',
                'so.soid', 'so.name', 'so.type', 'so.is_manager', 'so.owner_soid', 'so2.owner_soid', 'so2.name', 'so3.name',
                'ap.profile_id', 'ap.name', 'apcv.criterion_id', 'apcv.criterion_type',
                'act.name', 'act.validity', 'grad.lastcourse',
                'app.expire', 'app.period', 's.subid', 's.name', 'tcp.provider_id', 'tcp.name'
            ));

        //Фильтр по дате истечения
        $expire = date('Y') * 12 + date('m') + 2;
        $select->where('grad.lastcourse is NULL OR (YEAR(grad.lastcourse)*12 + MONTH(grad.lastcourse)+act.validity < ?)', $expire);

        //Фильтр по области ответственноcти
        if ($departmentIds) {
            $departments   = $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto(
                    array('soid in (?)'),
                    array($departmentIds)
                ));

            $whereDepartment = array();
            foreach ($departments as $department) {
                $whereDepartment[] = '(so.lft > ' . $department->lft . ' AND so.rgt < ' . $department->rgt . ')';
            }

            if ($whereDepartment) {
                $select->where(implode(' OR ', $whereDepartment));
            }
        }

        return $select;
    }

    public function getScListSource($sessionId = false, $additionalFields = array())
    {
        $arrayFields = array(
            'ap.application_id',
            'ap.user_id',
            'ap.session_department_id',
            'fio'           => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'department'      => 'so2.name ',
            'parent_department_name'      => 'pso.name ',
            'position'      => 'so.name',
            //ненене, Девид Блейн, нафиг эту наркоманию, есть вьюшка, ее цепляем
            //'criterion'     => new Zend_Db_Expr("CASE WHEN (ap.criterion_type = " . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION. ") THEN atc.name ELSE CASE WHEN (ap.criterion_type = " .HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST. ") THEN atct.name ELSE '' END END"),
            'criterion'     => "cr.name",
            'ap.expire',
            //'ap.period',
            'ap.status',
            'ap.subject_id',
            'subject_name'  => 's.name',
            'threshold_level' => 'atpcv.value',
            'at_result' => 'atr.value',
            'longtime' => new Zend_Db_Expr("s.theory_longtime + s.job_longtime"),
            'category' => 's.category',
            'subject_city'  => new Zend_Db_Expr("GROUP_CONCAT(c2.classifier_id)"),
            'is_st'  => new Zend_Db_Expr("COUNT(std.SID)"),
            'is_gr'  => new Zend_Db_Expr("COUNT(gr.SID)"),
            );
        $arrayGroupBy =array(
            'ap.application_id', 'ap.user_id', 'ap.subject_id', 'ap.status', 'ap.session_department_id',
            'expire', 'ap.criterion_id', 'ap.criterion_type',
            'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic',
            'so.soid', 'so.name', 'so.is_manager', 'so2.soid', 'so2.name', 's.name','s.category', 's.longtime','s.price',
            'cr.name', new Zend_Db_Expr("s.theory_longtime + s.job_longtime"),
            'atpcv.value','atr.value','pso.name '

        );

        if (!empty($additionalFields)) {
            $arrayFields  = array_merge($arrayFields, $additionalFields);
            $arrayGroupBy = array_merge($arrayGroupBy, array_values($additionalFields));
        }

        $userResultSelect = $this->getService('TcApplication')->getSelect();
        $latestUserResultSelect = $this->getService('TcApplication')->getSelect();
        $latestUserResultSelect->from(
            array('atsu2' => 'at_session_users'),
            array(
                'atsu2.user_id',
                'session_user_id' => new Zend_Db_Expr("MAX(atsu2.session_user_id)")
            )
        )->group(array('atsu2.user_id'));

        $userResultSelect->from(
            array('atsucv' => 'at_session_user_criterion_values'),
            array(
                'user_id'=>'maxsuid.user_id',
                'criterion_type'=>'atsucv.criterion_type',
                'criterion_id' => 'atsucv.criterion_id',
                'value' => 'atsucv.value'
            )
        )->joinInner(
            array('maxsuid' => $latestUserResultSelect),
            'maxsuid.session_user_id = atsucv.session_user_id',
            array()
        );
        $select = $this->getService('TcApplication')->getSelect();
        $select->from(
            array('ap' => 'tc_applications'),
            $arrayFields)
            ->joinInner(
                array('p' => 'People'),
                'ap.user_id = p.MID',
                array())
            ->joinInner(
                array('so' => 'structure_of_organ'),
                'so.MID = p.MID AND so.soid = ap.position_id',
                array())
            ->joinInner(
                array('so2' => 'structure_of_organ'),
                'so.owner_soid = so2.soid',
                array())
            ->joinInner(
                array('pso' => 'structure_of_organ'),
                'so2.owner_soid = pso.soid',
                array())
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'cl.item_id = so.soid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
            ->joinLeft(
                array('cl2' => 'classifiers_links'),
                'cl2.item_id = ap.subject_id AND cl2.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                array()
            )
            ->joinLeft(
                array('c2' => 'classifiers'),
                'cl2.classifier_id = c2.classifier_id AND c2.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
            ->joinLeft(
                array('cr' => 'criteria'),
                'ap.criterion_id = cr.criterion_id AND ap.criterion_type=cr.criterion_type',
                array()
            )
            ->joinLeft(
                array('tcda' => 'tc_department_applications'),
                'tcda.department_application_id =  ap.department_application_id',
                array()
            )
            ->joinLeft(
                array('s' => 'subjects'),
                'ap.subject_id = s.subid',
                array()
            )
            ->joinLeft(
                array('atpcv' => 'at_profile_criterion_values'),
                'ap.criterion_type = atpcv.criterion_type AND ap.criterion_id = atpcv.criterion_id AND tcda.profile_id = atpcv.profile_id',
                array()
            )
            ->joinLeft(
                array('ss' => 'subjects'),
                'ss.base_id = ap.subject_id',
                array()
            )
            ->joinLeft(
                array('std' => 'Students'),
                '(std.CID = ss.subid AND std.MID = ap.user_id)',
                array()
            )
            ->joinLeft(
                array('gr' => 'graduated'),
                '(gr.CID = ss.subid AND gr.MID = ap.user_id)',
                array()
            )
            ->joinLeft(
                array('atr' => $userResultSelect),
                '(atr.user_id = ap.user_id AND ap.criterion_type = atr.criterion_type AND ap.criterion_id = atr.criterion_id)',
                array()
            )
            ->where('cr.criterion_id is NOT NULL')
            ->group($arrayGroupBy);

        if ($sessionId) {
            $select->where('ap.session_id in (?)', $sessionId);
        }

        //Ограничение доступа для руководителя подразделения (ROLE_SUPERVISOR)
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $slavesIds = $this->getService('Supervisor')->getDirectSlaves($this->getService('User')->getCurrentUserId());
            if (empty($slavesIds)) {
                $slavesIds = array(0);
            }
            $select->where('ap.user_id in (?)', $slavesIds);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $departmentIds = $this->getService('Orgstructure')->getResponsibleDepartments();
            if (!empty($departmentIds)) {
                $select->where('ap.department_id in (?)', $departmentIds);
            }
        }

        return $select;
    }

    public function getYearPlanArray($sessionId, $status)
    {
        $options = array(
            'sessionId'  => $sessionId,
            'status'     => $status
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $this->_department = $this->getService('Orgstructure')->getResponsibleDepartment();
            $options['department'] = $this->_department;
        }

        $listSource = $this->getClaimantListSource($options);
        $listSource->joinLeft(array('pr' => 'at_profiles'), 'pr.profile_id = sop.profile_id', array());
        $listSource->joinLeft(array('cat' => 'at_categories'), 'cat.category_id=pr.category_id', array(
            'category'       => new Zend_Db_Expr('max(cat.name)'),
            'at_category_id' => 'cat.category_id',
        ));
        $listSource->joinLeft(
            array('cl3' => 'classifiers_links'),
            'cl3.item_id = s.subid AND cl3.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array());
        $listSource->joinLeft(
            array('c3' => 'classifiers'),
            'c3.classifier_id = s.direction_id AND c3.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
            array(
                'subject_direction'=>new Zend_Db_Expr('GROUP_CONCAT(DISTINCT(c3.name))')));
        $listSource->group(array('cat.category_id'));

        $sourceData = $listSource->query()->fetchAll();

        return $sourceData;
    }

    public function getYearFactArray()
    {
        $select = $this->getService('TcSubjectActualCosts')->getActualCostsIndexSelect();
        $select->joinLeft(
            array('c1' => 'cycles'),
            'c1.cycle_id = c.cycle_id',
            array(
                'cycle_id' => new Zend_Db_Expr('c1.cycle_id'),
                'year' => new Zend_Db_Expr('c1.year'),
                'quarter' => new Zend_Db_Expr('c1.quarter')
            )
        );
        $select->joinLeft(
            array('s' => 'subjects'),
            'ac.subject_id = s.subid',
            array()
        );
        $select->joinLeft(
            array('ss' => 'subjects'),
            'ss.base_id = s.subid',
            array()
        );
        $select->joinLeft(
            array('su' => 'subjects_users'),
            'ss.subid = su.subject_id AND su.status =' . HM_At_Session_User_UserModel::STATUS_COMPLETED,
            array(
                'MID' => new Zend_Db_Expr('su.user_id')
            )
        );
        $select->joinLeft(
            array('so' => 'structure_of_organ'),
            'so.mid = su.user_id',
            array()
        );
        $select->joinLeft(
            array('ap' => 'at_profiles'),
            'ap.profile_id = so.profile_id',
            array()
        );
        $select->joinLeft(
            array('cat' => 'at_categories'),
            'ap.category_id = cat.category_id',
            array(
                'at_category_id' => new Zend_Db_Expr('cat.category_id')
            )
        );
        $select->joinLeft(
            array('cls' => 'classifiers'),
            'cls.classifier_id = s.direction_id AND cls.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
            array(
                'subject_direction'=>new Zend_Db_Expr('GROUP_CONCAT(cls.name)')));
        $select->group(
            array(
                'su.user_id',
                'ac.actual_cost_id',
                'c.name',
                'pr.name',
                'ac.document_number',
                'ac.pay_date_document',
                'ac.pay_date_actual',
                'ac.pay_amount',
                'ac.subject_id',
                'su.user_id',
                'c1.cycle_id',
                'c1.year',
                'c1.quarter',
                'cat.category_id',
                'ss.base_id'
            )
        );

        $factData = $select->query()->fetchAll();

        return $factData;
    }

    /*  общие методы для формирования списков значений в разных формах */

    public function getSessionPeriodsForForm($session, $application = false)
    {
        $periods = array();
        $sessionStart = date('Y-m-01', strtotime($session->cycle->current()->begin_date));
        $sessionEnd   = $session->cycle->current()->end_date;

        // если это обязательное обучение - можно назначать только до срока истечения
        if ($application && $application->expire) {
            $dateExpire = new DateTime($application->expire);
            $lastDate   = $dateExpire->sub(new DateInterval('P2M'))->format('Y-m-d');
            $sessionEnd = ($lastDate > $sessionStart) ? $lastDate : $sessionStart;
        }

        while ($sessionStart <= $sessionEnd) {
            $periods[$sessionStart] = $this->_getMonthDate($sessionStart, false);
            $sessionStart = date('Y-m-01', strtotime($sessionStart) + 60*60*24*33);
        }
        return $periods;
    }

    public function getCostItemsForForm()
    {
        // только статьи, по которым учатся на курсах
        $costItems = array_intersect_key(
            HM_Tc_Application_ApplicationModel::getCostItems(),
            HM_Tc_Application_ApplicationModel::getCostItemsSubject()
        );

        return array(0 => '') + $costItems;
    }

    public function getDepartmentUsersForForm($application)
    {
        $users = array();
        
        $currentUserId = $this->getService('User')->getCurrentUserId();
        
        // Получаем заявку
        $application = $this->getService('TcApplication')->find($application->application_id)->current();
        
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $depId = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $currentUserId))->current();
            $depId = $depId->owner_soid;
        } else {
            $depId = $application->department_id;
        }
        
        $subDeps = $this->getService('Orgstructure')->getDescendants($depId, false, HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
        $depIds  = array_unique(array_merge(array($depId), $subDeps));
        $department  = $this->getService('Orgstructure')->find($application->department_id)->current();

        // Получаем членов подразделения вместе с вложенными
        $departmentUsers = array();
        foreach ($depIds as $depId) {
            $departmentUsers += $this->getService('Orgstructure')->fetchAll(
                array(
                    'owner_soid = ?' => $depId,
                    'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'blocked = ?' => 0
                )
            )->getList('mid');
        }

        $userModels = $this->getService('User')->fetchAll(
            array('MID IN (?)' => empty($departmentUsers) ? array(0) : $departmentUsers)
        );

        foreach ($userModels as $user) {
            $users[$user->MID] = sprintf('%s %s %s', $user->LastName, $user->FirstName, $user->Patronymic);
        }

        return $users;

    }

    // @todo: очень сложно, можно рефакторить
    public function getUsersForForm($session)
    {
        $users = array();
        $sessionId = $session->session_id;

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {

            $slaveIds = $this->getService('Supervisor')->getSlaves($this->getService('User')->getCurrentUserId());
            if (!$slaveIds) {
                return $users;
            }
            $whereDepartment = array();
            $departments   = $this->getService('Orgstructure')->fetchAllJoinInner('TcSessionDepartment', 'TcSessionDepartment.session_id=' . $sessionId);
            if (!$departments) {
                return $users;
            }

            foreach ($departments as $department) {
                $whereDepartment[] = '(so.lft > ' . $department->lft . ' AND so.rgt < ' . $department->rgt . ')';
            }

            $select = $this->getService('User')->getSelect();
            $select->from(
                array('p' => 'People'),
                array(
                    'p.MID',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                ))
                ->joinInner(
                    array('so' => 'structure_of_organ'),
                    'so.MID=p.MID',
                    array())
                ->where(implode(" OR ", $whereDepartment))
                ->where('so.blocked=0')
                ->order(array('LastName', 'FirstName'));

            if ($slaveIds) {
                $select->where('p.MID in (?)', array($slaveIds));
            }

            $departmentUsers = $select->query()->fetchAll();
            foreach ($departmentUsers as $user) {
                $users[$user['MID']] = $user['fio'];
            }
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

            $select = $this->getService('Orgstructure')->getSelect();
            $select->from(
                array('s' => 'structure_of_organ'),
                array(
                    's.soid',
                    's.lft',
                    's.rgt',
                ))
                ->joinInner(
                    array('tcsd' => 'tc_session_departments'),
                    'tcsd.department_id=s.soid',
                    array())
                ->joinInner(
                    array('sop' => 'state_of_process'),
                    'sop.item_id=tcsd.session_department_id and sop.process_type=' . HM_Process_ProcessModel::PROCESS_TC_SESSION_DEPARTMENT,
                    array())
                ->where("sop.current_state <>'HM_Tc_Session_Department_State_Complete'")
                ->where("sop.status <> ?", HM_Process_Abstract::PROCESS_STATUS_FAILED)
                ->where('tcsd.session_id=?', $sessionId);

            $departmentIds = $this->getService('Orgstructure')->getResponsibleDepartments();
            if (count($departmentIds)) {
                $select->where('tcsd.department_id in (?)', $departmentIds);
            }

            $departments = $select->query()->fetchAll();
            $whereDepartment = array();
            foreach ($departments as $department) {
                $whereDepartment[] = '(so.lft > ' . $department['lft'] . ' AND so.rgt < ' . $department['rgt'] . ')';
            }

            $select = $this->getService('User')->getSelect();
            $select->from(
                array('p' => 'People'),
                array(
                    'p.MID',
                    'department_id' => new Zend_Db_Expr('CASE WHEN so.is_manager > 0 THEN so3.soid ELSE so2.soid END'),
                    'department' => new Zend_Db_Expr('CASE WHEN so.is_manager > 0 THEN so3.name ELSE so2.name END'),
                    'position' => 'so.name',
                    'p.LastName',
                    'p.FirstName',
                    'p.Patronymic'
                ))
                ->joinInner(
                    array('so' => 'structure_of_organ'),
                    'so.MID=p.MID',
                    array())
                ->joinInner(
                    array('so2' => 'structure_of_organ'),
                    'so2.soid=so.owner_soid',
                    array())
                ->joinInner(
                    array('so3' => 'structure_of_organ'),
                    'so3.soid=so2.owner_soid',
                    array())
//                ->where(new Zend_Db_Expr('CASE WHEN so.is_manager > 0 THEN so3.soid ELSE so2.soid END in (?)'), $whereDepartment)
                ->where('so.blocked=0')
                ->order(array('LastName', 'FirstName', 'department_id'));

            if (count($whereDepartment)) {
                $select->where(implode(" OR ", $whereDepartment));
            }

            $departmentUsers    = $select->query()->fetchAll();
            foreach ($departmentUsers as $user) {
                $users[$user['MID']] = sprintf('%s %s %s (%s, %s)', $user['LastName'], $user['FirstName'], $user['Patronymic'], $user['department'], $user['position']);
            }
        }

        return array_unique($users);

    }

    public function getSubjectsForForm($subjectCategory = false)
    {
        $coursesSelect = $this->getService('Subject')->getSelect();
        $coursesSelect->from(
            array('s' => 'subjects'),
            array(
                'subject_id'    => 's.subid',
                's.provider_id',
                'subject_name'  => 's.name',
                'provider_name' => 'pr.name',
                'city' => 'c.name'
            ))
            ->joinLeft(
                array('pr' => 'tc_providers'),
                's.provider_id=pr.provider_id',
                array())
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'cl.item_id = s.subid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->where('s.base IS NULL OR s.base !=?', HM_Subject_SubjectModel::BASETYPE_SESSION)
            ->order('pr.name', 's.name');

        if ($subjectCategory) $coursesSelect->where('s.category = ?', $subjectCategory);

        $coursesResult = $coursesSelect->query()->fetchAll();
        $courses = array(0 => '');
        foreach ($coursesResult as $course) {

            $providerName = trim($course['provider_name']);
            $subjectName = trim($course['subject_name']);

            if (empty($providerName)) $providerName = _('[провайдер не указан]');
            if (!isset($courses[$providerName])) {
                $courses[$providerName] = array();
            }
            if (isset($courses[$providerName][$course['subject_id']] )) {
                $courses[$providerName][$course['subject_id']] =
                    substr($courses[$providerName][$course['subject_id']], 0, -1)
                    . ", " . $course['city'] . ")";
            } else {
                $courses[$providerName][$course['subject_id']] = $subjectName; // . " (" . $course['city'] . ")";
            }

            asort($courses[$providerName]);
        }

        ksort($courses);

        return $courses;
    }


    public function getSimilarSubjectsForForm($application)
    {
        $courses = array();
        if (!$application) return $courses;

        if ($application->subject_id) {
            $subject = $this->getService('Subject')->getOne(
                $this->getService('Subject')->findDependence('TcProvider', $application->subject_id)
            );
            if ($subject && count($subject->tcProvider)) {
                $provider = $subject->tcProvider->current();
                $courses[$provider->name][$subject->subid] = $subject->name;
            }
        }

        $coursesSelect = $this->getService('TcSubject')->getSelect();
        $coursesSelect->from(
            array('s' => 'subjects'),
            array(
                'subject_id'    => 's.subid',
                's.provider_id',
                'subject_name'  => 's.name',
                'provider_name' => 'pr.name',
                'city' => 'c.name'
            ))
            ->joinLeft(
                array('pr' => 'tc_providers'),
                's.provider_id=pr.provider_id',
                array())
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                'cl.item_id = s.subid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array()
            )
            ->where('s.category = ?', $application->category)
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->where('s.base IS NULL OR s.base !=?', HM_Subject_SubjectModel::BASETYPE_SESSION)
            ->where('s.status != 0');

        if ($application->criterion_type && $application->criterion_id) {
            $coursesSelect
                ->where('s.criterion_type = ?', $application->criterion_type)
                ->where('s.criterion_id = ?', $application->criterion_id);
        }

        // @todo: возможно нам нужен централизованный метод,
        // запрещающий создание заявки для определенного чела по определенному курсу
        if ($application->category != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {

            // курсы, на которых уже учился, заново не предлагаем
            // включая аналогичные у других провайдеров
            $graduatedSelect = $this->getService('Graduated')->getSelect();
            $graduatedSelect->from(
                array('gr' => 'graduated'),
                array('sub.base_id'))
                ->joinInner(
                    array('sub' => 'subjects'),
                    'gr.CID=sub.subid',
                    array())
                ->where('gr.MID=?', $application->user_id)
                ->group(array('sub.base_id', 'sub.criterion_id', 'sub.criterion_type', 'gr.MID'));

            if ($application->criterion_type && $application->criterion_id) {
                $graduatedSelect->where('sub.criterion_type = ?', $application->criterion_type)
                    ->where('sub.criterion_id = ?', $application->criterion_id);
            }

            $coursesSelect->where('s.subid NOT IN ?', $graduatedSelect);
        }

        $coursesResult = $coursesSelect->query()->fetchAll();
        foreach ($coursesResult as $course) {

            $providerName = trim($course['provider_name']);
            $subjectName = trim($course['subject_name']);

            if (!isset($courses[$providerName])) {
                $courses[$providerName] = array();
            }
            if (!isset($courses[$providerName][$subjectName] )) {
                $courses[$providerName][$course['subject_id']] = $subjectName;
            }

            asort($courses[$providerName]);
        }

        ksort($courses);

        return $courses;
    }


    protected function _getMonthDate($date, $checkSession = true)
    {
        $tst = strtotime($date);
        if (!$date || !$tst || (date('Y-m-d', $tst) == '1900-01-01')) {
            return '';
        }
        if (($checkSession && $date<$this->_session->date_begin)) {
            return '';
        }

        return month_name((int) date('m', $tst)) . " " . date('Y', $tst);
    }
}