<?php
echo $this->calendar(
    $this->source,
    array(
        //'eventDropFunctionName'   => 'sendCalendarChange',
        //'eventResizeFunctionName' => 'sendCalendarChange',
        'abstract'                => false,
        'editable'                => $this->editable,
        'saveDataUrl'             => $this->url(array('module'=>'newcomer', 'controller'=>'calendar', 'action'=>'save-calendar'))
    )
);
?>