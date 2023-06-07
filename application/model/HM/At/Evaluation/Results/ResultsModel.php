<?php
class HM_At_Evaluation_Results_ResultsModel extends HM_Model_Abstract
{
    const INDICATORS_STATUS_NOT_STARTED = 0;
    const INDICATORS_STATUS_IN_PROGRESS = 1;
    const INDICATORS_STATUS_FINISHED = 2;
    
    const MAX_RESULT_LIMIT = 0; // количество максимальных оценок в пределах одного подразделения, в %

    const PAIRS_STATUS_IN_PROGRESS = 0;
    const PAIRS_STATUS_FINISHED = 1;
}