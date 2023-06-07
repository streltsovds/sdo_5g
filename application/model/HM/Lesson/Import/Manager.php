<?php
class HM_Lesson_Import_Manager
{
    const CACHE_NAME = 'HM_Lesson_Import_Manager';

    protected $_inserts           = [];
    protected $_notProcessed      = [];
    private   $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getNotProcessed()
    {
        return $this->_notProcessed;
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getNotProcessedCount()
    {
        return count($this->_notProcessed);
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save([
            'inserts'      => $this->_inserts,
            'notProcessed' => $this->_notProcessed
        ],  self::CACHE_NAME);
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts           = $actions['inserts'];
            $this->_notProcessed      = $actions['notProcessed'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {
        if ($this->_restoredFromCache) return true;

        if (count($items)) {
            foreach ($items as $item) {
                if (empty($item->order) || empty($item->title)) {
                    $this->_notProcessed[] = $item;
                    continue;
                }
                $this->_inserts[$item->order] = $item;
            }
        }

        $this->saveToCache();

        return $this->_inserts;
    }

    public function import($subjectId)
    {
        if (count($this->_inserts)) {
            foreach($this->_inserts as $id => $insert) {
                $data = [
                    'title'         => $insert->title,
                    'CID'           => $subjectId,
                    'createDate'    => date('Y-m-d H:i:s'),
                    'createID'      => $this->getService('User')->getCurrentUserId(),
                    'notify_before' => 0,
                    'begin'         => $insert->begin ?: ($insert->end ?: 0),
                    'end'           => $insert->end ?: ($insert->begin ?: 0),
                    'isfree'        => HM_Lesson_LessonModel::MODE_PLAN,
                    'typeID'        => HM_Event_EventModel::TYPE_EMPTY
                ];
                if (empty($insert->begin) && empty($insert->begin)) $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_FREE;

                $lessons = $this->getService('Lesson')->fetchAll(['CID = ?' => $subjectId]);
                $lessonsOrders = $lessons->getList('order');
                if ($lessonsOrders) {
                    $highestValue = max(array_values($lessonsOrders));
                    $data['order'] = $highestValue + $insert->order;
                }
                $this->getService('Lesson')->insert($data);
            }
        }
    }
}