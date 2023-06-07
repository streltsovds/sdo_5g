<?php
/**
 * Методика оценки "анкетирование"
 *
 */
class HM_At_Evaluation_Method_FormModel extends HM_At_Evaluation_EvaluationModel
{
    public function isMultiEventEvaluation()
    {
        // это не совсем так; TYPE_FORM - не MultiEventEvaluation
        // просто удобно использовать этот интерфейс для установки quest_id  
        return true;    
    }
    
    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_FORM;
    }    
    
    static public function getMethodName()
    {
        return _('Заполнение произвольной оценочной формы');
    }
    
    static public function getRelationTypes()
    {
        return array(
            self::RELATION_TYPE_RECRUITER => _('Заполнение формы'),
        );
    }
        
    // DEPRECATED??
    // должно соответствовать HM_At_Evaluation_Method_CompetenceModel::getRelationTypes
    static public function getTitle()
    {
        return _('Менеджер заполняет произвольную форму');
    }
    
    public function getDefaults($user)
    {
        return array(
            'name' => _('Заполнение произвольной оценочной формы'),
        );
    }

    public function isValid()
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
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
//            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }

    // метод нужен для универсального построения programm
    // в данном случае это опубликованные анкеты (type=form)
    static public function getSubMethods($methodClass = false)
    {
        $return = array();
        $collection = Zend_Registry::get('serviceContainer')->getService('Quest')->fetchAll(array(
            'type = ?' => HM_Quest_QuestModel::TYPE_FORM,
            'status = ?' => HM_Quest_QuestModel::STATUS_RESTRICTED,
            'quest_id NOT IN(?)' => HM_Quest_QuestModel::getBuiltInTypeIds(),
        ), 'name');
        if (count($collection)) {
            foreach ($collection as $quest) {
                $key = sprintf('%s_%s', HM_At_Evaluation_EvaluationModel::TYPE_FORM, $quest->quest_id);
                $return[$key] = $quest->name;
            }
        } 
        return $return;
    }  

    // всегда возвращает массив из 1 элемента
    public function getMultiEventData()
    {
        $return = array();
        if (count($this->quest)) {
            $quest = $this->quest->current();
            $return[] = array(
                'quest_id' => $quest->quest_id,        
                'name' => $quest->name,        
                'description' => $quest->description, // отсюда description попадает в окно бизнес-процесса 
            );
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
    
    // достаточно, чтобы один из рекрутёров заполнил свою анкету
    // и процесс двинется дальше
    public function isFullCompletionRequired()
    {
        return false;
    }

    // relation_type зависит только от типа программы
    public function getDefaultRelationType($programmType = false)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                return HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF;
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                return HM_At_Evaluation_EvaluationModel::RELATION_TYPE_RECRUITER;
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return HM_At_Evaluation_EvaluationModel::RELATION_TYPE_HR;
        }
        return false;
    }
}