<?php

class HM_Integration_Task_Positions_Manager extends HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    protected $_targetServiceName = 'Orgstructure';

    static public function getTaskId()
    {
        return HM_Integration_Manager::TASK_POSITIONS;
    }

    public function import($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        try {
            if ($masterManager->isAllSources() && $masterManager->isFirstRun(HM_Integration_Manager::TASK_POSITIONS)) {
                $this->getTargetService()->deleteBy(array(
                    'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'soid_external LIKE ?' => $integrationSource['key'] . '-%'
                ));
            }

            $service = $this->getService()->setSource($integrationSource);
            $items = $service->fetchAll();

            foreach($items as $item) {

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

            $this->registerDeferedActions();

        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function update($integrationSource)
    {
        $this->registerDeferedActions();
        return parent::update($integrationSource);
    }

    public function sync($integrationSource)
    {
        $this->registerDeferedActions();
        return parent::sync($integrationSource);
    }

    public function registerDeferedActions()
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'rebuildNestedSet', 100);

        $masterManager->registerDeferredAction(self::getTaskId(), 'assignProfiles');
        $masterManager->registerDeferredAction(self::getTaskId(), 'assignSupervisors');
        $masterManager->registerDeferredAction(self::getTaskId(), 'createNewcomers');

        $masterManager->registerDeferredAction(HM_Integration_Task_Users_Manager::getTaskId(), 'blockUnemployed');
        $masterManager->registerDeferredAction(HM_Integration_Task_Users_Manager::getTaskId(), 'setLogins');
    }

    public function needUpdate($model, $id)
    {
        $updates = array();
        $profile = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($id)->current();
        foreach ($model->getAttributes() as $key => $value) {
            if ($profile->$key != $value) $updates[$key] = $value;
        }
        return $updates;
    }

    public function match(HM_Integration_Abstract_Model $model)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('soidExternal2soid')) {
            $masterManager->initCache('soidExternal2soid');
        }

        if ($value = $masterManager->getCachedValue($model->soid_external, 'soidExternal2soid')) {
            return $value;
        }

        return false;
    }

    protected function _insert(HM_Integration_Abstract_Model $model)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (
            ($masterManager->getMethod() != 'import') &&
            $masterManager->getCachedValue($model->mid, 'newMid2midExternal')
        ){
            $model->setAttribute('is_first_position', 1);
        }

        parent::_insert($model);

        if ($model->is_first_position) {
            if (!$masterManager->cacheExists('newMid2soid')) {
                $masterManager->initCache('newMid2soid');
            }
            $masterManager->setCachedValue($model->mid, $model->getId(), 'newMid2soid');
        }

        $masterManager->setCachedValue($model->soid_external, $model->getId(), 'soidExternal2soid');
        if ($model->mid) $masterManager->setCachedValue($model->soid_external, $model->mid, 'soidExternal2mid');
    }

    protected function _update(HM_Integration_Abstract_Model $model, $id)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->initCache('soid2customProfileId');

        // не перетираем профили, назначенные вручную
        if ($customProfileId = $masterManager->getCachedValue($id, 'soid2customProfileId')) {
            $model->setAttribute('profile_id', $customProfileId);
        }

        return parent::_update($model, $id);
    }

    protected function _delete($match)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(HM_Integration_Task_Positions_Manager::getTaskId(), 'removeBlocked');
        $masterManager->registerDeferredAction(HM_Integration_Task_Positions_Manager::getTaskId(), 'updateVacancies');
        $masterManager->registerDeferredAction(HM_Integration_Task_Users_Manager::getTaskId(), 'blockUnemployed');

        //$masterManager->registerDeferredAction(array(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'rebuildNestedSet')); // похоже, это не обязательно

        return parent::_softDelete($match);
    }

    /************* Deferred ******************/

    public function removeBlocked()
    {
        $this->_targetServiceAdapter->query("INSERT INTO structure_of_organ_history SELECT * FROM structure_of_organ WHERE blocked=1");
        $this->_targetServiceAdapter->query("DELETE FROM structure_of_organ WHERE blocked=1");
//        $this->_targetServiceAdapter->query("UPDATE structure_of_organ_history SET deleted_at = '{$now}'");

        $this->log('Удалённые должностие отправлены в архив');
    }
    public function updateVacancies()
    {
        $this->_targetServiceAdapter->query("UPDATE structure_of_organ SET [type] = 1 WHERE [type] = -3 AND mid != 0");
        $this->_targetServiceAdapter->query("UPDATE structure_of_organ SET [type] = -3 WHERE [type] = 1 AND mid = 0");
        $this->log('Актуализированы типы Вакансия <=> ШЕ');
    }

    public function assignSupervisors()
    {
        $this->_targetServiceAdapter->query("DELETE FROM supervisors");
        $this->_targetServiceAdapter->query("INSERT INTO supervisors SELECT DISTINCT mid FROM structure_of_organ WHERE is_manager=1 AND blocked=0");
        $this->_targetServiceAdapter->query("
            INSERT INTO responsibilities (user_id, item_type, item_id) 
            SELECT structure_of_organ.mid, 1, structure_of_organ.owner_soid FROM structure_of_organ 
            INNER JOIN roles ON (structure_of_organ.mid = roles.mid) 
            WHERE structure_of_organ.is_manager=1 
                AND roles.role NOT LIKE '%admin%'
                AND roles.role NOT LIKE '%dean%'
                AND roles.role NOT LIKE '%recruiter%'
                AND roles.role NOT LIKE '%atmanager%'
                AND roles.role NOT LIKE '%labor_safety%'
                AND roles.role NOT LIKE '%manager%'
        ");
        $this->log('Назначены супервайзеры');
    }

    public function assignProfiles()
    {
        $this->log('Старт назначения профилей');

        $subselect = $this->getTargetService()->getSelect()
            ->from(array('pu' => 'programm_users'), array('user_id'))
            ->join(array('p' => 'programm'), 'p.programm_id = pu.programm_id', array())
            ->where('p.item_id = soo.profile_id')
            ->where('p.item_type = 1')
            ->group('pu.user_id'); //HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE

        $select = $this->getTargetService()->getSelect()
            ->from(array('soo' => 'structure_of_organ'), array('soid', 'profile_id'))
            ->where('mid != 0')
            ->where('blocked != 1')
            ->where('profile_id IS NOT NULL')
            ->where("mid NOT IN ({$subselect})")
            ->group(array('soid', 'profile_id'));

        $profiles2soids = array();
        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {

            if (!isset($profiles2soids[$row['profile_id']])) $profiles2soids[$row['profile_id']] = array();
            $profiles2soids[$row['profile_id']][] = $row['soid'];
        }

        $_profileCount = 0;
        foreach ($profiles2soids as $profileId => $soids) {

            if (++$_profileCount%10 == 0) $this->log('profile assign loop: ' . $_profileCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);
            Zend_Registry::get('serviceContainer')->getService('AtProfile')->assign($profileId, $soids);
        }

        $this->log('Новым должностям назначены профили/программы');
    }

    public function createNewcomers()
    {
        return true;

        $this->log('Старт назначения сессий адаптации');

        $masterManager = Zend_Registry::get('integrationManager');
        $newMid2soid = $masterManager->getCache('newMid2soid');

        $cnt = 0;
        if ($newMid2soid) {
            foreach ($newMid2soid as $newMid => $soid){
                if ($position = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne(
                    Zend_Registry::get('serviceContainer')->getService('Orgstructure')->findDependence(array('Parent', 'User'), $soid))
                ){
                    try {
                        Zend_Registry::get('serviceContainer')->getService('RecruitNewcomer')->createByPosition($position);
                    } catch (Exception $e) {
                        $this->log($e->getMessage());
                    }
                    $cnt++;
                }
            }
        }

        $this->log(sprintf('Новым сотрудникам назначены сессии адаптации (%s)', $cnt));
    }
}