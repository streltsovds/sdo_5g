<?php
class HM_Form_Meeting extends HM_Form_Multi
{

    public function init()
    {
        $project = $this->getService('Project')->getOne($this->getService('Project')->find($this->getParam('project_id', 0)));

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->addSubForm(new HM_Form_MeetingStep1(), 'step1');
        $form2 = new HM_Form_MeetingStep2();
        if ($form2->getElements()) {
            $this->addSubForm($form2, 'step2');
        }
        $form3 = new HM_Form_MeetingStep3();
        if (!$form2->getElements()) {
            $form3->setDefault('prevSubForm', 'step1');
        }
        $this->addSubForm($form3, 'step3');
        return parent::init();
    }


}
