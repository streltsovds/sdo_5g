<?php class HM_Hr_Reserve_AssignRecruiter_AssignRecruiterService extends HM_Service_Abstract
{
    public function assign($reserveId, $recruiterId)
    {
        $where = $this->quoteInto(array('rotation_id = ?', ' AND recruiter_id = ?'), array($reserveId, $recruiterId));
        $rows = $this->fetchAll($where);
        
        // уже назначен
        if (count($rows)) {
            return;
        }
        
        $this->insert(array(
            'rotation_id' => $reserveId,
            'recruiter_id' => $recruiterId
        ));
    }
    
    public function unassign($reserveId, $recruiterId)
    {
        $this->deleteBy(array(
            'rotation_id = ?' => $reserveId,
            'recruiter_id = ?' => $recruiterId,
        ));
    }
}