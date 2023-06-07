<?php
class HM_At_Standard_Function_FunctionTable extends HM_Db_Table
{
    protected $_name = "at_ps_function";
    protected $_primary = "function_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(        
        'AtStandard' => array(
            'columns'       => 'standard_id',
            'refTableClass' => 'HM_At_Standard_StandardTable',
            'refColumns'    => 'standard_id',
            'propertyName'  => 'function'          
        ));
}