<?php
class HM_Tc_Feedback_FeedbackService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        $scaleValues = $this->getService('ScaleValue')->fetchAll('scale_id=' . HM_Scale_ScaleModel::TYPE_TC_FEEDBACK)->getList('value_id', 'value');
        $sumFields = array(
            "mark_goal",
            "mark_goal2",
            "mark_usefull",
            "mark_course",
            "mark_motivation",
            "mark_teacher",
            "mark_papers",
            "mark_organization",
        );

        $sum = 0;
        $cnt = 0;
        foreach ($sumFields as $field) {
            if (isset($data[$field]) && isset($scaleValues[$data[$field]])) {
                $sum += $scaleValues[$data[$field]];
                $cnt++;
            }
        }

        $mark = $cnt ? round($sum/$cnt) : 0;
        $data['mark'] = (int) array_search($mark, $scaleValues);

        parent::insert($data, $unsetNull);
    }

}