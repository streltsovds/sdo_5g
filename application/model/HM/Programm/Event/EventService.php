<?php
class HM_Programm_Event_EventService extends HM_Service_Abstract
{
    protected $_subject = array();

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('мероприятия во множественном числе', '%s мероприятий', $count), $count);
    }

    public function assignToUser($userId, $eventId)
    {
        $event = $this->find($eventId)->current();
        if($event){
            $this->getService('ProgrammEventUser')->assign($userId, $event);
            $serviceName = $event->getItemServiceName();
            if($this->getService($serviceName) instanceof HM_Programm_Event_Interface){
                // для evaluation'ов это не работает - там более сложная процедура назначения - см. SessionService
                $this->getService($serviceName)->assignToUser($userId, $event->item_id);
            }
        }
    }

    public function assignToUsers($event)
    {
        if ($event) {
            $programmUsers = $this->getService('ProgrammUser')->fetchAll(array('programm_id = ?' => $event->programm_id));
            foreach ($programmUsers as $programmUser) {
                $this->getService('ProgrammEventUser')->assign($programmUser->user_id, $event);
            }
        }
    }
    
    // @todo: копировать назначение юзеров?
    public function copy($event, $data)
    {
        $return = false;
        if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT ) {
            if (! isset($this->_subject[$event->item_id])) {
                $this->_subject[$event->item_id] = $this->getOne(
                    Zend_Registry::get('serviceContainer')->getService($event->getItemServiceName())->find($event->item_id)
                );

            }
            $item = $this->_subject[$event->item_id];

        } else {
            $item = Zend_Registry::get('serviceContainer')->getService($event->getItemServiceName())->copy($event->item_id);
        }

        if ($item) {
            $data['item_id'] = $item->getPrimaryKey();

            $data = array_merge($event->getData(), $data);
            unset($data['programm_event_id']);

            $return = parent::insert($data);
        }

        return $return;
    }    

    // каскадно удаляет event и user'ов
    public function deleteEvent($event)
    {
        $service = Zend_Registry::get('serviceContainer')->getService($event->getItemServiceName());
        // например, уч.курс тоже может быть элементом, но его не надо удалять при удалении из программы
        if (method_exists($service, 'deleteFromProgramm')) $service->deleteFromProgramm($event->item_id);

        if (count($event->programmEventUser)) {
            $programmEventUserIds = $event->programmEventUser->getList('programm_event_user_id');
            $this->getService('ProgrammEventUser')->deleteBy(array(
                'programm_event_user_id IN (?)' => $programmEventUserIds,
            )); 
        }
        $this->delete($event->programm_event_id);
    }    
    
    // имеет смысл только для оценки/подбора
    public function getEvaluationsByMethod($programmId)
    {
        $return = array();
            
        $events = array();
        $collection = $this->getService('ProgrammEvent')->fetchAllDependence('Evaluation',
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?'),
                array($programmId, HM_Programm_Event_EventModel::EVENT_TYPE_AT)
            ), 
            'ordr'
        );
        foreach ($collection as $event) {
            if (count($event->evaluation)) {
                $evaluation = $event->evaluation->current();
                $return[$evaluation->submethod] = $evaluation->evaluation_type_id;    
            }
        }        
        return $return;
    }    
}
