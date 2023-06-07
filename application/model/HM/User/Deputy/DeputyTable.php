<?php

class HM_User_Deputy_DeputyTable extends HM_Db_Table
{
    protected $_name = "deputy_assign";
    protected $_primary = "assign_id";
    protected $_sequence = 'S_45_1_DEPUTY';

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Deputy' => array(
            'columns'       => 'deputy_user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'deputy' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

}
