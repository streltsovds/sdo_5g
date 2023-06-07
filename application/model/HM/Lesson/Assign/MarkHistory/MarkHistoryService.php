<?php
class HM_Lesson_Assign_MarkHistory_MarkHistoryService extends HM_Service_Abstract
{
    private $_markHistoryCache = false; 
    
    // не фиксируем дублирующиеся оценки
    public function insert($data, $unsetNull = true)
    {
        $collection = $this->fetchAll(array(
            'MID = ?' => $data['MID'],
            'SSID = ?' => $data['SSID'],
        ), 'updated DESC');

        if (count($collection)) {
            $mark = $collection->current();
            if ($mark->mark == $data['mark']) return true;
        }
        return parent::insert($data, $unsetNull);
    }
    
    public function hasMarkHistory($subjectId, $lessonId, $userId)
    {
        if ($this->_markHistoryCache === false) {
            $this->_markHistoryCache = $this->getMarkHistory($subjectId, $userId);
        }
        return isset($this->_markHistoryCache[$lessonId]);
    }

    public function getMarkHistory($subjectId, $userId)
    {
        $return = array();

        $select = $this->getService('Lesson')->getSelect()
            ->from(array('s' => 'schedule'), array(
                's.SHEID', 'smh.SSID', 'smh.mark', 'smh.updated',
                'fio' => new Zend_Db_Expr("CASE WHEN p.MID IS NOT NULL THEN CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic) ELSE '' END"),
            ))
            ->join(array('si' => 'scheduleID'), 's.SHEID = si.SHEID', array())
            ->join(array('smh' => 'schedule_marks_history'), 'si.SSID = smh.SSID', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = smh.MID', array())
            ->where('s.CID = ?', $subjectId)
            ->where('si.MID = ?', $userId);

        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                if (!isset($return[$row['SHEID']])) $return[$row['SHEID']] = array();
                $date = new HM_Date($row['updated']);
                $row['updated'] = $date->get('dd.mm.YY H:i');
                $return[$row['SHEID']][$row['SSID']][$row['updated']] = $row;
            }
        }

        foreach ($return as &$item) {
            array_filter($item, function($subitem){
                return count($subitem) > 1;
            });
        }

        return $return;
    }
}