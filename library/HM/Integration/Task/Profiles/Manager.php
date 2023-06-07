<?php

class HM_Integration_Task_Profiles_Manager extends HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    protected $_targetServiceName = 'AtProfile';

    static public function getTaskId()
    {
        return HM_Integration_Manager::TASK_PROFILES;
    }

    public function import($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        try {
            if ($masterManager->isAllSources() && $masterManager->isFirstRun(HM_Integration_Manager::TASK_PROFILES)) {
                // удалить все программы кроме дефолтных (которые в db_dump2)
                // внимание! не стоит запускать импорт оргструктуры если в базе есть нужные программы
                //
                Zend_Registry::get('serviceContainer')->getService('Programm')->deleteBy('programm_id > 10');
                Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->deleteBy('programm_event_id > 6');
                Zend_Registry::get('serviceContainer')->getService('ProgrammEventUser')->deleteBy('1=1');
                Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->deleteBy('evaluation_type_id > 6');

                Zend_Registry::get('serviceContainer')->getService('AtCategory')->deleteBy(array(
                    'category_id_external IS NOT NULL AND category_id_external LIKE ?' => $integrationSource['key'] . '-%'
                ));
                $this->getTargetService()->deleteBy(array(
                    'profile_id_external IS NOT NULL AND profile_id_external LIKE ?' => $integrationSource['key'] . '-%'
                ));
            }

            $service = $this->getService()->setSource($integrationSource);
            $items = $service->fetchAll();

            foreach($items as $item) {

                if (empty($item['Category'])) $item['Category'] = _('Без названия');
                //$item['CategoryId'] = md5(mb_strtolower($item['Category']));

                try {
                    $model = $this->getAdapter()
                        ->init(new HM_Integration_Abstract_Model())
                        ->convert($item)
                        ->getModel();

                    $this->_insert($model);

                } catch (Exception $e) {
                    $this->log($e->getMessage());
                }
            }
        } catch (Exception $e) {
            // при импорте ответов не требуется
            //$this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_FAILURE);
            $this->breakAll($e->getMessage());
        }

        $masterManager->registerDeferredAction(self::getTaskId(), 'updateDepartmentPath', 300);
        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function sync($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(self::getTaskId(), 'updateDepartmentPath', 300);
        $masterManager->registerDeferredAction(self::getTaskId(), 'deleteUnusedProgramms');

        return parent::sync($integrationSource);
    }

    public function update($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(self::getTaskId(), 'updateDepartmentPath', 300);
        $masterManager->registerDeferredAction(self::getTaskId(), 'deleteUnusedProgramms');

        return parent::update($integrationSource);
    }

    public function needUpdate($model, $id)
    {
        $updates = array();
        $profile = Zend_Registry::get('serviceContainer')->getService('AtProfile')->find($id)->current();
        foreach ($model->getAttributes() as $key => $value) {
            if ($profile->$key != $value) $updates[$key] = $value;
        }
        return $updates;
    }

    public function match(HM_Integration_Abstract_Model $model)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('profileIdExternal2profileId')) {
            $masterManager->initCache('profileIdExternal2profileId');
        }

        if ($value = $masterManager->getCachedValue($model->profile_id_external, 'profileIdExternal2profileId')) {
            return $value;
        }

        return false;
    }

    protected function _insert(HM_Integration_Abstract_Model $model)
    {
        $profile = parent::_insert($model);

        $profile->setAttribute('profile_id', $profile->getId());
        // назначить дефолтную программу оценки
        Zend_Registry::get('serviceContainer')->getService('AtProfile')->assignProgramms($profile);

        // назначить все имеющиеся компетенции
        $criteriaIds = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->fetchAll(array('status = ?' => HM_At_Criterion_CriterionModel::STATUS_ACTUAL))->getList('criterion_id');
        Zend_Registry::get('serviceContainer')->getService('AtProfileCriterionValue')->assign($profile->profile_id, $criteriaIds, HM_At_Criterion_CriterionModel::TYPE_CORPORATE);

        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->setCachedValue($model->profile_id_external, $model->getId(), 'profileIdExternal2profileId');
    }

    /************* Deferred ******************/

    // только новым, пришедшим в процессе интеграции
    public function assignBaseProfiles()
    {
        $positions = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(array(
            'type = ?' => 1
        ));
        foreach ($positions as $position) {
            if ($position->profile_id) {
                $profile = Zend_Registry::get('serviceContainer')->getService('AtProfile')->findOne($position->profile_id);
                if ($profile && $profile->base_id) {
                    Zend_Registry::get('serviceContainer')->getService('AtProfile')->unassign(array($position->soid));
                    Zend_Registry::get('serviceContainer')->getService('AtProfile')->assign($profile->base_id, array($position->soid));
                }
            }
        }

        $this->log('Переназначены базовые профили');
    }

    public function updateDepartmentPath()
    {
        $paths = array();

        $profiles = Zend_Registry::get('serviceContainer')->getService('AtProfile')->fetchAll(array('department_path IS NULL OR department_path = ?' => ''));
        if (count($profiles)) {

            $soids = $profiles->getList('department_id');
            $departments = Zend_Registry::get('serviceContainer')->getService('Orgstructure')
                ->fetchAll(array('type = 0 AND soid IN (?)' => $soids));

            if (count($departments)) {
                foreach ($departments as $item) {
                    if (!empty($item->soid)) {
                        $paths[$item->soid] = $item->getOrgPath(true);
                    }
                }
            }

            foreach ($profiles as $profile) {
                $profile->department_path = isset($paths[$profile->department_id]) ? $paths[$profile->department_id] : '';
                Zend_Registry::get('serviceContainer')->getService('AtProfile')->update($profile->getData());
            }
        }

        $this->log('Отмечены подразделения - источники профилей');
    }

    public function deleteUnusedProgramms()
    {
        $todel = array();

        $select = Zend_Registry::get('serviceContainer')->getService('Programm')->getSelect()
            ->from(array('p' => 'programm'), array('programm_id'))
            ->joinLeft(array('ap' => 'at_profiles'), 'p.item_id = ap.profile_id', array())
            ->where("p.item_type = 1") //HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE
            ->where("p.programm_id > 10")
            ->where("ap.profile_id IS NULL");

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
            $todel[] = $row['programm_id'];
        }

        $select = Zend_Registry::get('serviceContainer')->getService('Programm')->getSelect()
            ->from(array('p' => 'programm'), array('programm_id'))
            ->joinLeft(array('ac' => 'at_categories'), 'p.item_id=ac.category_id', array())
            ->where("p.item_type = 0") //HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY
            ->where("p.programm_id > 10")
            ->where("ac.category_id IS NULL");

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
            $todel[] = $row['programm_id'];
        }

        $_updateCount = 0;
        foreach ($todel as $programmId) {
            Zend_Registry::get('serviceContainer')->getService('Programm')->delete($programmId);
            if (++$_updateCount%1000 == 0) $this->log('programm delete loop: ' . $_updateCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);
        }

        $this->log('Удалены неиспользуемые программы');
    }
}