<?php
class HM_Subject_Import_Manager
{
    protected $_existing = array();
    protected $_existingIds = array();

    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();

    const CACHE_NAME = 'HM_Subject_Import_Manager';

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $subjects = $this->getService('Subject')->fetchAll();

        if (count($subjects)) {
            foreach($subjects as $subject) {
                $this->_existing[$subject->subid] = $subject;
                if (strlen($subject->external_id)) {
                    $subject->external_id = trim($subject->external_id);
                    $this->_existingIds[$subject->external_id] = $subject->subid;
                }
            }
        }
    }

    protected function _needSubjectUpdate($subject)
    {
        $existingSubject = $this->_existing[$this->_existingIds[$subject->external_id]];

        $values = $subject->getValues(null, array('external_id'));

        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingSubject->{$key} != $value) {
                    return true;
                }
            }
        }

        return false;
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
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getUpdates()
    {
        return $this->_updates;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_inserts,
                 'updates' => $this->_updates,
                 'deletes' => $this->_deletes
            ),
            self::CACHE_NAME
        );
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

    public function init($items)
    {
        $this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                if (!isset($this->_existingIds[$item->external_id])) {
                    $this->_inserts[$item->external_id] = $item;
                } else {
                    $existingSubject = $this->_existing[$this->_existingIds[$item->external_id]];

                    if ($this->_needSubjectUpdate($item)) {
                        $item->subid = $existingSubject->subid;
                        $this->_updates[$item->external_id] = array('source' => $existingSubject, 'destination' => $item);
                    }

                    unset($this->_existingIds[$existingSubject->external_id]);
                    unset($this->_existing[$existingSubject->subid]);
                }
            }
        }

        if (count($this->_existing)) {
            $this->_deletes = $this->_existing;
        }

        $this->saveToCache();
    }

    public function import()
    {
        if (count($this->_inserts)) {

            foreach($this->_inserts as $id => $insert) {
                $values = $insert->getValues();
                $values['created'] = date('Y-m-d H:i:s');
                $subject = $this->getService('Subject')->insert($values);
                if ($subject) {
                    $this->_existingIds[$subject->external_id] = $subject->subid;
                }
            }
        }

        if (count($this->_updates)) {
            foreach($this->_updates as $id => $update) {
                $this->getService('Subject')->update($update['destination']->getValues());
            }
        }

    }

}