<?php

use HM_Document_Type_ClassAssignmentModel as Document_ClassAssignment;

class Documents_IndexController extends HM_Controller_Action
{

    public function indexAction()
    {
    }

    /**
     * Свидетельство о присвоении разряда
     */
    public function getClassAssignmentAction() {
        $ids = explode(',', $this->_getParam('postMassIds_grid', ''));

        $graduatedCollection = $this->getService('Graduated')->fetchAllDependence(
            array('User', 'Subject'),
            array('SID IN (?)' => $ids)
        );

        $documentTemplates = $this->getService('DocumentTemplate')->fetchAll(array(
            'type = ?' => HM_Document_DocumentTemplateModel::TYPE_CLASS_ASSIGNMENT,
            'item_type = ?' => HM_Document_DocumentTemplateModel::ITEM_TYPE_PROVIDER,
            'item_id = ?' => 0,
        ));

        if (!count($documentTemplates)) {
            //TODO: не найден шаблон
        }

        /** @var HM_Document_Type_StudyOrderModel $documentTemplate */
        $documentTemplate = $documentTemplates->current();

        $word = new HM_Word();

        $subjectIds = array();
        /** @type HM_Role_GraduatedModel $graduated */
        foreach($graduatedCollection as $graduated) {
            $subjectIds[] = $graduated->CID;
        }
        $subjectIds = array_unique($subjectIds);

        $providerSubjectCollection = $this->getService('TcProviderSubject')->fetchAllDependence(
            array('ScProvider'),
            array('subject_id IN (?)' => $subjectIds)
        );

        $providerBySubjectId = array();
        foreach($providerSubjectCollection as $providerSubject) {
            if (count($providerSubject->scProvider)) {
                $provider = $providerSubject->scProvider->current();
                $providerBySubjectId[$providerSubject->subject_id] = $provider;
            }
        }

        if (count($graduatedCollection))
        {

            /** @type HM_Role_GraduatedModel $graduated */
            foreach($graduatedCollection as $graduated) {
                $templateValues = array();

                $subjectId = $graduated->CID;

                if ($user = $graduated->getUser()) {
                    $templateValues[Document_ClassAssignment::USER_NAME] = $user->getName();
                }

                if (isset($providerBySubjectId[$subjectId])) {
                    $provider = $providerBySubjectId[$subjectId];
                    //Учебный центр
                    $templateValues[Document_ClassAssignment::PROVIDER_NAME] = $provider->name;
                    //Лицензия
                    $templateValues[Document_ClassAssignment::PROVIDER_LICENCE] = $provider->licence;
                    //Регистрационный №
                    $templateValues[Document_ClassAssignment::PROVIDER_REGISTRATION] = $provider->registration;
                }

                //Дата начала обучения
                $begin = new HM_Date($graduated->begin);
                $templateValues[Document_ClassAssignment::BEGIN_DATE] = $begin->toString('"dd" MMMM YYYYг.');
                //Дата окончания обучения
                $end = new HM_Date($graduated->end);
                $templateValues[Document_ClassAssignment::END_DATE] = $end->toString('"dd" MMMM YYYYг.');

                $subject = $graduated->getSubject();

                if ($subject) {
                    //Объем теоретического обучения, часов
                    $templateValues[Document_ClassAssignment::SUBJECT_LONGTIME_THEORY] = $subject->theory_longtime;
                    //Объем производственного обучения, часов
                    $templateValues[Document_ClassAssignment::SUBJECT_LONGTIME_JOB] = $subject->job_longtime;
                    //Наименование профессии
                    $templateValues[Document_ClassAssignment::SUBJECT_PROFESSION] = $subject->profession;
                    //Разряд по профессии
                    $templateValues[Document_ClassAssignment::SUBJECT_PROFESSION_RANK] = $subject->profession_rank;


                     $mark = $this->getService('SubjectMark')->fetchAll(
                         array(
                             'cid = ?' => $subjectId,
                             'mid = ?' => $user->MID,

                         )
                     )->current();

                    if ($mark) {
                        $templateValues[Document_ClassAssignment::FINAL_GRADE] = $mark->mark;
                    }


                }



                $documentTemplate->setTemplateValues($templateValues);

                $word->appendHtml($documentTemplate->getContent());
                $word->appendPageBreak();
            }

            $word->setMarginTop($documentTemplate->margin_top);
            $word->setMarginRight($documentTemplate->margin_right);
            $word->setMarginBottom($documentTemplate->margin_bottom);
            $word->setMarginLeft($documentTemplate->margin_left);

            $word->setPageOrientation(HM_Word::PAGE_ORIENTATION_ALBUM);


            $output = $word->generate();

            $fileName = 'certificate_'.md5(microtime());

            $filePath = APPLICATION_PATH . "/../data/upload/subject_documents/".$subjectId.'/';

            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $fullName = $filePath.$fileName.'.doc';

            file_put_contents($fullName, $output);

            $data = array();
            $data['name'] = 'Свидетельство о присвоении разряда '.date('Y-m-d H:i');
            $data['filename'] = $fileName.'.doc';
            $data['add_date'] = date('Y-m-d H:i:s');
            $data['type'] = HM_Tc_Document_DocumentModel::TYPE_BLANK;
            $data['subject_id'] = $subjectId;

            $this->getService('TcDocument')->insert($data);


            $word->send('certificate');
        }

    }

} 