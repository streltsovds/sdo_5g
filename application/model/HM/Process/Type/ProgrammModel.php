<?php
class HM_Process_Type_ProgrammModel extends HM_Process_ProcessModel
{
    protected $_stateParams = array();
    protected $_programmEventsCache = array();
    
    public function isStatic()
    {
        return false;
    }    
    
    
    public function isStrict()
    {
        if (count($this->programm)) {
            $programm = $this->programm->current();
            return $programm->mode_strict;
        }
        return true;
    }      
       
    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_PROGRMM;
    } 
    
    public function getStateClass($state)
    {
        if ($eventId = $state->getProgrammEventId()) {
            return $this->getStatePrefix() . $eventId;
        }
        return get_class($state);
    }    
    
    public function getChain()
    {
        $chain = array();
        if (strlen($this->chain)) {
            $chain = unserialize($this->chain);
        }
        return $chain;
    }
    
    public function getStatesHidden()
    {
        $chain = array();
        if (strlen($this->chain)) {
            $chain = unserialize($this->chain);
        }
        return $chain;
    }

    // @todo: как-то это неправильно кэшировать внутри модели..
    public function initProgrammEventsCache()
    {
        if (!isset($this->_programmEventsCache[$this->programm_id])) {
            $this->_programmEventsCache[$this->programm_id] = array();
            $collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', array('programm_id = ?' => $this->programm_id));
            if (count($collection)) {
                foreach ($collection as $programmEvent) {
                    $this->_programmEventsCache[$this->programm_id][$programmEvent->programm_event_id] = $programmEvent;
                }
            }
        }
    }

    public function getProgrammEvents()
    {
        $this->initProgrammEventsCache();
        return $this->_programmEventsCache[$this->programm_id];
    }

    public function getStateTitle($state)
    {
        $this->initProgrammEventsCache();

        $programmEventId = $state->getProgrammEventId();    
        if ($programmEventId &&  isset($this->_programmEventsCache[$this->programm_id][$programmEventId])) {
            return $this->_programmEventsCache[$this->programm_id][$programmEventId]->name;
        }
    }

    public function getIsHidden($state)
    {
        $this->initProgrammEventsCache();

        $programmEventId = $state->getProgrammEventId();
        if ($programmEventId &&  isset($this->_programmEventsCache[$this->programm_id][$programmEventId])) {
            return $this->_programmEventsCache[$this->programm_id][$programmEventId]->hidden;
        }
    }
    
    public function update($programm) // желательно сразу с even'ами чтоб не получать из базы здесь в цикле   
    {
        $programmEvents = array();
        if (!count($programm->events)) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programm->programm_id)))) {
                $programmEvents = $collection->asArrayOfObjects();
            } 
        } else {
            $programmEvents = $programm->events->asArrayOfObjects();
        }
        
        if (count($programmEvents)) {
            $class = $this->getStatePrefix();
            
            usort($programmEvents, array('HM_Process_Type_ProgrammModel', 'sortByOrdr')); // заодно сбросили ключи массива
            
            // не создаём тривиальный шаг "сессия назначена"
            //$chain = array($class . 'Open' => $class . $programmEvents[0]->programm_event_id);
            
            $chain = array();
            for ($i = 0; $i < count($programmEvents); $i++) {
                $currentItem = $class . $programmEvents[$i]->programm_event_id;
                if ($programmEvents[$i+1] != '') {
                    $nextItem = $class . $programmEvents[$i + 1]->programm_event_id;
                    $chain[$currentItem] = $nextItem;
                    $currentItem = $nextItem;
                } 
            }
            $chain[$currentItem] = $class . 'Complete';
            
            $data = $this->getData();
            $data['name'] = $programm->name;
            $data['chain'] = serialize($chain);
            return Zend_Registry::get('serviceContainer')->getService('Process')->update($data); 
        }
    }
    
    public function getStates()
    {
        $return = array();
        if (strlen($this->chain)) {
            $chain = unserialize($this->chain);
            $programmEventIds = array();
            foreach ($chain as $element) {
                list(,$programmEventIds[]) = explode('_', $element);
            }
            array_filter($programmEventIds); // filter empty
            $programmEvents = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->fetchAll(array('programm_event_id IN (?)' => $programmEventIds))->getList('programm_event_id', 'name');
            $return = array_combine($chain, $programmEvents);
        }
        return $return;
    }
    
    public function sortByOrdr($event1, $event2) 
    { 
        return $event1->ordr < $event2->ordr ? -1 : 1; 
    }

    // эти параметры нужны только для того, чтобы с ними стартовать процесс
    public function getStateParams()
    {
        return $this->_stateParams;
    }
    
    // добавляет $programmEventsData в качестве параметров к каждому шагу динамического процесса
    // нужно только для старта процесса, после чего параметры идут в базу
    // не пытайтесь добавлять их по ходу процесса
    public function addStateParams($programmEventsData, $key)
    {
        $prefix = $this->getStatePrefix();
        foreach ($programmEventsData as $programmEventId => $programmEventData) {
            if (!isset($this->_stateParams[$prefix . $programmEventId])) {
                $this->_stateParams[$prefix . $programmEventId] = array();
            }
            $this->_stateParams[$prefix . $programmEventId][$key] = $programmEventData;
        }
    }

    public function addStateSameParam($programmEventsIds, $programmEventData, $key)
    {
        $prefix = $this->getStatePrefix();
        foreach ($programmEventsIds as $programmEventId) {
            if (!isset($this->_stateParams[$prefix . $programmEventId])) {
                $this->_stateParams[$prefix . $programmEventId] = array();
            }
            $this->_stateParams[$prefix . $programmEventId][$key] = $programmEventData;
        }
    }
}