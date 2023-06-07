<?php

class HM_Integration_Task_Users_Manager extends HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    protected $_targetServiceName = 'User';

    static public function getTaskId()
    {
        return HM_Integration_Manager::TASK_USERS;
    }

    public function import($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        try {

            if ($masterManager->isAllSources() && $masterManager->isFirstRun(HM_Integration_Manager::TASK_USERS)) {
                $userId = $this->getTargetService()->getCurrentUserId();
                $condition = $userId ? 'MID != ' . $userId : '1=1';
                $this->getTargetService()->deleteBy(array(
                    $condition . ' AND mid_external LIKE ?' => $integrationSource['key'] . '-%'
                ));
            }

            $items = $this->getService()->fetchAll();

            foreach($items as $item) {

                $model = $this->getAdapter()
                    ->init(new HM_Integration_Abstract_Model())
                    ->convert($item)
                    ->getModel();

                $this->_insert($model);
            }
        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function importHistory()
    {
        try {
            $items = $this->getService()->fetchAll();
            foreach($items as $item) {

                $adapter = $this->getAdapter()
                    ->init(new HM_Integration_Abstract_Model())
                    ->setDefaultAttributes(array(
                        'blocked' => 1
                    ))
                    ->setExternalAttributes($item)
                    ->mapAttributes($item);

                $model = $adapter->getModel();

                // загружаем только тех, кого сейчас уже нет
                if (!$this->match($model)) {
                    $this->_insert($model);
                }
            }
        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function needUpdate($model, $id)
    {
        $updates = array();
        $person = Zend_Registry::get('serviceContainer')->getService('User')->find($id)->current();
        foreach ($model->getAttributes() as $key => $value) {
            if ($key == 'BirthDate') $value .= ' 00:00:00.000';
            if ($person->$key != $value) $updates[$key] = $value;
        }
        return $updates;
    }

    public function match(HM_Integration_Abstract_Model $model)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('midExternal2mid')) {
            $masterManager->initCache('midExternal2mid');
        }

        if ($value = $masterManager->getCachedValue($model->mid_external, 'midExternal2mid')) {
            return $value;
        }

        return false;
    }

    protected function _insert(HM_Integration_Abstract_Model $model)
    {
        parent::_insert($model);

        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('newMid2midExternal')) {
            $masterManager->initCache('newMid2midExternal');
        }

        $masterManager->setCachedValue($model->mid_external, $userId = $model->getId(), 'midExternal2mid');
        $masterManager->setCachedValue($userId, $model->mid_external, 'newMid2midExternal');

        $this->_targetServiceAdapter->query("INSERT INTO es_user_notifies VALUES ({$userId}, 1, 16, 1)");
    }

    protected function _delete($match)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'blockEmptyDepartments', 200);

        return parent::_softDelete($match);
    }

    protected function _unblock($model)
    {
        $model->setAttribute('blocked', 0);
        return $this;
    }

    /************* Deferred ******************/

    public function blockUnemployed()
    {
        // либерализация интеграции
        return true;

        $this->_targetServiceAdapter->query("UPDATE People SET blocked=1 WHERE mid_external IS NOT NULL AND (LEN(mid_external) > 36) AND MID NOT IN (SELECT DISTINCT mid FROM structure_of_organ)");
        $this->log('Заблокированы пользователи, не привязанные к оргструктуре');
    }

    public function setLogins()
    {
        return true; // берём из AD

        $this->_targetServiceAdapter->query("UPDATE People SET Login = CASE WHEN (EMail != '') AND (CHARINDEX('@', EMail) > 0) THEN LEFT(Email, CHARINDEX('@', EMail)-1) ELSE '' END");
        $this->log('Пользователям назначены Login');
    }
}