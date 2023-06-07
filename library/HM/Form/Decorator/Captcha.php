<?php
class HM_Form_Decorator_Captcha extends Zend_Form_Decorator_Abstract
{
    /**
     * Render captcha
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        if (!method_exists($element, 'getCaptcha')) {
            return $content;
        }

        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $placement = $this->getPlacement();
        $separator = $this->getSeparator();

        $captcha = $element->getCaptcha();
        $markup  = $captcha->render($view, $element);
        switch ($placement) {
            case 'PREPEND':
                $content =
                    '<div id="refresh" valign="middle">'.
                        $markup .
                        '
                    </div>
                    ' .
                    $separator .
                    $content;
                break;
            case 'APPEND':
            default:
                $content = $content . $separator . $markup;
        }
        return $content;
    }
}