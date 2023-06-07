<?php

class Lesson_AjaxController extends HM_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }

    public function groupsListAction()
    {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $collection = $this->getService('StudyGroupCourse')->getCourseGroups($this->getParam('subject_id', 0));

        $groups = array();
        if (count($collection)) {
            foreach($collection as $group) {
                $groups[$group->group_id] = $group->name;
            }
        }

        /*
        if (is_array($groups) && count($groups)) {
            $count = 0;
            foreach($groups as $groupId => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($lesson && $lesson->isStudentAssigned($studentId)) {
                    $studentId .= '+';
                }
                echo sprintf("%s=%s", $groupId, $name);
                $count++;
            }
        }
        */
    }

    public function studentsListAction()
    {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', $lessonId));

        $subjectId = (int) $this->_getParam('subject_id', 0);

        $q = urldecode($this->_getParam('q', ''));

        $where = "CID = '".$subjectId."'";
        if (strlen($q)) {
            $q = '%'.iconv('UTF-8', Zend_Registry::get('config')->charset, $q).'%';
            $where = '('.
                    $this->getService('User')->quoteInto('LOWER(LastName) LIKE LOWER(?)', $q).
                    $this->getService('User')->quoteInto('OR LOWER(FirstName) LIKE LOWER(?)', $q).
                    $this->getService('User')->quoteInto('OR LOWER(Patronymic) LIKE LOWER(?)', $q).
                    $this->getService('User')->quoteInto('OR LOWER(Login) LIKE LOWER(?)', $q).
                    ')';
        }

        $collection = $this->getService('User')->fetchAllJoinInner(
            'Student',
            $where,
            array('LastName', 'FirstName', 'Patronymic', 'Login')
        );

        $students = array();

        if (count($collection)) {
            foreach($collection as $student) {
                $students[$student->MID] = $student->getName();
            }
        }

        $result = [];

        if (is_array($students) && count($students)) {
            $position = 0;
            foreach($students as $studentId => $name) {

                $result[] = [
                    'id' => $studentId,
                    'name' => $name,
                    'selected' => $lesson && $lesson->isStudentAssigned($studentId),
                    'level' => 1,
                    'lft' => ++$position,
                    'rgt' => ++$position,
                ];
            }
        }

        $this->_helper->json($result);
    }

    public function modulesListAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $itemId = $this->_getParam('item_id', 0);


        /** @var HM_Course_CourseService $courseService */
        $courseService = $this->getService('Course');
        /** @var HM_Course_Item_ItemService $courseItemService */
        $courseItemService = $this->getService('CourseItem');

        $items = array();

        $parent = 0;

        if (!$itemId) {
            $collection = $this->getService('Subject')->getCourses($subjectId);
            if (count($collection)) {
                foreach($collection as $course) {
                    $items[] = '<item id="course_'.$course->CID.'" value="'.htmlspecialchars($course->Title).'"/>';
                }
            }
        }

        $courseId = 0;
        if (substr($itemId, 0, 7) == 'course_') {
            $courseId = substr($itemId, 7);
            $itemId = -1;
        } else {
            $itemId = (int) $itemId;
            $item = $courseItemService->getOne($courseItemService->find($itemId));
            if ($item) {
                $courseId = $item->cid;
            }
        }

        if ($courseId) {
            list($plainTree) = $courseItemService->getTree($courseId, $itemId);
            if (!empty($plainTree[0]['parent'])) {
                $parent = $plainTree[0]['parent']['item']->oid;
            }

            $collection = $courseService->getChildrenLevelItems($courseId, $itemId);

            if (count($collection)) {
                foreach($collection as $item) {
                    $items[] = '<item id="'.$item->oid.'" value="'.htmlspecialchars($item->title).'"/>';
                }
            }

            if (($parent <= 0) && ($itemId > 0)) {
                $parent = 'course_'.$courseId;
            }

        }

        $xml = "<?xml version=\"1.0\" encoding=\"".Zend_Registry::get('config')->charset."\"?><tree owner=\"".$parent."\">".join('', $items)."</tree>";
        echo $xml;
    }

    public function changeTitleAction()
    {
        $text = $this->_getParam('text', '');
        $lessonId = $this->_getParam('lesson_id', 0);

        if ($lessonId) {
            $this->updateLessonField($lessonId, $text, 'title');
        }
    }

    public function changeDescriptionAction()
    {
        $text = $this->_getParam('text', '');
        $lessonId = $this->_getParam('lesson_id', 0);

        if ($lessonId) {
            $this->updateLessonField($lessonId, $text, 'descript');
        }
    }

    protected function updateLessonField($lessonId, $text, $field)
    {
        $result = array();

        $primaryKey = $this->getService('Lesson')->getMapper()->getAdapter()->getPrimaryKey();
        $field = ($field == 'description') ? 'descript' : $field;

        $this->getService('Lesson')
            ->updateWhere(
                [$field => $text],
                [$primaryKey . ' = ?' => $lessonId]
            );

        $result['text'] = $text;

        $this->_helper->json($result);
    }
}