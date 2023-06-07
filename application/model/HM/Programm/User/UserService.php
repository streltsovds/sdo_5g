<?php
class HM_Programm_User_UserService extends HM_Service_Abstract
{
    public function assign($userId, $programmId, $assignContextEvents = false, $newcomer_id = null)
    {
        $this->insert(array('user_id' => $userId, 'programm_id' => $programmId, 'assign_date' => $this->getDateTime()));


        $contextEvents = array();
        $events = $this->getService('ProgrammEvent')->fetchAll($this->quoteInto('programm_id = ?', $programmId));

        if (count($events)) {
            foreach($events as $event) {
                if ($assignContextEvents) {
                    if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                        $contextEvents[] =  $event->item_id;
                        /* Подписываем только на обязательные курсы */
                        if ($event->isElective == 0) {
                            $this->getService('Subject')->assignStudent(
                                $event->item_id,
                                $userId,
                                array(
                                    'newcomer_id' => $newcomer_id,
                                    'event' => $event
                                )
                            );
                        }
                    }
                }

                // создаём event'ы даже в случае обучения
                $this->getService('ProgrammEvent')->assignToUser($userId, $event->programm_event_id);
            }
        }
        return $contextEvents;
    }

    /*
     * Когда программа уже назначена, а надо сгенерить функциональные объекты,
     * соответствующие элементам программы
     *
     * пока это могут быть только курсы как элементы программы нач.обучения
     */
    public function assignContextEvents($userId, $programmId, $newcomer_id = null)
    {
        $events = $this->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', $this->quoteInto('programm_id = ?', $programmId));

        if (count($events)) {
            foreach($events as $event) {
                if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                    $contextEvents[] =  $event->item_id;
                    /* Подписываем только на обязательные курсы */
                    if ($event->isElective == 0) {
                        $this->getService('Subject')->assignStudent(
                            $event->item_id,
                            $userId,
                            array(
                                'newcomer_id' => $newcomer_id,
                                'event' => $event
                            )
                        );
                    }
                }
            }
        }
        return $contextEvents;
    }

    public function unassign($userId, $programmId)
    {
        $events = $this->getService('ProgrammEvent')->fetchAll($this->quoteInto('programm_id = ?', $programmId));
		$eventIds = $events->getList('programm_event_id');
		
		// отдельная логика для программ обучения - удалить юзера с этих курсов
        if (count($events)) {
            foreach($events as $event) {
                if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                    $this->getService('Subject')->unassignStudent($event->item_id, $userId);
                    $this->getService('Claimant')->deleteBy(
                        $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($userId, $event->item_id))
                    );
                    // удаляем event'ы даже в случае обучения
                    //unset($eventIds[$event->programm_event_id]);
                }
            }
        }
        
        if (count($eventIds)) {
            $this->getService('ProgrammEventUser')->deleteBy(
                $this->quoteInto(
                    array('user_id = ?', ' AND programm_event_id IN (?)'),
                    array($userId, $eventIds)
                )
            );
        }        

        $this->deleteBy(
            $this->quoteInto(
                array('user_id = ?', ' AND programm_id = ?'),
                array($userId, $programmId)
            )
        );
    }
}