<?php
class HM_Form_Decorator_Materialize_Text extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        //return $content;

        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }
        $label = $element->getLabel();
        $id = $element->getId();
        $name = $element->getName();


        //$label     = $this->getLabel();
        //$separator = $this->getSeparator();
        //$placement = $this->getPlacement();
        //$tag       = $this->getTag();
        //$id        = $this->getName();
        //$class     = $this->getClass();
        $options   = $this->getOptions();
        $r = $element->getType();
        $w  = $element->getType();


        $value = $element->getValue() ? 'value="'.$element->getValue().'"' : '';
        $required = $element->isRequired() ? 'required' : '';
        $label_text = $element->isRequired() ? $element->getLabel().'<sup class="error--text">*</sup>' : $label;
        //$errors = $element->getDecorator('RedErrors');
        $helper_text = $element->getDescription() ? '<span class="helper-text">'.$element->getDescription() .'</span>': '';
        $return_markup =
            '<div class="input-field">
                <input name="'.$name.'" id="'.$id.'" type="text" '.$value.' '.$required.'>
                <label for="'.$id.'">'.$label_text.'</label>
                '.$helper_text.'
            </div>';
        return $return_markup;

    }
}