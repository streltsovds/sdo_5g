<?php
class HM_Tc_Application_Import_Template_TemplateMapper extends HM_Mapper_Abstract
{
    protected $_directionsNamesUnified;
    protected $_providersNamesUnified;
    protected $_level2OrgCache;

    protected function _init()
    {
        $this->_directionsNamesUnified = $this->_getDirectionsNamesUnified();
        $this->_providersNamesUnified  = $this->_getProvidersNamesUnified();
        $this->_level2OrgCache = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(array('level = ? ' => 2));
    }

    protected function _createModel($rows)
    {
        $this->_init();

        list($rows) = $rows;

        $request     = Zend_Controller_Front::getInstance()->getRequest();
        $sessionId   = (int) $request->getParam('session_id');

        $session     = Zend_Registry::get('serviceContainer')->getService('TcSession')->getOne(
            Zend_Registry::get('serviceContainer')->getService('TcSession')->find($sessionId)
        );

        $cycle = Zend_Registry::get('serviceContainer')->getService('Cycle')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Cycle')->find($session->cycle_id)
        );

        $year = $cycle->year;

        $notHandled  = array();
        $noNameCount = 0;

        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {

            foreach($rows as $row) {
                list(
                    $number,
                    $depHighLevel,
                    $fio,
                    $position,
                    $department,
                    $course,
                    $direction,
                    $category,
                    $costItem,
                    $provider,
                    $price,
                    $quarter
                    ) = $row;
                $intFio = (int) $fio;

                if ($intFio) $noNameCount++;

                // Если в поле ФИО не указано кол-во человек,
                //      если данные не похожи на ФИО, ничего не создаём и сохраняем данные для отчётности,
                //      если похожи - создаём персональную заявку,
                // иначе,
                //      создаём обезличенную заявку на это кол-во.

                if ($intFio == 0) {
                    if (mb_strtolower(trim($fio)) == 'резерв' || count(explode(' ', trim($fio))) == 1) {
                        $notHandled['noFio'][] = $row;
                    } else {
                        list($lastName, $firstName, $patronymic) = explode(' ', trim($fio));
                        $user = Zend_Registry::get('serviceContainer')->getService('User')->getOne(
                            Zend_Registry::get('serviceContainer')->getService('User')->fetchAll(
                                array(
                                    'LastName = ?  ' => $lastName ? $lastName : '',
                                    'FirstName = ? ' => $firstName ? $firstName : '',
                                    'Patronymic = ?' => $patronymic ? $patronymic : '',
                                )
                            )
                        );

                        if ($user) {
                            $org = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne(
                                Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(
                                    array(
                                        'mid = ? ' => $user->MID
                                    )
                                )
                            );

                            $model = $this->_prepareInsert($sessionId, $provider, $course, $price, $direction, $quarter, $year, $org, $user);
                            if ($model) $models[count($models)] = $model;
                        } else {
                            $notHandled['noUser'][] = $row;
                        }
                    }
                } else {

                    $orgstructureService = Zend_Registry::get('serviceContainer')->getService('Orgstructure');
                    $condition = array(
                        'name = ? ' => trim($depHighLevel),
                    );

                    $org = $orgstructureService->getOne($orgstructureService->fetchAll($condition));

                    if ($org && $org->readyForImpersonalAssigns()) {
                        $model = $this->_prepareInsert($sessionId, $provider, $course, $price, $direction, $quarter, $year, $org, null, $intFio);
                        if ($model) $models[count($models)] = $model;
                    } else {
                        $key = $org ? 'tooHighDep' : 'noDepartment';
                        $notHandled[$key][] = $row;
                    }
                }
            }
        }

        $results['models']      = $models;
        $results['notHandled']  = $notHandled;
        $results['noNameCount'] = $noNameCount;
        $results['rowsCount']   = count($rows);

        return $results;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

    protected function _prepareInsert($sessionId, $provider, $course, $price, $direction, $quarter, $year, $org, $user = null, $quantity = null)
    {
        try {
            if (!$org) {
                $session = Zend_Registry::get('serviceContainer')->getService('TcSession')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('TcSession')->find($sessionId)
                );

                $checkedItems = unserialize($session->checked_items);
                $orgstructure = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find(reset($checkedItems))
                );

                $org = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($orgstructure->owner_soid)
                );
            }

            // #27618
            $categoryRequired = false;
            if (($this->_stringUnify(trim($direction)) == $this->_stringUnify('Производственное обучение')) ||
                ($this->_stringUnify(trim($direction)) == $this->_stringUnify('ОТ, ПБ и ООС'))) {
                $categoryRequired = true;
            }

            $insertArray = array(
                'price' => '',
                'status' => HM_Tc_Application_ApplicationModel::STATUS_COMPLETE,
                'expire' => '',
                'period' => '',
                'user_id' => $user ? '' : null,
                'created' => date('Y-m-d'),
                'category' => ($categoryRequired) ? HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED : HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION,
                'cost_item' => HM_Tc_Application_ApplicationModel::DEFAULT_COST_ITEM,
                'event_name' => '',
                'session_id' => $sessionId,
                'subject_id' => '',
                'position_id' => $user ? '' : null,
                'provider_id' => '',
//            'study_status'     => HM_Tc_Application_ApplicationModel::STUDY_STATUS_NONE,
                'criterion_id' => '',
                'primary_type' => '',
                'department_id' => '',
                'criterion_type' => '',
                'session_quarter_id' => '',
                'session_department_id' => '',
                'department_application_id' => '',
            );

            $providerId = $this->_createNewProvider($provider);
            $subject = ($course) ? $this->_createNewSubject($providerId, $course, $price, $categoryRequired) : null;
            $subjectId = ($subject->subid) ? $subject->subid : null;
            $this->_createNewDirection($direction, $subjectId);

            $sessionDepartment = Zend_Registry::get('serviceContainer')->getService('TcSessionDepartment')->getOne(
                Zend_Registry::get('serviceContainer')->getService('TcSessionDepartment')->fetchAll(
                    array(
                        'department_id = ? ' => $user ? $this->_getUpLevelDepartment($org, $this->_level2OrgCache) : $org->soid,
                        'session_id = ? ' => $sessionId
                    )
                )
            );

            if (!$sessionDepartment) {
                $sessionDepartment = Zend_Registry::get('serviceContainer')->getService('TcSessionDepartment')->insert(
                    array(
                        'department_id' => $user ? $org->owner_soid : $org->soid,
                        'session_id' => $sessionId
                    )
                );
            }

            $cycle = Zend_Registry::get('serviceContainer')->getService('Cycle')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Cycle')->fetchAll(
                    array(
                        'year = ? ' => $year,
                        'quarter = ? ' => $quarter,
                        'type = ? ' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING,
                    )
                )
            );

            if ($cycle) {
                $sessionQuarter = Zend_Registry::get('serviceContainer')->getService('TcSessionQuarter')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('TcSessionQuarter')->fetchAll(
                        array(
                            'session_id = ? ' => $sessionId,
                            'cycle_id = ? ' => $cycle->cycle_id,
                        )
                    )
                );

                $data = array(
                    'department_id' => $user ? $org->owner_soid : $org->soid,
                    'session_department_id' => $sessionDepartment->session_department_id,
                    'session_id' => $sessionId,
                    'subject_id' => $subject->subid ? $subject->subid : null,
                    'session_quarter_id' => $sessionQuarter ? $sessionQuarter->session_quarter_id : null,
                );

                $departmentApplication = Zend_Registry::get('serviceContainer')->getService('TcSessionDepartmentApplication')->insert($data);
            }

            $insertArray['provider_id'] = $providerId;
            $insertArray['position_id'] = $user ? $org->soid : null;
            $insertArray['department_id'] = $user ? $org->owner_soid : $org->soid;
            $insertArray['user_id'] = $user ? $user->MID : null;
            $insertArray['price'] = $price;
            $insertArray['quantity'] = $quantity ?: null;
            $insertArray['subject_id'] = $subject->subid ? $subject->subid : null;
            $insertArray['period'] = $this->_getPeriodFromQuarter($quarter, $year);
            $insertArray['session_department_id'] = $sessionDepartment->session_department_id;
            $insertArray['department_application_id'] = $departmentApplication ? $departmentApplication->department_application_id : 0;

            return $insertArray;

        } catch (Exception $e) {
            return false;
        }
    }

    protected function _getUpLevelDepartment($org, $level2OrgCache)
    {
        foreach ($level2OrgCache as $item) {
            if ($org->lft > $item->lft && $org->rgt < $item->rgt) return $item->soid;
        }
    }

    protected function _createNewDirection($direction, $subjectId)
    {
        $directionsNamesUnified = $this->_directionsNamesUnified;

        if (!($directionId = array_search($this->_stringUnify($direction), $directionsNamesUnified))) {
            $newDirection = Zend_Registry::get('serviceContainer')->getService('Classifier')->insert(
                array(
                    'name' => $direction,
                    'type' => HM_Classifier_ClassifierModel::TYPE_DIRECTION
                )
            );
            $directionId = $newDirection->classifier_id;
            $this->_directionsNamesUnified[$newDirection->classifier_id] = $this->_stringUnify($newDirection->name);
        }

        if ($subjectId) {
            $existsClassifierLink = Zend_Registry::get('serviceContainer')->getService('ClassifierLink')->getOne(
                Zend_Registry::get('serviceContainer')->getService('ClassifierLink')->fetchAll(
                    array(
                        'item_id = ? ' => $subjectId,
                        'classifier_id = ? ' => $directionId,
                        'type = ? ' => HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                    )
                )
            );

            if (!$existsClassifierLink) {
                Zend_Registry::get('serviceContainer')->getService('ClassifierLink')->insert(
                    array(
                        'item_id' => $subjectId,
                        'classifier_id' => $directionId,
                        'type' => HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                    )
                );
            }

            Zend_Registry::get('serviceContainer')->getService('Subject')->updateWhere(
                array('direction_id' => $directionId),
                array('subid = ?'    => $subjectId)
            );
        }
    }

    protected function _createNewSubject($providerId, $course, $price, $categoryRequired)
    {
        $subjectsNamesUnified = $this->_getSubjectsNamesUnified($providerId);

        // странная проблема адаптера
        // запрос с более длинным like просто выносит
        if (strlen($course) < 80) {
            $criterionTest = Zend_Registry::get('serviceContainer')->getService('AtCriterionTest')->getOne(
                Zend_Registry::get('serviceContainer')->getService('AtCriterionTest')->fetchAll(
                    Zend_Registry::get('serviceContainer')->getService('AtCriterionTest')->quoteInto(
                        'name LIKE ?', '%' . $course . '%'
                    )
                )
            );
        }

        if (!($subjectId = array_search($this->_stringUnify($course), $subjectsNamesUnified))) {
            $subj = Zend_Registry::get('serviceContainer')->getService('Subject')->insert(
                array(
                    'name'                => $course,
                    'shortname'           => $course,
                    'short_description'   => '',
                    'description'         => '',
                    'external_id'         => '',
                    'code'                => '',
                    'type'                => HM_Tc_Subject_SubjectModel::TYPE_EDUCATION_OUTER,
                    'reg_type'            => '',
                    'begin'               => null,
                    'end'                 => null,
                    'begin'       => null,
                    'end'         => null,
                    'longtime'            => 0,
                    'price'               => $price,
                    'price_currency'      => 'RUB',
                    'plan_users'          => 0,
                    'period'              => HM_Subject_SubjectModel::PERIOD_FREE,
                    'access_elements'     => 0,
                    'auto_done'           => 0,
                    'base'                => 1,
                    'base_id'             => 0,
                    'base_color'          => '',
                    'claimant_process_id' => 0,
                    'scale_id'            => 0,
                    'auto_mark'           => 0,
                    'auto_graduate'       => 0,
                    'formula_id'          => null,
                    'threshold'           => null,
                    'in_banner'           => 0,
                    'status'              => 1,
                    'provider_id'         => $providerId,
                    'category'            => $categoryRequired ? HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY : HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_ADDITION,
                    'direction_id'        => 0,
                    'criterion_id'        => ($criterionTest && $categoryRequired) ? $criterionTest->criterion_id : null,
                    'criterion_type'      => ($criterionTest && $categoryRequired) ? HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST : null,
                    'created'             => date('Y-m-d H:i:s')
                )
            );
        } else {
            $subj = Zend_Registry::get('serviceContainer')->getService('Subject')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Subject')->find($subjectId)
            );

            Zend_Registry::get('serviceContainer')->getService('Subject')->updateWhere(array(
                'price' => $price,
                'provider_id' => $providerId,
            ), array('subid = ?' => $subj->subid));
        }

        if ($subj && $providerId) {
            $providerSubject = Zend_Registry::get('serviceContainer')->getService('TcProviderSubject')->getOne(
                Zend_Registry::get('serviceContainer')->getService('TcProviderSubject')->fetchAll(
                    array(
                        'subject_id = ? '  => $subj->subid,
                        'provider_id = ? ' => $providerId,
                    )
                )
            );
            if (!$providerSubject) {
                Zend_Registry::get('serviceContainer')->getService('TcProviderSubject')->insert(
                    array(
                        'subject_id'  => $subj->subid,
                        'provider_id' => $providerId,
                    )
                );
            }
        }
        return $subj;
    }

    protected function _createNewProvider($provider)
    {
        $providersNamesUnified = $this->_providersNamesUnified;
        if (!($providerId = array_search($this->_stringUnify($provider), $providersNamesUnified))) {
            $prov = Zend_Registry::get('serviceContainer')->getService('TcProvider')->insert(
                array(
                    'name' => $provider,
                    'status' => HM_Tc_Provider_ProviderModel::STATUS_PUBLISHED,
                    'type' => HM_Tc_Provider_ProviderModel::TYPE_PROVIDER,
                    'created' => date('Y-m-d'),
                    'created_by' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
                    'department_id' => 0,
                    'dzo_id' => 0,
                    'pass_by' => 0,
                    'prefix_id' => 0
                )
            );
            $this->_providersNamesUnified[$prov->provider_id] = $this->_stringUnify($prov->name);
        } else {
            $prov = Zend_Registry::get('serviceContainer')->getService('TcProvider')->getOne(
                Zend_Registry::get('serviceContainer')->getService('TcProvider')->find($providerId)
            );
        }

        $providerId = $prov->provider_id;
        return $providerId;
    }

    protected function _getPeriodFromQuarter($quarter, $year)
    {
        switch ($quarter) {
            case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_1:
                return $year.'-01-01';
            case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_2:
                return $year.'-04-01';
            case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_3:
                return $year.'-07-01';
            case HM_Tc_SessionQuarter_SessionQuarterModel::QUARTER_4:
                return $year.'-10-01';
        }
    }

    protected function _getProvidersNamesUnified()
    {
        $providersNamesUnified = array();
        $where = array();
        $providers = Zend_Registry::get('serviceContainer')->getService('TcProvider')->fetchAll($where);

        foreach ($providers as $provider) {
            $providersNamesUnified[$provider->provider_id] = $this->_stringUnify($provider->name);
        }

        return $providersNamesUnified;
    }

    protected function _getSubjectsNamesUnified($providerId = false)
    {
        $subjectsNamesUnified = array();
        $where = array();

        if ($providerId) $where['provider_id = ? '] = $providerId;
        $providerSubjectLinks = Zend_Registry::get('serviceContainer')->getService('TcProviderSubject')->fetchAll($where);

        foreach ($providerSubjectLinks as $link) {
            $subject = Zend_Registry::get('serviceContainer')->getService('Subject')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Subject')->find($link->subject_id)
            );
            $subjectsNamesUnified[$subject->subid] = $this->_stringUnify($subject->name);
        }

        return $subjectsNamesUnified;
    }

    protected function _getDirectionsNamesUnified($subjectId = false)
    {
        $directionsNamesUnified = array();
        $select = Zend_Registry::get('serviceContainer')->getService('Classifier')->getSelect();
        $select->from('classifiers', array('classifier_id', 'name'))
            ->joinLeft(array('cl' => 'classifiers_links'), 'cl.classifier_id = classifiers.classifier_id', array())
            ->where('cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT);

        if ($subjectId) $select->where('cl.item_id = ' . $subjectId);

        $results = $select->query()->fetchAll();

        foreach ($results as $result) {
            $directionsNamesUnified[$result['classifier_id']] = $this->_stringUnify($result['name']);
        }

        return $directionsNamesUnified;
    }

    private function _stringUnify($string)
    {
        $string = mb_strtolower($string);
        $string = trim(preg_replace('/[^a-zа-яё\d]+/iu', '', $string));
        return $string;
    }

}