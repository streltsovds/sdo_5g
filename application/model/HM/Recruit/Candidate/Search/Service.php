<?php
/**
 * Description of Service
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_Service extends HM_Service_Primitive {
    
    /**
     * 
     * @return HM_Recruit_Candidate_Search_Result_AbstractItemsCollection
     */
    public function newSearchResultCollection() {
        return $this->getService('RecruitCandidateSearchResultItemsCollection');
    }
    
    /**
     * 
     * @return HM_Recruit_Candidate_Search_Result_AbstractItem
     */
    public function newSearchResultItem($type = HM_Recruit_Candidate_Search_Result_AbstractItem::ITEM_TYPE_DEFAULT) {
        return $this->getService('RecruitCandidateSearchResultItem'.ucfirst($type));
    }
    
}
