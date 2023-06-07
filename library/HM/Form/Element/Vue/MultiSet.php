<?php
class HM_Form_Element_Vue_MultiSet extends HM_Form_Element_Vue_Element
{
    const ITEMS_NEW = 'new';

    public $helper = 'vueMultiSet';

    /** @var Zend_Form_Element[] */
    private $_dependencesByName;
    /** @var Zend_Form_Element[] */
    private $_setElements;

    public function init()
    {
        $this->setIsArray(true)
            ->setFilters(array(array('multiSet', array($this->getName(), $this->getAttrib('dependences')))));

        parent::init();
    }

    /**
     * Возвращает зависимость по имени (болванку для создания элементов)
     * @param $name
     * @return bool|Zend_Form_Element
     */
    public function getDependence($name) {
        if (!isset($this->_dependencesByName)) {
            $this->_dependencesByName = array();
            /** @var Zend_Form_Element $dependence */
            foreach ($this->dependences as &$dependence) {
                $depName = $dependence->getName();
                $this->_dependencesByName[$depName] = &$dependence;
            }
        }

        if (isset($this->_dependencesByName[$name])) {
            return $this->_dependencesByName[$name];
        }

        return false;
    }

    /**
     * Воссоздаёт элементы на основе массива данных и зависимостей,
     * и возвращает их массив
     * @return array|Zend_Form_Element[]
     */
    public function getElements() {
        if (!isset($this->_setElements)) {
            $this->_setElements = array();

            $data = $this->getValue();
            foreach($data as  $multisetRow) {
                foreach($multisetRow as $rowItemName => $rowItem) {
                    $dependence = $this->getDependence($rowItemName);

                    if (!$dependence) {
                        continue;
                    }

                    if (!is_array($rowItem)) {
                        $rowItem = array($rowItem);
                    }

                    foreach ($rowItem as $item) {
                        $element = clone $dependence;
                        $element->setValue($item);

                        array_push($this->_setElements, $element);
                    }
                }
            }
        }

        return $this->_setElements;
    }

    public function isValid($value, $context = NULL)
    {
        $result = true;

        $elements = $this->getElements();
        $this->_errors   = array();
        $this->_messages = array();

        //валидация всех элементов
        foreach ($elements as $element) {
            if (!$element->isValid($element->getValue())) {
                $errors   = $element->getErrors();
                $messages = $element->getMessages();

                $this->_errors   = array_merge($this->_errors,   $errors);
                $this->_messages = array_merge($this->_messages, $messages);

                $result = false;
            }
        }

        //общая валидация мультисета
        /** @var Zend_Validate_Abstract $validator */
        foreach ($this->getValidators() as $validator) {
            if (!$validator->isValid($this)) {
                $messages = $validator->getMessages();
                $errors   = array_keys($messages);

                $this->_errors   = array_merge($this->_errors,   $errors);
                $this->_messages = array_merge($this->_messages, $messages);

                $result = false;
            }
        }

//        if (!$result) {
//            $this->addDecorator('Errors');
//        }

        return $result;
    }

    protected function _getErrorMessages()
    {
        return $this->getErrorMessages();
    }

    public function getValue()
    {
        $this->_filterValue($this->_value, $this->_value);
        return $this->_value;
    }

    public function prependElement($element)
    {
        if ($element) {
            array_unshift($this->dependences, $element);
        }
    }

    public function appendElement($element)
    {
        if ($element) {
            array_push($this->dependences, $element);
        }
    }
}
