<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 9:45 AM
 */

class HM_Form_Decorator_Materialize_TreeCheckbox extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();

        if (null === $view) {
            return $content;
        }

        $attribs = $element->getAttribs();
        $checkboxTree = $attribs['params'];

        return  $this->renderTree($checkboxTree);

    }

    private function renderTree($checkboxTree)
    {
        $result = '';
        $element = $this->getElement();
        $name = $element->getName();
        $value = $element->getValue();
        $value = is_array($value) ? : array();

        foreach ($checkboxTree as $checkboxGroup)
        {
            $result .= '
                <div>
                    <div>' . $checkboxGroup['title'] . '</div>';

            $required = $element->isRequired() ? 'required' : '';

            foreach ($checkboxGroup['items'] as $checkboxItemValue => $checkboxItemName) {
                $inputId = $name . '-' . $checkboxItemValue;

                $checked = '';
                if (in_array($checkboxItemValue, $value)) {
                    $checked = ' checked="checked"';
                }

                $result .=
                    '<label>
                        <input
                            name="'.$name.'[]"
                            class="filled-in"
                            id="'.$inputId.'"
                            value="' . $checkboxItemValue . '"
                            type="checkbox" ' .
                            $checked . ' ' .
                            $required.'>
                            
                        <span>'.$checkboxItemName.'</span>
                    </label>';
            }

            if(!empty($checkboxGroup['children'])) {
                foreach ($checkboxGroup['children'] as $subGroup) {
                    $result .= $this->renderTree($subGroup);
                }
            }

            $result .= '</div>';
        }

        $result .= $element->getDescription() ? '<span class="helper-text">'.$element->getDescription() .'</span>': '';

        return $result;
    }
}