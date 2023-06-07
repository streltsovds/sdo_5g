<?php
echo $this->headSwitcher(array('module' => 'session', 'controller' => 'event', 'action' => 'calendar', 'switcher' => 'calendar'));
echo $this->calendar(
    $this->source,
    array(
        //'eventDropFunctionName'   => 'sendCalendarChange',
        //'eventResizeFunctionName' => 'sendCalendarChange',
        'abstract'                => false,
        'editable'                => $this->editable,
        'saveDataUrl'             => $this->url(array('module'=>'session', 'controller'=>'event', 'action'=>'save-calendar'))
    )
);
?>