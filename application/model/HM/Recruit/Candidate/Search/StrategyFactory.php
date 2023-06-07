<?php
/**
 * StrategyFactory
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_StrategyFactory extends HM_Service_Primitive
{
    
    /**
     * Get search strategy (ex: HM_Recruit_Candidate_Search_ExternalStrategy for hh.ru source)
     * @param string $source
     * @return HM_Recruit_Candidate_Search_SearchBehavior
     */
    public function getStrategy($source)
    {
        $strategyServiceNamePrefix = "RecruitCandidate";
        $strategyServiceName = $strategyServiceNamePrefix.ucfirst($source)."SearchStrategy";
        if ($strategyServiceName == "RecruitCandidateExternalSearchStrategy") {
            $config = Zend_Registry::get('config');
            $externalSourceName = $config->vacancy->externalSource;
            $strategyServiceName = $strategyServiceNamePrefix.ucfirst($externalSourceName)."ExternalSearchStrategy";
        }
        try {
            $strategy = $this->getService($strategyServiceName);
            return $strategy;
        } catch (InvalidArgumentException $e) {
            throw new HM_Recruit_Candidate_Search_Exception_InvalidSearchStrategyException('Required candidate search strategy is not defined!', $e->getCode(), $e);
        }
    }
    
}
