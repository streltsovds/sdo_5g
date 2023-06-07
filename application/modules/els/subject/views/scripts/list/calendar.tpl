<?php
echo $this->calendar(
    $this->source,
    array(
        //'eventDropFunctionName'   => 'sendCalendarChange',
        //'eventResizeFunctionName' => 'sendCalendarChange',
        'editable'                => $this->editable,
        'saveDataUrl'             => $this->url(array('module'=>'subject', 'controller'=>'list','action'=>'save-calendar'))
    )
);
?>