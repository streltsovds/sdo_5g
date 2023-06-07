<?php

/**
 * Description of JsonDecorator
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_Result_JsonDecorator extends HM_Recruit_Candidate_Search_Result_OutDecorator {

    public function out() {
        /* @var $collection HM_Recruit_Candidate_Search_Result_AbstractItemsCollection */
        $collection = $this->getDecorableCollection();
        $collection->rewind();
        $arrayResult = array();
        if ($collection !== null) {
            if (count($collection) > 0) {
                /* @var $item HM_Recruit_Candidate_Search_Result_AbstractItem */
                foreach ($collection as $item) {
                    $birtDate = $item->getCandidateBirthDate()->toString("dd-MM-YYYY");
                    $arrayResult[$item->getCandidateId()] = array(
                        'CandidateId' => $item->getCandidateId(),
                        'CandidateName' => $item->getCandidateName(),
                        'CandidateLastName' => $item->getCandidateSecondName(),
                        'CandidateBirthDate' => $birtDate,
                    );
                }
            }
        }
        return HM_Json::encodeErrorSkip($arrayResult);
    }

}
