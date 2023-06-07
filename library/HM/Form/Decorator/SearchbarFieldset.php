<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/29/18
 * Time: 6:00 PM
 */

class HM_Form_Decorator_SearchbarFieldset extends Zend_Form_Decorator_Fieldset
{
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $legend  = $this->getLegend();
        $attribs = $this->getOptions();
        $name    = $element->getFullyQualifiedName();
        $id      = (string)$element->getId();

        if (!array_key_exists('id', $attribs) && '' !== $id) {
            $attribs['id'] = 'fieldset-' . $id;
        }

        if (null !== $legend) {
            if (null !== ($translator = $element->getTranslator())) {
                $legend = $translator->translate($legend);
            }

            $attribs['legend'] = $legend;
        }

        foreach (array_keys($attribs) as $attrib) {
            $testAttrib = strtolower($attrib);
            if (in_array($testAttrib, $this->stripAttribs)) {
                unset($attribs[$attrib]);
            }
        }

        return $view->searchbarFieldset($name, $content, $attribs);
    }
}