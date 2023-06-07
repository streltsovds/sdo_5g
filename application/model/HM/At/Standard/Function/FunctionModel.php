<?php
class HM_At_Standard_Function_FunctionModel extends HM_Model_Abstract
{
    const TYPE_EDUCATION = 1;
    const TYPE_WORKING = 2;
    const TYPE_SPECIAL = 3;
    const TYPE_SGC_EDUCATION = 4;
    const TYPE_SGC_WORKING = 5;

    static public function getTypes()
    {
        return array(
            self::TYPE_EDUCATION => _('Требования к образованию и обучению'),
            self::TYPE_WORKING => _('Требования к опыту работы'),
            self::TYPE_SPECIAL => _('Особые требования'),
            self::TYPE_SGC_EDUCATION => _('Требования компании к образовательному уровню'),
            self::TYPE_SGC_WORKING => _('Требования компании к опыту работы'),
        );
    }

    public function getName()
    {
        return $this->name;    
    }
    
    public function getIcon()
    {
        return Zend_Registry::get('config')->url->base . 'images/session-icons/function.png';
    }

    public function getShortName()
    {
        return $this->name;
    }


    
}
