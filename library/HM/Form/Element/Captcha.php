<?php
class HM_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    // Костыль над капчей, потому что у неё нет своего helper'а и создаётся formText
    public function getJsonType()
    {
        return 'formCaptcha';
    }

    const NOISE_LEVEL = 2;
    
    public function render(Zend_View_Interface $view = null)
    {
        $captcha    = $this->getCaptcha();
        $captcha->setName($this->getFullyQualifiedName());

        $this
           ->addPrefixPath('HM_Form_Decorator', 'HM/Form/Decorator', 'decorator')
           //->clearDecorators()
           ->removeDecorator('ViewHelper');

        $decorators = $this->getDecorators();

        $decorator  = $captcha->getDecorator();
        if (!empty($decorator)) {
            array_unshift($decorators, $decorator);
        }

        $this
        ->addPrefixPath('HM_Form_Decorator', 'HM/Form/Decorator', 'decorator');

        $decorator = array('Captcha', array('captcha' => $captcha, 'separator' => '', 'placement' => 'PREPEND'));
        array_unshift($decorators, $decorator);
        $this->setDecorators($decorators);

        $this->setValue($this->getCaptcha()->generate());

        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
    }
}