<?php
class HM_Quest_Question_Import_Manager
{
    protected $_inserts = array();
    protected $_questName = '';

    const CACHE_NAME = 'HM_Quest_Question_Import_Manager';

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {

    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }


    public function getCount()
    {
        return $this->getInsertsCount();
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_inserts,
                 'questName' => $this->_questName
            ),
            self::CACHE_NAME
        );
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_questName = $actions['questName'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items, $fileName = '', $nameOnly = false)
    {
        $this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        $name = $nameOnly ? pathinfo($fileName)['filename'] : $fileName;
        $this->_questName = !empty($name) ? $name : _('Новый тест');

        if (count($items)) {
            foreach($items as $item) {
                $this->_inserts[] = $item;
            }
        }

        $this->saveToCache();

        return true;
    }


    public function import($subjectId = null, $quest = null)
    {
        $subjectId = (int) $subjectId ?: Zend_Controller_Front::getInstance()->getRequest()->getParam('subject_id', 0);

        if (!$quest) {
            $quest = $this->getService('Quest')->insert([
                'type' => HM_Quest_QuestModel::TYPE_TEST,
                'status' => HM_Quest_QuestModel::STATUS_RESTRICTED,
                'subject_id' => $subjectId,
                'scale_id' => HM_Scale_ScaleModel::MODE_SUBJECT,
                'name' => $this->_questName,
                'creator_role' => '',
            ]);
        }
        $questId = $quest->quest_id;

        if ($quest && count($this->_inserts)) {
            foreach($this->_inserts as $insert) {

                // defaults
                $insert->quest_type        = $quest->type;
                $insert->mode_scoring      = HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT;
                $insert->show_free_variant = HM_Quest_Question_QuestionModel::SHOW_FREE_VARIANT_OFF;
                $insert->subject_id        = $subjectId;
                $insert->score_min         = 0;
                $insert->score_max         = 1;

                $answers = $insert->answers;
                unset($insert->answers);
                
                $question = $this->getService('QuestQuestion')->insert($insert->getValues());
                $this->getService('QuestQuestionQuest')->insert([
                    'quest_id' => $questId,
                    'question_id' => $question->question_id,
                    'cluster_id' => HM_Quest_Cluster_ClusterModel::NONCLUSTERED
                ]);
                
                if (count($answers)) {
                    foreach($answers as $answer) {
                        $answer['question_id'] = $question->question_id;
                        $this->getService('QuestQuestionVariant')->insert($answer);
                    }
                }
            }
        }
    }
}