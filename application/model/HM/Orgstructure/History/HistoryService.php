<?php
class HM_Orgstructure_History_HistoryService extends HM_Service_Nested
{
    protected $_adapter;
    protected $_childrenMap = array();

    /**
     * @method Добавляет подразделение в архив
     * @param array $unit - набор данных подразделения
     */
    public function archive($unit)
    {
        $this->insert($unit);
    }

    /**
     * @method Получает soid`ы всех подразделений ветки,
     * которые есть в structure_of_organ и в архиве
     * @param array $soids - soid`ы ветки подраздеений Orgstructure
     * @return array - Массив soid`в|Пустой массив
     */
    public function getSameOrgstructureBranch($soids)
    {
        return $this->getSelect()
            ->from('structure_of_organ_history', array('soid'))
            ->where($this->quoteInto(
                array('soid IN (?)'),
                array($soids),
                false,
                null,
                array('lft')
            ))
            ->query()
            ->fetchAll();
    }

    /**
     * @method Восстанавливет left-right-level по owner_soid
     */
    public function repairStructure()
    {
        // если в структуре есть blocked - неправильно работает восстановление
        $this->deleteBy(array('blocked = ?' => 1));
        $this->_adapter = $this->getService('User')->getMapper()->getAdapter()->getAdapter();

        $select = $this->getSelect()->from('structure_of_organ_history', array('soid', 'owner_soid'));
        $rowset = $select->query()->fetchAll();
        foreach ($rowset as $row) {

            if (++$_childrenMapCount % 1000 == 0) Zend_Registry::get('log_system')->log('Orgstructure history children map loop: ' . $_childrenMapCount, Zend_Log::ERR);

            if (!isset($this->_childrenMap[$row['owner_soid']])) {
                $this->_childrenMap[$row['owner_soid']] = array($row['soid']);
            } else {
                $this->_childrenMap[$row['owner_soid']][] = $row['soid'];
            }
        }

        Zend_Registry::get('log_system')->log('Orgstructure history recursive update start', Zend_Log::ERR);
        if (count($this->_childrenMap[0])) {
            $left = 0;
            foreach ($this->_childrenMap[0] as $soid) {
                $left = $this->_update($soid, ++$left);
            }
        }
    }

    protected function _update($soid, $left = 1, $level = 0, $ownerSoid = 0)
    {
        static $_updateCount;
        if (++$_updateCount % 1000 == 0) Zend_Registry::get('log_system')->log('Orgstructure history recursive update loop: ' . $_updateCount, Zend_Log::ERR);

        $right = $left + 1;
        if (is_array($this->_childrenMap[$soid]))
            foreach ($this->_childrenMap[$soid] as $childSoid) {
                $right = $this->_update($childSoid, $right, $level + 1, $soid);
            }

        $this->_adapter->query("UPDATE structure_of_organ_history SET lft = {$left}, rgt = {$right}, level = {$level} WHERE soid = {$soid}");
        return ++$right;
    }
}
