<?php

class HM_Idea_Url_UrlTable extends HM_Db_Table
{
    protected $_name = "idea_url";
    protected $_primary = "idea_url_id";

    protected $_referenceMap = array(
        'Idea' => array(
            'columns'       => 'idea_id',
            'refTableClass' => 'HM_Idea_IdeaTable',
            'refColumns'    => 'idea_id',
            'propertyName'  => 'idea' 
        ),

    );
}