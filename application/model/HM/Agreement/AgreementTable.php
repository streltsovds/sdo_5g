<?php

class HM_Agreement_AgreementTable extends HM_Db_Table
{
    protected $_name = "agreements";
    protected $_primary = "agreement_id";
//     protected $_sequence = "";

    protected $_referenceMap = array(
        'ProgrammEvent' => array(
            'columns'       => 'agreement_id',
            'refTableClass' => 'HM_Programm_Event_EventTable',
            'refColumns'    => 'item_id', // ВНИМАНИЕ! нужно еще отфильтровать по type
            'propertyName'  => 'programmEvent'
        ),
    );
}
