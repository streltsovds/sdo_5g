<?php
/**
 * PersistentModel - набор данных, хранимый в сессии на протяжении всего Quest'а
 *
 */
abstract class HM_Quest_Attempt_Type_Abstract extends HM_Multipage_PersistentModel_Abstract implements HM_Multipage_PersistentModel_Interface
{
    protected $_index;
    protected $_quest;
    protected $_clusters;
    protected $_numbers;
    protected $_questions;

    protected $_contextModel;

    protected $_mode; // HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_*

    public function getModel()
    {
        // чтобы не тащить за собой много переменных во view
        return array(
            'index' => $this->_index,
            'quest' => $this->_quest,
            'clusters' => $this->_clusters,
            'numbers' => $this->_numbers,
            'questions' => $this->_questions,
            'mode' => $this->_mode,
            'attempt' => $this->getData(),
        );
    }

    /**
     * @param HM_Quest_Type_TestModel $quest
     * @return $this
     * @throws Zend_Exception
     */
    public function setupModel()
    {
        /** @var HM_Quest_QuestModel $quest */
        $quest = $this->_quest;

        if (!$quest) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')
                ->findDependence(['Settings', 'Cluster', 'QuestionQuest'], $this->quest_id);
        }

        $questions = [];
        $questionIds = [];

        if (count($quest->questionQuest)) {
            /* думаю стоит обнулять _questions при новом вызове setupModel
                иначе беда  начинается если лимиты есть.
            */
            $this->_questions = [];
            $questionIds = $quest->questionQuest->getList('question_id');

            /** @var HM_Question_QuestionService $questQuestionService */
            $questQuestionService = Zend_Registry::get('serviceContainer')->getService('QuestQuestion');

            $questions = $questQuestionService->fetchAll([
                    'question_id IN (?)' => count($questionIds) ? $questionIds : [0]]
            )->getList('question_id', 'order');
            asort($questions);
            $questionIds = array_keys($questions);

            switch ($quest->mode_selection) {
                case HM_Quest_QuestModel::MODE_SELECTION_ALL:
                    if ($quest->mode_selection_all_shuffle) {
                        shuffle($questionIds);
                    }
                    break;
                case HM_Quest_QuestModel::MODE_SELECTION_LIMIT:
                    if ($quest->mode_selection_questions) {
                        shuffle($questionIds);
                        $questionIds = array_slice($questionIds, 0, $quest->mode_selection_questions);
                    }
                    break;
                case HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER:
                    if ($quest->mode_selection_questions) {
                        $questionIds = $this->_reduceByCluster($quest->questionQuest, $quest->mode_selection_questions);
                    }
                    break;
                case HM_Quest_QuestModel::MODE_SELECTION_LIMIT_CLUSTER:
                    $questionIds = $this->_reduceByCluster($quest->questionQuest, $quest->getClusterLimits());
                    break;
            }

            if (count($questionIds)) {
                $questions = $questQuestionService->fetchAllDependence(['Variant', 'QuestionQuest'], ['question_id IN (?)' => $questionIds]);
                // $this->attempt_id может быть пустым в случае preview
                if ($this->attempt_id && count($collection = Zend_Registry::get('serviceContainer')->getService('QuestQuestionResult')->fetchAll([
                        'question_id IN (?)' => $questionIds,
                        'attempt_id  = ?' => $this->attempt_id,
                    ]))) {
                    foreach ($collection as $questionResult) {
                        $results[$questionResult->question_id] = $questionResult->variant ? $questionResult->variant : $questionResult->free_variant;
                    }
                }
            } else {
                $questions = new HM_Collection();
            }
        }

        $externalVariants = [];
        if ($quest->scale_id) {
            $scaleValues = Zend_Registry::get('serviceContainer')->getService('ScaleValue')
                ->fetchAll('scale_id=' . $quest->scale_id);

            foreach ($scaleValues as $scaleValue) {
                $variant = new stdClass();
                $variant->variant = $scaleValue->text;
                $variant->question_variant_id = $scaleValue->value_id;
                $externalVariants[] = $variant;
            }
        }

        $this->_quest = $quest;

        if (count($questions)) {
            $questions = $questions->asArrayOfObjects();

            $i = 0;
            foreach ($questionIds as $questionId) { // $questionIds - перемешанный порядок
                if (!$questions[$questionId]) {
                    continue;
                }
                $question = $questions[$questionId];
                $questionClusterId = 0;
                foreach ($question->questionQuest as $questionQuest) {
                    if ($questionQuest->quest_id == $quest->quest_id) {
                        $questionClusterId = $questionQuest->cluster_id;
                        break;
                    }
                }

                $clusterId = $this->_getCluster($questionClusterId, ++$i, count($questionIds));

                if (!isset($this->_index[$clusterId])) {
                    $this->_index[$clusterId] = [];
                    // На случай совпадения order делаем двойной ключ
                    $this->_items[intval($this->_clusters[$clusterId]->order) . '_' . $clusterId] = $clusterId;
                }

                if ($externalVariants) {
                    $question->variants = $externalVariants;
                } elseif ($this->_contextModel && ($question->type == HM_Quest_Question_QuestionModel::TYPE_SUBJECTS)) {
                    $question->variants = $this->_getSubjectsAsVariants();
                } elseif ($this->_contextModel && ($question->type == HM_Quest_Question_QuestionModel::TYPE_RESERVE_POSITIONS)) {
                    $question->variants = $this->_getReservePositionsAsVariants();
                } elseif (count($question->variants)) {
                    if (!is_array($question->variants)) {
                        $variants = $question->variants->asArrayOfObjects();
                    } else {
                        $variants = $question->variants;
                    }
                    ksort($variants);
                    $selfTestMode = $this->getQuest()->getSettings()->mode_self_test;
                    foreach ($variants as $variant) {

                        if (!$selfTestMode)
                            unset($variant->is_correct);

                        unset($variant->weight);
                    }

                    $question->variants = $variants;
                }

                $this->_questions[$question->question_id] = $question;
                $this->_index[$clusterId][] = $question->question_id;

                if (isset($results[$question->question_id])) {
                    $arr = unserialize($results[$question->question_id]);
                    $this->_results[$clusterId][$question->question_id] = is_array($arr) ? $arr : $results[$question->question_id];
                }
            }

            $emptyClusterIds = array_diff(array_keys($this->_clusters), array_keys($this->_index));
            foreach ($emptyClusterIds as $emptyClusterId) {
                unset($this->_clusters[$emptyClusterId]);
            }

            try {
                // Сортируем по order'у кластеров
                uksort($this->_items, [$this, '_clusterSort']);

            } catch (Exception $e) {
            }

            // А потом скидываем значения ключей, иначе где-то дальше всё ломается
            $this->_items = array_values($this->_items);

            try {
                foreach ($this->_index as $index => &$cluster) {
                    usort($cluster, [$this, '_questionsSort']);
                }
                unset($cluster);
            } catch (Exception $e) {
            }

            // сквозная нумерация вопросов
            $i = 0;
            foreach ($this->_items as $clusterId) {
                $questions = $this->_index[$clusterId];

                foreach ($questions as $questionId) {
                    $this->_numbers[$questionId] = ++$i;
                }
            }

        }
        return $this;
    }

    protected function _getCluster($clusterId, $i, $questionsCount = 0)
    {
        switch ($this->_quest->mode_display) {
            case HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS:
                if ($this->_quest->mode_display_clusters) {
                    $limitQuestions = (int)ceil($questionsCount / $this->_quest->mode_display_clusters);
                }
                // здесь не нужно break, проваливаемся дальше
            case HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS:
                if ($this->_quest->mode_display_questions) {
                    $limitQuestions = $this->_quest->mode_display_questions;
                }

                $clusterId = (int)ceil($i / $limitQuestions);
                if (!isset($this->_clusters[$clusterId])) {
                    $cluster = new stdClass();
                    $cluster->name = sprintf(_('%s'), $clusterId);
                    $this->_clusters[$clusterId] = $cluster;
                }
                break;
            case HM_Quest_QuestModel::MODE_DISPLAY_BY_CLUSTERS:
                if (empty($this->_clusters)) {
                    $nonClustered = new stdClass();
                    $nonClustered->name = _('Все вопросы');
                    $this->_clusters = array(HM_Quest_Cluster_ClusterModel::NONCLUSTERED => $nonClustered);

                    $clusterIds  = array_filter($this->_quest->questionQuest->getList('cluster_id'));

                    if (count($clusterIds)) {
                        $this->_clusters = Zend_Registry::get('serviceContainer')->getService('QuestCluster')
                            ->fetchAll(array('cluster_id in (?)' => $clusterIds))->asArrayOfObjects();
                    }
                }
                $clusterId = $clusterId ? $clusterId : HM_Quest_Cluster_ClusterModel::NONCLUSTERED;
            break;

        }
        return $clusterId;
    }


    public function setContextModel($model, $contextEventType = null)
    {
        $this->_contextModel = $model;
        Zend_Registry::get('serviceContainer')->getService('QuestAttempt')->updateWhere($model->getQuestContext($contextEventType), array('attempt_id = ?' => $this->attempt_id));
    }

    public function getContextModel()
    {
        return $this->_contextModel;
    }


    public function getQuest()
    {
        return $this->_quest;
    }

    public function setQuest($quest)
    {
        $this->_quest = $quest;
        return $this;
    }

    public function updateByType()
    {
        return true;
    }

    protected function _reduceByCluster($questions, $limits)
    {
        $return = array();
        $questionsByClusters = array();
        foreach ($questions as $question) {
            if (!isset($questionsByClusters[$question->cluster_id])) $questionsByClusters[$question->cluster_id] = array();
            $questionsByClusters[$question->cluster_id][] = $question->question_id;
        }
        foreach ($questionsByClusters as $clusterId => $questionIds) {
            if (is_array($limits)) {
                if (!isset($limits['cluster_limit_' . $clusterId]) || ($limits['cluster_limit_' . $clusterId] === '0')) {
                    continue;
                }

                $limit = $limits['cluster_limit_' . $clusterId];
            } else {
                $limit = $limits;
            }
            shuffle($questionIds);
            if ($limit && (count($questionIds) > $limit)) {
                $questionIds = array_slice($questionIds, 0, $limit);
            }
            $return = array_merge($return, $questionIds);
        }
        shuffle($return);
        return $return;
    }

    //  Сортировка по весу ASC
    public function _clusterSort($a, $b) {

        list($orderA, $idA) = explode('_', $a);
        list($orderB, $idB) = explode('_', $b);

        // Это чтобы безблочные были в самом конце
        if($idA < 0) return 1;
        if($idB < 0) return -11;

        if($orderA == $orderB)
            return strnatcmp($idA, $idB);
        else
            return strnatcmp($orderA, $orderB);
    }

    //  Сортировка по весу ASC
    public function _questionsSort($a, $b) {
        return strnatcmp($this->_questions[$a]->order, $this->_questions[$b]->order);
    }

    protected function _getSubjectsAsVariants()
    {
        $services = Zend_Registry::get('serviceContainer');

        $variants = $criteriaToDevelop = array();
        if ($position = $services->getService('Orgstructure')->getOne($services->getService('Orgstructure')->find($this->_contextModel->position_id))) {

            if (count($collection = $services->getService('AtProfileCriterionValue')->fetchAll(array(
                'profile_id = ?' => $position->profile_id,
                'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
            )))) {

                $profileCriteriaValues = $collection->getList('criterion_id', 'value');

                $userCriteriaValues = array();
                if (count($collection = $services->getService('AtSessionUserCriterionValue')->fetchAll(array(
                    'session_user_id = ?' => $this->_contextModel->session_user_id,
                    'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                )))) {
                    $userCriteriaValues = $collection->getList('criterion_id', 'value');
                }

                $criteriaToDevelop = array();
                foreach ($profileCriteriaValues as $criterionId => $value) {
                    if (!isset($userCriteriaValues[$criterionId]) || ($userCriteriaValues[$criterionId] < $value)) {
                        $criteriaToDevelop[] = $criterionId;
                    }
                }
            }
        }

        if (count($criteriaToDevelop)) {
            if (count($collection = $services->getService('AtCriterionTest')->fetchAllDependence('Subject', array(
                'criterion_id IN (?)' => $criteriaToDevelop
            )))) {
                foreach ($collection as $criterion) {
                    if (count($criterion->subject)) {
                        $subject = $criterion->subject->current();
                        $variants[$subject->subid] = sprintf("%s (%s, %d/%d)", $subject->name, $criterion->name, $userCriteriaValues[$criterion->criterion_id], $profileCriteriaValues[$criterion->criterion_id]);
                    }
                }
            }
        }

        return $variants;
    }

    protected function _getReservePositionsAsVariants()
    {
        $services = Zend_Registry::get('serviceContainer');

        $variants = $criteriaToDevelop = array();
        if ($position = $services->getService('Orgstructure')->getOne($services->getService('Orgstructure')->find($this->_contextModel->position_id))) {

            if (count($collection = $services->getService('AtProfileCriterionValue')->fetchAll(array(
                'profile_id = ?' => $position->profile_id,
                'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
            )))) {

                $profileCriteriaValues = $collection->getList('criterion_id', 'value');

                $userCriteriaValues = array();
                if (count($collection = $services->getService('AtSessionUserCriterionValue')->fetchAll(array(
                    'session_user_id = ?' => $this->_contextModel->session_user_id,
                    'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                )))) {
                    $userCriteriaValues = $collection->getList('criterion_id', 'value');
                }

                $criteriaToDevelop = array();
                foreach ($profileCriteriaValues as $criterionId => $value) {
                    if (!isset($userCriteriaValues[$criterionId]) || ($userCriteriaValues[$criterionId] < $value)) {
                        $criteriaToDevelop[] = $criterionId;
                    }
                }
            }
        }

        if (count($criteriaToDevelop)) {
            if (count($collection = $services->getService('AtCriterionTest')->fetchAllDependence('ReservePosition', array(
                'criterion_id IN (?)' => $criteriaToDevelop
            )))) {
                foreach ($collection as $criterion) {
                    if (count($criterion->reservePosition)) {
                        $reservePosition = $criterion->reservePosition->current();
                        $variants[$reservePosition->reserve_position_id] = sprintf("%s (%s, %d/%d)", $reservePosition->name, $criterion->name, $userCriteriaValues[$criterion->criterion_id], $profileCriteriaValues[$criterion->criterion_id]);
                    }
                }
            }
        }

        return $variants;
    }

    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

}