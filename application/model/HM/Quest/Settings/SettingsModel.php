<?php
class HM_Quest_Settings_SettingsModel extends HM_Model_Abstract
{

    // список атрибутов тестов, которые НЕ хранятся непосредственно в questionnaires;
    // они настраиваются для разных контекстов (в quest_settings);
    // обязательно добавлять сюда новые settings!
    static public function getSettingsAttributes()
    {
        return array(
            'info',
            'comments',
            'mode_selection',
            'mode_selection_questions',
            'mode_selection_all_shuffle',
            'mode_selection_questions_cluster',
            'mode_passing',
            'mode_display',
            'mode_display_clusters',
            'mode_display_questions',
            'show_result',
            'show_log',
            'limit_time',
            'limit_attempts',
            'limit_clean',
            'cluster_limits',
            'mode_test_page',
            'mode_self_test',
        );
    }
    
    static public function split($data)
    {
        $dataQuest = $dataSettings = array();
        $clusterLimits = '';

        foreach($data as $field => $value) {
            if (substr($field, 0, 14) == 'cluster_limit_') {
                $clusterLimits = ($clusterLimits ? $clusterLimits.';' : '') .
                    substr($field, 14). ';' . $value;
                unset($data[$field]);
            }
        }
        $data['cluster_limits'] = $clusterLimits;

        foreach (self::getSettingsAttributes() as $key) {
            if (isset($data[$key])) {
                $dataSettings[$key] = $data[$key];
            }
        }
        $dataQuest = array_diff_key($data, $dataSettings);
        return array($dataQuest, $dataSettings);
    }

    public function getClusterLimits()
    {
        $result = array();

        $clusterLimits = explode(';', $this->cluster_limits);
        for($i=0; $i<count($clusterLimits); $i+=2) {
            $result['cluster_limit_' . $clusterLimits[$i]] = $clusterLimits[$i+1];
        }

        return $result;
    }
}