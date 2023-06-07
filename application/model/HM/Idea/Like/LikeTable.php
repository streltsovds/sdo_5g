<?php

class HM_Idea_Like_LikeTable extends HM_Db_Table
{
    protected $_name = "idea_like";
    protected $_primary = "idea_like_id";

    protected $_referenceMap = array(
        'Idea' => array(
            'columns'       => 'idea_id',
            'refTableClass' => 'HM_Idea_IdeaTable',
            'refColumns'    => 'idea_id',
            'propertyName'  => 'idea' 
        ),

    );
}