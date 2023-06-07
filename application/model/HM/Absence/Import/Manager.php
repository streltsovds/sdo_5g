<?php
class HM_Absence_Import_Manager
{
    const CACHE_NAME = 'HM_Absence_Import_Manager';

    protected $_items = null;

    protected $_existingItems     = array();
    // в данном классе это не массив externalId => realId, а скорее realId => uniqueString (userExtId+type+begin)
    protected $_existingIds       = array();

    protected $_existingPeople    = array();
    protected $_existingPeopleIds = array();

    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();

    protected $_loginCount = 0;
    private $_restoredFromCache = false;

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'inserts' => $this->_inserts,
                'updates' => $this->_updates,
                'deletes' => $this->_deletes,
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }
    
    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_updates = $actions['updates'];
            $this->_deletes = $actions['deletes'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getUpdates()
    {
        return $this->_updates;
    }

    public function getDeletes()
    {
        return $this->_deletes;
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getUpdatesCount()
    {
        return count($this->_updates);
    }

    public function getDeletesCount()
    {
        return count($this->_deletes);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount() + $this->getDeletesCount() + $this->getInsertsPeopleCount() + $this->getUpdatesPeopleCount();
    }

    private function _formatDate($date)
    {
        if (!strtotime($date)) return '';

        $begin =  new HM_date ($date);
        return $this->getService('Absence')->getDateTime($begin->getTimestamp());
    }

    protected function _init()
    {
        $this->getService('Absence')->deleteBy(array('1 = ?'=>1)); //#18214 - решили удалить все данные, т.к. к ним нет привязки по ключу

        $items = $this->getService('Absence')->fetchAll();

        if (count($items)) {
            foreach($items as $item) {
                $this->_existingItems[$item->absence_id] = $item;
                $this->_existingIds[$item->absence_id]   = $this->_getItemUniqueId($item);
            }
        }

        $this->_items = &$items;

        $persons = $this->getService('User')->fetchAll();

        if (count($persons)) {
            foreach($persons as $person) {
                $this->_existingPeople[$person->MID] = $person;
                if (strlen($person->mid_external)) {
                    $person->mid_external = trim($person->mid_external);
                    $this->_existingPeopleIds[$person->mid_external] = $person->MID;
                }
            }
        }

    }

    protected function _isItemExists($itemUniqueId)
    {
        return in_array($itemUniqueId, $this->_existingIds)? array_shift(array_keys($this->_existingIds, $itemUniqueId)) : false;
    }

    protected function _isPersonExists($personExternalId)
    {
        if (isset($this->_existingPeopleIds[$personExternalId])) {
            return $this->_existingPeopleIds[$personExternalId];
        }

        return false;
    }

    /**
     * Возвращает страку однозначно описывающее событие
     * @param $item
     * @return string
     */
    private function _getItemUniqueId($item)
    {
        $begin = $this->_formatDate($item->absence_begin);
        return  md5(sprintf("%s:%s:%s", $item->user_external_id, $item->type, $begin));
    }

    public function init($items)
    {

        if ($this->_restoredFromCache) {
            $this->_init();
            return true;
        }

        if (count($items)) {

            $this->_init();

            $deleteItems = $this->_existingItems;

            foreach($items as $item) {

                $uniqueId = $this->_getItemUniqueId($item);

                $existsId = $this->_isItemExists($uniqueId);

                if ( !$existsId ) {
                    // insert new item
                    $this->_inserts[] = $item;
                } else {
                    unset($deleteItems[$existsId]);
                }

            }

            $this->_deletes = $deleteItems;
        }

        $this->saveToCache();
    }


    public function import()
    {
        if (Zend_Registry::get('config')->integration->import->debug) {
            Zend_Registry::get('log_integration')->log(sprintf('ABST (назначены|отменены): %s|%s', count($this->_inserts), count($this->_deletes)), Zend_Log::ERR);
        }

        if ( count($this->_deletes) ) {

            foreach($this->_deletes as $key => $val){
                $this->getService('Absence')->delete($key);
            }
        }


        if ( count($this->_inserts) ) {
            foreach($this->_inserts as $insert){
                if ($insert->user_id = (int) $this->_isPersonExists($insert->user_external_id)) {
                    $this->getService('Absence')->insert($insert->getValues());
                }
            }
        }

        if (Zend_Registry::get('config')->integration->import->debug) {
            Zend_Registry::get('log_integration')->log(sprintf('ABST (завершен)'), Zend_Log::ERR);
        }

        return true;
    }

}