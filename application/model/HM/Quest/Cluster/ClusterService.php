<?php
class HM_Quest_Cluster_ClusterService extends HM_Service_Abstract
{
    public function getQuestClusters($questId)
    {
        
        $select = $this->getSelect();
        $select->from(
            array('qc' => 'quest_clusters'),
            array('qc.cluster_id', 'qc.name')
        );
        $select->joinLeft(array('qqq' => 'quest_question_quests'), 'qqq.cluster_id = qc.cluster_id', array());
        $select->where($this->quoteInto(
            array('qqq.quest_id = ?', ' OR qc.quest_id = ?'),
            array($questId, $questId)
        ));
        $select->group(array('qc.cluster_id', 'qc.name'));
        
        $clustersRows = $select->query()->fetchAll();
        
        $clusters = array();
        foreach($clustersRows as $clusterRow){
            $clusters[$clusterRow['cluster_id']] = $clusterRow['name'];
        }
        
        $clusters = array( 0 => _('Выберите блок вопросов')) + $clusters;
            
        return $clusters;
    }

    public function getUsefullQuestClusters($questId, $reportMode = true)
    {
        $select = $this->getSelect();
        $select->from(
            array('qc' => 'quest_clusters'),
            array('qc.cluster_id', 'qc.name'))
        ->joinInner(array('qqq' => 'quest_question_quests'), 'qc.cluster_id=qqq.cluster_id', array())
        ->where('qqq.quest_id=?', $questId);

        $result   = $select->query()->fetchAll();
        $clusters = array();
        foreach ($result as $row) {
            if ($reportMode) {
                $clusters[$row['name']] = '';
            } else {
                $clusters[$row['cluster_id']] = $row['name'];
            }
        }

        return $clusters;
    }

    public function copy($fromQuestId, $toQuestId) {
        $clusters = $this->fetchAll(
            $this->quoteInto('quest_id = ?', $fromQuestId)
        );
        $questionsArray = array();
        $nonClusteredQuestionIds = $this->getService('QuestQuestionQuest')->fetchAll(
            $this->quoteInto(array('(cluster_id IS NULL OR cluster_id = ?) ', ' AND quest_id = ?'),
                array(0, $fromQuestId))
        );

        if (count($nonClusteredQuestionIds)) {
            $nonClusteredQuestions = $this->getService('QuestQuestion')->fetchAllDependence('Variant',
                $this->quoteInto('question_id IN (?) ', $nonClusteredQuestionIds->getList('question_id', 'question_id'))
            );
            if(count($nonClusteredQuestions)) {
                foreach ($nonClusteredQuestions as $question) {
                    $questionsArray[$question->question_id] = $question;
                }
            }
        }
        if (count($clusters)) {
            foreach ($clusters as $cluster) {
                $cluster->quest_id = $toQuestId;
                $data = $cluster->getValues(null, array('cluster_id'));
                $newCluster = $this->insert($data);
                $questionIds = $this->getService('QuestQuestionQuest')->fetchAll(
                    $this->quoteInto(array('cluster_id = ? ', ' AND quest_id = ?'),
                        array($cluster->cluster_id, $fromQuestId))
                );

                $questionIds = $questionIds->getList('question_id');

                if(count($questionIds)) {
                    $questions = $this->getService('QuestQuestion')->fetchAllDependence('Variant',
                        $this->quoteInto('question_id IN (?)', $questionIds)
                    );
                    if (count($questions)) {
                        foreach ($questions as $question) {
                            $question->cluster_id = $newCluster->cluster_id;
                            $questionsArray[$question->question_id] = $question;
                        }
                    }
                }
            }
        }
        return $questionsArray;
    }
}