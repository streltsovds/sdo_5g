<?php
class Subject_AjaxController extends HM_Controller_Action
{
    public function init() {
        parent::init();

        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('add-bookmark', 'json')
            ->addActionContext('remove-bookmark', 'json')
            ->addActionContext('check-viewed-oids', 'json')
            ->initContext('json');
    }

    /**
     * Проверяет, какие ресурсы пользователь уже просматривал, а какие нет
     */
    public function checkViewedOidsAction() {
        $oids = $this->_getParam('oids', array());
        $courseId = $this->_getParam('course_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        /** @var HM_Scorm_Track_TrackService $scormTrackService */
        $scormTrackService = $this->getService('ScormTrack');
        $scormTrackData = $scormTrackService->fetchAll(array(
            'mid = ?' => $userService->getCurrentUserId(),
            'ModID IN(?)' => $oids,
            'cid = ?' => $courseId,
            'lesson_id = ?' => $lessonId,
            'status IN (?)' => array(HM_Scorm_Track_Data_DataModel::STATUS_PASSED, HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED)
        ));

        $completed = array_values($scormTrackData->getList('ModID'));

        $oids = array_fill_keys($oids, false);
        foreach ($oids as $key => $value) {
            if (in_array($key, $completed)) {
                $oids[$key] = true;
            }
        }

        $this->view->assign(array(
            'oids' => $oids
        ));
    }

    public function addBookmarkAction()
    {
        $result = array(
            'success' => false,
            'message' => _('Что-то пошло не так')
        );

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $userRole = $userService->getCurrentUserRole();

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $isEnduser = $aclService->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $itemId = (int)$this->_getParam('item_id', 0);
        $lessonId = (int)$this->_getParam('lesson_id', 0);

        if ($isEnduser && $itemId) {
            $userId = $userService->getCurrentUserId();

            /** @var HM_Course_Bookmark_BookmarkService $courseBookmarkService */
            $courseBookmarkService = $this->getService('CourseBookmark');

            $courseItemService = $this->getService('CourseItem');
            //раздел учебного модуля
            $courseItem = $courseItemService->fetchAll(array(
                'oid = ?' => $itemId
            ));
            $resourceId = 0;
            if (count($courseItem)) {
                $courseItem = $courseItem->current();
                $resourceId = $courseItem->vol2 ? $courseItem->vol2 : 0;
            }

            try {
                $courseBookmarkService->insert(array(
                    'item_id' => $itemId,
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                    'resource_id' => $resourceId,
                ));
                $result['success'] = true;
                $result['message'] = _('Закладка добавлена');

            } catch (Exception $e) {
                if ($e->getCode() == 23000) {
                    $result['success'] = false;
                    $result['message'] = _('У вас уже есть эта закладка');
                }
            }
        }

        $this->view->assign($result);
    }

    public function removeBookmarkAction()
    {
        $result = array(
            'success' => false,
            'message' => _('Что-то пошло не так')
        );

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $userRole = $userService->getCurrentUserRole();

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $isEnduser = $aclService->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        $itemId = (int)$this->_getParam('item_id', 0);
        $lessonId = (int)$this->_getParam('lesson_id', 0);

        if ($isEnduser && $itemId && $lessonId) {
            $userId = $userService->getCurrentUserId();

            /** @var HM_Course_Bookmark_BookmarkService $courseBookmarkService */
            $courseBookmarkService = $this->getService('CourseBookmark');


            try {
                $courseBookmarkService->deleteBy(array(
                    'item_id = ?' => $itemId,
                    'user_id = ?' => $userId,
                    'lesson_id = ?' => $lessonId,
                ));
                $result['success'] = true;
                $result['message'] = _('Закладка удалена');

            } catch (Exception $e) {
            }
        }

        $this->view->assign($result);
    }


}