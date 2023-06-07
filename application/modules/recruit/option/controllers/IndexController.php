<?php

class Option_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {
        $this->view->setHeader(_('Настройка методик оценки (в процессах подбора, адаптации, кадрового резерва)'));
        $form = new HM_Form_RecruitOptions();
        if ($readonly = $this->getService('Acl')->isCurrentAllowed('privileges:edit') ? null : true) {
            foreach ($form->getElements() as $element) {
                $element->readonly = $element->disabled = $readonly;
            }
        }

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $update = array(
                    'competenceUseIndicators' => $form->getValue('competenceUseIndicators'),
                    'competenceUseScaleValues' => $form->getValue('competenceUseScaleValues'),
                    'competenceUseIndicatorsDescriptions' => $form->getValue('competenceUseIndicatorsDescriptions'),
                    'competenceUseIndicatorsReversive' => $form->getValue('competenceUseIndicatorsReversive'),
                    'competenceUseIndicatorsScaleValues' => $form->getValue('competenceUseIndicatorsScaleValues'),
                    'competenceUseClusters' => $form->getValue('competenceUseClusters'),
                    'competenceScaleId' => $form->getValue('competenceScaleId'),
                    'competenceComment' => $form->getValue('competenceComment'),
                    'competenceReportComment' => $form->getValue('competenceReportComment'),
                    'kpiUseCriteria' => $form->getValue('kpiUseCriteria'),
                    'kpiScaleId' => $form->getValue('kpiScaleId'),
                    'kpiUseClusters' => $form->getValue('kpiUseClusters'),
                    'kpiComment' => $form->getValue('kpiComment'),
                    'kpiReportComment' => $form->getValue('kpiReportComment'),
                    'sessionComment' => $form->getValue('sessionComment'),
                );
                $this->getService('Option')->setOptions($update, HM_Option_OptionModel::MODIFIER_RECRUIT);
                $this->_flashMessenger->addMessage(_('Настройки методик оценки успешно изменены.'));
                $this->_redirector->gotoSimple('index', 'index', 'option');
            } else {
                $form->populate($this->_request->getParams());
            }
        } else {
            $default = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, HM_Option_OptionModel::MODIFIER_RECRUIT);
            $form->populate($default);
        }

        $this->view->form = $form;
    }


    public function publicationAction()
    {
        $form = new HM_Form_RecruitPublicationOptions();
        if ($readonly = $this->getService('Acl')->isCurrentAllowed('privileges:edit') ? null : true) {
            foreach ($form->getElements() as $element) {
                $element->readonly = $element->disabled = $readonly;
            }
        }

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $update = array(
                    'publicationCompanyName' => $form->getValue('publicationCompanyName'),
                    'publicationCompanyDescription' => $form->getValue('publicationCompanyDescription'),
                    'publicationCompanyConditions' => $form->getValue('publicationCompanyConditions'),
                );
                $this->getService('Option')->setOptions($update, HM_Option_OptionModel::MODIFIER_RECRUIT);
                $this->_flashMessenger->addMessage(_('Настройки публикации вакансий успешно изменены.'));
                $this->_redirector->gotoSimple('publication', 'index', 'option');
            } else {
                $form->populate($this->_request->getParams());
            }
        } else {
            $default = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_RECRUIT_PUBLICATIONS, HM_Option_OptionModel::MODIFIER_RECRUIT);
            $form->populate($default);
        }

        $this->view->form = $form;
    }
}