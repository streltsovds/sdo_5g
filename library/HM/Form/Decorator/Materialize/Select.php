<?php
class HM_Form_Decorator_Materialize_Select extends Zend_Form_Decorator_Abstract
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
        $elementAttribs = $element->getAttribs();

        $disabled = '';
        if (true === $elementAttribs['disabled']) {
            $disabled = ' disabled="disabled"';
        }


        //$label     = $this->getLabel();
        //$separator = $this->getSeparator();
        //$placement = $this->getPlacement();
        //$tag       = $this->getTag();
        //$id        = $this->getName();
        //$class     = $this->getClass();
        $options   = $element->options;
        $markup = '';

        foreach ($options as $optValue => $optLabel) {
            $selected = $optValue === $element->getValue() ? 'selected' : '';
            $elementStatement = $selected ? : $disabled;

            $markup .=
                "<option
                    label=\"$optLabel\"
                    title=\"$optLabel\"
                    value=\"$optValue\"
                    $elementStatement
                >$optLabel
                </option>";
        }


        $value = $element->getValue() ? 'value="'.$element->getValue().'"' : '';
        $required = $element->isRequired() ? 'required' : '';
        $label_text = $element->isRequired() ? $element->getLabel().'<sup class="error--text">*</sup>' : $label;
        //$errors = $element->getDecorator('RedErrors');
        $helper_text = $element->getDescription() ? '<span class="helper-text">'.$element->getDescription() .'</span>': '';
        $return_markup =
            '<div class="input-field">
                <select
                    name="' . $name .
                    '" id="' . $id . '" ' .
                    $required .
                '>' .
                $markup .
                '</select>
                <label for="'.$id.'">'.$label_text.'</label>
                '.$helper_text.'
            </div>';

        $js = <<<JS
M.FormSelect.init(document.getElementById('$id'));
JS;

        $view->inlineScript()->appendScript($js);
        $view->headLink()->prependStylesheet('/css/form-styles.css');
        return $return_markup;

    }
}