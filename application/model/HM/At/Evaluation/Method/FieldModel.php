<?php
/**
 * Методика оценки "field-coaching" (pm)
 *
 */
class HM_At_Evaluation_Method_FieldModel extends HM_Model_Abstract
{
    static public function getCriterionTypes()
    {
        return array(
            HM_At_Criterion_CriterionModel::TYPE_FIELD,
        );        
    } 
}