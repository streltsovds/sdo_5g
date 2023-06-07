<?php

/**
 * ExternalSearchStrategy
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_HhExternalStrategy extends HM_Service_Primitive implements HM_Recruit_Candidate_Search_SearchBehavior {

    public function search(HM_Recruit_Vacancy_VacancyModel $model) 
    {
        /* @var $hhCurlApi HM_Recruit_RecruitingServices_Curl_Hh */
        $hhCurlApi = $this->getService('RecruitServiceFactory')
                ->getRecruitingService(
                HM_Recruit_RecruitingServices_PlacementBehavior::SERVICE_HH, HM_Recruit_RecruitingServices_PlacementBehavior::API_REST
        );
        $vacancies = $hhCurlApi->getCandidatesByVacancy($model);
        /* @var $searchService HM_Recruit_Candidate_Search_Service */
        $searchService = $this->getService('RecruitCandidateSearchService');
        /*@var $collection HM_Recruit_Candidate_Search_Result_AbstractItemsCollection */
        $collection = $searchService->newSearchResultCollection();
        /*@var $event sfEvent */
        $collection->getEventDispatcher()->connect(HM_Recruit_Candidate_Search_Result_AbstractItemsCollection::EVENT_ADD_ITEM_PRE, array('HM_Recruit_Candidate_Search_HhExternalStrategy', '_check'));
        if (sizeof($vacancies > 0)) {
            foreach ($vacancies as $resumeId => $hhVacancy) {
                /*@var $searchResultItem HM_Recruit_Candidate_Search_Result_ItemExternal */
                $searchResultItem = $searchService->newSearchResultItem(HM_Recruit_Candidate_Search_Result_AbstractItem::ITEM_TYPE_EXTERNAL);
                $searchResultItem->setCandidateId($resumeId);
                $searchResultItem->setFullName($hhVacancy['fullName']);
                $searchResultItem->setRawHtmlDescription($hhVacancy['description']);
                $searchResultItem->addition('age', $hhVacancy['age']);
                $searchResultItem->addition('resumeHash', $hhVacancy['resumeHash']);
                $searchResultItem->addition('response', $hhVacancy['response']);
                $searchResultItem->sourceName('hh');
                $collection->add($searchResultItem);
            }
        }
        return $collection;
    }

    public function _check($event)
    {
        $item = $event->getSubject();
        if ($item instanceof HM_Recruit_Candidate_Search_Result_ItemExternal && $item->sourceName() === null) {
            throw new RuntimeException("Search result item from external head hunting resource must have sourceName additional parameter! Check external candidate search strategy.");
        }
    }
}
