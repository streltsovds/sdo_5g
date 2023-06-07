<?php

class HM_Integration_Task_Orgstructure_Manager extends HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    const ROOT = 0;

    protected $_targetServiceName = 'Orgstructure';

    protected $_models = array();

    protected $_childrenMap = array();
    protected $_parentsMap = array();
    protected $_idMap = array();

    protected $_items;

    static public function getTaskId()
    {
        return HM_Integration_Manager::TASK_ORGSTRUCTURE;
    }

    public function import($integrationSource)
    {
        try {
            $excludeRoot = self::getRootLevel();

            // kill'em all
            $masterManager = Zend_Registry::get('integrationManager');
            if ($masterManager->isAllSources() && $masterManager->isFirstRun(HM_Integration_Manager::TASK_ORGSTRUCTURE) && count($excludeRoot)) {
                $this->getTargetService()->deleteBy($this->getTargetService()->quoteInto('soid_external IS NULL'));
                $this->getTargetService()->deleteBy($this->getTargetService()->quoteInto(
                    array('(soid_external NOT IN (?))', ' AND (soid_external LIKE ?)'),
                    array($excludeRoot, $integrationSource['key'] . '-%')
                ));
            }

            $source = $this->getService()->getSource();
            if (!isset($source['custom_group_soid'])) $source['custom_group_soid'] = $source['soid'];
            if (!isset($source['custom_group_soid_external'])) $source['custom_group_soid_external'] = $source['soid_external'];

            $customGroup = new HM_Integration_Abstract_Model_Nested();
            $customGroup->setId($source['custom_group_soid'])
                ->setExternalId($source['custom_group_soid_external'])
                ->setParentId($source['soid'])
                ->setParentExternalId($source['soid_external']);
            $this->_models[$source['custom_group_soid_external']] = $customGroup;

            $items = $this->getService()->fetchAll();

            foreach ($items as $item) {

                $this->_processCustomGroup($item);

                $model = $this->getAdapter()
                    ->init(new HM_Integration_Abstract_Model_Nested())
                    ->convert($item)
                    ->getModel();

                $this->_models[$model->getExternalId()] = $model;
            }

            $this->_insertAll();

            $masterManager = Zend_Registry::get('integrationManager');
            $masterManager->registerDeferredAction($this->getTaskId(), 'rebuildNestedSet', 100);
//            $masterManager->registerDeferredAction($this->getTaskId(), 'deleteUnusedClassifierLinks');
//
//            $masterManager->registerDeferredAction($this->getTaskId(), 'blockHangingInAir');
            $masterManager->registerDeferredAction($this->getTaskId(), 'blockEmptyDepartments', 200); // after nested set

        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function update($integrationSource)
    {
        try {

            $this->_items = $this->getService()->fetchChanged();
            $this->_initItemsNestedLevel();

            uasort($this->_items, array('HM_Integration_Task_Orgstructure_Manager', '_sortByNestedLevel'));

            $cntDeleted = $cntUpdated = $cntInserted = 0;
            foreach($this->_items as $item) {

                $this->_processCustomGroup($item);

                try {
                    $model = $this->getAdapter()
                        ->init(new HM_Integration_Abstract_Model_Nested())
                        ->convert($item)
                        ->getModel();

                    $match = $this->match($model);
                    if ($this->todel($model)) {
                        if ($match) {
                            $this->_delete($match);
                            $cntDeleted++;
                        }
                    } else {
                        if ($match) {
                            $this->_update($model, $match);
                            $cntUpdated++;
                        } else {
                            $this->_insert($model);
                            $cntInserted++;
                        }
                    }
                } catch (Exception $e) {
                    $this->log(sprintf('Ошибка синхронизации записи %s: %s', $model->getExternalId(), $e->getMessage()));
                }
            }

            $this->log(sprintf('Добавлено: %s, изменено: %s, удалено: %s', $cntInserted, $cntUpdated, $cntDeleted));

        } catch (Exception $e) {
            $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_FAILURE);
            $this->log('Общая ошибка, синхронизация остановлено');
            return $this;
        }

        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'rebuildNestedSet', 100);

        $masterManager->registerDeferredAction($this->getTaskId(), 'blockEmptyDepartments', 200); // after nested set

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function sync($integrationSource)
    {
        parent::sync($integrationSource);

        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'rebuildNestedSet', 100);

        $masterManager->registerDeferredAction($this->getTaskId(), 'blockEmptyDepartments', 200); // after nested set

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
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

        if ($parentId = $masterManager->getCachedValue($model->getParentExternalId(), 'soidExternal2soid')) {

            $model->setAttribute('owner_soid', $parentId);
            parent::_insert($model);

            $masterManager->setCachedValue($model->soid_external, $model->getId(), 'soidExternal2soid');
            return true;
        }
    }

    protected function _update(HM_Integration_Abstract_Model $model, $id)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        if ($parentId = $masterManager->getCachedValue($model->getParentExternalId(), 'soidExternal2soid')) {
            $model->setAttribute('owner_soid', $masterManager->getCachedValue($model->getParentExternalId(), 'soidExternal2soid'));
            parent::_update($model, $id);

            return true;
        }
    }


    protected function _delete($soid)
    {
        $children = $this->_targetService->getChildren($soid, false);
        $todel = $children->getList('soid');
        $todel[] = $soid; // itself

        // backup
        $this->_targetServiceAdapter->query(sprintf(
            "INSERT INTO structure_of_organ_history
                SELECT * FROM structure_of_organ WHERE type=%d AND soid IN (%s)
            ",
            HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
            implode(',', $todel)
        ));

        // kill'em all
        $this->_targetServiceAdapter->query(sprintf(
            "DELETE FROM structure_of_organ WHERE soid IN (%s)",
            implode(',', $todel)
        ));

        // remove from cache
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->unsetCachedValues($todel, 'soidExternal2soid');

        return true;
    }

    protected function _unblock($model)
    {
        $model->setAttribute('blocked', 0);
        return $this;
    }

    protected function _getCustomGroups()
    {
        $soidExternals = array();

        $masterManager = Zend_Registry::get('integrationManager');
        $sources = $masterManager->getSources();

        foreach ($sources as $source) {
            $soidExternals[] = $source['custom_group_soid_external'];
        }
        return $soidExternals;
    }

    static public function getRootLevel()
    {
        $soidExternals = array();

        $masterManager = Zend_Registry::get('integrationManager');

        $sources = $masterManager->getSources();

        foreach ($sources as $source) {
            $soidExternals[] = $source['soid_external'];
            $soidExternals[] = $source['custom_group_soid_external'];
        }
        return $soidExternals;
    }

    protected function _insertAll()
    {
        $_childrenMapCount = $_ownerUpdateCount = 0;

        $source = $this->getService()->getSource();
        $this->_idMap[$source['soid_external']] = $source['soid'];

        foreach ($this->_models as $model) {

            // иначе зацикливание
            if ($model->getParentExternalId() == $model->getExternalId()) continue;

            if (++$_childrenMapCount%1000 == 0) $this->log('children map loop: ' . $_childrenMapCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

            if (!isset($this->_childrenMap[$model->getParentExternalId()])) {
                $this->_childrenMap[$model->getParentExternalId()] = array($model->getExternalId());
            } else {
                $this->_childrenMap[$model->getParentExternalId()][] = $model->getExternalId();
            }
        }

        $this->log('recursive insert started', HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

        $roots = array($source['soid_external'] => $this->_childrenMap[$source['soid_external']]);

        if (count($roots)) {
            foreach ($roots as $rootExternalId => $root) {
                foreach ($root as $externalId) {
                    $this->_insertRecursive($this->_models[$externalId], $rootExternalId);
                }
            }
        }

        $this->log('owner update started', HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

        foreach ($this->_parentsMap as $id => $parentExternalId) {

            if (++$_ownerUpdateCount%1000 == 0) $this->log('owner update loop: ' . $_ownerUpdateCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

            $this->_targetServiceAdapter->update(
                $this->_targetServiceTable->getTableName(),
                array('owner_soid' => $this->_idMap[$parentExternalId]),
                $this->_targetServiceAdapter->quoteInto('soid = ?', $id)
            );
        }
        $this->log('owner update finished', HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);
    }

    protected function _insertRecursive($model, $parentExternalId)
    {
        static $_plainInsertCount;
        if (++$_plainInsertCount%1000 == 0) $this->log('plain insert loop: ' . $_plainInsertCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

        if (is_array($this->_childrenMap[$model->getExternalId()])) {
            foreach ($this->_childrenMap[$model->getExternalId()] as $externalId) {
                $this->_insertRecursive($this->_models[$externalId], $model->getExternalId());
            }
        }

        // не вставляем custom_group
        if (!$model->getId()) {
            // пока не заботимся о lft/rgt - всё равно еще вставлять должности
            $this->_targetServiceAdapter->insert($this->_targetServiceTable->getTableName(), $model->getAttributes());
            $model->setId($this->_targetServiceAdapter->lastInsertId());
        }

        if ($classifierId = $model->getExternalAttribute('city')) {
            Zend_Registry::get('serviceContainer')->getService('Classifier')->linkItem(
                $model->getId(),
                HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
                $classifierId
            );
        }

        $this->_parentsMap[$model->getId()] = $parentExternalId;
        $this->_idMap[$model->getExternalId()] = $model->getId();

        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->setCachedValue($model->soid_external, $model->getId(), 'soidExternal2soid');

        return true;
    }

    protected function _processCustomGroup(&$item)
    {
        if ($item['DetachedDep'] && ($item['DetachedDep'] == "Да")) {
            $source = $this->getService()->getSource();
            if ($source['custom_group_soid_external']) {
                $item['idParentSubdivision'] = $source['custom_group_soid_external'];
            }
        }
    }

    protected function _sortByNestedLevel($item1, $item2)
    {
        return $item1['level'] < $item2['level'] ? -1 : 1;
    }

    protected function _initItemsNestedLevel()
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('soidExternal2soid')) {
            $masterManager->initCache('soidExternal2soid');
        }

        $items = $this->_items;
        $newItems = array();

        foreach ($this->_items as $key => &$item) {

            // все удалённые - в первую очередь
            if ($item['isDeleted']) {
                $item['level'] = 0;
                unset($items[$key]);
                continue;
            }

            // все измененные + добавляеvst потомки 1-го уровня
            if ($masterManager->getCachedValue($item['idParentSubdivision'], 'soidExternal2soid')) {
                $item['level'] = 1;
                unset($items[$key]);
                $newItems[$key] = true;
                continue;
            }
        }

        $level = 2;
        while (count($items)) {
            $newLevelItems = array();
            foreach ($items as $key => &$item) {
                if (isset($newItems[$item['idParentSubdivision']])) {
                    $this->_items[$key]['level'] = $level;
                    $newLevelItems[$key] = true;
                }
            }
            $newItems = array_merge($newItems, $newLevelItems);
            $items = array_diff_key($items, $newItems);
            if (++$level >= 20) {
                foreach ($items as $key => $item) {
                    $this->_items[$key]['level'] = $level;
                }
                break;
            } // мы не хотим infinite loop из-за какой-нить лажи в данных
        }
    }

    /************* Deferred ******************/


    public function rebuildNestedSet()
    {
        $_childrenMapCount = 0;

        $select = $this->getTargetService()->getSelect()
            ->from('structure_of_organ', array('soid', 'owner_soid'))
            ->where('blocked != 1');

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {

            if (++$_childrenMapCount%1000 == 0) $this->log('children map loop: ' . $_childrenMapCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

            if (!isset($this->_childrenMap[$row['owner_soid']])) {
                $this->_childrenMap[(int)$row['owner_soid']] = array($row['soid']);
            } else {
                $this->_childrenMap[(int)$row['owner_soid']][] = $row['soid'];
            }
        }

        $this->log('recursive update started', HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);
        if (count($this->_childrenMap[self::ROOT])) {
            $left = 0;
            foreach ($this->_childrenMap[self::ROOT] as $soid) {
                $left = $this->_updateRecursive($soid, ++$left, 0);
            }
        }
        $this->log('recursive update finished', HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);
        $this->log('Восстановлена древовидная структура');

    }

    protected function _updateRecursive($soid, $left, $level)
    {
        static $_updateCount;
        if (++$_updateCount%1000 == 0) $this->log('recursive update loop: ' . $_updateCount, HM_Integration_Abstract_Manager::LOG_LEVEL_PARANOID);

        $right = $left + 1;
        if (is_array($this->_childrenMap[$soid])) {
            foreach ($this->_childrenMap[$soid] as $childSoid) {
                $right = $this->_updateRecursive($childSoid, $right, $level + 1);
            }
        }
        $this->_targetServiceAdapter->update(
            $this->_targetServiceTable->getTableName(),
            array('lft' => $left, 'rgt' => $right, 'level' => $level),
            $this->_targetServiceAdapter->quoteInto('soid = ?', $soid)
        );
        return ++$right;
    }

    public function deleteUnusedClassifierLinks()
    {
        $todel = array();
        $service = Zend_Registry::get('serviceContainer')->getService('ClassifierLink');

        $select = $service->getSelect()
            ->from(array('cl' => 'classifiers_links'), array('classifier_id', 'item_id', 'type'))
            ->joinLeft(array('p' => 'People'), 'p.MID = cl.item_id', array())
            ->where("cl.type = 3") //HM_Classifier_Link_LinkModel::TYPE_PEOPLE
            ->where("p.MID IS NULL");

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
            $todel[] = $row;
        }

        $select = $service->getSelect()
            ->from(array('cl' => 'classifiers_links'), array('classifier_id', 'item_id', 'type'))
            ->joinLeft(array('soo' => 'structure_of_organ'), 'soo.soid = cl.item_id', array())
            ->where("cl.type = 4") //HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
            ->where("soo.soid IS NULL");

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
            $todel[] = $row;
        }

        foreach ($todel as $item) {
            $service->deleteBy($service->quoteInto(array(
                'classifier_id = ? AND ',
                'item_id = ? AND ',
                'type = ?',
            ), array(
                $item['classifier_id'],
                $item['item_id'],
                $item['type'],
            )));
        }

        $this->log('Удалены неиспользуемые связи с классификаторами');
    }

    public function blockHangingInAir()
    {
        $roots = implode("','", self::getRootLevel());
        $this->_targetServiceAdapter->query("UPDATE structure_of_organ SET blocked=1 WHERE soid IN (SELECT s.soid FROM structure_of_organ s LEFT JOIN structure_of_organ p ON (s.owner_soid = p.soid) WHERE p.soid IS NULL) AND soid_external NOT IN ('{$roots}')");
        $this->log('Заблокированы подразделения и должности, висящие в воздухе');
    }

    public function blockEmptyDepartments()
    {
        // либерализация интеграции
        return true;

        $this->_targetServiceAdapter->query("UPDATE structure_of_organ SET blocked=1 WHERE mid IN (SELECT p.MID FROM People p WHERE p.blocked=1)");

        $subSelect = "SELECT s.soid FROM structure_of_organ s LEFT JOIN structure_of_organ pos ON (s.lft < pos.lft AND s.rgt > pos.rgt AND pos.type IN (1, -3) AND pos.blocked != 1 AND ((pos.employee_status != 1) OR pos.employee_status IS NULL)";

        $this->_targetServiceAdapter->query("UPDATE structure_of_organ SET blocked=1 WHERE soid IN ({$subSelect}) WHERE s.type=0 AND pos.soid IS NULL)");
            $this->_targetServiceAdapter->query("UPDATE structure_of_organ SET blocked=0 WHERE soid IN ({$subSelect}) WHERE s.type=0 AND pos.soid IS NOT NULL)");

        $this->log('Заблокированы пустые подразделения и должности без людей и без вакансий; разблокированы подразделения, в которые пришли люди');
    }
}