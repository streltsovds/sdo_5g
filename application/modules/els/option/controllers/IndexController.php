<?php

class Option_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {
        $form = new HM_Form_Options();

        /** @var HM_Option_OptionService $optionService */
        $optionService = $this->getService('Option');

        /** @var HM_Controller_Request_Http $request */
        $request = $this->_request;
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $update = $form->getValues();
                $optionService->setOptions($update);
                $this->_flashMessenger->addMessage(_('Параметры системы успешно изменены'));
                $this->_redirector->gotoSimple('index', 'index', 'option');
            } else {
                $form->populate($request->getParams());
            }
        } else {
            $default = $optionService->getOptions(HM_Option_OptionModel::SCOPE_BASE);
            $form->populate($default);
        }

        $this->view->form = $form;
    }
}