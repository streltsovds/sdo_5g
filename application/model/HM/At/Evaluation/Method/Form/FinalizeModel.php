<?php
/**
 * Итоговое мероприятие; может быть только одно и только последним; 
 * разные формы для разных видов программ
 *
 */
class HM_At_Evaluation_Method_Form_FinalizeModel extends HM_At_Evaluation_Method_FormModel
{
    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE;
    }    
    
    static public function getMethodName()
    {
        return _('Заполнение итоговой оценочной формы');
    }
    
    // должно соответствовать HM_At_Evaluation_Method_CompetenceModel::getRelationTypes
    static public function getTitle()
    {
        return _('Менеджер заполняет итоговую форму');
    }
    
    public function getDefaults($user)
    {
        return array(
            'name' => _('Заполнение итоговой оценочной формы'),
        );
    }
    
    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}test.png";
    }    
    
    static public function getSubMethods($methodClass = false)
    {
        $return = array();
        foreach (array_keys(HM_Programm_ProgrammModel::getTypes()) as $type) {
            $return[HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE . '_' . $type] = _('Итоговая оценочная форма');
        }
        return $return;
    }
    
    public function getQuestId()
    {
        return 0;
    }
    
    public function isOtherRespondentsEventsVisible()
    {
        return false;
    }       
}