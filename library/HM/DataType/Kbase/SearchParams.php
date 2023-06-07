<?php


class HM_DataType_Kbase_SearchParams extends HM_DataType_Abstract
{
    public $query = '';

    public $order;

    /** @var array
     * types from HM_Kbase_KbaseModel
     */
    public $types = [];

    public $classifiers = [];

    public $tags = [];


    public function hasType($type): bool
    {
        if (empty($this->types) or
            !count($this->types) or
            in_array($type, $this->types)
        ) {
            return true;
        } else {
            return false;
        }
    }
}