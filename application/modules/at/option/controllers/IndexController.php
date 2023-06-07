<?php

class Option_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {
        $form = new HM_Form_AtOptions();
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
                    //'competenceUseRandom' => $form->getValue('competenceUseRandom'),
                    'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN => $form->getValue('competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN),
                    'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS => $form->getValue('competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS),
                    'competenceScaleId' => $form->getValue('competenceScaleId'),
                    'competenceComment' => $form->getValue('competenceComment'),
                    'sessionComment' => $form->getValue('sessionComment'),
                    'competenceReportComment' => $form->getValue('competenceReportComment'),
                    'competenceDisableStop' => $form->getValue('competenceDisableStop'),
                    'kpiUseCriteria' => $form->getValue('kpiUseCriteria'),
                    'kpiScaleId' => $form->getValue('kpiScaleId'),
                    'kpiUseClusters' => $form->getValue('kpiUseClusters'),
                    'kpiComment' => $form->getValue('kpiComment'),
                    'kpiReportComment' => $form->getValue('kpiReportComment'),
                    'ratingComment' => $form->getValue('ratingComment'),
                    'ratingReportComment' => $form->getValue('ratingReportComment'),
                    'competenceEmployedBeforeDays' => $form->getValue('competenceEmployedBeforeDays'),
                );
                $this->getService('Option')->setOptions($update);
                $this->_flashMessenger->addMessage(_('Настройки методик оценки успешно изменены.'));
                $this->_redirector->gotoSimple('index', 'index', 'option');
            } else {
                $form->populate($this->_request->getParams());
            }
        } else {
            $default = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS);
            $form->populate($default);
        }

        $this->view->form = $form;
    }
}