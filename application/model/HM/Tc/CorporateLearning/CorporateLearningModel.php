<?php
class HM_Tc_CorporateLearning_CorporateLearningModel extends HM_Model_Abstract
{
    const CORPORATE_TYPE_ORGANIZER  = 1;
    const CORPORATE_TYPE_PARICIPANT = 2;

    CONST MEETING_TYPE_CLUSTER  = 0;
    CONST MEETING_TYPE_BRANCH   = 1;
    CONST MEETING_TYPE_TOGETHER = 2;
    
    protected $_primaryName = 'corporate_learning_id';

    public function getServiceName()
    {
        return 'TcCorporateLearning';
    }
    
    public static function getMeetingsTypes(){
        $types = array(
            self::MEETING_TYPE_CLUSTER  => 'Кластер/филиал',
            self::MEETING_TYPE_BRANCH   => 'Отделение',
            self::MEETING_TYPE_TOGETHER => 'Совместно',
        );
        return $types;
    }


    public function getCities($implode = false)
    {
        $cities = $this->getService()->getCities($this->corporate_learning_id);
        if ($implode){
            return implode(', ', $cities->getList('classifier_id', 'name'));
        }
        return $cities->getList('classifier_id', 'name');
    }
    
    public function getFunctionalDirection($implode = false)
    {
        $cities = $this->getService()->getFunctionalDirection($this->corporate_learning_id);
        if ($implode){
            return implode(', ', $cities->getList('classifier_id', 'name'));
        }
        return $cities->getList('classifier_id', 'name');
    }

    public static function getCorporateTypes()
    {
        return array (
            self::CORPORATE_TYPE_ORGANIZER  => _('Принимающая сторона'),
            self::CORPORATE_TYPE_PARICIPANT => _('Участник'),
        );
    }

    public static function getCorporateType($type)
    {
        $types = self::getCorporateTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }

}