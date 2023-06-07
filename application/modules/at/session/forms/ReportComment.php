<?php

class HM_Form_ReportComment extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('resource');
        $this->setAction($this->getView()->url());

        $display = [];
        $this->addElement('hidden', $display[] = 'session_user_id', [
            'required' => false,
            'filters' => [
                'Int'
            ],
            'value' => $this->getParam('session_user_id')
        ]);

        $this->addElement($this->getDefaultTextAreaElementName(), $display[] = 'comment', []);

        $this->addElement($this->getDefaultSubmitElementName(), $display[] = 'submit', [
            'Label' => _('Сохранить')
        ]);

        $this->addDisplayGroup($display, 'commentGroup', ['legend' => _('Общий комментарий')]);

        parent::init(); // required!
    }

}
