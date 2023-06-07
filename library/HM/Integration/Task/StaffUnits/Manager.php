<?php

class HM_Integration_Task_StaffUnits_Manager extends HM_Integration_Abstract_Manager implements HM_Integration_Interface_Manager
{
    const POSITION_FREE = 'tmp-employee-0000-0000-0000-000000000000';

    protected $_targetServiceName = 'StaffUnit';

    static public function getTaskId()
    {
        return HM_Integration_Manager::TASK_STAFF_UNITS;
    }

    public function import($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        try {

            if ($masterManager->isAllSources() && $masterManager->isFirstRun(HM_Integration_Manager::TASK_STAFF_UNITS)) {
                $this->getTargetService()->deleteBy(array(
                    'staff_unit_id_external LIKE ?' => $integrationSource['key'] . '-%'
                ));
            }

            $items = $this->getService()->fetchAll();

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

            $masterManager = Zend_Registry::get('integrationManager');
            $masterManager->registerDeferredAction(self::getTaskId(), 'createVacantPositions');
            $masterManager->registerDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'rebuildNestedSet', 100);

        } catch (Exception $e) {
            $this->breakAll($e->getMessage());
        }

        $this->answer(HM_Integration_Abstract_Manager::TASK_STATUS_SUCCESS);

        return $this;
    }

    public function sync($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(self::getTaskId(), 'createVacantPositions');

        return parent::sync($integrationSource);
    }

    public function update($integrationSource)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->registerDeferredAction(self::getTaskId(), 'createVacantPositions');

        return parent::update($integrationSource);
    }

    public function needUpdate($model, $id)
    {
        $updates = array();
        $profile = Zend_Registry::get('serviceContainer')->getService('StaffUnit')->find($id)->current();
        foreach ($model->getAttributes() as $key => $value) {
            if ($profile->$key != $value) $updates[$key] = $value;
        }
        return $updates;
    }

    public function match(HM_Integration_Abstract_Model $model)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('staffUnitIdExternal2staffUnitId')) {
            $masterManager->initCache('staffUnitIdExternal2staffUnitId');
        }

        if ($value = $masterManager->getCachedValue($model->staff_unit_id_external, 'staffUnitIdExternal2staffUnitId')) {
            return $value;
        }

        return false;
    }

    protected function _insert(HM_Integration_Abstract_Model $model)
    {
        parent::_insert($model);

        $masterManager = Zend_Registry::get('integrationManager');
        $masterManager->setCachedValue($model->staff_unit_id_external, $model->getId(), 'staffUnitIdExternal2staffUnitId');
    }

    // не удаляем, но сводим до нуля
    protected function _delete($match)
    {
        $primaryKey = $this->_targetServiceTable->getPrimaryKey();
        if (is_array($primaryKey)) $primaryKey = array_shift($primaryKey);

        $this->_targetServiceAdapter->update(
            $this->_targetServiceTable->getTableName(),
            array('quantity' => 0),
            $this->_targetServiceAdapter->quoteInto($primaryKey . ' = ?', $match)
        );
    }

    /************* Deferred ******************/

    public function createVacantPositions()
    {
        $masterManager = Zend_Registry::get('integrationManager');

        $orgstructureService = Zend_Registry::get('serviceContainer')->getService('Orgstructure');
        $vacanciesService = Zend_Registry::get('serviceContainer')->getService('RecruitVacancy');

        $activeVacancies = array();
        $select = $vacanciesService->getSelect()
            ->from(array('rv' => 'recruit_vacancies'), array('rv.position_id'))
            ->join(array('soo' => 'structure_of_organ'), 'rv.position_id = soo.soid', array('soo.owner_soid', 'soo.staff_unit_id'))
            ->where('rv.status != 2');

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
            $activeVacancies[$row['owner_soid']][$row['staff_unit_id']][] = $row['position_id'];
        }

        $select = $orgstructureService->getSelect()
            ->from(array('su' => 'staff_units'), array(
                'department_id' => 'su.soid',
                'su.staff_unit_id',
                'su.profile_id',
                'su.name',
                'profile' => 'ap.name',
                'su.quantity',
                'soids_actual' => new Zend_Db_Expr('GROUP_CONCAT(soo.soid)'),
                'quantity_actual' => new Zend_Db_Expr('COUNT(soo.soid)'),
            ))
            ->joinLeft(
                array('ap' => 'at_profiles'),
                'su.profile_id = ap.profile_id',
                array()
            )
            ->joinLeft(
                array('soo' => 'structure_of_organ'),
                'su.staff_unit_id = soo.staff_unit_id AND soo.type = ' . HM_Orgstructure_OrgstructureModel::TYPE_VACANCY,
                array()
            )
            ->group(array('soo.owner_soid' ,'su.soid', 'su.staff_unit_id', 'su.profile_id', 'su.quantity', 'su.name', 'ap.name'));

        $rowset = $select->query()->fetchAll();
        if (count($rowset)) {
            foreach ($rowset as $row) {

                if (strlen($row['profile'])) {
                    $arr = explode('/', $row['profile']);
                    $name = trim($arr[0]) . '*';
                } else {
                    $name = _('Вакансия');
                }

                if ($diff = $row['quantity'] - $row['quantity_actual']) {
                    if ($diff > 0) {

                        // если по ШР положено больше чем есть - вставляем
                        for ($i = 0; $i < $diff; $i++) {
                            $this->_targetServiceAdapter->insert('structure_of_organ', array(
                                'name' => $name,
                                'owner_soid' => $row['department_id'],
                                'staff_unit_id' => $row['staff_unit_id'],
                                'profile_id' => $row['profile_id'],
                                'soid_external' => self::POSITION_FREE,
                                'type' => HM_Orgstructure_OrgstructureModel::TYPE_VACANCY,
                                'created_at' => date('Y-m-d'),
                            ));
                        }
                    } else {
                        // если по ШР положено меньше чем есть - удаляем, но не те, по которым подбор
                        $soidsToDel = explode(',', $row['soids_actual']);

                        if (isset($activeVacancies[$row['department_id']][$row['staff_unit_id']])) {
                            $soidsToDel = array_diff($soidsToDel, $activeVacancies[$row['department_id']][$row['staff_unit_id']]);
                        }

                        $soidsToDel = array_slice($soidsToDel, $row['quantity']);

                        if (count($soidsToDel)) {
                            $orgstructureService->deleteBy(array(
                                'soid IN (?)' => $soidsToDel,
                            ));
                        }
                    }
                }

                if ($row['soids_actual']) {

                    $soidsToUpdate = explode(',', $row['soids_actual']);
                    $orgstructureService->updateWhere(array(
                        'name' => $name,
                    ), array(
                        'soid IN (?)' => $soidsToUpdate,
                    ));
                }
            }
            $masterManager->executeDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'blockHangingInAir');
            $masterManager->executeDeferredAction(HM_Integration_Task_Orgstructure_Manager::getTaskId(), 'rebuildNestedSet', 100);
        }

        $this->log('Должности приведены в соответствие с штатным расписанием');
    }
}