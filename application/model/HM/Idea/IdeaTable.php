<?php

class HM_Idea_IdeaTable extends HM_Db_Table
{
    protected $_name = "idea";
    protected $_primary = "idea_id";

    protected $_referenceMap = array(
/*
        'Lesson' => array(
            'columns'       => 'event_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'typeID', // не совсем так
            'propertyName'  => 'lessons' 
        ),
*/
    );
}