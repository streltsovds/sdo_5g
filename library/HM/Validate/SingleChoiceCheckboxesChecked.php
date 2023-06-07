<?php
class HM_Validate_SingleChoiceCheckboxesChecked extends Zend_Validate_Abstract
{
    const CHECKED_MIN_OUT = 'checkedMinOut';
    const CHECKED_MAX_OUT = 'checkedMaxOut';

    public $_name = null;
    
    /**
     * @todo что отметить? со склонениями
     * @var array
     */
    protected $_messageTemplates = array(
        self::CHECKED_MIN_OUT => 'Необходимо отметить как минимум %min%',
        self::CHECKED_MAX_OUT => 'Невозможно отметить более %max%',
    );

    protected $_min;
    protected $_max;
    protected $_checkedCount = 0;
    protected $_uncheckedCount = 0;

    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max',
        'checkedCount' => '_checkedCount',
        'uncheckedCount' => '_uncheckedCount',
    );

    public function __construct($options)
    {
        foreach($options as $key => $value) {
            $this->{'_'.$key} = $value;
        }
    }

    /**
     * @param HM_Form_Element_MultiSet $singleChoice
     * @return bool
     * @throws Zend_Validate_Exception
     */
    public function isValid($singleChoice)
    {
        if (!($singleChoice instanceof HM_Form_Element_Vue_SingleChoice)) {
            throw new Zend_Validate_Exception('Value is not instanceof HM_Form_Element_SingleChoice');
        }

        $elements = $singleChoice->getElements();

        foreach ($elements as $element) {
            if ($element instanceof HM_Form_Element_Vue_Checkbox) {
                $value = (int)$element->getValue();
                if ($value) {
                    $this->_checkedCount++;
                } else {
                    $this->_uncheckedCount++;
                }
            }
        }

        if (isset($this->_min) && $this->_checkedCount < $this->_min) {
            $this->_error(self::CHECKED_MIN_OUT);
            return false;
        }

        if (isset($this->_max) && $this->_checkedCount > $this->_max) {
            $this->_error(self::CHECKED_MAX_OUT);
            return false;
        }

        return true;
    }
}