<?php

/**
 * Description of ArrayDecorator
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_Result_ArrayDecorator extends HM_Recruit_Candidate_Search_Result_OutDecorator {

    public function out() {
        /* @var $collection HM_Recruit_Candidate_Search_Result_AbstractItemsCollection */
        $collection = $this->getDecorableCollection();
        $collection->rewind();
        $arrayResult = array();
        if ($collection !== null && ($collection instanceof Countable) && count($collection) > 0) {
            /* @var $item HM_Recruit_Candidate_Search_Result_AbstractItem */
            foreach ($collection as $item) {
                $birtDate = $item->getCandidateBirthDate()->toString("dd-MM-YYYY");
                $arrayResult[] = array(
                    'CandidateId' => $item->getCandidateId(),
                    'CandidateName' => $item->getCandidateName(),
                    'CandidateLastName' => $item->getCandidateSecondName(),
                    'CandidateBirthDate' => $birtDate,
                );
            }
        }
        return $arrayResult;
    }

}
