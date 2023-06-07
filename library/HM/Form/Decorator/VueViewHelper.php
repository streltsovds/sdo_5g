<?php
class HM_Form_Decorator_VueViewHelper extends Zend_Form_Decorator_ViewHelper
{
    /**
     * Render an element using a view helper
     *
     * Determine view helper from 'viewHelper' option, or, if none set, from
     * the element type. Then call as
     * helper($element->getName(), $element->getValue(), $element->getAttribs())
     *
     * @param  string $content
     * @return string
     * @throws Zend_Form_Decorator_Exception if element or view are not registered
     */
    public function render($content)
    {
        $element = $this->getElement();

        $view = $element->getView();
        if (null === $view) {
            require_once 'Zend/Form/Decorator/Exception.php';
            throw new Zend_Form_Decorator_Exception('ViewHelper decorator cannot render without a registered view object');
        }

        if (method_exists($element, 'getMultiOptions')) {
            $element->getMultiOptions();
        }

        $helper        = $this->getHelper();
        $separator     = $this->getSeparator();
        $value         = $this->getValue($element);
        $attribs       = $this->getElementAttribs();
        $name          = $element->getFullyQualifiedName();
        $id            = $element->getId();
        $attribs['id'] = $id;
        $attribs['required'] =  $element->isRequired();
        $attribs['label'] =  $this->clear($element->getLabel());
        $attribs['description'] =  $this->clear($element->getDescription());
        $attribs['formId'] =  $this->getOption('formName');

        if (method_exists($element, 'isDisabled')) {
            $attribs['disabled'] =  $element->isDisabled();
        }

        $errors =  $this->clear($element->getMessages());

        $helperObject  = $view->getHelper($helper);
        if (method_exists($helperObject, 'setTranslator')) {
            $helperObject->setTranslator($element->getTranslator());
        }

        $elementContent = $view->$helper($name, $value, $attribs, $errors, $element->options);
        switch ($this->getPlacement()) {
            case self::APPEND:
                return $content . $separator . $elementContent;
            case self::PREPEND:
                return $elementContent . $separator . $content;
            default:
                return $elementContent;
        }
    }

    /**
     * Замена кавычек
     *
     * @param string $str
     * @return string
     */
    public function clear($str) {
        return str_replace("'", " ", $str);
    }
}