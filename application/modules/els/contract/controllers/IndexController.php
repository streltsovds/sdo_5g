<?php

class Contract_IndexController extends HM_Controller_Action
{
    
    public function indexAction()
    {
        $form = new HM_Form_Offer();

        /** @var HM_Option_OptionService $optionService */
        $optionService = $this->getService('Option');

        if ( $this->_request->isPost() ) {
            
            if ( $form->isValid($this->_request->getParams()) ) {
                $fieldNames = $optionService->processUserFields($form->getValue('userFields'));

                $update = [
//                	'regAllow' => $form->getValue('regAllow'),
                	'regDeny' => $form->getValue('regDeny'),
                	'loginStart' => $form->getValue('loginStart'),
                	'regRequireAgreement' => $form->getValue('regRequireAgreement'),
                	'regUseCaptcha' => $form->getValue('regUseCaptcha'),
                	'regValidateEmail' => $form->getValue('regValidateEmail'),
                	'regAutoBlock' => $form->getValue('regAutoBlock'),
                    'codeword' => $form->getValue('codeword'),
                	'contractOfferText'       => $form->getValue('contractOfferText'),
    	        	'contractPersonalDataText' => $form->getValue('contractPersonalDataText'),
                    'userFields' => json_encode($fieldNames)
                ];
                
                $optionService->setOptions($update);
                $this->_flashMessenger->addMessage(_('Обновление регистрационных требований успешно выполнено.'));
                $this->_redirector->gotoSimple('index', 'index', 'contract');
                
            } else {
                $form->populate($this->_request->getParams());
            }
            
        } else {
            
            $default = $optionService->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);

            if ($default['userFields']) {
                $userFields = [];
                $default['userFields'] = json_decode($default['userFields']);
                foreach ($default['userFields'] as $userField) {
                    $userFields[] = (array) $userField;
                }
                $default['userFields'] = $userFields;
            }

            $form->populate($default);
            
        }
        
        $this->view->form = $form;
    }

    public function viewAction()
    {
        $contract = ($this->_getParam('contract','') == 'offer')? 'contractOfferText' : 'contractPersonalDataText';
        $texts = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);
        $this->view->text = $texts[$contract]; 
    }
    
    public function printAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        
        $this->viewAction();
    }
    
}