<?php
class HM_At_Kpi_User_Result_ResultService extends HM_Service_Abstract
{
    public function setResult($userKpiId, $newResult, $relationType = HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF)
    {
        $collection = $this->getService('AtKpiUser')->findDependence('Result', $userKpiId);
        if (count($collection)) {
            $userKpi = $collection->current();
            foreach ($userKpi->results as $result) {
                if ($result->relation_type == $relationType) {
                    $data = $result->getValues();
                    if (isset($newResult['value_fact'])) $data['value_fact'] = $newResult['value_fact'];
                    if (isset($newResult['comments'])) $data['comments'] = $newResult['comments'];
                    $data['change_date'] = date('Y-m-d H:i:s');
                    $userKpiResult = $this->getService('AtKpiUserResult')->update($data);
                    $this->getService('AtKpiUser')->update(
                        array(
                            'user_kpi_id' => $data['user_kpi_id'],
                            'value_fact' => $data['value_fact']
                        ));
                    return $userKpiResult;
                }
            }

            $data = array(
                'user_kpi_id' => $userKpiId,
                'user_id' => $userKpi->user_id,
                'respondent_id' => $this->getService('User')->getCurrentUserId(),
                'relation_type' => $relationType,
                'value_fact' => $newResult['value_fact'],
                'comments' => $newResult['comments'],
                'change_date' => date('Y-m-d H:i:s'),
            );
            $userKpiResult = $this->getService('AtKpiUserResult')->insert($data);
            $this->getService('AtKpiUser')->update(
                array(
                    'user_kpi_id' => $userKpiId,
                    'value_fact' => $newResult['value_fact']
                ));
            return $userKpiResult;
        }

        return false;
    }
}