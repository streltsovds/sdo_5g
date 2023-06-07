<?php
class HM_Form_Question extends HM_Form_Multi
{
    public function init()
    {
        if ($questId = $this->getParam('quest_id', 0)) {
            $quest = Zend_Registry::get('serviceContainer')->getService('Quest')->find($questId)->current();
        }

        $this->setMethod(Zend_Form::METHOD_POST);

        $form1 = new HM_Form_QuestionStep1();
        $this->addSubForm($form1, 'questionStep1');

        if (!$quest || empty($quest->scale_id)) {
            $session = $this->getSubForm('questionStep1')->getSession();
            if($session['questionStep1']['variants_use_wysiwyg'] == HM_Quest_Question_QuestionModel::VARIANTS_USE_WYSIWYG_ON) {
                $form2 = new HM_Form_QuestionStep2Wysiwyg();
            } else {
                $form2 = new HM_Form_QuestionStep2();
            }
            $this->addSubForm($form2, 'questionStep2');
        }

        parent::init();
    }

}
