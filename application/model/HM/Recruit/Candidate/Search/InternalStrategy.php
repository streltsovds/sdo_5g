<?php

/**
 * InternalStrategy
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_InternalStrategy extends HM_Service_Primitive implements HM_Recruit_Candidate_Search_SearchBehavior {

    public function search(HM_Recruit_Vacancy_VacancyModel $model) 
    {
        $profileId = count($model->profile) ? (int) $model->profile->current()->profile_id : 0;
        if($model->position){
            $currentPositionUserId = (int)$model->position->current()->mid;
        } else {
            $currentPositionUserId = 0;
        }
        
        $requiredCategories = array();

        $requiredCriteria = $this->getService('RecruitVacancy')->getVacancyCriteria($model);
        
        if (count($requiredCriteria)) {
            $select = $this->getService('User')->getSelect();
            $select->from(array('u' => 'People'), array('mid' => 'u.MID'))
                    ->join(array('asu' => 'at_session_users'), 'u.MID = asu.user_id', array())
                    ->join(array('ats' => 'at_sessions'), 'asu.session_id = ats.session_id', array())
                    ->join(array('asucv' => 'at_session_user_criterion_values'), 'asucv.session_user_id = asu.session_user_id', array('criterion_id' => 'asucv.criterion_id', 'value' => 'MAX(asucv.value)'))
                    ->join(array('sto' => 'structure_of_organ'), 'u.MID = sto.mid', array('sto.soid'))
                    ->join(array('atp' => 'at_profiles'), 'sto.profile_id = atp.profile_id', array('atpac' => 'atp.profile_id'))
                    ->where('u.MID != ?', $currentPositionUserId)
                    ->where('ats.programm_type = ?', HM_Programm_ProgrammModel::TYPE_ASSESSMENT)
                    ->where('asucv.criterion_id IN (?)', array_keys($requiredCriteria))
                    ->group(array('u.MID', 'sto.soid', 'asucv.criterion_id', 'atp.profile_id'));
            
            if (count($requiredCategories)) {
                $select->where('atp.category_id IN(?)', $requiredCategories, "ARRAY");
            }
            
            if ($endAge = intval($model->age_max)) {
                $startDate = date("Y/m/d", strtotime("-" . $endAge . " years"));                
                $select->where(new Zend_Db_Expr($this->getService('User')->quoteInto(array("u.BirthDate > ? "), array($startDate))));
            }
            
            if ($startAge = intval($model->age_min)) {
                $endDate = date("Y/m/d", strtotime("-" . ($startAge - 1) . " years")); 
                $select->where(new Zend_Db_Expr($this->getService('User')->quoteInto(array("u.BirthDate < ? "), array($endDate))));
            }
//  exit($select->__toString());
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
        }        
        
        /* @var $searchService HM_Recruit_Candidate_Search_Service */
        $searchService = $this->getService('RecruitCandidateSearchService');
        $searchResultCollection = $searchService->newSearchResultCollection();
        $results = $users = array();
        if (sizeof($rows) > 0) {
            
            foreach ($rows as $row) {
                if (!isset($results[$row['mid']])) {
                    $results[$row['mid']] = array();
                    $users[$row['mid']] = $row;
                }
                $requiredCriterion = $requiredCriteria[$row['criterion_id']];
                if (($row['value'] >= $requiredCriterion->value) || !is_a($requiredCriterion, 'HM_At_Profile_CriterionValue_CriterionValueModel')) {
                    $results[$row['mid']][$row['criterion_id']] = true;
                }
            }
            
            foreach ($results as $userId => $userResults) {
                if (count($userResults) == count($requiredCriteria)) {
                    /*@var $resultItem HM_Recruit_Candidate_Search_Result_AbstractItem */
                    $resultItem = $searchService->newSearchResultItem();
                    $resultItem->setCandidateId(intval($userId));
                    $resultItem->addition('alreadyInserted', true);
                    $resultItem->addition('currentPositionId', $users[$userId]['soid']);
                    $searchResultCollection->add($resultItem);
                }
            }
        }
        return $searchResultCollection;
    }

}