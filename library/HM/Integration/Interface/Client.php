<?php

interface HM_Integration_Interface_Client
{
    public function setRequireInputParam($requireInputParam);
    public function call($method, $primaryKey = 'id', $keySalt = false);

    // @todo-int : это надо?
    public function callExport($item);

    public function answer($status);
}