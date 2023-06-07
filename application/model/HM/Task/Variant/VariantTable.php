<?php

class HM_Task_Variant_VariantTable extends HM_Db_Table
{
    protected $_name = "tasks_variants";
    protected $_primary = "variant_id";

    protected $_referenceMap = array(
/*        'SubjectAssign' => array(
            'columns'       => 'task_id',
            'refTableClass' => 'HM_Subject_Task_TaskTable',
            'refColumns'    => 'task_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
*/
    );
}