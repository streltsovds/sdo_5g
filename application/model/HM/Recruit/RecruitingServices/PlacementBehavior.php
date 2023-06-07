<?php

/**
 *
 * @author tutrinov
 */
interface HM_Recruit_RecruitingServices_PlacementBehavior {

    const API_CURL = 'curl';
    const API_SOAP = 'soap';
    const API_REST = 'rest';
    
    const SERVICE_HH = 'hh';
    const SERVICE_SUPERJOB = 'superjob';

    public function createVacancy(array $postData);

    public function findDependentVacancy(HM_Recruit_Vacancy_VacancyModel $vacancyModel);

    public function getCandidatesByVacancy(HM_Recruit_Vacancy_VacancyModel $vacancyModel);
    
    public function getCandidateResume(array $params = null);

    public function archiveVacancy($vacancyId);

    public function removeVacancy();
}
