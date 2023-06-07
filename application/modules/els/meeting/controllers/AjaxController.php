<?php

class Meeting_AjaxController extends HM_Controller_Action
{

    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }

    public function participantsListAction()
    {
        $meetingId = (int) $this->_getParam('meeting_id', 0);

        $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->findDependence('Assign',$meetingId));

        $projectId = (int) $this->_getParam('project_id', 0);

        $q = urldecode($this->_getParam('q', ''));

        $where = "CID = '".$projectId."'";
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
            'Participant',
            $where,
            array('LastName', 'FirstName', 'Patronymic', 'Login')
        );
        $participants = array();

        if (count($collection)) {
            foreach($collection as $participant) {
                $participants[$participant->MID] = $participant->getName();
            }
        }

        if (is_array($participants) && count($participants)) {
            $count = 0;
            foreach($participants as $participantId => $name) {
                if ($count > 0) {
                    echo "\n";
                }
                if ($meeting && $meeting->isParticipantAssigned($participantId)) {
                    $participantId .= '+';
                }
                echo sprintf("%s=%s", $participantId, $name);
                $count++;
            }
        }
    }

    public function modulesListAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $itemId = $this->_getParam('item_id', 0);

        $items = array();

        $parent = 0;

        if (!$itemId) {
            $collection = $this->getService('Project')->getCourses($projectId);
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
            $item = $this->getService('CourseItem')->getOne($this->getService('CourseItem')->find($itemId));
            if ($item) {
                $courseId = $item->cid;
            }
        }

        if ($courseId) {
            $parentItem = $this->getService('Course')->getParentItem($itemId);
            if ($parentItem) {
                $parent = $parentItem->oid;
            }

            $collection = $this->getService('Course')->getChildrenLevelItems($courseId, $itemId);
            if (!count($collection)) {
                $collection = $this->getService('Course')->getChildrenLevelItems($courseId, $parent);
                $parentItem = $this->getService('Course')->getParentItem($parent);
                if ($parentItem) {
                    $parent = $parentItem->oid;
                } else {
                    $parent = 0;
                }
            }

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
}