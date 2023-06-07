<?php
class HM_Quest_Category_Result_ResultService extends HM_Service_Abstract
{
    public function getResultByScore($formula, $score)
    {
        $score = round($score, 0);

        if (!is_array($formula)) {
            $formula = unserialize($formula);
        }
        foreach ($formula as $item) {
            if ($item['from'] === '') $item['from'] = -1000;
            if ($item['to'] === '') $item['to'] = 1000;
            if (($score >= $item['from']) && ($score <= $item['to'])) return $item['description'];
        }
        return false;
    }
}