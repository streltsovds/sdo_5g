<?php
class HM_At_Criterion_Test_TestModel extends HM_Model_Abstract
{
    const STATUS_ACTUAL  = 1;
    const STATUS_DELETED = 0;

    const TYPE_TEST = 7; // на всякий случай не пересекается с HM_At_Criterion_CriterionModel

    const BUILTIN_BRANCH_PROFILES = 1; 
    
    const EMPLOYEE_TYPE_EMPLOYEE = 0;
    const EMPLOYEE_TYPE_CANDIDATE = 1;
    
    protected $_primaryName = 'criterion_id';
    
    public static function getEmploeeTypes(){
        $types = array(
            self::EMPLOYEE_TYPE_EMPLOYEE  => _('Пользователь'),
            self::EMPLOYEE_TYPE_CANDIDATE => _('Кандидат'),
        );
        return $types;
    }
    
    public function getServiceName()
    {
        return 'AtCriterionTest';
    }
}