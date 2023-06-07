<?php
class HM_Meeting_Poll_Curator_Assign_AssignService extends HM_Service_Abstract
{

    public function assignParticipant($meetingId, $participantId, $headId)
    {
        return $this->insert(
            array(
                    'meeting_id'   => $meetingId,
                    'participant_mid' => $participantId,
                    'head_mid'    => $headId
            )
        );
    }
    
    public function unassignParticipant($meetingId, $participantId)
    {
        return $this->deleteBy(array('meeting_id = ?' => $meetingId, 'participant_mid = ?' => $participantId));
    }
    
    
    public function unassignParticipants($meetingId)
    {
        return $this->deleteBy(array('meeting_id = ?' => $meetingId));
    }
    
    public function assignParticipants($meetingId, $participants, $headId, $unassign = true)
    {
        if (is_array($participants) && count($participants)) {
            $assigns = $this->fetchAll($this->quoteInto('meeting_id = ? AND participant_mid > 0', $meetingId));
            if (count($assigns)) {
                foreach($assigns as $assign) {
                    if (in_array($assign->participant_mid, $participants)) {
                        $key = array_search($assign->participant_mid, $participants);
                        if (false !== $key) {
                            unset($participants[$key]);
                        }
                    } else {
                        if($unassign == true){
                            $this->unassignParticipant($meetingId, $assign->participant_mid);
                        }
                    }
                }

            }
            
            foreach($participants as $participantId) {
                $this->assignParticipant($meetingId, $participantId, $headId);
            }
        }
    }
    
    



    
}