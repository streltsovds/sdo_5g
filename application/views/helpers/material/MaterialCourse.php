<?php

class HM_View_Helper_MaterialCourse extends HM_View_Helper_MaterialAbstract
{
    public function materialCourse($material, $lesson)
    {
        /** @var HM_User_UserService $userService */
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $userId = $userService->getCurrentUserId();

        $courseId = $material->CID;
        $subjectId = $lesson->CID;
        $lessonId = $lesson->SHEID;

        /** @var HM_Course_Item_Current_CurrentService $courseItemCurrentService */
        $courseItemCurrentService = Zend_Registry::get('serviceContainer')->getService('CourseItemCurrent');
        $currentItemId = $courseItemCurrentService->getCurrent($userId, $subjectId, $courseId, $lessonId);

        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = Zend_Registry::get('serviceContainer')->getService('CourseItem');
        $isDegeneratedTree = $courseItemService->isDegeneratedTree($courseId);

        $this->view->material = $material;
        $this->view->lesson = $lesson;

        if ($material->new_window && $isDegeneratedTree) {
            $singleItemUrl = $this->view->url(array(
                'module' => 'course',
                'controller' => 'item',
                'action' => 'view',
                'subject_id' => $subjectId,
                'course_id' => $courseId,
                'item_id' => $currentItemId,
                'lesson_id' => $lessonId
            ));

            $this->view->singleItemUrl = $singleItemUrl;
            return $this->view->render("material/course-without-menu.tpl");

        } else {


            /** @var HM_Course_Item_ItemService $courseItemService */
            $courseItemService = Zend_Registry::get('serviceContainer')->getService('CourseItem');
            $treeData = $courseItemService->getHmTreeData($courseId);

            if ($currentItemId) {
                $currentItem = $courseItemService->getOne($courseItemService->find($currentItemId));
            }

            $this->view->currentItem = $currentItem;
            $this->view->treeData = $treeData;

            return $this->view->render("material/course.tpl");

        }
    }
}