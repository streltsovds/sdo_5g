<?php
class HM_At_Criterion_CriterionModel extends HM_Model_Abstract
{
    const STATUS_ACTUAL  = 1;
    const STATUS_DELETED = 0;

    const TYPE_UNDEFINED = 0;
    const TYPE_CORPORATE = 1;
    const TYPE_PROFESSIONAL = 2;
    const TYPE_PERSONAL = 3;
//     const TYPE_FIELD = 4;
//     const TYPE_AUDIT = 5;

    const IMPORTANCE_DOESNT_MATTERS = 0;
    const IMPORTANCE_SO_SO = 1;
    const IMPORTANCE_MATTERS = 2;

    protected $_primaryName = 'criterion_id';

    static public function getCompetenceType($type) 
    {
        $types = self::getCompetenceTypes();
        return $types[$type];        
    }
    
    static public function getCompetenceTypes()
    {
        return array(
            self::TYPE_UNDEFINED  => '',
            self::TYPE_CORPORATE  => _('Компетенция'),
            self::TYPE_PROFESSIONAL => _('Проф.компетенция'),
            self::TYPE_PERSONAL => _('Личностная характеристика'),
        );
    }    
    
    public function getType()
    {
        $types = self::getTypes();
        return $types[$this->type];
    }    
    
    
    static public function getCriteriaTypeByMethod($method)
    {
        switch ($method) {
            case HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO:
                return HM_At_Criterion_CriterionModel::TYPE_PERSONAL;
            case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                return HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL;
            case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                return HM_At_Criterion_CriterionModel::TYPE_CORPORATE;
        }
        return HM_At_Criterion_CriterionModel::TYPE_UNDEFINED;
    }    
    
    // старый код из pmа
    public function getPlainContent()
    {
        $return = array();
    	$children = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->fetchAll(array('lft >= ?' => $this->lft, 'rgt <= ?' => $this->rgt));
    	if (count($children)) {
    	    foreach ($children as $childCriterion) {
    	    	$return[$childCriterion->criterion_id] = $childCriterion;
    	    }
    	}
        return $return;
    }
}