<?php
class HM_At_Criterion_Personal_PersonalModel extends HM_Model_Abstract
{
    const TYPE_TEST = 7; // на всякий случай не пересекается с HM_At_Criterion_CriterionModel
    
    protected $_primaryName = 'criterion_id';
    
    public function getServiceName()
    {
        return 'AtCriterionPersonal';
    }    
}