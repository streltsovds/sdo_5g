<?php
class HM_Quest_Question_Result_ResultService extends HM_Service_Abstract
{
    public function reportResults($persistentModel)
    {
        $model        = $persistentModel->getModel();

        $clusters     = $model['quest']->clusters
            ? $model['quest']->clusters->getList('cluster_id', 'name', _('Вопросы без темы'))
            : array();

        $clusters[-1] = $clusters[0];

        $questionClusters = array();
        if ($model['quest']->mode_display == HM_Quest_QuestModel::MODE_DISPLAY_BY_CLUSTERS) {
            foreach ($model['index'] as $clusterNum => $questions) {
                foreach ($questions as $questionId) {
                    $questionClusters[$questionId] = $clusterNum;
                }
            }
        } else {
            $questionClusters = $model['quest']->questionQuest->getList('question_id', 'cluster_id');
        }

        $questionResults = $persistentModel->getResults();

        $report = array(
            'global'   => array(),
            'clusters' => array(),
        );

        $questionIds = array_keys($model['numbers']);

// смотрите и учитесь:) эта конструкция легко валит сервер 40 ядер 128 Гб оперативки
//        $questions = $this->getService('QuestQuestion')->fetchAllHybrid(array('Result', 'Variant'), 'Quest', 'QuestionQuest', array(
//            'question_id IN (?)' => $questionIds
//        ))->asArrayOfObjects();

        $questions = $this->getQuestionsData($questionIds, $persistentModel->attempt_id, $persistentModel->getQuest());

        $globalCntAll     = 0;
        $globalCntCorrect = 0;
        $globalCntMin = 0;
        $globalCntMax = 0;
        /* Для рачета результатов по темам на лету
        $clusterCntAll     = array();
        $clusterCntCorrect = array();
        $clusterCntMin = array();
        $clusterCntMax = array();
        $clusterCnt = array();
        */

        foreach ($model['index'] as $clusterNum => $clusterQuestions) {

            foreach ($clusterQuestions as $questionId) {
                $answer = $questionResults[$clusterNum][$questionId];

                if (!is_subclass_of($questions[$questionId], 'HM_Quest_Question_QuestionModel')) continue;
                if (is_a($questions[$questionId], 'HM_Quest_Question_Type_FileModel')) continue;

                $result = $questions[$questionId]->getResult($answer) + $questions[$questionId]->getScale();

                /* Для рачета результатов по темам на лету
                $clusterId = isset($questionClusters[$questionId]) ? $questionClusters[$questionId] : 0;
                $globalCntAll++;
                $clusterCntAll[$clusterId]++;

                if(!isset($clusterCnt[$clusterId]))
                    $clusterCnt[$clusterId] = 1;
                else
                    $clusterCnt[$clusterId]++;
                */
                $globalCntAll++;

                $globalCntMin += $result['score_min'];
                $globalCntMax += $result['score_max'];

                /* Для рачета результатов по темам на лету
                if(!isset($clusterCntMin[$clusterId]))
                    $clusterCntMin[$clusterId] = $result['score_min'];
                else
                    $clusterCntMin[$clusterId] += $result['score_min'];

                if(!isset($clusterCntMax[$clusterId]))
                    $clusterCntMax[$clusterId] = $result['score_max'];
                else
                    $clusterCntMax[$clusterId] += $result['score_max'];
                */

                if (isset($result['score_weighted'])) {
                    /* Для рачета результатов по темам на лету
                    $clusterCntCorrect[$clusterId] += $result['score_weighted'];
                    */
                    $globalCntCorrect  += $result['score_weighted'];

                } elseif (isset($result['is_correct'])) {

                    if ($result['is_correct']) {
                        /* Для рачета результатов по темам на лету
                        $clusterCntCorrect[$clusterId] += $result['score_max'];
                        */
                        $globalCntCorrect  += $result['score_max'];
                    } else {
                        /* Для рачета результатов по темам на лету
                        $clusterCntCorrect[$clusterId] += $result['score_min'];
                        */
                        $globalCntCorrect  += $result['score_min'];
                    }
                }

/*
                if (isset($result['is_correct'])) {
                    if ($result['is_correct']) {
                        $clusterCntCorrect[$clusterId] += 1;
                        $globalCntCorrect  += 1;
                    }
                } elseif (!empty($result['score_weighted'])) {

                    $clusterCntCorrect[$clusterId] += $result['score_weighted'];
                    $globalCntCorrect  += $result['score_weighted'];
                }
*/
            }
        }

		$report['show_result'] = $model['quest']->show_result;

        /* Для рачета результатов по темам на лету
        foreach ($clusterCntAll as $clusterId => $cntAll) {
            if ($clusterCntAll && isset($clusters[$clusterId])) {
                $report['clusters'][$clusters[$clusterId]] = $model['quest']->show_result ? (round(  ($clusterCntCorrect[$clusterId] - $clusterCntMin[$clusterId]) * 100 / ($clusterCntMax[$clusterId] - $clusterCntMin[$clusterId]))  . "%") : $clusterCnt[$clusterId];
            }
        }
        */

        $globalCntDiff = $globalCntMax - $globalCntMin;
        $result = ($globalCntDiff ? round(($globalCntCorrect - $globalCntMin) * 100 / $globalCntDiff) : 0);
		if($model['quest']->show_result) {
        	$report['global'][_('Результат')] = $result . '% <span class="ball">(набрано баллов: ' . $globalCntCorrect . ')</span>';
		} else {
        	$report['global'][_('Вопросов')] = $globalCntAll;
		}

        $dateBegin = new HM_Date($model['attempt']['date_begin']);
        $dateEnd   = new HM_Date($model['attempt']['date_end'] ? $model['attempt']['date_end'] : date('Y-m-d H:i:s'));

        $report['global'][_('Затраченное время')] = HM_Date::getDurationString($dateEnd->get(Zend_Date::TIMESTAMP) - $dateBegin->get(Zend_Date::TIMESTAMP));

        if  ($contextModel = $persistentModel->getContextModel()) {

            //только для уроков
            if ($contextModel->vedomost) {
                $mark   = $this->getService('Quest')->countScaleMark($contextModel, $result);
                if ($mark !== false) {
                    $report['global'][_('Оценка за занятие')] = $mark;
                }
            }

            if($model['quest']->limit_attempts)
            {
                $context = $contextModel->getQuestContext();
                $a = $this->getService('QuestAttempt')->fetchAll(array(
                        'user_id = ?'           => $this->getService('User')->getCurrentUserId(),
                        'context_event_id  = ?' => $context['context_event_id'],
                        'quest_id  = ?'         => $model['quest']->quest_id)
                );
                $attemptsLimit = (int)$model['quest']->limit_attempts;
                $attempts2Left = $attemptsLimit - count($a);
                if($attempts2Left<0 && $model['quest']->limit_clean) {
                    $attempts2Left = count($a)%$model['quest']->limit_attempts ? ($model['quest']->limit_attempts - count($a)%$model['quest']->limit_attempts) : 0;
                }
                $report['global'][_('Осталось попыток')] = $attempts2Left;
            }
        }

        /* Для рачета результатов по темам на лету
        ksort($report['clusters']);
        */

        /* Результатов по темам из базы */
        if($model['attempt']['attempt_id']){
            $report['clusters'] = $this->getService('QuestAttemptCluster')->getAttemptResults($model['attempt']['attempt_id']);
        }


        return $report;
    }

    public function saveResults($quest, $questAttemptId, $questionIds, $results, $comment = null)
    {
        // если не установлен $questAttemptId - значит preview-режим
        if (is_array($questionIds) && count($questionIds)) {
            if ($questAttemptId) {

                $toUpdate = $this->fetchAll(array(
                    'attempt_id = ?' => $questAttemptId,
                    'question_id IN (?)' => $questionIds
                ))->getList('question_id', 'question_result_id');

//                $questions = $this->getService('QuestQuestion')->fetchAllHybrid(array('Result', 'Variant'), 'Quest', 'QuestionQuest', array(
//                    'question_id IN (?)' => $questionIds
//                ))->asArrayOfObjects();
                $questions = $this->getQuestionsData($questionIds, $questAttemptId, $quest);

                $request = Zend_Controller_Front::getInstance()->getRequest();
                $timestop = $request->getParam('timestop', 0);

                foreach ($questions as $questionId => $question) {

                	$value = isset($results[$questionId]) ? $results[$questionId] : null;

                    /** @var HM_Quest_Question_Type_Interface $question */
                    $question = $questions[$questionId];

                    // @todo: зачем тут is_subclass_of?? может, тоже юзать instanceof??
                    if (!is_subclass_of($question, 'HM_Quest_Question_QuestionModel')) {
                        continue;
                    }

                    if ($question instanceof HM_Quest_Question_Type_FileModel) {
                        $value = $questAttemptId;
                    }

                    $result = array(
		                'question_id' => $questionId,
		                'attempt_id' => $questAttemptId,
		                'comment' => $comment[$questionId],
	                );

	                $result = $result + $question->getResult($value);
                    $result = $result + $question->getScale();

                    if($timestop && !in_array($questionId, array_keys($results))) {
                    	$results[$questionId] = $value;
                    }

                    if (array_key_exists($questionId, $toUpdate)) {
                        $result['question_result_id'] = $toUpdate[$questionId];
                        $this->update($result);
                        unset($toUpdate[$questionId]);
                    } else {
                        $result['variant'] = $result['variant'] ? : '';
                        $this->insert($result);
                    }
                }
                
                if (count($toUpdate)) {
                    $this->deleteBy(array('question_result_id IN (?)' => $toUpdate));
                }

                return (count($questionIds) == count($results));
                
            } else {
                // если preview - возвращаем true как будто всё OK
                return true; 
            }
        }
        return false; 
    }

    public function getQuestionsData($questionIds, $questAttemptId, $quest)
    {
        $questions = $this->getService('QuestQuestion')->fetchAll(array(
            'question_id IN (?)' => $questionIds
        ))->asArrayOfObjects();

        $variants = $this->getService('QuestQuestionVariant')->fetchAll(array(
            'question_id IN (?)' => $questionIds
        ))->asArrayOfObjects();

        $results = array();
        if ($questAttemptId) {
            $results = $this->getService('QuestQuestionResult')->fetchAll(array(
                'attempt_id = ?' => $questAttemptId,
                'question_id IN (?)' => $questionIds,
            ))->asArrayOfObjects();
        }

        $variantsByQuestion = array();
        foreach ($variants as $variant) {
            if (!isset($variantsByQuestion[$variant->question_id])) $variantsByQuestion[$variant->question_id] = new HM_Collection(array(), 'HM_Quest_Question_Variant_VariantModel');
            $variantsByQuestion[$variant->question_id][] = $variant;
        }

        $resultsByQuestion = array();
        foreach ($results as $result) {
            if (!isset($resultsByQuestion[$result->question_id])) $resultsByQuestion[$result->question_id] = new HM_Collection(array(), 'HM_Quest_Question_Result_ResultModel');
            $resultsByQuestion[$result->question_id][] = $result;
        }

        foreach ($questions as &$question) {
            $question->quest = new HM_Collection(array(), get_class($quest));
            $question->quest[] = $quest;
            if (isset($variantsByQuestion[$question->question_id])) $questions[$question->question_id]->variants = $variantsByQuestion[$question->question_id];
            if (isset($resultsByQuestion[$question->question_id])) $questions[$question->question_id]->results = $resultsByQuestion[$question->question_id];
        }

        return $questions;
    }
}