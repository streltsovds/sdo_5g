<?php
class HM_Meeting_Poll_Curator_CuratorService extends HM_Meeting_MeetingService
{

    /**
     * don't use outside of service layer !!!important!!!
     * @param  $meetingId
     * @param  $participantId
     * @return bool
     */
    public function assignParticipant($meetingId, $participantId)
    {
        $return = parent::assignParticipant($meetingId, $participantId);
        if ($return) {
            $meeting = $this->getOne($this->find($meetingId));
            if ($meeting) {
                $this->getService('PollFeedback')->assign($participantId, $meeting->CID, $meetingId);
            }
        }
        return $return;
    }

    protected function _sendAssignParticipantsMessage($meeting, $participants, $templateId, $slaves = null)
    {
        if (!count($participants)) return false;

        $messenger = $this->getService('Messenger');
        $messenger->setTemplate($templateId);

        $messenger->assign(
            array(
                'project_id' => $meeting->CID,
                'url2' => Zend_Registry::get('view')->serverUrl(
                    Zend_Registry::get('view')->url(array(
                        'module' => 'meeting',
                        'controller' => 'execute',
                        'action' => 'index',
                        'meeting_id' => $meeting->meeting_id
                    ), null, true)
                ),
                'title' => $meeting->title,
                'begin' => $meeting->getBeginDatetime($meeting->created),
                'end' => $meeting->getEndDatetime($meeting->created),
                'slaves' => ''
            )
        );

        if ($meeting->isTimeFree()) {
            $messenger->assignValue('begin', _('неограничено'));
            $messenger->assignValue('end', _('неограничено'));
        }

        foreach($participants as $participantId) {
            if ( $participantId === null ) {continue;}
            $messenger->assignValue('slaves', '');
            if ((null !== $slaves) && ($templateId == HM_Messenger::TEMPLATE_POLL_LEADERS)) {
                if (isset($slaves[$participantId])) {
                    $collection = $this->getService('User')->fetchAll(
                        $this->quoteInto('MID IN (?)', $slaves[$participantId])
                    );

                    $participantSlaves = array();
                    if (count($collection)) {
                        foreach($collection as $user) {
                            $participantSlaves[] = $user->getName();
                        }
                    }

                    if (count($participantSlaves)) {
                        $messenger->assignValue('slaves', join(', ', $participantSlaves));
                    }
                }
            }
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $participantId);
        }
        return true;
    }

    protected function _isExecutableForDefault($meeting)
    {

        $registered = null;
        if (in_array($meeting->typeID, HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT)
            && !$this->getService('Project')->isGraduated($meeting->CID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не являетесь прошедшим обучения на курсе'));
        }

        if (!$this->isUserAssigned($meeting->meeting_id, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не назначены на занятие'));
        }

        if ($meeting->timetype == HM_Meeting_MeetingModel::TIMETYPE_RELATIVE) { // Относительное занятие
            $assign = $this->getOne(
                $this->getService('MeetingAssign')->fetchAll(
                    $this->quoteInto(array('meeting_id = ?', ' AND MID = ?'), array($meeting->meeting_id, $this->getService('User')->getCurrentUserId()))
                )
            );
            if ($assign) {
                $registered = $assign->created;
            }
        }

        // Проверка дат
        if (!$meeting->isExecutable($registered)) {
            throw new HM_Exception(_('Занятие назначено на другое время'));
        }

        return true;
    }

    protected function _isExecutableForRole($meeting)
    {

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)) {
            $this->_isExecutableForCurator($meeting);
        } else {
            $this->_isExecutableForDefault($meeting);
        }

/*        switch($this->getService('User')->getCurrentUserRole()) {
            case HM_Role_Abstract_RoleModel::ROLE_CURATOR:
                $this->_isExecutableForCurator($meeting);
                break;
            default:
                $this->_isExecutableForDefault($meeting);
                break;
        }*/
    }

    /*
    public function assignParticipants($meetingId, $participants, $unassign = true)
    {
        $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($meetingId));
        if ($meeting) {
            switch($meeting->typeID) {
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                        $participants = $this->getService('Project')->getAssignedModerators($meeting->CID)->getList('MID', 'MID');
                    break;
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
                        $collection = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', $participants));
                        $participants = $leaders = array();
                        if (count($collection)) {
                            foreach($collection as $user) {
                                if ($user->head_mid > 0) {
                                    $participants[$user->head_mid] = $user->head_mid;
                                    $leaders[$user->head_mid][$user->MID] = $user->MID;
                                }
                            }

                            if (count($leaders)) {
                                foreach($leaders as $leaderId => $slaves) {
                                    $this->getService('MeetingCuratorPollAssign')->assignParticipants($meetingId, $slaves, $leaderId);
                                }
                            }
                        }
                    break;
            }

            // Чтобы обновился created у опросов руководителю и тьюторам
            if (in_array($meeting->typeID, array(HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR, HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER))) {
                if (count($participants)) {
                    $this->getService('MeetingAssign')->deleteBy(
                        $this->quoteInto(
                            array('meeting_id = ?', ' AND MID IN (?)'),
                            array($meetingId, $participants)
                        )
                    );
                }
            }

            $return = parent::assignParticipants($meetingId, $participants, $unassign);

            if ($return) {
                if (count($participants)) {
                    $messenger = $this->getService('Messenger');
                    switch($meeting->typeID) {
                        case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                            $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_MODERATORS);
                            break;
                        case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
                            $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_LEADERS);
                            break;
                        default:
                            $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_PARTICIPANTS);

                    }

                    $messenger->assign(
                        array(
                            'project_id' => $meeting->CID,
                            'url2' => Zend_Registry::get('view')->serverUrl(
                                Zend_Registry::get('view')->url(array(
                                    'module' => 'meeting',
                                    'controller' => 'execute',
                                    'action' => 'index',
                                    'meeting_id' => $meetingId
                                ))
                            )
                        )
                    );
                    
                    foreach($participants as $participantId) {
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $participantId);
                    }
                }
            }
            
            return $return;

        }
        return false;
    }
    */

    public function unassignParticipant($meetingId, $participantId)
    {
        $this->getService('MeetingCuratorPollAssign')->deleteBy(
            $this->quoteInto(
                array('meeting_id = ?', ' AND head_mid = ?'),
                array($meetingId, $participantId)
            )
        );

        $meeting = $this->getOne($this->find($meetingId));
        if ($meeting) {
            $this->getService('PollFeedback')->cancel($participantId, $meeting->CID, $meetingId);
        }

        return parent::unassignParticipant($meetingId, $participantId);
    }

    private function _prepareWhere($where = null)
    {
        if (null === $where) {
            $where = '';
        }

        if (strlen($where)) {
            $where .= ' AND ';
        }

        $where .= $this->quoteInto('typeID IN (?)', array_keys(HM_Event_EventModel::getCuratorPollTypes()));
        return $where;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return parent::fetchAll($this->_prepareWhere($where), $order, $count, $offset);
    }

    public function countAll($where = null)
    {
        return parent::countAll($this->_prepareWhere($where));
    }

    public function fetchAllDependence($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return parent::fetchAllDependence($dependence, $this->_prepareWhere($where), $order, $count, $offset);
    }
    

    
}