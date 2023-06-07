<?php

class Lesson_IndexController extends HM_Controller_Action  {

    public function startAction() {

        $lessonId = (int) $this->_getParam('lesson_id');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));

        $where = $this->getService('LessonAssign')->quoteInto(
            array('SHEID = ?', ' AND MID = ?'),
            array($lessonId, $this->getService('User')->getCurrentUserId())
        );
        $assign = $this->getService('LessonAssign')->fetchAll($where)->current();

        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        $this->view->title = $lesson->title;
        $types = HM_Event_EventModel::getTypes();
        $this->view->info = array('Учебный курс'=>$subject->name, 'Занятие'=>$lesson->title, 'Тип занятия'=>$types[$lesson->typeID]);

        $this->view->messages = HM_Proctoring_ProctoringService::getMessages();
        $this->view->stopUrl = $_SERVER['HTTP_REFERER'];
        $this->view->continueUrl = "/subject/lesson/index/subject_id/{$subjectId}/lesson_id/{$lessonId}/final_link/1";
        $this->view->passed_proctoring = $assign->passed_proctoring;
        $this->view->lessonId = $lessonId;
        $this->view->SSID = $assign->SSID;
    }

    public function checkAction()
    {
        $SSID = (int) $this->_getParam('SSID');
        $where = $this->getService('LessonAssign')->quoteInto(array('SSID = ?'),array($SSID));
        $assign = $this->getService('LessonAssign')->fetchAll($where)->current();
        $lesson = $this->getService('Lesson')->fetchAll($this->getService('LessonAssign')->quoteInto(
            array('SHEID = ?'),
            array($assign->SHEID)
        ))->current();
        /** @var HM_Acl $acl */
        $acl = Zend_Registry::get('serviceContainer')->getService('Acl');
        /** @var HM_User_UserService $userService */
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $currentUserRole = $userService->getCurrentUserRole(true);
        $currentUserIsEndUser = $acl->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $result = (int) ($assign->passed_proctoring || !$lesson->has_proctoring || !$currentUserIsEndUser);

        die("{$result}");
    }

    public function _apiFindLessonAssign($elsEventId, $elsUserId) {
        if (!$elsEventId) {
            die(json_encode(array('error' => 'els_event_id not set')));
        }
        if (!$elsUserId) {
            die(json_encode(array('error' => 'els_user_id not set')));
        }

        $lessonAssignService = $this->getService('LessonAssign');

        $where = $lessonAssignService->quoteInto(
            array(
                'SHEID = ?',
                ' AND MID = ?'
            ),
            array(
                $elsEventId,
                $elsUserId,
            ));
        $assign = $lessonAssignService->fetchAll($where)->current();

        if(!$assign || !$assign->getData()) {
            die(json_encode(array('error' => 'lesson assign not found')));
        }

        return $assign;
    }

    public function _apiGetState($elsEventId, $elsUserId) {
        $assign = $this->_apiFindLessonAssign($elsEventId, $elsUserId);

        return array(
            'passed_proctoring' => $assign->passed_proctoring,
            'video_proctoring' => $assign->video_proctoring,
        );
    }

    /**
     * Переименовано из exchangeAction()
     *
     * Сделано по аналогии с
     * @see Lesson_ListController::validateProctoringAction()
     */
    public function apiSetAction() {
        $elsEventId = (int) $this->_getParam('els_event_id');
        $elsUserId = (int) $this->_getParam('els_user_id');
        $passed_proctoring = $this->_getParam('passed_proctoring');
        $video_proctoring = $this->_getParam('video_proctoring');

        $assign = $this->_apiFindLessonAssign($elsEventId, $elsUserId);

        if(!is_null($passed_proctoring)) {
            $assign->passed_proctoring = $passed_proctoring;
        }
        if(!is_null($video_proctoring)) {
            $assign->video_proctoring = $video_proctoring;
        }

        $this->getService('LessonAssign')->update($assign->getValues());

        die(json_encode(array_merge(
            array('status' => 'ok'),
            $this->_apiGetState($elsEventId, $elsUserId)
        )));
    }

    public function apiGetAction() {
        $elsEventId = (int) $this->_getParam('els_event_id');
        $elsUserId = (int) $this->_getParam('els_user_id');

        die(json_encode($this->_apiGetState($elsEventId, $elsUserId)));
    }

    public function _getRequestBody() {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            /** это preflight-запрос, на него не нужно отвечать */
            die();
        }

        return file_get_contents('php://input');
    }

    public function _getRequestBodyJsonData() {
        $result = json_decode($this->_getRequestBody(), 1);

        if (!$result) {
            die (json_encode(array('error' => 'empty request')));
        }

        return $result;
    }

    /**
     * Переименовано из photoAction
     * #35441
     * image:'data:image/jpeg;base64,R0lGODlhE.....'
     */
    public function apiProctoringPhotoChangeAction() {
        $request = $this->_getRequestBodyJsonData();
        $elsEventId = $request['els_event_id'];//(int) $this->_getParam('event_id');
        $elsUserId = (int) $request['els_user_id'];

        $lessonAssign = $this->_apiFindLessonAssign($elsEventId, $elsUserId);

        if(isset($request['image'])) {

            $file = $this->getService('Files')->addFileFromBinary(base64_decode(substr($request['image'], 23)), 'proctoring_'.date('d.m.Y_H-i-s').'.jpg');
            if($file) {
                if($lessonAssign->file_id) {
                    $this->getService('Files')->delete($lessonAssign->file_id);
                }
                $lessonAssign->file_id = $file->file_id;
                $this->getService('LessonAssign')->update($lessonAssign->getValues());
            }
        }

        die('{}');
    }

    /**
     * Добавление видеозаписей
     *
     * Переименовано из urlAction
     * #35442
     * stamp: '19.10.2020 10:00'
     *
     * type:
     * @see HM_Proctoring_File_FileModel::TYPE_*
     */
    public function apiProctoringFileAddAction() {
        $request = $this->_getRequestBodyJsonData();

        $elsEventId = (int) $request['els_event_id'];
        $elsUserId = (int) $request['els_user_id'];
        $type = $request['type'];
        $url = $request['url'];
        $stamp = $request['stamp'];

        $lessonAssign = $this->_apiFindLessonAssign($elsEventId, $elsUserId);

        if(isset($request['url'])) {
            $this->getService('ProctoringFile')->insert(array(
                'type' => $type,
                'SSID' => $lessonAssign->SSID,
                'url' => $url,
                'stamp' => $stamp
            ));
        }

        die('{}');
    }
}
