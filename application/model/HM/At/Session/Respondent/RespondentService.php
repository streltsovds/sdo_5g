<?php
class HM_At_Session_Respondent_RespondentService extends HM_Service_Abstract
{
    // DEPRECATED!
    // теперь вычисляется на лету 
    public function updateProgress($sessionRespondentId)
    {
        $progress = 0;
        $sessionRespondent = $this->findDependence('SessionEvents', $sessionRespondentId)->current();
        if (count($sessionRespondent->sessionEvents)) {
            $count = 0;
            foreach ($sessionRespondent->sessionEvents as $event) {
                if ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) $count++;
            }
            $progress = ceil(100 * $count/count($sessionRespondent->sessionEvents));
        }
        $this->update(array(
            'progress' => $progress,
            'session_respondent_id' => $sessionRespondent->session_respondent_id,
        ));
        return $progress;
    }

    public function duplicate($userId, $newUserId, $sessionId, $skipCompletedEvents = true)
    {
        if (count($collection = $this->fetchAllDependence('SessionEvents', array(
            'user_id = ?' => $userId,
            'session_id = ?' => $sessionId,
        )))) {
            $respondent = $collection->current();

            if ($user = $this->getService('User')->getOne($this->getService('User')->findDependence('Position', $newUserId))) {
                if (count($user->positions)) {
                    $positionId = $user->positions->current()->soid;
                }
                $sessionRespondent = $this->insert(array(
                    'session_id' => $sessionId,
                    'position_id' => $positionId,
                    'user_id' => $newUserId,
                ));
            }

            if (count($respondent->sessionEvents)) {
                foreach ($respondent->sessionEvents as $event) {
                    if ($skipCompletedEvents && ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED)) continue;
                    unset($event->session_event_id);
                    $event->session_respondent_id = $sessionRespondent->session_respondent_id;
                    $event->respondent_id = $newUserId;
                    $this->getService('AtSessionEvent')->insert($event->getValues());
                }
            }

        }
    }

    
    public function deleteRespondentsWithoutEvents($sessionId)
    {
        $select = $this->getSelect();
        $select->from(array('asr' => 'at_session_respondents'), array('session_respondent_id'))
            ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_respondent_id = asr.session_respondent_id', array())
            ->where('asr.session_id = ?', $sessionId)
            ->where('ase.session_event_id IS NULL');
        
        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                $this->delete($row['session_respondent_id']);
            }
        }
        return true;
    }


    public function safeAddRespondentToSession($userId, $sessionId) {

        $respondents = $this->fetchAll(
            $this->quoteInto(array(
                'session_id = ? '
            ),array(
                $sessionId
            ))
        );

        $sessionRespondent = null;

        foreach ($respondents as $respondent) {
            if ($respondent->user_id == $userId) {
                $sessionRespondent = $respondent;
                break;
            }
        }

        if ($sessionRespondent == null) {
            $position = $this->getOne(
                $this->getService('Orgstructure')->fetchAll(
                    $this->quoteInto("mid = ?", $userId)
                )
            );

            if (false !== $position) {
                $sessionRespondent = $this->insert(array(
                    'user_id' => $userId,
                    'position_id' => $position->soid,
                    'session_id' => $sessionId
                ));
            }
        }

        return $sessionRespondent;
    }
}