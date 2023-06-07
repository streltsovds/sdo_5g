<?php

class HM_Subject_Criteria_CriteriaTable extends HM_Db_Table
{
    protected $_name = "subject_criteria";
    protected $_primary = array("subject_id", "criterion_id", "criterion_type");

    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subject'
        ),
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion'
        ),
    );
}
