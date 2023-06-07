<?php

interface HM_Integration_Interface_Manager
{
    static public function getTaskId();

    public function import($integrationSource);

    public function update($integrationSource);

    public function sync($integrationSource);

    public function match(HM_Integration_Abstract_Model $model);

    public function todel(HM_Integration_Abstract_Model $model);
}