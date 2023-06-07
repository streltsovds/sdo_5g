<?php
class HM_Project_Import_Manager
{
    protected $_existing = array();
    protected $_existingIds = array();

    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();

    const CACHE_NAME = 'HM_Project_Import_Manager';

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $projects = $this->getService('Project')->fetchAll();

        if (count($projects)) {
            foreach($projects as $project) {
                $this->_existing[$project->projid] = $project;
                if (strlen($project->external_id)) {
                    $project->external_id = trim($project->external_id);
                    $this->_existingIds[$project->external_id] = $project->projid;
                }
            }
        }
    }

    protected function _needProjectUpdate($project)
    {
        $existingProject = $this->_existing[$this->_existingIds[$project->external_id]];

        $values = $project->getValues(null, array('external_id'));

        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingProject->{$key} != $value) {
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
                    $existingProject = $this->_existing[$this->_existingIds[$item->external_id]];

                    if ($this->_needProjectUpdate($item)) {
                        $item->projid = $existingProject->projid;
                        $this->_updates[$item->external_id] = array('source' => $existingProject, 'destination' => $item);
                    }

                    unset($this->_existingIds[$existingProject->external_id]);
                    unset($this->_existing[$existingProject->projid]);
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

                $project = $this->getService('Project')->insert($insert->getValues());
                if ($project) {
                    $this->_existingIds[$project->external_id] = $project->projid;
                }
            }
        }

        if (count($this->_updates)) {
            foreach($this->_updates as $id => $update) {
                $this->getService('Project')->update($update['destination']->getValues());
            }
        }

    }

}