<?php echo $this->calendar(
    $this->source,
    array(
        //'eventDropFunctionName'   => 'sendCalendarChange',
        //'eventResizeFunctionName' => 'sendCalendarChange',
        'abstract'                => true,
        'editable'                => $this->editable,
        'saveDataUrl'             => $this->url(array('module'=>'programm', 'controller'=>'evaluation', 'action'=>'save-calendar'))
    )
);
?>