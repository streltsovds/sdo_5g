<?php

/**
 * Description of OutDecorator
 *
 * @author tutrinov
 */
abstract class HM_Recruit_Candidate_Search_Result_OutDecorator extends HM_Recruit_Candidate_Search_Result_AbstractItemsCollection {
    
    protected $decorableCollection = null;
    
    public function __construct(HM_Recruit_Candidate_Search_Result_AbstractItemsCollection $collection) {
        $this->setDecorableCollection($collection);
    }
    
    public function getDecorableCollection() {
        return $this->decorableCollection;
    }

    public function setDecorableCollection($decorableCollection) {
        $this->decorableCollection = $decorableCollection;
    }
    
}
