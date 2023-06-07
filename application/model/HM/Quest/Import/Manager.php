<?php
class HM_Quest_Import_Manager
{
    protected $_inserts = array();

    const CACHE_NAME = 'HM_Quest_Import_Manager';

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
                 'inserts' => $this->_inserts
            ),
            self::CACHE_NAME
        );
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {
        $this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                $this->_inserts[] = $item;
            }
        }

        $this->saveToCache();
    }


    public function import($subjectId = null, $intoKnowlBase=false)
    {
        $importedQuesCount = 0;

        if (count($this->_inserts)) {

            foreach($this->_inserts as $insert) {

                // defaults
                $insert->type = HM_Quest_QuestModel::TYPE_TEST;
                $insert->status = HM_Quest_QuestModel::STATUS_RESTRICTED;

                $questions = $insert->questions;
                unset($insert->questions);

                $values = $insert->getValues();
                if (!$intoKnowlBase && $subjectId) {
                    $values['subject_id'] = $subjectId;
                }
                $quest = $this->getService('Quest')->insert($values);
                $importedQuesCount++;

                if ($subjectId && $quest) {
                    $this->getService('SubjectQuest')->insert(array('subject_id'=>$subjectId, 'quest_id'=>$quest->quest_id));
                }



                if (count($questions)) {
                    foreach ($questions as $insertQuestion) {
                        
                        $insertQuestion['quest_type'] = HM_Quest_QuestModel::TYPE_TEST;
                        $insertQuestion['mode_scoring'] = HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT;
                        $insertQuestion['show_free_variant'] = HM_Quest_Question_QuestionModel::SHOW_FREE_VARIANT_OFF;
                        
                        $variants = $insertQuestion['variants'];

                        if (!$intoKnowlBase && $subjectId) {
                            $insertQuestion['subject_id'] = $subjectId;
                        }

                        unset($insertQuestion['variants']);
                        
                        $question = $this->getService('QuestQuestion')->insert($insertQuestion);
                        $this->getService('QuestQuestionQuest')->insert(array(
                            'quest_id' => $quest->quest_id,
                            'question_id' => $question->question_id,
                        ));
                        
                        if (count($variants)) {
                            foreach($variants as $insertVariant) {
                                $insertVariant['question_id'] = $question->question_id;
                                $this->getService('QuestQuestionVariant')->insert($insertVariant);
                            }
                        }
                    }
                }
            }
        }

        return count($this->_inserts) == $importedQuesCount;
    }
}