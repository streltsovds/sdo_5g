<?php
/**
 * Методика оценки "псих.тестирование"
 *
 */
class HM_At_Evaluation_Method_PsychoModel extends HM_At_Evaluation_EvaluationModel
{
    public function isMultiEventEvaluation()
    {
        return true;    
    }
    
    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO;
    }
        
    static public function getMethodName()
    {
        return _('Психологическое тестирование');
    }
    
    static public function getRelationTypes()
    {
        return array(
            self::RELATION_TYPE_SELF => _('Психологическое тестирование'),
        );
    }
        
    // должно соответствовать HM_At_Evaluation_Method_CompetenceModel::getRelationTypes
    static public function getTitle()
    {
        return _('Участник проходит тестирование');
    }
    
    public function getDefaults($user)
    {
        return array(
            'name' => _('Психологическое тестирование'),
        );
    }
    
    public function isValid($userId)
    {
        return true;
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
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                return true;
            break;
        }
        return false;
    }
    
    // дублируется в HM_At_Evaluation_Method_TestModel
    public function getMultiEventData()
    {
        $return = array();
        if (count($this->criteriaPersonal)) {
            // психоопросы могут быть переопределены на уровне программы подбора вакансии 
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('AtEvaluationCriterion')->fetchAllDependence('Quest', array(
                'evaluation_type_id = ?' => $this->evaluation_type_id,     
                'criterion_id IN (?)' => $this->criteriaPersonal->getList('criterion_id'),//array_keys($this->criteriaPersonal),     
                'quest_id != ?' => 0     
            )))) {
                foreach ($collection as $evaluationCriterion) {
                    if (count($evaluationCriterion->quest)) {
                        $this->criteriaPersonal[$evaluationCriterion->criterion_id]->quest = $evaluationCriterion->quest;
                    }
                }
            }     
       
            foreach ($this->criteriaPersonal as $criterionPersonal) {
//                if (count($criterionPersonal->quest)) {
//                    foreach ($criterionPersonal->quest as $quest) {
                        $return[] = array(
                            'criterion_id' => $criterionPersonal->criterion_id,        
                            'criterion_type' => HM_At_Criterion_CriterionModel::TYPE_PERSONAL,        
                            'quest_id' => $criterionPersonal->quest_id, //$quest->quest_id,        
                            'name' => $criterionPersonal->name,        
                        );
//                    }
//                }
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