<?php
class Infoblock_RandomSubjectsController extends HM_Controller_Action
{
	public function init()
	{
		parent::init();
		$this->_helper->ContextSwitch()->addActionContext('next', 'json')->initContext();
	}

	public function nextAction(){
		
		$this->view->result = true;
		$user_id = $this->getService('User')->getCurrentUserId();		
		$classifier_type = 1; //HM_View_Infoblock_RandomSubjects::CLASSIFIER_TYPE
		$count = 20; //HM_View_Infoblock_RandomSubjects::COUNT_ITEM
		
		$this->session = new Zend_Session_Namespace('infoblock_random_subjects');
		$subjects = (is_array($this->session->subjects)) ? $this->session->subjects : array();
		
		if (!count($subjects)) {
            if ($user_id) {
                $relevant_subjects = $this->getService('ClassifierLink')->getRelevantSubjectsForUser((int) $user_id, $classifier_type);
            } else {
                $relevant_subjects = array();
            }
            
            $free_subjects = (count($relevant_subjects) < $count) ? $this->getService('Subject')->getFreeSubjects($count-count($relevant_subjects), $user_id) : array();
            $subjects = array_merge($relevant_subjects, $free_subjects);
		}
		
		$subject_id = array_shift($subjects);
		if ($subject_id) {
            $subjects[] = $subject_id;
		}
		$subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subject_id));
		$this->session->subjects = $subjects;

		$subjectClassifiersCollection = $this->getService('ClassifierLink')
            ->fetchAllDependenceJoinInner('Classifier', $this->quoteInto(
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
		
		$this->view->id = $subject_id;
		$this->view->title = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $subject->name);
		$this->view->description = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $subject->description);
		$this->view->url = $this->view->url(array(
            'module' => 'user',
            'controller' => 'reg',
            'action' => 'subject',
            'subid' => $subject->subid,
        ));
		$this->view->teacherPhoto = $teacherPhoto;
		$this->view->teacherUrl = $teacherUrl;
		$this->view->classifiers = $subjectClassifiers;
		$this->view->begin = $subject->getBegin();
		$this->view->end = $subject->getEnd();
		$this->view->applyLabel = $subject->claimant_process_id == HM_Subject_SubjectModel::APPROVE_NONE ? _('Записаться') : _('Подать заявку');
		$this->view->end = $subject->getEnd();
        $this->view->userIcon = $subject->getUserIcon();
        $this->view->defaultIcon = $subject->getDefaultIcon();
	}

}