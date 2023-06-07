<?php

class Lesson_PlayerController extends HM_Controller_Action
{
    public function init()
    {
        $this->initPrint();
        parent::init();
    }

    public function videoPlayerAction()
    {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));

        $lessonAssignId = 0;
        $camera =
        $screen = array();

        $userId = $this->_request->getParam('MID', 0);
        $lessonAssign = $this->getService('LessonAssign')->fetchAll(array(
            'SHEID = ?' => $lesson->SHEID,
            'MID   = ?' => $userId
        ));

        if (count($lessonAssign)) {
            $lessonAssignId = $lessonAssign->current()->getValue('SSID');
        }

        $proctoringFiles = $this->getService('ProctoringFile')->fetchAll(array(
            'SSID = ?' => $lessonAssignId
        ));

        if (count($proctoringFiles)) {
            foreach ($proctoringFiles as $proctoringFile) {
                $obj = new StdClass;
                $obj->id = $proctoringFile->getValue('proctoring_file_id');
                $obj->src = $proctoringFile->getValue('url');
                $obj->fileName = basename($obj->src);
                $obj->course = ''; //$this->_subject->name;
                $obj->stamp = $proctoringFile->stamp;

                switch ($proctoringFile->getValue('type')) {
                    case 'camera':
                        $camera[] = $obj;
                        break;
                    case 'screen':
                        $screen[] = $obj;
                        break;
                }
            }
        }

        $result = new StdClass;
        $this->view->camera = json_encode($camera);
        $this->view->screen = json_encode($screen);
        $this->view->backUrl = $this->view->url(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'proctored',
            'lesson_id' => $this->_request->getParam('lesson_id', 0),
            'subject_id' => $this->_request->getParam('subject_id', 0),
        ), null, true);

        /** @var HM_User_UserModel $user */
        $user = $this->getService('User')->fetchRow(array('MID = ?' => $userId));
        $this->view->userFullName = $user->getName();
    }
}
