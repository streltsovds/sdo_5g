<?php echo $this->partial(HM_At_Session_Event_EventModel::FORM_REPORT . '.tpl', array(
    'totalResults' => $this->totalResults, 
    'event' => $this->event
));?>