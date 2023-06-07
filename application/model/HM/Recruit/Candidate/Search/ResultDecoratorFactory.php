<?php

/**
 * Description of ResultDecoratorFactory
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_ResultDecoratorFactory extends HM_Service_Primitive {
    
    public static function getDecorator($decoratorName, HM_Recruit_Candidate_Search_Result_AbstractItemsCollection $collection) {
        $fullDecoratorName = "HM_Recruit_Candidate_Search_Result_".ucfirst($decoratorName)."Decorator";
        if (!class_exists($fullDecoratorName)) {
            throw new HM_Recruit_Candidate_Search_Exception_InvalidResultDecoratorException('Search result decorator class '.$fullDecoratorName.' doesn\'t exist');
        }
        return new $fullDecoratorName($collection);
    }
    
}
