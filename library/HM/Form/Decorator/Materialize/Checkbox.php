<?php
class HM_Form_Decorator_Materialize_Checkbox extends Zend_Form_Decorator_Abstract
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
        $val = $element->getValue();
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


        $value = $element->getValue() ? : '1';
        $checked = $val === '1' ? 'checked="checked"' : '';
        $required = $element->isRequired() ? 'required' : '';
        $label_text = $element->isRequired() ? $element->getLabel().'<sup class="error--text">*</sup>' : $label;
        //$errors = $element->getDecorator('RedErrors');
        $helper_text = $element->getDescription() ? '<span class="helper-text">'.$element->getDescription() .'</span>': '';
        $return_markup =
            '<label style="display: block; margin: 12px 0;">
                <input name="'.$name.'" class="filled-in" id="'.$id.'" type="checkbox" '.$checked.' '.$required.' value="'.$value.'">
                <span>'.$label_text.'</span>
                '.$helper_text.'
            </label>';
        return $return_markup;

    }
}