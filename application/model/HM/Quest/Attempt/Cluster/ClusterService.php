<?php
class HM_Quest_Attempt_Cluster_ClusterService extends HM_Service_Abstract
{

    public function saveAttemptResults($attemptId)
    {

        if (count($collection = $this->getService('QuestAttempt')->findDependence(array('Quest', 'User', 'QuestionResult'), $attemptId))) {

            $questAttempt = $collection->current();

            if ($questAttempt && $questAttempt->questionResults) {

                $quest = $questAttempt->quest->current();
                $questionIds = $questAttempt->questionResults->getList('question_id');
                $questions = $this->getService('QuestQuestion')->fetchAllDependence(array('Variant', 'QuestionQuest'), array('question_id IN (?)' => $questionIds), 'question_id');

                $clusterIds = $this->getService('QuestQuestionQuest')->fetchAll(array('quest_id = ?' => $quest->quest_id))->getList('cluster_id');
                $clusters = $this->getService('QuestCluster')->fetchAll(array('cluster_id IN (?)' => $clusterIds))->getList('cluster_id', 'name', _('Вопросы без темы'));
                $questions = $questions->asArrayOfObjects();

                if (count($questAttempt->questionResults)) {
                    $clusterResults = array();
                    $clusterMin = array();
                    $clusterMax = array();
                    foreach ($questAttempt->questionResults as $questionResult) {

                        $question = $questions[$questionResult->question_id];
                        if ($question->questionQuest) {
                            $questionQuest = $question->questionQuest->getList('question_id', 'cluster_id');
                            $clusterId = $questionQuest[$questionResult->question_id];

                            if (isset($questionResult->score_weighted)) {
                                $clusterResults[$clusterId] += $questionResult->score_weighted;
                            } elseif (isset($questionResult->is_correct)) {
                                if ($questionResult->is_correct) {
                                    $clusterResults[$clusterId] += $questionResult->score_max;
                                } else {
                                    $clusterResults[$clusterId] += $questionResult->score_min;
                                }
                            }
                            $clusterMin[$clusterId] += $questionResult->score_min;
                            $clusterMax[$clusterId] += $questionResult->score_max;
                        }
                    }

                    $resClusters = array();
                    if (count($clusterResults) > 1) {
                        foreach ($clusterResults as $clusterId => $clusterResult) {
                            $resClusters[$clusterId] = round(($clusterResult - $clusterMin[$clusterId]) / ($clusterMax[$clusterId] - $clusterMin[$clusterId]),2);
                        }
                    }

                    if (is_array($resClusters)) {
                        ksort($resClusters);
                    }

                    foreach ($resClusters as $key => $resCluster) {
                        $this->insert(array(
                            'quest_attempt_id' => $questAttempt->attempt_id,
                            'cluster_id' => $key,
                            'score_percented' => $resCluster
                        ));
                    }
                }
            }
        }
    }

    public function getAttemptResults($attemptId)
    {
        $attemptClusters = $this->fetchAllDependence('Cluster', array('quest_attempt_id = ?' => $attemptId), 'cluster_id ASC');
        foreach ($attemptClusters as $attemptCluster) {
            $clusterCollection = $attemptCluster->cluster;
            if (!count($clusterCollection)) {
                $returnData['Без темы'] = $attemptCluster->score_percented * 100 . "%";
            } else {
                $cluster = $this->getService('QuestCluster')->getOne($clusterCollection);
                $returnData[$cluster->name] = $attemptCluster->score_percented * 100 . "%";
            }
        }
        return $returnData;


    }



}