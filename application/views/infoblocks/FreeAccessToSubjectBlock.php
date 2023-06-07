<?php


class HM_View_Infoblock_FreeAccessToSubjectBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;

    protected $id = 'freeAccessToSubject';

    public function freeAccessToSubjectBlock($param = null)
    {

        $subject = $options['subject'];
        $services = Zend_Registry::get('serviceContainer');

        $coursesArr = $resourcesArr = $materials = array();
        if ($courses = $services->getService('Course')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $subject->subid)) {
            foreach ($courses as $course) {
                $coursesArr[$course->CID] = $course;
            }
        }

        if ($resources = $services->getService('Resource')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $subject->subid)) {
            foreach ($resources as $resource) {
                $resourcesArr[$resource->resource_id] = $resource;
            }
        }

        $lessonAssigns = $services->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $this->getService('Lesson')->quoteInto(array(
                'self.MID = ?',
                ' AND Lesson.CID = ?',
                ' AND Lesson.isfree = ?',
            ), array(
                $this->getService('User')->getCurrentUserId(),
                $subject->subid,
                HM_Lesson_LessonModel::MODE_FREE
            )),
            array('launched DESC'),//, 'Lesson.createDate'),
            self::MAX_ITEMS
        );

        foreach ($lessonAssigns as $lessonAssign) {

            $lesson = $lessonAssign->lessons->current();
            $moduleId = $lesson->getModuleId();
            $arr = ($lesson->typeID == HM_Event_EventModel::TYPE_COURSE) ? $coursesArr : $resourcesArr;
            if (isset($arr[$moduleId])) {
                $lesson->material = $arr[$moduleId];
            } else {
                continue;
            }
            $materials[$lesson->SHEID] = $lesson;

        }

        $this->view->materials = $materials;
        $this->view->subject = $subject;

		$content = $this->view->render('freeAccessToSubjectBlock.tpl');

        
        return $this->render($content);
    }
}