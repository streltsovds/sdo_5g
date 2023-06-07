<?php
class HM_Tc_Prefix_PrefixTable extends HM_Db_Table
{
	protected $_name = "tc_prefixes";
	protected $_primary = "prefix_id";

    protected $_referenceMap = array(
        'Subject' => array(
            'columns'       => 'prefix_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'prefix_id',
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );
}