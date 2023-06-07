<?php

/**
 *
 * @author tutrinov
 */
interface HM_Recruit_Candidate_Search_SearchBehavior
{
    
    /**
     * @param HM_Recruit_Vacancy_VacancyModel $model
     * @return HM_Recruit_Candidate_Search_Result_ItemsCollection
     */
    public function search(HM_Recruit_Vacancy_VacancyModel $model);
    
}
