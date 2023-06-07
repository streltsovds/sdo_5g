<?php
class Course_BookmarksController extends HM_Controller_Action_Course
{
    public function init()
    {
        parent::init();

        $this->_helper->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('update',   'json')
            ->initContext('json');

    }

    /**
     * Редирект на просмотр ресурса
     */
    public function viewAction() {
        $bookmarkId = (int)$this->_getParam('bookmark_id', 0);

        $courseBookmarkService = $this->getService('CourseBookmark');
        $bookmark = $courseBookmarkService->find($bookmarkId);

        if (count($bookmark)) {
            /** @var HM_Course_Bookmark_BookmarkModel $bookmark */
            $bookmark = $bookmark->current();

            $courseItemService = $this->getService('CourseItem');
            //раздел учебного модуля
            $courseItem = $courseItemService->fetchAll(array(
                'oid = ?' => $bookmark->item_id
            ));

            //если есть раздел учебного модуля
            if (count($courseItem)) {
                $courseItem = $courseItem->current();

                $lessonService = $this->getService('Lesson');
                $lesson = $lessonService->find($bookmark->lesson_id);

                //если есть занятие, в котором была сделана закладка
                if (count($lesson)) {
                    $lesson = $lesson->current();
                    $url = $this->view->url(array(
                        'module' => 'subject',
                        'controller' => 'index',
                        'action' => 'index',
                        'course_id' => $courseItem->cid,
                        'item_id' => $bookmark->item_id,
                        'subject_id' => $lesson->CID,
                        'lesson_id' => $lesson->SHEID
                    ), null, true);
                } else {
                    $url = $this->view->url(array(
                        'module' => 'course',
                        'controller' => 'index',
                        'action' => 'index',
                        'course_id' => $courseItem->cid,
                        'item_id' => $bookmark->item_id,
                    ), null, true);
                }
            } elseif(isset($bookmark->resource_id) && $bookmark->resource_id) {
                //переходим к ресурсу, если занятие и уч. модуль не найдены
                $url = $this->view->url(array(
                    'module' => 'resource',
                    'controller' => 'index',
                    'action' => 'index',
                    'resource_id' => $bookmark->resource_id,
                ), null, true);
            }
        }

        if (!isset($url)) {
            $url = $_SERVER['HTTP_REFERER'];
            $this->_flashMessenger->addMessage(array(
                'message' => _('Ресурс не найден.'),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR
            ));
        }

        $this->_redirector->gotoUrlAndExit($url);
    }

    public function updateAction() {
        $nodes = $this->_getParam('nodes', array());

        $updateNodes = $nodes['update'];
        $deleteNodes = $nodes['delete'];
        $insertNodes = $nodes['insert'];

        $bookmarksService = $this->getService('CourseBookmark');

        //update
        foreach($updateNodes as $nodeData) {
            unset($nodeData['key']);
            $bookmarksService->update($nodeData);
        }

        //delete
        if(count($deleteNodes)) {
            foreach ($deleteNodes as &$node) {
                $node = $node['bookmark_id'];
            }

            $bookmarksService->deleteBy($bookmarksService->quoteInto(
                'bookmark_id IN (?)',
                $deleteNodes
            ));
        }

        //insert
        $newNodes = array();
        if (count($insertNodes)) {
            $userService = $this->getService('User');
            $currentUserId = $userService->getCurrentUserId();

            foreach($insertNodes as $nodeData) {
                $nodeKey = $nodeData['key'];
                unset($nodeData['key']);
                $nodeData['user_id'] = $currentUserId;
                $bookmark = $bookmarksService->insert($nodeData);
                $newNodes[] = array(
                    'key' => $nodeKey,
                    'data' => $bookmark->getData(),
                );
            }
        }

        //возвращаем новые ноды
        $this->view->assign(array(
            'nodes' => $newNodes
        ));
    }

}