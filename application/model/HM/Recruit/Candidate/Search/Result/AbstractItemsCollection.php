<?php
/**
 * AbstractResultItemsCollection
 *
 * @author tutrinov
 */
abstract class HM_Recruit_Candidate_Search_Result_AbstractItemsCollection extends HM_Collection_Primitive {
    
    const EVENT_ADD_ITEM_PRE = 'candidateSearchResultItemAddPre';
    const EVENT_ADD_ITEM_POST = 'candidateSearchResultItemAddPost';
    
    /**
     * Event dispatcher
     * @var sfEventDispatcher
     */
    protected $eventDispatcher = null;
    
    public function __construct() 
    {
        $this->setEventDispatcher(Zend_Registry::get('serviceContainer')->getService('EventDispatcher'));
    }
    
    public function getEventDispatcher() {
        return $this->eventDispatcher;
    }

    public function setEventDispatcher(sfEventDispatcher $eventDispatcher) 
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * 
     * @param HM_Recruit_Candidate_Search_Result_AbstractItem $item
     * @param mixed $key
     * @return void
     */
    public function add(HM_Recruit_Candidate_Search_Result_AbstractItem $item, $key = null) 
    {
        $this->getEventDispatcher()->notify(new sfEvent($item, self::EVENT_ADD_ITEM_PRE));
        if (null !== $key) {
            parent::add($item, $key);
            return;
        }
        $candidateId = $item->getCandidateId();
        if ($candidateId === null) {
            throw new HM_Recruit_Candidate_Search_Exception_InvalidSearchResultItemException("Candidate ID is not defined!");
        }
        $this->_raw[$candidateId] = $item;
        ++$this->_count;
        $this->getEventDispatcher()->notify(new sfEvent($item, self::EVENT_ADD_ITEM_POST));
        return;
    }
    
    abstract function out();
    
}
