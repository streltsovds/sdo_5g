<?php

class HM_Material_Criteria_CriteriaTable extends HM_Db_Table
{
    protected $_name = "material_criteria";
    protected $_primary = array("material_id", "material_type");

    protected $_referenceMap = array(
        'Course' => array(
            'columns' => 'material_id',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns' => 'CID',
            'propertyName' => 'courses' // отфильтровать по material_type!
        ),
        'Resource' => array(
            'columns' => 'material_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns' => 'resource_id',
            'propertyName' => 'resources' // отфильтровать по material_type!
        ),
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion' // отфильтровать по criterion_type!
        ),
        'CriterionTest' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionTest' // отфильтровать по criterion_type!
        ),
        'CriterionPersonal' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Personal_PersonalTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionPersonal' // отфильтровать по criterion_type!
        ),
    );


    public function getDefaultOrder()
    {
        return array('subjects_resources.resource_id ASC');
    }
}
