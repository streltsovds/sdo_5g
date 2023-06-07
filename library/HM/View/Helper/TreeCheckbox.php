<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/29/18
 * Time: 4:47 PM
 */

class HM_View_Helper_TreeCheckbox extends Zend_View_Helper_HtmlElement
{
    public function treeCheckbox($name, $value = null, array $attribs = array())
    {
        $checkboxTree = $attribs['params'];

        return $this->renderTree($name, $value, $checkboxTree);
    }

    private function renderTree($name, $value, $checkboxTree)
    {
        $result = '';
        $value = is_array($value) ? : array();

        foreach ($checkboxTree as $checkboxGroup)
        {
            $result .= '
                <div>
                    <div>' . $checkboxGroup['title'] . '</div>';

            foreach ($checkboxGroup['items'] as $checkboxItemValue => $checkboxItemName) {
                $inputId = $name . '-' . $checkboxItemValue;

                $checked = '';
                if (in_array($checkboxItemValue, $value)) {
                    $checked = ' checked="checked"';
                }

                $result .= '
                    <div>
                        <input
                            type="checkbox"
                            name="' . $name . '[]"
                            value="' . $checkboxItemValue . '"
                            id="' . $inputId . '" ' .
                    $checked . '>
                            
                        <lablel for="' . $inputId . '">' . $checkboxItemName . '</label>
                    </div>';
            }

            if(!empty($checkboxGroup['children'])) {
                foreach ($checkboxGroup['children'] as $subGroup) {
                    $result .= $this->treeCheckbox($name, $value, $subGroup);
                }
            }

            $result .= '</div>';
        }

        return $result;
    }
}