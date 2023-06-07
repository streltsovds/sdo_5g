<?php
class Marksheet_IndexController extends HM_Controller_Action_Subject
{
    public function indexAction()
    {
        $result = new HM_DataType_Marksheet_Data();
        if (!$this->_subjectId) return $result;

        $userId = $this->getService('User')->getCurrentUserId();
        if ($userId) {
            $filters = Zend_Registry::get('session_namespace_default')->marksheetFilter[$userId][$this->_subjectId];
        }

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');
        $markSheetItems = $subjectService->getMarkSheetItems($this->_subjectId, $filters);

        $this->view->assign((array) $markSheetItems);
    }

    public function getMarksheetDataAction()
    {
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        if (
            $this->isAjaxRequest() &&
            $acl->checkRoles(HM_Responsibility_ResponsibilityModel::getMarksheetResponsibilityRoles())
        ) {

            $fromDate = $this->_getParam('from');
            $toDate = $this->_getParam('to');
            $currentGroup = $this->_getParam('current_group');
            $currentPersonId = $this->_getParam('current_person');
            $subjectId = $this->_getParam('subject_id');
            $searchQuery = $this->_getParam('search_user');

            if (!$subjectId) {
                $result = new HM_DataType_Marksheet_Data();
                return $result;
            };

            $groupParamParts = explode('_', $currentGroup);
            if ('group' == $groupParamParts[0]) {
                $group = $groupParamParts[1];
            } elseif ('subgroup' == $groupParamParts[0]) {
                $subGroup = $groupParamParts[1];
            }

            $filters = [
                'from' => $fromDate ? $fromDate : '',
                'to' => $toDate ? $toDate : '',
                'currentGroup' => $currentGroup ? $currentGroup : '',  // (sub)group_{n}
                'currentPersonId' => $currentPersonId ? $currentPersonId : '',
                'group' => $group ? $group : null,
                'subGroup' => $subGroup ? $subGroup : null,
                'searchQuery' => $searchQuery ? $searchQuery : null,
                'forGraduated' => false
            ];

            $userId = $this->getService('User')->getCurrentUserId();
            if ($userId) {
                Zend_Registry::get('session_namespace_default')->marksheetFilter[$userId][$subjectId] = $filters;
            }

            /** @var HM_Subject_SubjectService $subjectService */
            $subjectService = $this->getService('Subject');
            $data = $subjectService->getMarksheetData($subjectId, $filters);

            return $this->responseJson($data);
        }

        $result = new HM_DataType_Marksheet_Data();
        return $result;
    }

    public function setScoreAction()
    {
        $lessonId = $this->_getParam('lessonId');
        $userId = $this->_getParam('userId');
        $score = $this->_getParam('score', -1);

        if (null === $score || '' === $score) {
            $score = -1;
        }

        $this->_setScore($userId, $lessonId, $score);

        return $this->responseJson([
            'lessonId' => $lessonId,
            'userId' => $userId,
            'score' => $score,
        ]);
    }

    private function _setScore($userId, $lessonId, $score)
    {
        $score = iconv('UTF-8', Zend_Registry::get('config')->charset, $score);
        $this->getService('LessonAssign')->setUserScore($userId, $lessonId, $score, $this->_subjectId);
    }

    public function setTotalScoreAction()
    {
        $userIds = $this->_getParam('user_ids', 0);
        $subjectId = $this->_getParam('subject_id', 0);

        /** @var HM_Lesson_Assign_AssignService $lessonAssignService */
        $lessonAssignService = $this->getService('LessonAssign');
        $userService = $this->getService('User');

        $marks = [];
        $notMarked = [];

        $error = '';
        $message = '';

        foreach ($userIds as $userId) {
            try {
                $mark = $lessonAssignService->onLessonScoreChanged($subjectId, $userId, true, true);
            } catch (HM_Exception $e) {
                /**
                 * Проброс логических ошибок (например, "Курс пройден не полностью")
                 * на frontend
                 */
                $error = $e->getMessage();
                $mark = false;
            }
            if ($mark) {
                $marks[$userId] = $mark;
            } else {
                $notMarked[] = $userId;
            }
        }

        if (!$error) {
            if (count($notMarked)) {
                $error = _("Оценки не были выставлены: ");
                $users = $userService->fetchAll(array('MID IN (?)' => $notMarked));
                $userNames = [];

                if (count($users)) {
                    foreach ($users as $user) {
                        $userNames[] = $user->getName();
                    }
                }

                $error .= implode(', ', $userNames);
            } else {
                $message = _("Оценки выставлены успешно!");
            }
        }

        return $this->responseJson(array_filter([
            'marks' => $marks,
            'message' => $message,
            'error' => $error,
        ]));
    }

    public function setCommentAction()
    {
        $comment = $this->_getParam('comment');
        $userId = $this->_getParam('userId');
        $lessonId = $this->_getParam('lessonId');
        $subjectId = $this->_getParam('subject_id', 0);
        $comment = iconv('UTF-8', Zend_Registry::get('config')->charset, $comment);

        $this->getService('LessonAssign')->setUserComments($userId, $lessonId, $comment, $subjectId);

        return $this->responseJson([
            'lessonId' => $lessonId,
            'userId' => $userId,
            'subjectId' => $subjectId,
            'comment' => $comment,
        ]);
    }

    // from marksheet
    public function graduateStudentsAction()
    {
        $userIds = $this->_getParam('user_ids', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $certificateDate = $this->_getParam('certificate_date', false);
        $certificateDate = str_replace('-', '.', $certificateDate);

        $collection = $this->getService('SubjectMark')->fetchAll(
            array(
                'cid = ?' => $subjectId,
                'mid IN (?)' => $userIds
            )
        );
        $certificatePeriods = $collection->getList('mid', 'certificate_validity_period');
        $assignedUserIds = [];
        $notAssignedUserIds = [];

        foreach ($userIds as $userId) {
            $period = isset($certificatePeriods[$userId]) ? $certificatePeriods[$userId] : false;
            $graduated = $this->getService('Subject')->assignGraduated($subjectId, $userId, null, $period, $certificateDate);
            if ($graduated) {
                $assignedUserIds[] = $userId;
            } else {
                $notAssignedUserIds[] = $userId;
            }
        }

        return $this->responseJson([
            'assigned_user_ids' => $assignedUserIds,
            'not_assigned_user_ids' => $notAssignedUserIds,
            'subject_id' => $subjectId,
            'certificate_date' => $certificateDate,
        ]);
    }

    public function graduateStudentsGridAction()
    {
        $count = 0;
        $mids = array();
        $subjectId = $this->_getParam('subject_id', 0);

        $gridId = ($subjectId) ? "grid{$subjectId}" : 'grid';
        $postMassIds = $this->_getParam('postMassIds_' . $gridId, ''); // from students
        $mids = explode(',', $postMassIds);

        foreach ($mids as $mid) {
            if ($this->getService('Subject')->assignGraduated($subjectId, $mid)) {
                $count++;
            }
        }

        $this->_flashMessenger->addMessage(array(
            'type'        => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message'    => sprintf(_('Успешно переведены в прошедшие обучение %s пользователя (-ей)'), $count)
        ));
        $this->_redirector->gotoSimple('index', 'graduated', 'assign', array('subject_id' => $subjectId));
    }


    public function clearScheduleAction()
    {
        $schedule = $this->_getParam('schedule', 0);
        $subjectId = $this->_request->getParam('subject_id');
        foreach ($schedule as $key => $value) {
            if ($key == 'total') {
                //                if (! empty($subjectId)) {
                //                    $this->getService("SubjectMark")->deleteBy(array("cid = ?" => $subjectId));
                //                }
            } elseif ($key == 'certificate') {
                //                if (! empty($subjectId)) {
                //                    $this->getService("SubjectMark")->updateWhere(array("certificate_validity_period" => -1), array("cid = ?" => $subjectId));
                //                }
            } else {
                $this->getService('LessonAssign')->updateWhere(array('V_STATUS' => -1), array('SHEID = ?' => $key));
            }
        }
        echo 'Ok';
        exit;
    }

    public function printAction()
    {
        $result = new HM_DataType_Marksheet_ExportScore();
        if (!$this->_subjectId) return $result;

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset, true);

        $result = $this->getService('Subject')->getMarkSheetExportScore($this->_subjectId);
        $this->view->assign((array) $result);
    }

    public function wordAction()
    {
        $result = new HM_DataType_Marksheet_ExportScore();
        if (!$this->_subjectId) return $result;

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $result = $this->getService('Subject')->getMarkSheetExportScore($this->_subjectId);
        
        $this->view->assign((array) $result);
        $data =  $this->view->render('index/export.tpl');

        $doc = fopen(Zend_Registry::get('config')->path->upload->marksheets . '/' . $subjectId, 'w');

        // Файлы Excel и Doc читаем в кодировке 'Windows-1251'
        $getCharset = Zend_Registry::get('config')->charset;
        if ('UTF-8' == $getCharset) {
            $data = iconv($getCharset, 'Windows-1251', $data);
        }
        fwrite($doc, $data);
        fclose($doc);

        $this->sendFile($subjectId, 'doc', $subject->name);
    }

    public function excelAction()
    {
        $result = new HM_DataType_Marksheet_ExportScore();
        if (!$this->_subjectId) return $result;

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $result = $this->getService('Subject')->getMarkSheetExportScore($this->_subjectId);

        $this->view->assign((array) $result);
        $data =  $this->view->render('index/export.tpl');

        $xls = fopen(Zend_Registry::get('config')->path->upload->marksheets . '/' . $subjectId, 'w');

        // Файлы Excel и Doc читаем в кодировке 'Windows-1251'
        $getCharset = Zend_Registry::get('config')->charset;
        if ('UTF-8' == $getCharset) {
            $data = iconv($getCharset, 'Windows-1251', $data);
        }
        fwrite($xls, $data);
        fclose($xls);

        $this->sendFile($subjectId, 'xls', $subject->name);
    }

    public function sendFile($subjectId, $ext = 'doc', $name = null)
    {

        if ($subjectId) {
            $name = $name ? $name : $subjectId;
            $options = array('filename' => $name . '.' . $ext);

            switch (true) {
                case $ext == 'doc':
                    $contentType = 'application/word';
                    break;
                case $ext == 'xls':
                    $contentType = 'application/vnd.ms-excel';
                    break;
                case $ext == 'xlsx':
                    $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    break;
                case strpos($this->getRequest()->getHeader('user_agent'), 'opera'):
                    $contentType = 'application/x-download';
                    break;
                default:
                    $contentType = 'application/unknown';
            }

            $this->_helper->SendFile(
                Zend_Registry::get('config')->path->upload->marksheets . '/' . $subjectId,
                $contentType,
                $options
            );
            die();
        }
        $this->_flashMessenger->addMessage(_('Файл не найден'));
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }
}
