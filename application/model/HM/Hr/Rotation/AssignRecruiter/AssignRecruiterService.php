<?php class HM_Hr_Rotation_AssignRecruiter_AssignRecruiterService extends HM_Service_Abstract
{
    public function assign($rotationId, $recruiterId)
    {
        $where = $this->quoteInto(array('rotation_id = ?', ' AND recruiter_id = ?'), array($rotationId, $recruiterId));
        $rows = $this->fetchAll($where);
        
        // уже назначен
        if (count($rows)) {
            return;
        }
        
        $this->insert(array(
            'rotation_id' => $rotationId,
            'recruiter_id' => $recruiterId
        ));
    }
    
    public function unassign($rotationId, $recruiterId)
    {
        $this->deleteBy(array(
            'rotation_id = ?' => $rotationId,        
            'recruiter_id = ?' => $recruiterId,
        ));
    }
}