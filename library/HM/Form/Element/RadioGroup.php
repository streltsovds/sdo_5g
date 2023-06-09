<?php

/**
 * Class HM_Form_Element_RadioGroup
 *
 * RadioGroup с dependences должен быть выше в форме чем зависимые поля,
 * чтобы его метод isValid снимал required с зависимых полей неиспользуемых блоков
 * до того как проверяет isValid на этих полях (edited)
 */
class HM_Form_Element_RadioGroup extends Zend_Form_Element_Radio
{
    const NOT_CHECKED = 0;
    const CHECKED = 1;
    
    public $helper = 'formRadioGroup';
    
    public function isValid($data, $context = null)
    {
        // отставляем валидаторы только у подэлементов выбранного радиобаттона, остальные удаляем.
        foreach ($this->dependences as $key => $elements) {
            if ($data == $key) continue;
            foreach ($elements as $elementName) {
                $element = $this->form->getElement($elementName);
                if ($element) {
                    $element->clearValidators();
                    $element->setRequired(false);
                }
            }
        }
        return parent::isValid($data, $context);
    }
}
