<?php
class HM_Meeting_Assign_MarkHistory_MarkHistoryService extends HM_Service_Abstract
{
    private $_markHistoryCache = false; 
    
    // не фиксируем дублирующиеся оценки
    public function insert($data)
    {
        $collection = $this->fetchAll(array(
            'MID = ?' => $data['MID'],
            'SSID = ?' => $data['SSID'],
        ), 'updated DESC');

        if (count($collection)) {
            $mark = $collection->current();
            if ($mark->mark == $data['mark']) return true;
        }
        return parent::insert($data);
    }
    
    public function hasMarkHistory($projectId, $meetingId, $userId)
    {
        if ($this->_markHistoryCache === false) {
            $this->_markHistoryCache = array();
            
// как-то очень неоптимально через сервисный слой..            
//             $collection = $this->getService('Meeting')->fetchAllDependenceJoinInner('MeetingAssign', $this->quoteInto(array(
//                 'self.CID = ? AND ',        
//                 'MeetingAssign.MID = ?',
//             ), array(
//                 $projectId,
//                 $userId,        
//             )));
//             if (count($collection)) {
//                 $ssids = $collection->getList('SSID');
//                 $collection = $this->fetchAll(array('SSID IN (?)' => $ssids));
//             }

            $select = $this->getService('Meeting')->getSelect()
                ->from(array('s' => 'meetings'), array('meeting_id'))
                ->join(array('si' => 'meetingsID'), 's.meeting_id = si.meeting_id', array())
                ->join(array('smh' => 'meetings_marks_history'), 'si.SSID = smh.SSID', array(new Zend_Db_Expr('COUNT(mark)')))
                ->where('s.CID = ?', $projectId)
                ->where('si.MID = ?', $userId)
                ->group(array('s.meeting_id'))
                ->having(new Zend_Db_Expr('COUNT(mark) > 1'));
            
            if ($rowset = $select->query()->fetchAll()) {
        	    foreach ($rowset as $row) {
        	        $this->_markHistoryCache[$row['meeting_id']] = true;
        	    }   
            }         
        }
        return isset($this->_markHistoryCache[$meetingId]);
    }
}