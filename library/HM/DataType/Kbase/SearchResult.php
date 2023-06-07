<?php


class HM_DataType_Kbase_SearchResult extends HM_DataType_Abstract
{
    /**
     * @var array
     * Массив результатов с id и type из HM_Kbase_KbaseModel
     */
    public $matches = [];
    public $words = [];
}