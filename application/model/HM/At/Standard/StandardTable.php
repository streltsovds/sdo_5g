<?php
class HM_At_Standard_StandardTable extends HM_Db_Table
{
    protected $_name = "at_ps_standard";
    protected $_primary = "standard_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(        
        'AtStandardFunction' => array(
            'columns'       => 'standard_id',
            'refTableClass' => 'HM_At_Standard_Function_FunctionTable',
            'refColumns'    => 'standard_id',
            'propertyName'  => 'function'          
        ));
}