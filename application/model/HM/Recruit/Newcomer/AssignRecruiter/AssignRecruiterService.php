<?php class HM_Recruit_Newcomer_AssignRecruiter_AssignRecruiterService extends HM_Service_Abstract
{
    public function assign($newcomerId, $recruiterId)
    {
        $where = $this->quoteInto(array('newcomer_id = ?', ' AND recruiter_id = ?'), array($newcomerId, $recruiterId));
        $rows = $this->fetchAll($where);
        
        // уже назначен
        if (count($rows)) {
            return;
        }
        
        $this->insert(array(
            'newcomer_id' => $newcomerId,
            'recruiter_id' => $recruiterId
        ));
    }
    
    public function unassign($newcomerId, $recruiterId)
    {
        $this->deleteBy(array(
            'newcomer_id = ?' => $newcomerId,        
            'recruiter_id = ?' => $recruiterId,        
        ));
    }
}