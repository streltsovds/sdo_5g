<?php
class HM_Section_SectionService extends HM_Service_Abstract
{
    public function getSectionsMaterials($subjectId)
    {
        $sections = $this->fetchAll($this->quoteInto("subject_id = ?", $subjectId), 'order ASC');
        $lessons = $this->getService('Lesson')->fetchAll($this->quoteInto(array(
            'CID = ?',
            ' AND isfree = ?',
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_FREE
        )), 'order ASC');

        $coursesArr = $resourcesArr = array();
        if ($courses = $this->getService('Course')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $itemId)) {
            foreach ($courses as $course) {
                $coursesArr[$course->CID] = $course;
            }
        }

        if ($resources = $this->getService('Resource')->fetchAllDependenceJoinInner("SubjectAssign", "SubjectAssign.subject = 'subject' AND SubjectAssign.subject_id = ". $itemId)) {
            foreach ($resources as $resource) {
                $resourcesArr[$resource->resource_id] = $resource;
            }
        }

        // сортировка fetchAllDependenceJoinInner не работает..(
        // и вообще здесь нужен fetchAllDependenceJoinLeft
        // $sections = $this->fetchAllDependenceJoinInner('Lesson', $this->quoteInto('subject_id = ?', $subjectId), array('self.order ASC', 'Lesson.order ASC'));

        /*
         * костыль ((
         * т.к. нигде не контролируется значение удаленных section_id в schedule (lesson)
         * при удалении раздела остаются никуда не привязанные занятия и при выводе они просто отбрасываются
         * пробегаюсь по занятиям и присваюваю им в модели id дефолтной секции.
         */
        
        $defaultSectionId = 0;
        foreach ($sections as $section) {
            if($section->name == ''){
                $defaultSectionId = $section->section_id;
            }
        }

        if ($defaultSectionId == 0) {
            $sectionId = $sections->current() ? $sections->current()->section_id : 0;
            $defaultSectionId = $sectionId;
        }
                
        $sections_ids = $sections->getList('section_id', 'section_id');

        foreach ($lessons as $lesson) {
            if(!$sections_ids[$lesson->section_id]){
                $lesson->section_id = $defaultSectionId;
            }
        }
        
        
        foreach ($sections as $section) {
            $lessonArr = array();
            foreach ($lessons as $lesson) {
                if ($lesson->section_id != $section->section_id) continue;

                $moduleId = $lesson->getModuleId();
                $arr = ($lesson->typeID == HM_Event_EventModel::TYPE_COURSE) ? $coursesArr : $resourcesArr;
                $lesson->material = isset($arr[$moduleId]) ? $arr[$moduleId] : false;

                $lessonArr[] = $lesson;
            }
            $section->lessons = $lessonArr;
        }
        return $sections;
    }

    public function getDefaultSection($itemId, $itemType = HM_Section_SectionModel::ITEM_TYPE_SUBJECT)
    {
        $sections = $this->fetchAll(array("{$itemType}_id = ?" => $itemId), 'order', 1);
        if (!count($sections)) {
            $section = $this->createSection($itemId, $itemType);
        } else {
            $section = $sections->current();
        }
        return $section;
    }

    public function createSection($itemId, $itemType = HM_Section_SectionModel::ITEM_TYPE_SUBJECT, $name = '', $order = 1)
    {
        return $this->insert([
            "{$itemType}_id" => $itemId,
            'name' => $name,
            'order' => $order,
        ]);
    }

    public function getCurrentOrder($section)
    {
        if ($section->subject_id) {
            $materials = $this->getService('Lesson')->fetchAll($this->quoteInto(
                array('CID = ?', ' AND section_id = ?', ' AND isfree = ?'),
                array($section->subject_id, $section->section_id, HM_Lesson_LessonModel::MODE_FREE)
            ), 'order DESC', 1);
        } else {
            $materials = $this->getService('Meeting')->fetchAll($this->quoteInto(
                array('project_id = ?', ' AND section_id = ?', ' AND isfree = ?'),
                array($section->project_id, $section->section_id, HM_Meeting_MeetingModel::MODE_FREE)
            ), 'order DESC', 1);
        }
        if (count($materials)) {
            return $materials->current()->order;
        }
        return 0;
    }

    public function getCurrentSectionOrder($itemId, $itemType = HM_Section_SectionModel::ITEM_TYPE_SUBJECT)
    {
        $sections = $this->fetchAll(array(
            "{$itemType}_id = ?" => $itemId,
        ), 'order DESC', 1);

        if (count($sections)) {
            return $sections->current()->order;
        }
        return 0;
    }

    public function setMaterialsOrder($sectionId, $materials, $itemType = HM_Section_SectionModel::ITEM_TYPE_SUBJECT)
    {
        if (is_array($materials)) {
            foreach ($materials as $order => $lesson_id) {
                $service = ($itemType == HM_Section_SectionModel::ITEM_TYPE_SUBJECT) ? 'Lesson' : 'Meeting';
                $key = ($itemType == HM_Section_SectionModel::ITEM_TYPE_SUBJECT) ? 'SHEID' : 'meeting_id';
                $this->getService($service)->updateWhere(array(
                    'section_id' => $sectionId,
                    'order' => $order,
                ), array(
                    "{$key} = ?" => $lesson_id,
                ));
            }
            return true;
        }
    }

    public function copy($section, $toSubjectId = null)
    {
        if ($section) {

            if (null !== $toSubjectId) {
                $section->subject_id = $toSubjectId;
            }
            $newSection = $this->insert($section->getValues(null, array('section_id')));

            return $newSection;
        }
        return false;
    }

    public function getSectionsLessons($itemId, $addingWhere, &$titles, $itemType = HM_Section_SectionModel::ITEM_TYPE_SUBJECT)
    {
        $sections = $this->fetchAll($this->quoteInto("{$itemType}_id = ?", $itemId), 'order ASC');
        $service = ($itemType == HM_Section_SectionModel::ITEM_TYPE_SUBJECT) ? 'Lesson' : 'Meeting';
        $key = ($itemType == HM_Section_SectionModel::ITEM_TYPE_SUBJECT) ? 'CID' : 'project_id';

        if (!count($sections)) {
            $this->createSection($itemId, $itemType);
            $sections = $this->fetchAll($this->quoteInto("{$itemType}_id = ?", $itemId), 'order ASC');
        }

        if (count($sections)) {
            $this->getService($service)->updateWhere(
                array('section_id' => $sections->current()->section_id),
                $this->quoteInto('CID = ? AND (section_id = 0 OR section_id IS NULL)', $itemId)
            );
        }

        $lessons = $this->getService($service)->fetchAllDependence(
        		array('Assign', 'Teacher'),
        		array(
        			"{$key} = ?" => $itemId,
                    'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
    		        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
        		) + $addingWhere,
        		array('order', 'begin', 'SHEID')
        );

        if (count($sections) && count($lessons)) {
            foreach ($sections as $section) {
                $lessonArr = array();
                foreach ($lessons as $lesson) {
                    if ($lesson->section_id != $section->section_id) continue;

                    $lessonArr[$lesson->SHEID] = $lesson;
                    $titles[$lesson->SHEID] = $lesson->title;
                }
                $section->lessons = $lessonArr;
            }
        }
        return $sections;
    }

    public function setSectionOrder($sectionId, $order)
    {
        $section = $this->getOne($this->find($sectionId));
        if ($section) {
            $this->updateWhere(
                array('order' => $order),
                array("section_id = ?" => $sectionId)
            );

            return true;
        }

        return false;
    }

    public function setLessonsOrder($sectionId, $materials, $itemType = HM_Section_SectionModel::ITEM_TYPE_SUBJECT)
    {
        $service = ($itemType == HM_Section_SectionModel::ITEM_TYPE_SUBJECT) ? 'Lesson' : 'Meeting';
        if (is_array($materials)) {
            $section = $this->getOne($this->find($sectionId));
            if ($section) {
                foreach ($materials as $order => $lesson_id) {
                    $order = $section->order . $order;
                    $this->getService($service)->updateWhere(array(
                        'section_id' => $sectionId,
                        'order' => $order,
                    ), array(
                        'SHEID = ?' => $lesson_id,
                    ));
                }
                return true;
            }
        }
        return false;
    }

    public function updateField($sectionId, $text, $field)
    {
        $result = $this->updateWhere(
            array($field => $text),
            array('section_id = ?' => $sectionId)
        );

        return $result;
    }

    public function deleteSection($sectionId)
    {

        $return = parent::delete($sectionId);
        $lessons = $this->getSectionLessons($sectionId);

        if (count($lessons)) {
            foreach ($lessons as $lesson) {
                $this->getService('Lesson')->updateWhere(
                    array('section_id' => null),
                    array('SHEID = ?' => $lesson->SHEID)
                );
            }
        }

        return $return;
    }

    public function getSectionLessons($sectionId)
    {
        return $this->getService('Lesson')->fetchAll($this->quoteInto(
            array('section_id = ?'),
            array($sectionId)
        ));
    }
}