<?php

class HM_VueForm extends Zend_Form
{
    const ELEMENT_STATE_STANDART = 'standart';
    const ELEMENT_STATE_DARKER = 'darker';
    const ELEMENT_STATE_VARIANT = 'variant';
    const ELEMENT_STATE_DEFAULT_ACTION = 'default_action';
    const ELEMENT_STATE_DISABLED = 'disabled';
    const ELEMENT_STATE_DISABLED_COLORED = 'disabled_colored';

    protected $header;

    public function __construct($options = null)
    {
        $this->addPrefixPath('HM_Form_Element', 'HM/Form/Element/', 'element');
        parent::__construct($options);
    }

    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $this->_setIsRendered();
        $elements = $this->getElements();
        /** @var  $element Zend_Form_Element */
        foreach ($elements as &$element) {
            $element = $element->getJsonData();
        }
        $form['settings'] = [
            'header' => $this->getHeader(),
            'isValid' => $this->valid(),
            'legend' => $this->getLegend(),
            'name' => $this->getName(),
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'attribs' => $this->_attribs,
            'method' => $this->getMethod(),
            'action' => $this->getAction()
        ];
        $form['elements'] = array_values($elements);

        return '<hm-form-alternative>'.json_encode($form).'</hm-form-alternative>';
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

}