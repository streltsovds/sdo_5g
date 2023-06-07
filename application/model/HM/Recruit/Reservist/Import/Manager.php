<?php
class HM_Recruit_Reservist_Import_Manager
{
    protected $_inserts = array();
    protected $_sourceCount = 0;

    const CACHE_NAME = 'HM_Recruit_Reservist_Import_Manager';

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init($items)
    {
        $this->_sourceCount = $items['rowsCount'];
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getSourceCount()
    {
        return $this->_sourceCount;
    }


    public function getCount()
    {
        return $this->getInsertsCount();
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_inserts
            ),
            self::CACHE_NAME
        );
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {
        $this->_init($items);

        if ($this->_restoredFromCache) {
            return true;
        }

        $items = $items['models'];

        if (count($items)) {
            foreach($items as $item) {
                $this->_inserts[] = $item;
            }
        }

        $this->saveToCache();
    }

    public function import()
    {
        if (count($this->_inserts)) {
            $currentUser = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
            foreach($this->_inserts as $insert) {
                $values = $insert->getValues();
                $values['birthday'] = date('Y-m-d', strtotime($values['birthday']));
                $values['import_date'] = date('Y-m-d');
                $values['importer_id'] = $currentUser;
                $values['age'] = (int) $values['age'];

                $service = 'RecruitReservist';
                try {
                    Zend_Registry::get('serviceContainer')->getService($service)->insert($values);
                } catch (Exception $e) {
                    // just skip
                }
            }
        }
    }
}