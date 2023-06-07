<?php

class Documents_EditController extends HM_Controller_Action
{

    public function activitiesAssessmentAction()
    {
        $documentTemplateService = $this->getService('DocumentTemplate');

        $collection = $documentTemplateService->fetchAll(array(
            'type = ?' => HM_Document_DocumentTemplateModel::TYPE_ACTIVITIES_ASSESSMENT
        ));

        $form = new HM_Form_ActivitiesAssessmentDocument();

        if (count($collection)) {
            $document = $collection->current();
            $form->populate($document->getData());
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();

                unset($values['variables']);

                if (isset($document)) {
                    $documentTemplateService->update($values);
                } else {
                    unset($values['document_template_id']);
                    $documentTemplateService->insert($values);
                }

                $this->_flashMessenger->addMessage(_('Шаблон сохранен'));
                $this->_redirector->setGotoSimple('activities-assessment');
                $this->_redirector->redirectAndExit();
            }
        }

        $this->view->form = $form;
    }

} 