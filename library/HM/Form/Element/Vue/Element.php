<?php

class HM_Form_Element_Vue_Element extends Zend_Form_Element
{
    /**
     * Element disabled
     * @var string
     */
    protected $_disabled;

    /**
     * Constructor
     *
     * @param  mixed $spec
     * @param  mixed $options
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        $this->addPrefixPath('HM_Form_Decorator', 'HM/Form/Decorator', 'decorator');
        $this->addPrefixPath('HM_Validate', 'HM/Validate', 'validate');
        $this->addPrefixPath('HM_Filter', 'HM/Filter', 'filter');

        parent::__construct($spec, $options);
    }

    /**
     * Load default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('VueViewHelper');
        }
    }

    public function init()
    {
        // Добавляем дефолтные переводы ошибок валидации формы
        if (!$this->hasTranslator()) {
            $translator = new Zend_Translate('array', APPLICATION_PATH . '/system/errors.php');
            $this->setTranslator($translator);
        }
    }

    /**
     * Set element disabled
     *
     * @param  boolean $disabled
     * @return Zend_Form_Element
     */
    public function setDisabled($disabled)
    {
        $this->_disabled = (bool) $disabled;
        return $this;
    }

    /**
     * Is the element disabled?
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->_disabled;
    }
}