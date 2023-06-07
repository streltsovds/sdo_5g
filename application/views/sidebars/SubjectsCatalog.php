<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_SubjectsCatalog extends HM_View_Sidebar_Abstract
{
    const ITEMS_COUNT = 5;

    public function getIcon()
    {
        return 'courses-catalog'; // @todo
    }

    public function getTitle()
    {
        return _('Каталог курсов');
    }

    public function getContent()
    {
        $data = [];

        $currentUserId = $this->getService('User')->getCurrentUserId();

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        $this->session = new Zend_Session_Namespace('sidebar_random_subjects');
        $subjects = (is_array($this->session->subjects)) ? $this->session->subjects : array();

        $assignedSubjects = $this->getService('Student')->fetchAll(array('MID = ?' => $currentUserId));
        $assignedSubjects = count($assignedSubjects) ? $assignedSubjects->getList('CID') : array();

        if (!count($subjects)) {
            if ($currentUserId) {
                $relevantSubjects = $this->getService('ClassifierLink')->getRelevantSubjectsForUser($currentUserId);
                $relevantSubjectsForUserSubjects = $this->getService('ClassifierLink')
                    ->getRelevantSubjectsForUserSubjects($currentUserId, $relevantSubjects);

                $relevantSubjects = array_merge($relevantSubjects, $relevantSubjectsForUserSubjects);
                $relevantSubjects = array_diff($relevantSubjects, $assignedSubjects);
            } else {
                $relevantSubjects = array();
            }

            $freeSubjects = (count($relevantSubjects) < self::ITEMS_COUNT) ?
                $subjectService->getFreeSubjects(self::ITEMS_COUNT - count($relevantSubjects), $currentUserId, $relevantSubjects) :
                array();

            $subjects = array_merge($relevantSubjects, $freeSubjects);
            $subjects = array_intersect_key($subjects, array_rand($subjects, count($subjects) < self::ITEMS_COUNT ? count($subjects) : self::ITEMS_COUNT ));
        }


        $where = [];
        if(count($subjects))
            $where['subid in (?)'] = $subjects;

        if(count($assignedSubjects))
            $where['subid not in (?)'] = $assignedSubjects;

        $subjectModels = count($subjects) ? $this->getService('Subject')->fetchAll($where) : [];
        foreach ($subjectModels as $subjectModel) {
            $subjectModel->icon = $subjectModel->getDefaultIcon();
            $subjectModel->image = $subjectModel->getUserIcon();
            $subjectModel->viewUrl = $this->view->url($subjectModel->getViewUrl());
        }
        $subjectModelsData = $subjectModels ? $subjectModels->asArrayOfArrays() : [];

        $this->session->subjects = $subjects;
        $data['relatedSubjects'] = $subjectModelsData;
        $data['catalogUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'catalog', 'action' => 'index']);

        $jsonData = HM_Json::encodeErrorSkip($data);
        return $this->view->partial('subjects-catalog.tpl', ['data' => $jsonData]);
    }
}
