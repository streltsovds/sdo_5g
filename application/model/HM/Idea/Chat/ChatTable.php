<?php

class HM_Idea_Chat_ChatTable extends HM_Db_Table
{
    protected $_name = "idea_chat";
    protected $_primary = "idea_chat_id";

    protected $_referenceMap = array(

        'Idea' => array(
            'columns'       => 'idea_id',
            'refTableClass' => 'HM_Idea_IdeaTable',
            'refColumns'    => 'idea_id',
            'propertyName'  => 'idea' 
        ),

    );
}