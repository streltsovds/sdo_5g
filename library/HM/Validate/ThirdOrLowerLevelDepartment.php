<?php
class HM_Validate_ThirdOrLowerLevelDepartment extends Zend_Validate_Abstract
{
    const NOT_THIRD = 'notThirdOrLowerLevelDepartment';

    public $_name = null;
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_THIRD => "Можно выбрать только подразделения 3-го и ниже уровня и только из ГСП."
    );
    
    protected $_messageVariables = array();

    public function isValid($value)
    {
        $this->_setValue($value);

        $department = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($value)->current();

        if (!$department->readyForImpersonalAssigns()) {
            $this->_error(self::NOT_THIRD);
            return false;
        }

        return true;
    }
    

}