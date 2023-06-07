<?php

class HM_Tc_Subject_SubjectService extends HM_Subject_SubjectService
{
    public function getListOfNewSubjectsSource($sessionId = 0)
    {
        $subjectService = $this->getService('TcSubject');
        $classifierLinkService = $this->getService('ClassifierLink');
        $classifierService = $this->getService('Classifier');

        $select = $this->getSelect();

        $select
            ->from(array('s' => 'subjects'), array(
                's.subid',
                's.name',
                'status' => 's.status',
                'provider_id' => 'pr.provider_id',
                'provider_name' => 'pr.name',
                'cities' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.classifier_id)'),
                'created_by' => new Zend_Db_Expr('s.created_by'),
                'created_by_name' => new Zend_Db_Expr("CONCAT(p.LastName, CONCAT(' ', p.FirstName))"),
                'applications_count' => new Zend_Db_Expr('COUNT(DISTINCT a.application_id)'),
            ))
            ->joinLeft(array('pr' => 'tc_providers'), 'pr.provider_id = s.provider_id', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = s.created_by', array())
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                $classifierLinkService->quoteInto('cl.item_id = s.subid AND cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_SUBJECT),
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                $classifierService->quoteInto('c.classifier_id = cl.classifier_id AND c.type = ?', 1),
                array()
            )
            ->joinLeft(
                array('a' => 'tc_applications'),
                's.subid = a.subject_id',
                array()
            )
            ->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_BASE)
            //->where('s.status = ?', HM_Tc_Provider_ProviderModel::STATUS_NOT_PUBLISHED)
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->group(array(
                's.subid',
                's.name',
                's.status',
                's.created_by',
                's.create_from_tc_session',
                'pr.provider_id',
                'pr.name',
                'p.LastName',
                'p.FirstName',
            ));

        if ($sessionId) {
            $select->where('s.create_from_tc_session=?', $sessionId);
        }

        return $select;

    }

    public function findSubjectsForAutoComplete($search, $limit = 20, $addProviderName = false)
    {
        if (strlen($search) < 3) {
            return array();
        }

        $select = $this->getSelect();

        $select
            ->from(array('s' => 'subjects'), array(
                's.subid',
                's.name',
                'provider_name' => 'p.name'
            ))
            ->join(array('p' => 'tc_providers'), 'p.provider_id = s.provider_id', array())
            ->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_BASE)
            ->where('s.status = ?', 1)
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->where('LOWER(s.name) LIKE ?', '%' . strtolower($search) . '%')
            ->limit($limit);

        $rows = $select->query()->fetchAll();

        $result = array();

        foreach ($rows as $row) {

            $item = array(
                'value' => $row['subid'],
                'key' => $row['name']
            );

            if ($addProviderName) {
                $item['key'] .= ' (' . $row['provider_name'] . ')';
            }

            $result[] = $item;
        }

        return $result;

    }

    /**
     * @param HM_Subject_SubjectModel $targetSubject
     * @param $subjectIds
     */
    public function concatenate($targetSubject, $subjectIds)
    {
        /** @var HM_Tc_Application_ApplicationService $applicationService */
        $applicationService = $this->getService('TcApplication');

        $targetSubjectId = $targetSubject->subid;
        $targetProviderId = $targetSubject->provider_id;

        foreach ($subjectIds as $subjectId) {

            $where = $this->quoteInto(
                array('subject_id = ?'),
                array($subjectId)
            );

            $data = array(
                'subject_id' => $targetSubjectId,
                'provider_id' => $targetProviderId
            );

            $applicationService->updateWhere($data, $where);

            $this->delete($subjectId);

        }
    }

    public function getScListSource($options)
    {
        $default = array(
            'subjectId'   => 0,
            'providerId'  => 0,
        );
        $options = array_merge($default, $options);

        $select = $this->getSelect();

        /*$citiesSelect = clone $select;
        $citiesSelect
            ->from(
                array('cl' => 'classifiers_links'),
                array('cl.classifier_id', 'cl.item_id', 'cl.type'))
            ->joinInner(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array());
         */
        $select->from(array('s' => 'subjects'),
            array(
                'created_by'   => 's.created_by',
                'subid'        => 's.subid',
                'subject_name' => 's.name',
                'base_type'    => 's.base',
                'begin'     => 's.begin',
                'end' => 's.end',
                'programm' => 'pr.name',
                'category'     => 's.category',
                'education_type' => 's.education_type',
                'provider_type' => 's.provider_type',
                'tcprovider'   => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT tcps.provider_id)"),
                'provider'     => new Zend_Db_Expr("''"),
                'status'       => 's.status',
                'format'       => 's.format',
                'competence'   => new Zend_Db_Expr("CASE WHEN (s.criterion_type = " . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION. ") THEN atc.name ELSE CASE WHEN (s.criterion_type = " .HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST. ") THEN atct.name ELSE '' END END"),
                //'city'         => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl.classifier_id)'),
                'price'        => 's.price',
                'duration'     => new Zend_Db_Expr("s.theory_longtime + s.job_longtime"),
                'tags'         => 's.subid',
                'teachers'     => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tpts.MID)'),
                'min_users' => 's.min_users',
                'max_users' => 's.max_users',
                'students'     => new Zend_Db_Expr('COUNT(DISTINCT st.MID)'),
                'group_number' => 's.group_number'
            )
        )
        ->joinLeft(
            array('tcps' => 'tc_providers_subjects'),
            's.subid = tcps.subject_id',
            array()
        )
        ->joinLeft(
            array('st' => 'students'),
            's.subid = st.CID',
            array()
        )
        /*->joinLeft(
            array('cl' => $citiesSelect),
            'cl.item_id = s.subid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array()
        )*/
        ->joinLeft(
            array('atc' => 'at_criteria'),
            's.criterion_id = atc.criterion_id AND s.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION,
            array()
        )
        ->joinLeft(
            array('atct' => 'at_criteria_test'),
            's.criterion_id = atct.criterion_id AND s.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST,
            array()
        )
        /*->joinLeft(
            array('tpts' => 'tc_provider_teachers2subjects'),
            'tpts.subject_id = s.subid',
            array()
        )*/
        ->joinLeft(
            array('tpts' => 'Teachers'),
            'tpts.CID = s.subid',
            array()
        )
        ->joinLeft(
            array('pe' => 'programm_events'),
            'pe.item_id = s.subid AND pe.type ='.HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
            array()
        )
        ->joinLeft(
            array('pr' => 'programm'),
            'pr.programm_id = pe.programm_id',
            array()
        );
        // костыль из-за того, что при удалении последней сессии бейзтайп курса становится практис
        if($options['base_type'] == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $select->where('s.base = ?', $options['base_type']);
        } else {
            $select->where("(s.base = ? OR s.base = ".HM_Subject_SubjectModel::BASETYPE_PRACTICE. ")", $options['base_type']);
        }
        $select->where('provider_type = ?', HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER);

        $select->group(array(
            's.subid',
            's.created_by',
            's.name',
            's.category',
            's.criterion_type',
            's.provider_type',
            's.education_type',
            's.theory_longtime',
            's.job_longtime',
            'atc.name',
            'atct.name',
            's.begin',
            's.end',
            's.price',
            's.format',
            //'s.city',
            's.status',
            's.base',
            's.group_number',
            'rt.graduated',
            'rt.rating',
            's.min_users',
            's.max_users',
            'pr.name'
        ));

        if ($options['providerId']) {
            $select->where(
                '(tcps.provider_id = ? OR tcps.provider_id IS NULL) ',
                $options['providerId']
            );
        }
        if ($options['base_id']) {
            $select->where(
                's.base_id = ?',
                $options['base_id']
            );
        }

        if($options['isLr'] || $options['providerContext']) {
            $select->where(
                'tcps.provider_id = ?',
                $options['providerId']
            );
        }

        if ($options['base_type'] == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $ratingSelect = $this->getService('TcSubject')->getSelect();
            $ratingSelect->from(
                array('gr' => 'graduated'),
                array(
                    'subid'     => 'gr.CID',
                    'rating'    => new Zend_Db_Expr('COUNT(gr.mid) * AVG(gr.effectivity) * AVG(sv.value)'),
                    'graduated' => new Zend_Db_Expr('COUNT(gr.mid)'),
                ))
                ->joinLeft(
                    array('f' => 'tc_feedbacks'),
                    'gr.CID = f.subject_id AND gr.MID=f.user_id',
                    array())
                ->joinLeft(
                    array('sv' => 'scale_values'),
                    'sv.value_id = f.mark',
                    array())
                ->group('gr.CID')
            ;
            $select->joinLeft(array('rt' => $ratingSelect), 'rt.subid = s.subid', array(
                'graduated' => 'rt.graduated',
                'rating'    => 'rt.rating',
            ));
            $select->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
        } else {
            $select->where('s.base != ? OR s.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
            $select->joinLeft(
                array('rt' => 'subjects_fulltime_rating'),
                'rt.subid=s.subid',
                array(
                    'graduated' => 'rt.graduated',
                    'rating'    => 'rt.rating',
                ));
        }


        /*
        if ($options['isDean'] && $options['base_type'] != HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $tcSelect = $this->getTcListSource($options);
            $unionSelect = $this->getSelect();

            $unionSelect->union(array($select, $tcSelect));
            $resultSelect = $this->getSelect();
            //простите меня за ЭТО
            $resultSelect->from(array('s' => 'subjects'),array());
            $resultSelect->joinInner(array('usel' => $unionSelect),
                'usel.subid = s.subid',
                array(
                'usel.created_by'   ,
                'usel.subid'   ,
                'usel.subject_name' ,
                'usel.tcprovider'   ,
                'usel.provider_type',
                'usel.base_type'    ,
                'usel.begin',
                'usel.end',
                'usel.programm',
                'usel.category'     ,
                'usel.education_type',
                'usel.provider'     ,
                'usel.status'       ,
                'usel.format'       ,
                'usel.competence'   ,
                //'usel.city'         ,
                'usel.price'        ,
                'usel.duration'     ,
                'usel.tags'         ,
                'usel.teachers'     ,
                'usel.min_users'       ,
                'usel.max_users' ,
                'usel.students'     ,
                'usel.graduated'       ,
                'usel.rating'       ,
                'usel.group_number' ,
            ));
            return $resultSelect;
        }
        */

        return $select;
    }

    public function getTcListSource($options = array())
    {
        $default = array(
            'subjectId'   => 0,
            'providerId'  => 0,
        );
        $options = array_merge($default, $options);

        $select = $this->getSelect();

        /*$citiesSelect = clone $select;
        $citiesSelect
            ->from(
                array('cl' => 'classifiers_links'),
                array('cl.classifier_id', 'cl.item_id', 'cl.type'))
            ->joinInner(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array());
        */
        $select->from(array('s' => 'subjects'),
            array(
                'created_by'   => 's.created_by',
                'subid'        => 's.subid',
                'subject_name' => 's.name',
                'base_type'    => 's.base',
                'begin'     => 's.begin',
                'end' => 's.end',
                'programm' => 'pr.name',
                'category'     => 's.category',
                'education_type' => 's.education_type',
                'provider_type' => 's.provider_type',
                'tcprovider'   => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT tcpr.provider_id)"),
                'provider'     => new Zend_Db_Expr("''"),
                'status'       => 's.status',
                'format'       => 's.format',
                'competence'   => new Zend_Db_Expr("CASE WHEN (s.criterion_type = " . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION. ") THEN atc.name ELSE CASE WHEN (s.criterion_type = " .HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST. ") THEN atct.name ELSE '' END END"),
                //'city'         => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl.classifier_id)'),
                'price'        => 's.price',
                'duration'     => new Zend_Db_Expr("''"),
                'tags'         => 's.subid',
                'teachers'     => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tpts.teacher_id)'),
                'students'     => new Zend_Db_Expr('COUNT(DISTINCT st.MID)'),
                'group_number' => new Zend_Db_Expr("NULL"),
                'min_users'  => new Zend_Db_Expr("''"),
                'max_users'  => new Zend_Db_Expr("''"),
            )
        )
        /*->joinLeft(
            array('cl' => $citiesSelect),
            'cl.item_id = s.subid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array()
        )*/
        ->joinLeft(
            array('tcpr' => 'tc_providers'),
            's.provider_id = tcpr.provider_id AND tcpr.type = '.HM_Tc_Provider_ProviderModel::TYPE_PROVIDER,
            array()
        )
        ->joinLeft(
            array('st' => 'students'),
            's.subid = st.CID',
            array()
        )
        ->joinLeft(
            array('atc' => 'at_criteria'),
            's.criterion_id = atc.criterion_id AND s.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION,
            array()
        )
        ->joinLeft(
            array('atct' => 'at_criteria_test'),
            's.criterion_id = atct.criterion_id AND s.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST,
            array()
        )
        ->joinLeft(
            array('tpts' => 'tc_provider_teachers2subjects'),
            'tpts.subject_id = s.subid',
            array()
        )
        ->joinLeft(
            array('pe' => 'programm_events'),
            'pe.item_id = s.subid AND pe.type ='.HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
            array()
        )
        ->joinLeft(
            array('pr' => 'programm'),
            'pr.programm_id = pe.programm_id',
            array()
        )
        ->where('s.base = ?', $options['base_type'])
        ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
        ->where('provider_type = ?', HM_Tc_Provider_ProviderModel::TYPE_PROVIDER)
        ->group(array(
            's.subid',
            's.created_by',
            's.name',
            's.category',
            's.education_type',
            's.criterion_type',
            's.provider_type',
            'atc.name',
            'atct.name',
            's.begin',
            's.end',
            's.price',
            's.format',
            'tcpr.provider_id',
            'pr.name',
            //'s.city',
            's.status',
            's.base',
            'rt.graduated',
            'rt.rating',
            's.group_number',
            'pr.name'
        ));

        $restricredEdit =
            (($options['base_type'] != HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) &&
                $this->getService('Acl')->inheritsRole(
                    $this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) ? 1 : 0;
        $onlyMy = null;

        if ($restricredEdit) {
            $default = new Zend_Session_Namespace('default');
            $frontController = Zend_Controller_Front::getInstance();
            $request = $frontController->getRequest();
            $onlyMy  = $request->getParam('all', isset($default->grid['subject-fulltime-index'][$options['grid_id']]['all'])
                ? $default->grid['subject-fulltime-index'][$options['grid_id']]['all']
                : null);
        }
        if ($onlyMy) {
            $select->where('s.created_by='.$this->getService('User')->getCurrentUserId());
        }
        if ($options['base_type'] == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $ratingSelect = $this->getService('TcSubject')->getSelect();
            $ratingSelect->from(
                array('gr' => 'graduated'),
                array(
                    'subid'     => 'gr.CID',
                    'rating'    => new Zend_Db_Expr('COUNT(gr.mid) * AVG(gr.effectivity) * AVG(sv.value)'),
                    'graduated' => new Zend_Db_Expr('COUNT(gr.mid)'),
                ))
                ->joinLeft(
                    array('f' => 'tc_feedbacks'),
                    'gr.CID = f.subject_id AND gr.MID=f.user_id',
                    array())
                ->joinLeft(
                    array('sv' => 'scale_values'),
                    'sv.value_id = f.mark',
                    array())
                ->group('gr.CID')
            ;
            $select->joinLeft(array('rt' => $ratingSelect), 'rt.subid = s.subid', array(
                'graduated' => 'rt.graduated',
                'rating'    => 'rt.rating',
            ));
            $select->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
        } else {
            $select->where('s.base != ? OR s.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
            $select->joinLeft(
                array('rt' => 'subjects_fulltime_rating'),
                'rt.subid=s.subid',
                array(
                    'graduated' => 'rt.graduated',
                    'rating'    => 'rt.rating',
                ));
        }

        if ($options['base_id']) {
            $select->where(
                's.base_id = ?',
                $options['base_id']
            );
        }
        return $select;
    }

    public function getScSessionListSource($options)
    {
        $default = array(
            'subjectId'   => 0,
            'providerId'  => 0,
        );
        $options = array_merge($default, $options);

        $select = $this->getSelect();
        $select->from(array('s' => 'subjects'),
            array(
                'created_by'   => 's.created_by',
                'subid'        => 's.subid',
                'subject_name' => 's.name',
                'base_type'    => 's.base',
                'provider_type' => 's.provider_type',
                'tcprovider'   => 'pr.provider_id',
                'provider'     => new Zend_Db_Expr("''"),
                'status'       => 's.status',
                'format'       => 's.format',
                'competence'   => new Zend_Db_Expr("CASE WHEN (s.criterion_type = " . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION. ") THEN atc.name ELSE CASE WHEN (s.criterion_type = " .HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST. ") THEN atct.name ELSE '' END END"),
                'city'         => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl.classifier_id)'),
                'price'        => 's.price',
                'duration'     => new Zend_Db_Expr("''"),
                'tags'         => 's.subid',
                'teachers'     => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tpts.teacher_id)'),
                'students'     => new Zend_Db_Expr('COUNT(DISTINCT st.MID)'),
            )
        );
    }

    public function getStudyCenter($subjectId, $asObject = false)
    {
        /** @var HM_Tc_Provider_Subject_SubjectService $service */
        $service  = $this->getService('TcProviderSubject');
        $studyCenters = $this->getOne($service->fetchAllDependenceJoinInner('ScProvider', $service->quoteInto(
            array('subject_id = ?'),
            array($subjectId)
        )));
        if (count($studyCenters->scProvider)) {
            $studyCenter = $this->getOne($studyCenters->scProvider);
        }
        if ($asObject) {
            return ($studyCenter) ? $studyCenter: false;
        }
        return ($studyCenter) ? $studyCenter->name : _('Нет');
    }

    public function getStudyCenterId($subjectId)
    {
        $studyCenter = $this->getStudyCenter($subjectId, true);
        if ($studyCenter->provider_id) {
            return $studyCenter->provider_id;
        }
        else {
            return 0;
        }
    }

    /**
     * @param $subjectId
     * @param $values array('teacher_id', 'user_id')
     */

    public function assignTeacherToScSubject($subjectId, $values)
    {
        if ($subjectId) {
            parent::assignTeacher($subjectId, $values['user_id']);
            $this->getService('TcTeacherSubject')->assign($values['teacher_id'], $subjectId);
        }

    }

    //
    public function unAssignTeacherFromSubject($subjectId, $teacherId = 0)
    {
        static $_assigns = null;
        if($_assigns === null) {
            $_assigns = $this->getService('TcProviderTeacher')->fetchAllJoinInner('TeacherSubjects',
                $this->quoteInto('TeacherSubjects.subject_id = ?', $subjectId)
            )->getList( 'teacher_id', 'user_id');
        }
        $where = array('CID = ?' => $subjectId);
        if($_assigns[$teacherId]) {
            $where['MID = ?'] = $_assigns[$teacherId];
        }
        $this->getService('Teacher')->deleteBy($where);

        $this->getService('TcTeacherSubject')->unAssign($teacherId, 0, $subjectId);
    }

    public function getScGraduatedListSource($options)
    {
        $default = array(
            'subjectId'   => 0,
            'providerId'  => 0,
        );
        $options = array_merge($default, $options);

        $select = $this->getSelect();

        if ($options['subjectId'])
        {
            $fields = array(
                'g.SID',
                'g.MID',
                'g.CID',
                'g.status',
                'notempty' => "CASE WHEN (p.LastName IS NULL AND p.FirstName IS NULL AND  p.Patronymic IS NULL) OR (p.LastName = '' AND p.FirstName = '' AND p.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'position' => 'pos.name',
                'department' => 'd.name',
                's.scale_id',
                'g.begin',
                'g.end',
                'g.certificate_id',
                'g.effectivity',
                'm.mark');
        }
        else
        {
            $fields = array(
                'g.SID',
                'g.MID',
                'g.CID',
                'g.status',
                'notempty' => "CASE WHEN (p.LastName IS NULL AND p.FirstName IS NULL AND  p.Patronymic IS NULL) OR (p.LastName = '' AND p.FirstName = '' AND p.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'position' => 'pos.name',
                'department' => 'd.name',
                's.name',
                's.scale_id',
                'g.begin',
                'g.end',
                'g.certificate_id',
                'g.effectivity',
                'm.mark');
        }



        $select->from(
            array('g' => 'graduated'),
            $fields
        )->joinInner(
            array('p' => 'People'),
            'p.MID = g.MID',
            array()
        )->joinInner(
            array('s' => 'subjects'),
            $this->quoteInto(
                array('s.subid = g.CID AND s.provider_type = ?', 'AND s.base = ?'),
                array(HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER, HM_Subject_SubjectModel::BASETYPE_SESSION)
            ),
            array()
        )->joinInner(
            array('tcps' => 'tc_providers_subjects'),
            'tcps.subject_id = s.subid',
            array()
        )->joinLeft(
            array('m' => 'courses_marks'),
            '(m.cid = g.CID AND m.mid = g.MID)',
            array()
        )->joinLeft(
            array('pos' => 'structure_of_organ'),
            'pos.mid = p.MID',
            array()
        )->joinLeft(
            array('d' => 'structure_of_organ'),
            'd.soid = pos.owner_soid',
            array()
        );
        if ($options['providerId']) {
            $select->where(
                '(tcps.provider_id = ? )',
                $options['providerId']
            );
        }
        if ($options['subjectId']) {
            $select->where(
                '(s.subid = ? )',
                $options['subjectId']
            );
        }

        return $select;
    }

    /**
     * @param $subjectId
     * @return HM_Collection
     */
    public function getRequiredLessons($subjectId)
    {
        return $this->getService('Lesson')->fetchAll($this->quoteInto(
            array('CID = ? ', ' AND required_for_mark = ?'),
            array($subjectId, 1)
        ));
    }

    /**
     * @param $subject
     * проверяем дату конца курса, если все занятия не умещаются - удлинняем
     */
    public function checkSubjectEnd($subject)
    {
        $select = $this->getSelect();
        $select->from(array('l' => 'schedule'),
            array(
                'subject_id' => 'l.CID',
                'max_stopday' => new Zend_Db_Expr("MAX(l.stopday)")
            )
        )->group(
            array('l.CID')
        )->where(
            $this->quoteInto('l.CID = ?', $subject->subid)
        );
        $result = $select->query()->fetch();
        $maxStopDate = HM_Date::getRelativeDate(
            new Zend_Date(strtotime($subject->begin)),
            ((int)$result['max_stopday'])/24/60/60
        );
        $endSubject   = new HM_Date($subject->end);
        if($endSubject->compare($maxStopDate) < 0) {
            $endSubject->set($maxStopDate);
            $subject->end = $endSubject->toString(HM_Date::SQL);
            $this->update($subject->getValues());
        }

    }
}