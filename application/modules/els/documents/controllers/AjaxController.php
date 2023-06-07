<?php

class Documents_AjaxController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)

            ->addActionContext('get-document-variables', 'json')

            ->initContext('json');

    }

    public function getDocumentVariablesAction() {
        $type = $this->_getParam('type');

        $variables = HM_Document_Type_StudyOrderModel::getTemplateVariablesDescription($type);

        $this->view->assign(array(
            'variables' => $variables
        ));
    }

}