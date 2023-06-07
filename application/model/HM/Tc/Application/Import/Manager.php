<?php
class HM_Tc_Application_Import_Manager
{
    protected $_inserts = array();
    protected $_notHandled = array();
    protected $_noNameCount = 0;
    protected $_sourceCount = 0;

    const CACHE_NAME = 'HM_Tc_Application_Import_Manager';

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init($items)
    {
        $this->_notHandled = $items['notHandled'];
        $this->_noNameCount = $items['noNameCount'];
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

    public function getNoNameCount()
    {
        return $this->_noNameCount;
    }

    public function getSkipped()
    {
        return array(
            'noFio'        => $this->_notHandled['noFio'],
            'noUser'       => $this->_notHandled['noUser'],
            'noDepartment' => $this->_notHandled['noDepartment'],
            'tooHighDep'   => $this->_notHandled['tooHighDep']
        );
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
            foreach($this->_inserts as $insert) {
                $values = $insert->getValues();

                $service = $values['user_id'] ? 'TcApplication' : 'TcApplicationImpersonal';
                Zend_Registry::get('serviceContainer')->getService($service)->insert($values);

            }
        }
    }

    static public function prepareString($str)
    {
        return strtolower(preg_replace('/\s./', '', $str));
    }
}