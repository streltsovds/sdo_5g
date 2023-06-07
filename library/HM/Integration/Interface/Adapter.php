<?php

interface HM_Integration_Interface_Adapter
{
    public function init(HM_Integration_Abstract_Model $model);

    public function convert(Array $item);
}