<?php
/**
 * Методика оценки "проф.тестирование"
 *
 */
class HM_At_Evaluation_Method_TestModel extends HM_At_Evaluation_EvaluationModel
{
    public function isMultiEventEvaluation()
    {
        return true;    
    }
    
    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_TEST;
    }
        
    static public function getMethodName()
    {
        return _('Профессиональное тестирование');
    }
    
    static public function getRelationTypes()
    {
        return array(
            self::RELATION_TYPE_SELF => _('Профессиональное тестирование'),
        );
    }
        
    // должно соответствовать HM_At_Evaluation_Method_CompetenceModel::getRelationTypes
    static public function getTitle()
    {
        return _('Пользователь проходит тестирование');
    }
    
    public function getDefaults($user)
    {
        return array(
            'name' => _('Профессиональное тестирование'),
        );
    }
    
    public function isValid($userId)
    {
        return true;
    }
    
    public function getTestDefaults()
    {
        return array(
            'cid' => -1,
            'datatype' => 1,
            'sort' => 0,
            'free' => 0,
            'rating' => 0,
            'status' => 1,
            'last' => 0,
            'cidowner' => 0,
            'lesson_id' => 0,
            'mode' => 0,
            'lim' => 0,
            'qty' => 1,
            'startlimit' => 0,
            'limitclean' => 0,
            'timelimit' => 0,
            'random' => 0,
            'questres' => 0,
            'showurl' => 0,
            'endres' => 1,
            'skip' => 0,
            'allow_view_log' => 1, // разрешить или нет просмотр лога по умолчанию?
            'comments' => '',
        );        
    }
    
    public function getLessonDefaults()
    {
        return array(
            'typeID' => HM_Event_EventModel::TYPE_TEST,
            'CID' => -1,
            'vedomost' => 1,
            'recommend' => 0,
            'timetype' => HM_Lesson_LessonModel::TIMETYPE_DATES,
            'cond_sheid' => '',
            'cond_mark' => '',
            'cond_progress' => '',
            'cond_avgbal' => '',
            'cond_sumbal' => '',
            'gid' => 0,
            'isfree' => HM_Lesson_LessonModel::MODE_PLAN,                
        );        
    }
    
    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}test.png";
    }    
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }    
    
    // дублируется в HM_At_Evaluation_Method_PsychoModel
    public function getMultiEventData()
    {
        $return = array();
        if (count($this->criteriaTest)) {
            foreach ($this->criteriaTest as $criterionTest) {
                $return[] = array(
                    'criterion_id' => $criterionTest->criterion_id,        
                    'criterion_type' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,        
                    'quest_id' => $criterionTest->quest_id,        
                    'name' => $criterionTest->name,        
                );
/*
                if (count($criterionTest->quest)) {
                    foreach ($criterionTest->quest as $quest) {
                        $return[] = array(
                            'criterion_id' => $criterionTest->criterion_id,        
                            'criterion_type' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,        
                            'quest_id' => $quest->quest_id,        
                            'name' => $quest->name,        
                        );
                    }
                }
*/  
            }
        }   
        return $return; 
    }

    public function getRespondentsCustom($position)
    {
        return array(); // custom'ная настройка респондентов неприменима и не влияет
    }

    static public function parseSubmethod($submethod, $part = 'method')
    {
        list($method, $questId) = explode('_', $submethod);
        return $$part;
    }    
   
}