<?php
/*
 * Custom Event Meeting
 */
class HM_Meeting_Custom_CustomModel extends HM_Meeting_MeetingModel
{
    private $_meeting = null;
    private $_event = null;

    public function __construct($data)
    {
        parent::__construct($data);

        $data['typeID'] = $data['tool'];
        $this->_meeting = HM_Meeting_MeetingModel::factory($data);
    }
    
    public function setEvent(HM_Event_EventModel $event)
    {
        $this->_event = $event;
    }

    public function getType()
    {
        return $this->_meeting->getType();
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $icon = Zend_Registry::get('config')->path->upload->event . (-$this->typeID).".jpg";
        if (file_exists($icon)) {
            return Zend_Registry::get('config')->url->base . 'upload/events/'.(-$this->typeID).'.jpg';
        }
        return $this->_meeting->getIcon($size);
    }

    public function isExternalExecuting()
    {
        return $this->_meeting->isExternalExecuting();
    }

    public function getExecuteUrl()
    {
        return $this->_meeting->getExecuteUrl();
    }

    public function getResultsUrl($options = array())
    {
        return $this->_meeting->getResultsUrl($options);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, array('getIcon'))) {
            return call_user_func_array(array($this, $name), $arguments);
        }
        pr($name);die();
        if (method_exists($this->_meeting, $name)) {
            return call_user_func_array(array($this->_meeting, $name), $arguments);
        }

        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $arguments);
        }

        throw new HM_Exception(sprintf('Method %s does not exists', $name));
    }
    
    public function getScale()
    {
        if (!isset($this->_event) && $this->typeID) {
            if ($collection = Zend_Registry::get('serviceContainer')->getService('Event')->find(-$this->typeID)) {
                $this->_event = $collection->current();
            }
        }
        if ($this->_event) {
            return $this->_event->scale_id;
        }
        return HM_Scale_ScaleModel::TYPE_CONTINUOUS; // default
    }     
}