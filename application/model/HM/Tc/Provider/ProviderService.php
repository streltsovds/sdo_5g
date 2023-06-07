<?php
class HM_Tc_Provider_ProviderService extends HM_Service_Abstract
{
    protected $_providerCache = null;

    public function insert($data, $unsetNull = true)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $data['created'] = HM_Date::now()->toString(HM_Date::SQL);
        $data['created_by'] = $userService->getCurrentUserId();

        return parent::insert($data, $unsetNull);

    }

    /**
     * Нет времени возиться с изучением каскадного удаления
     *
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        if ($id > 0) {
            $subjectService->deleteBy($subjectService->quoteInto(
                'provider_id = ?',
                $id
            ));
        }

        return parent::delete($id);

    }

    public function getCities($providerId)
    {
        return $this->getService('Classifier')->fetchAllDependenceJoinInner('ClassifierLink',
            $this->quoteInto(array('ClassifierLink.type = ? ', ' AND ClassifierLink.item_id = ?'),
                array(HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER, $providerId)
            )
        );
    }

    public function getDepartment($departmentId)
    {
        $department = $this->getService('Orgstructure')->find($departmentId);
        if (!$department->count()) {
            return array();
        }

        $department = $department->current();
        $departments = $this->getService('Orgstructure')->fetchAll($this->quoteInto(
            array('soid=? OR ', '(lft<?', ' AND rgt>? AND level<2 )'),
            array($departmentId, $department->lft, $department->rgt)
        ), 'level');
        $result = array($departmentId => '');
        foreach ($departments as $department) {
            $result[$departmentId] .= ($result[$departmentId] ? ' / ' : '')
                . $department->name;
        }
        return $result;
    }

    public function updateContacts($contacts, $providerId)
    {
        $contactService = $this->getService('TcProviderContact');
        $currentContacts = $contactService->fetchAll(
            $this->quoteInto('provider_id = ?', $providerId))->getList('contact_id', 'contact_id');
        if (isset($contacts) && count($contacts)) {
            foreach ($contacts as $contactId => $contact) {
                if ($contactId != HM_Form_Element_MultiSet::ITEMS_NEW) {
                    $data = array(
                        'contact_id' => $contactId,
                        'provider_id' => $providerId,
                        'name' => $contact['fio'],
                        'position' => $contact['position'],
                        'phone' => $contact['phone'],
                        'email' => $contact['email'],
                    );
                    $contactService->update($data);
                    unset($currentContacts[$contactId]);
                } else {
                    foreach ($contact['fio'] as $key => $item) {
                        if (!strlen($item)) {
                            continue;
                        }
                        $data = array(
                            'provider_id' => $providerId,
                            'name' => $contact['fio'][$key],
                            'position' => $contact['position'][$key],
                            'phone' => $contact['phone'][$key],
                            'email' => $contact['email'][$key],
                        );
                        $contactService->insert($data);
                    }
                }
            }
            if (is_array($currentContacts) && count($currentContacts)) {
                $contactService->deleteBy(array('contact_id IN (?)' => array_keys($currentContacts)));
            }
        }
    }

    public function setClassifiers($classifiers, $providerId)
    {
        $res = $this->getService('ClassifierLink')->deleteBy($this->quoteInto(
            array('item_id = ? ', ' AND type = ?'),
            array($providerId, HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER)
        ));

        if (count($classifiers) && $providerId) {

            foreach ($classifiers as $classifier) {
                $data = array(
                    'item_id' => $providerId,
                    'classifier_id' => (int)$classifier,
                    'type' => HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER
                );
                $this->getService('ClassifierLink')->insert($data);
            }
        }
    }

    public function getListSource($type = HM_Tc_Provider_ProviderModel::TYPE_PROVIDER, $onlyMy = false)

    {
        $select = $this->getSelect();
        $select->from(array('tcp' => 'tc_providers'), array(
            'provider_id' => 'tcp.provider_id',
            'provider_name' => 'tcp.name',
            'status' => 'tcp.status',
            'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.classifier_id)'),
            'contacts' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tcpc.contact_id)'),
            'courses' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT s.subid)'),
            'teachers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tcpt.teacher_id)'),
//            'graduated_count' => new Zend_Db_Expr('COUNT(DISTINCT g.SID)'),
            'tags' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tr.tag_id)'),
            'rating' => new Zend_Db_Expr('AVG(s.rating)'),
//            'graduated_count' => new Zend_Db_Expr('SUM(rt.graduated)'),
            'tcp.created_by',
        ));

        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            $this->getService('ClassifierLink')->quoteInto('cl.item_id = tcp.provider_id AND cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER),
            array()
        );
        $select->joinLeft(
            array('c' => 'classifiers'),
            'c.classifier_id = cl.classifier_id',
            array()
        );
        $select->joinLeft(
            array('tcpc' => 'tc_provider_contacts'),
            'tcpc.provider_id = tcp.provider_id',
            array()
        );
        $select->joinLeft(
            array('s' => 'subjects'),
            $this->quoteInto(
                array('tcp.provider_id = s.provider_id AND (s.base IS NULL OR s.base != ?)', 'AND s.status = ?'),
                array(HM_Subject_SubjectModel::BASETYPE_SESSION, HM_Tc_Subject_SubjectModel::FULLTIME_STATUS_PUBLISHED)
            ),
            array()
        );
        /*$select->joinLeft(
            array('rt' => 'subjects_fulltime_rating'),
            'rt.subid=s.subid',
            array());*/
        $select->joinLeft(
            array('tcpt' => 'tc_provider_teachers'),
            'tcp.provider_id = tcpt.provider_id',
            array()
        );
        $select->joinLeft(
            array('ss' => 'subjects'),
            'tcp.provider_id = ss.provider_id',
            array()
        );
/*        $select->joinLeft(
            array('g' => 'graduated'),
            'ss.subid = g.CID',
            array()
        );
*/
        $select->joinLeft(
            array('tr' => 'tag_ref'),
            's.subid = tr.item_id AND tr.item_type = ' . HM_Tag_Ref_RefModel::TYPE_SUBJECT,
            array()
        );

        $select->where('tcp.type = ?', $type);

        if ($onlyMy) {
            $select->where('tcp.created_by=?', $this->getService('User')->getCurrentUserId());
        }

        $select->group(array('tcp.provider_id', 'tcp.name', 'tcp.status', 'tcp.created_by'));

        return $select;
    }

    public function getStudyGroupsSource($onlyMy = false)
    {


        $select = $this->getSelect();
        $select->from(array('tcp' => 'tc_providers'), array(
            'provider_id' => 'tcp.provider_id',
            'provider_name' => 'tcp.name',
            'results' => 'scps.koef',
            'results_period' => 'scps.period',
            'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.classifier_id)'),
            'contacts' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT tcpc.contact_id)'),
            'pass_by' => 'tcp.pass_by',
            'dzo' => 'tcp.dzo_id',
            'department_id' => 'tcp.department_id',
            'department' => 'o.name',
            /*'department'     => new Zend_Db_Expr(
                "CASE WHEN ".
                    "op2.soid IS NULL ".
                "THEN ".
                "(".
                    "CASE WHEN ".
                        "op1.soid IS NULL ".
                    "THEN ".
                        "o.name ".
                    "ELSE ".
                        "CONCAT(op1.name, CONCAT(' / ', o.name)) ".
                    "END".
                ") ".
                "ELSE ".
                "(".
                    "CASE WHEN ".
                        "op1.soid IS NULL ".
                    "THEN ".
                        "CONCAT(op2.name, CONCAT(' / ', o.name)) ".
                    "ELSE ".
                        "CONCAT(op2.name, CONCAT(' / ', CONCAT(op1.name, CONCAT(' / ', o.name)))) ".
                     "END".
                ") ".
            "END"),*/
            'uses_year' => new Zend_Db_Expr("'-'"),
            'uses_month' => new Zend_Db_Expr("'-'"),
            'rating' => new Zend_Db_Expr('AVG(rt.rating)'),

            'tcp.created_by',
        ));
        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            $this->quoteInto('cl.item_id = tcp.provider_id AND cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER),
            array()
        );
        $select->joinLeft(
            array('c' => 'classifiers'),
            $this->quoteInto('c.classifier_id = cl.classifier_id AND c.type = ?', HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES),
            array()
        );

        $select
            ->joinLeft(array('o' => 'structure_of_organ'), 'o.soid=tcp.department_id', array());
        /*->joinLeft(array('op1' => 'structure_of_organ'), 'op1.lft < o.lft AND op1.lft < o.rgt AND op1.rgt > o.rgt AND op1.rgt > o.lft AND op1.level = 1', array())
        ->joinLeft(array('op2' => 'structure_of_organ'), 'op2.lft < o.lft AND op2.lft < o.rgt AND op2.rgt > o.rgt AND op2.rgt > o.lft AND op2.level = 0', array());*/

        $select->joinLeft(
            array('tcps' => 'tc_providers_subjects'),
            'tcp.provider_id = tcps.provider_id',
            array());

        /*$select->joinLeft(
            array('s' => 'subjects'),
            $this->quoteInto(
                array('tcps.subject_id = s.subid AND (s.base IS NULL OR s.base != ?)', 'AND s.status = ?'),
                array(HM_Subject_SubjectModel::BASETYPE_SESSION, HM_Tc_Subject_SubjectModel::FULLTIME_STATUS_PUBLISHED)
            ),
            array()
        );*/
        $select->joinLeft(
            array('rt' => 'subjects_fulltime_rating'),
            'rt.subid = tcps.subject_id',
            array());

        $select->joinLeft(
            array('tcpc' => 'tc_provider_contacts'),
            'tcpc.provider_id = tcp.provider_id',
            array()
        );

        $subSelect = $this->getSelect();
        $subSelect->from(array('scps2' => 'sc_poll_session'), array(
                'provider_id' => new Zend_Db_Expr('scps2.provider_id'),
                'period' => new Zend_Db_Expr('MAX(scps2.period)'))
        );
        $subSelect->group(array('scps2.provider_id'));


        $select->joinLeft(
            array('scps_max_p' => $subSelect),
            'tcp.provider_id = scps_max_p.provider_id',
            array()
        );

        $select->joinLeft(
            array('scps' => 'sc_poll_session'),
            'scps.provider_id = scps_max_p.provider_id AND scps.period = scps_max_p.period',
            array()
        );


        $departmentIds = $this->getService('Orgstructure')->getResponsibleDepartments();
        if ($departmentIds) {
            $select->where(
                $this->quoteInto(
                    'tcp.department_id IN (?)',
                    $departmentIds
                )
            );

        }


        $select->where('tcp.type = ?', HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER);

        if ($onlyMy) {
            $select->where('tcp.created_by=?', $this->getService('User')->getCurrentUserId());
        }

        $select->group(
            array(
                'tcp.provider_id',
                'tcp.department_id',
                'tcp.name',
                'tcp.created_by',
                'tcp.pass_by',
                'o.name',
                /*'o.soid',

                'op1.soid',
                'op1.name',
                'op2.soid',
                'op2.name',*/
                'tcp.dzo_id',
                'scps.period',
                'scps.koef'
            )
        );


        return $select;
    }

    public function getListOfNewProvidersSource($sessionId = 0)
    {
        $subjectService = $this->getService('Subject');
        $classifierLinkService = $this->getService('ClassifierLink');
        $classifierService = $this->getService('Classifier');

        $select = $this->getSelect();

        $select
            ->from(array('tcp' => 'tc_providers'), array(
                'provider_id' => 'tcp.provider_id',
                'provider_name' => 'tcp.name',
                'status' => 'tcp.status',
                'cities' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.classifier_id)'),
                'creator_id' => 'tcp.created_by',
                'creator_name' => new Zend_Db_Expr("CONCAT(p.LastName, CONCAT(' ', p.FirstName))"),
                'courses_count' => new Zend_Db_Expr('COUNT(DISTINCT s.subid)'),
                'applications_count' => new Zend_Db_Expr('COUNT(DISTINCT a.application_id)'),
            ))
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                $classifierLinkService->quoteInto('cl.item_id = tcp.provider_id AND cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER),
                array()
            )
            ->joinLeft(
                array('c' => 'classifiers'),
                $classifierService->quoteInto('c.classifier_id = cl.classifier_id AND c.type = ?', 1),
                array()
            )
            ->joinLeft(
                array('s' => 'subjects'),
                $subjectService->quoteInto(
                    array(
                        'tcp.provider_id = s.provider_id',
                        ' AND s.type = ?',
                    ),
                    array(
                        HM_Tc_Subject_SubjectModel::BASETYPE_BASE,
                        HM_Tc_Subject_SubjectModel::TYPE_FULLTIME,
                    )
                ),
                array()
            )
            ->joinLeft(
                array('a' => 'tc_applications'),
                $subjectService->quoteInto(
                    array(
                        'tcp.provider_id = a.provider_id AND s.status = ?',
                    ),
                    array(
                        HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                    )
                ),
                array()
            )
            ->joinLeft(
                array('p' => 'People'),
                'p.MID = tcp.created_by',
                array()
            )
            //->where('tcp.status = ?', HM_Tc_Provider_ProviderModel::STATUS_NOT_PUBLISHED)

            ->group(array(
                'tcp.provider_id',
                'tcp.name',
                'tcp.status',
                'tcp.created_by',
                'tcp.create_from_tc_session',
                'p.LastName',
                'p.FirstName',
            ));

        if ($sessionId) {
            $select->where('tcp.create_from_tc_session=?', $sessionId);
        }
        return $select;
    }

    public function getTeachers($providerId)
    {

    }

    public function getContacts($providerId)
    {

    }

    public function concatenate($targetProviderId, $providerIds = array())
    {
        if (empty($providerIds)) {
            return true;
        }

        $subjectService = $this->getService('Subject');
        $applicationService = $this->getService('TcApplication');

        $updateWhere = $this->quoteInto(
            array('provider_id IN (?)'),
            array($providerIds)
        );

        $updateData = array(
            'provider_id' => $targetProviderId
        );

        $subjectService->updateWhere($updateData, $updateWhere);
        $applicationService->updateWhere($updateData, $updateWhere);

        // delete вместо deleteBy, чтобы ненужный мусор всякий удалился (на будущее)
        foreach ($providerIds as $providerId) {
            $this->delete($providerId);
        }

    }

    public function getSelectList()
    {
        $providerTypes = HM_Tc_Provider_ProviderModel::getTypes();
        $result = array();

        $providers = $this->fetchAll(null, 'name');
        foreach ($providers as $provider) {
            if (!isset($result[$providerTypes[$provider->type]])) {
                $result[$providerTypes[$provider->type]] = array();
            }
            $result[$providerTypes[$provider->type]][$provider->provider_id] = $provider->name;
        }

        return $result;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('провайдер plural', '%s провайдер', $count), $count);
    }

    public function pluralFormCountSc($count)
    {
        return !$count ? _('Нет') : sprintf(_n('учебный центр plural', '%s учебный центр', $count), $count);
    }

    public function getDepartmentsStudyCenterIds(HM_Collection $departments)
    {
        $where = array();
        $begin = time();
        $departmentStudyCenterIds = array();
        $select = $this->getSelect();
        $select->from(array('d' => 'structure_of_organ'),
            array(
                'department_id' => 'd.soid',
                'parent_department_id' => 'pd.soid',
                'provider_id' => 'tp.provider_id'
            )
        )->joinLeft(
            array('pd' => 'structure_of_organ'),
            'd.lft >= pd.lft AND d.rgt <= pd.rgt and pd.level IN (0,1) AND pd.type ='.HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
            array()
        )->joinLeft(
            array('tp' => 'tc_providers'),
            'tp.department_id = pd.soid',
            array()
        )->where(
            'd.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
        )->where(
            'd.soid IN (?)', $departments->getList('soid')
        );
        $result = $select->query()->fetchAll();
        foreach($result as $row) {
            if(!isset($departmentStudyCenterIds[$row['department_id']]) ||
                (isset($departmentStudyCenterIds[$row['user_id']]) && $departmentStudyCenterIds[$row['department_id']] == null)
            ) {
                $departmentStudyCenterIds[$row['department_id']] = $row['provider_id'];
            }
        }
        $end = time() - $begin;
        return $departmentStudyCenterIds;
    }

    public function getUsersStudyCenterIds($userIds)
    {
        $where = array();
        $begin = time();
        $userStudyCenterIds = array();
        $select = $this->getSelect();
        $select->from(array('p' => 'structure_of_organ'),
            array(
                'user_id' => 'p.mid',
                'department_id' => 'pd.soid',
                'provider_id' => 'tp.provider_id'
            )
        )->joinLeft(
            array('pd' => 'structure_of_organ'),
            'p.lft > pd.lft AND p.rgt < pd.rgt and pd.level IN (0,1) AND pd.type ='.HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
            array()
        )->joinLeft(
            array('tp' => 'tc_providers'),
            'tp.department_id = pd.soid',
            array()
        )->where(
            'p.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION
        )->where(
            'p.mid IN (?)', $userIds
        );
        $result = $select->query()->fetchAll();
        foreach($result as $row) {
            if(!isset($userStudyCenterIds[$row['user_id']]) ||
                (isset($userStudyCenterIds[$row['user_id']]) && $userStudyCenterIds[$row['user_id']] == null)
            ) {
                $userStudyCenterIds[$row['user_id']] = $row['provider_id'];
            }
        }
        $end = time() - $begin;
        return $userStudyCenterIds;
    }

}