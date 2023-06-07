<?php
class HM_Form_Quest extends HM_Form_Multi
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $form1 = new HM_Form_QuestStep1();
        $this->addSubForm($form1, 'questStep1');
        
        $form2 = new HM_Form_QuestStep2();
        $this->addSubForm($form2, 'questStep2');
        
        return parent::init();
    }
}
