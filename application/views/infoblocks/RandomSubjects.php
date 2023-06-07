<?php

/*
выбираем предметы, у которых классифиер_линкс такие же как у юзера - линейный вариант
выбираем классификаторы, к которым привязан юзер и всех их потомков. выбираем предметы, привязанные к этим классификаторам - рекурсивный вариант
TODO усложненный вариант с диффиринцированием курсов по принципу вкусности
*/
class HM_View_Infoblock_RandomSubjects extends HM_View_Infoblock_Abstract
{
	protected $id = 'randomSubjects';
	protected $class = 'randomSubjects';
	
	const CLASSIFIER_TYPE = 1; // Классификатор видов деятельности и тем обучения
	const COUNT_ITEM = 20;

	public function randomSubjects($param = null)
	{
		// линейный вариант
		$serviceContainer = Zend_Registry::get('serviceContainer');
		$currentUserId = $serviceContainer->getService('User')->getCurrentUserId();

		$this->session = new Zend_Session_Namespace('infoblock_random_subjects');
		$subjects = (is_array($this->session->subjects)) ? $this->session->subjects : array();
		
		if (!count($subjects)) {
            if ($currentUserId) {
                $relevant_subjects = $serviceContainer->getService('ClassifierLink')->getRelevantSubjectsForUser((int) $currentUserId, self::CLASSIFIER_TYPE);
            } else {
                $relevant_subjects = array();
            }

            $free_subjects = (count($relevant_subjects) < self::COUNT_ITEM) ? $serviceContainer->getService('Subject')->getFreeSubjects(self::COUNT_ITEM-count($relevant_subjects), $currentUserId) : array();
            $subjects = array_merge($relevant_subjects, $free_subjects);
		}

		// workaround
        if (!count($subjects)) {
            $subjects = $serviceContainer->getService('Subject')->fetchAll()->getList('subid');
        }

		do {
            $subject_id = array_shift($subjects);
            if ($subject_id) {
                $subjects[] = $subject_id;
            }
            $subject = $serviceContainer->getService('Subject')->getOne($serviceContainer->getService('Subject')->find($subject_id));
        } while (!$subject || !count($subjects));

		$classifierLinkService = $this->getService('ClassifierLink');
        $subjectClassifiersCollection = $classifierLinkService
            ->fetchAllDependenceJoinInner('Classifier', $classifierLinkService->quoteInto(
                array(
                    'self.item_id = ?',
                    ' AND self.type = ?'
                ),
                array(
                    $subject->subid,
                    HM_Classifier_Link_LinkModel::TYPE_SUBJECT
                )
            ));

        $subjectClassifiers = [];
        foreach ($subjectClassifiersCollection as $subjectClassifierModel) {
            $classifierId = $subjectClassifierModel->classifier_id;
            $classifierName = $this->getOne($subjectClassifierModel->classifiers)->name;

            $subjectClassifiers[$classifierId] = $classifierName;
        }

        $teacher = $this->getService('User')->fetchAllDependenceJoinInner(
            'Teacher',
            "Teacher.CID = {$subject->subid}"
        )->current();

        if(!empty($teacher)) {
            $teacherPhoto = !empty($teacher->getPhoto()) ? $teacher->getPhoto() : ''; //$teacher->getDefaultPhoto()
            $teacherUrl = $this->view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $teacher->MID]);
        } else {
            $teacherPhoto = '';
            $teacherUrl = '';
        }

		$this->session->subjects = $subjects;
		$this->view->subject = $subject;
		$this->view->classifier_type = self::CLASSIFIER_TYPE;

        $this->view->teacherPhoto = $teacherPhoto;
        $this->view->teacherUrl = $teacherUrl;
        $this->view->classifiers = $subjectClassifiers;
        $this->view->begin = $subject->getBegin();
        $this->view->end = $subject->getEnd();
        $this->view->applyLabel = $subject->claimant_process_id == HM_Subject_SubjectModel::APPROVE_NONE ? _('Записаться') : _('Подать заявку');
        $this->view->userIcon = $subject->getUserIcon();
        $this->view->defaultIcon = $subject->getDefaultIcon();

		$content = $this->view->render('randomSubjects.tpl');
		
		return $this->render($content);
	}
}