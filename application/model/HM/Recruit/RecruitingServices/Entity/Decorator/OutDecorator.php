<?php
/**
 * Description of OutDecorator
 *
 * @author slava
 */
abstract class HM_Recruit_RecruitingServices_Entity_Decorator_OutDecorator extends HM_Recruit_RecruitingServices_Entity_Collection {
    
    protected $decorableCollection = null;
    
    public function __construct(HM_Recruit_RecruitingServices_Entity_Collection $collection) {
        $this->setDecorableCollection($collection);
    }
    
    public function getDecorableCollection() {
        return $this->decorableCollection;
    }

    public function setDecorableCollection($decorableCollection) {
        $this->decorableCollection = $decorableCollection;
    }
    
}

?>