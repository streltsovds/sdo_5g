<?php
class HM_Classifier_Type_TypeModel extends HM_Model_Abstract
{
    const TYPES_SEPARATOR = ' ';

    // используется в обучении
    const BUILTIN_TYPE_STUDY_DIRECTIONS   = 6;
    // используется в виджете Учёт рабочего времени
    const BUILTIN_TYPE_ACTION_TYPES       = 7;

    // DEPRECATED! (либо используется в очень боковых модулях)
    const BUILTIN_TYPE_CITIES             = -1;
    const BUILTIN_TYPE_UNIVERSITIES       = 2;
    const BUILTIN_TYPE_SPECIALITIES       = 3;
    const BUILTIN_TYPE_HH_SPECIALIZATIONS = 4;
    const BUILTIN_TYPE_FUNC_DIRECTION     = 5;

    static public function getBuiltInTypes()
    {
        return array(
            self::BUILTIN_TYPE_CITIES, 
            //self::BUILTIN_TYPE_UNIVERSITIES,
            //self::BUILTIN_TYPE_SPECIALITIES,
            //self::BUILTIN_TYPE_HH_SPECIALIZATIONS,
            self::BUILTIN_TYPE_STUDY_DIRECTIONS,
        );
    }    
    
    public function getTypes()
    {
        if(trim($this->link_types) == '') return array();
        return explode(self::TYPES_SEPARATOR, $this->link_types);
    }

    public function setTypes($types)
    {
        $this->link_types = (string) join(self::TYPES_SEPARATOR, $types);
    }


    public function getIcon()
    {

        $image = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->fetchAll(array('type = ?' => HM_Classifier_Image_ImageModel::TYPE_CATEGORY, 'item_id = ?' => $this->type_id));
        $image = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getOne($image);

        $path = Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getImageSrc($image->classifier_image_id);

        if($image){
            return Zend_Registry::get('view')->serverUrl($path);
        }
        return;
    }




}