<?php


class HM_View_Infoblock_TrainingsCalendarBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'trainingsCalendar';

    public function trainingsCalendarBlock($param = null)
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $scheduleEventCond = 'CASE WHEN isnumeric(sch.typeID)=1 THEN abs(CAST(sch.typeID as INT)) ELSE 0 END=ev.event_id';
        } else {
            $scheduleEventCond = 'CASE WHEN (CONCAT(\'\',(sch.typeID * 1)) = sch.typeID) THEN abs(cast(sch.typeID as signed)) ELSE 0 END = ev.event_id';
        }

        $scheduleEventsSelect = $this->getService('Lesson')->getSelect()
            ->from(
                ['sid' => $this->getService('LessonAssign')->getMapper()->getAdapter()->getTableName()],
                [
                    'id' => 'sch.SHEID',
                    'name' => 'sch.title',
                    'begin_date' => 'sid.begin_personal',
                    'type' => new Zend_Db_Expr("'lesson'"),
                    'subtype' => new Zend_Db_Expr("
                        (CASE
                            WHEN (ev.tool is not null) THEN ev.tool
                            ELSE sch.typeID
                        END)
                    "),
                    'subject_id' => 'sch.CID',
                    'user_id' => 'sid.MID',
                ]
            )
            ->joinInner(['sch' => 'schedule'], 'sch.SHEID = sid.SHEID', [])
            ->joinLeft(['ev' => 'events'], $scheduleEventCond, [])
            ->where('sid.begin_personal is not null')
            ->where('sch.timetype <> 2')
            ->where('sid.MID = ?', $currentUserId)
            ->where('begin_personal <> 0')
            ->order('sch.order');
        $scheduleEvents = $scheduleEventsSelect->query()->fetchAll();

        $subjectEventSelect = $this->getService('Subject')->getSelect()
            ->from(
                ['s' => 'subjects'],
                [
                    'id' => 's.subid',
                    'name' => 's.name',
                    'begin_date' => 's.begin',
                    'type' => new Zend_Db_Expr("'subject'"),
                    'subtype' => new Zend_Db_Expr("''"),
                    'subject_id' => new Zend_Db_Expr("0"),
                    'user_id' => 'st.MID',
                ]
            )
            ->joinInner(['st' => $this->getService('Student')->getMapper()->getAdapter()->getTableName()], 'st.CID = s.subid', [])
            ->where('s.begin is not null')
            ->where('s.period <> 1')
            ->where('st.MID = ?', $currentUserId)
            ->where('s.begin <> 0');
        $subjectEvent = $subjectEventSelect->query()->fetchAll();

        $sessionEventSelect = $this->getService('Subject')->getSelect()
            ->from(
                ['s' => 'at_sessions'],
                [
                    'id' => 's.session_id',
                    'name' => 's.name',
                    'begin_date' => 's.begin_date',
                    'type' => new Zend_Db_Expr("'at_session'"),
                    'subtype' => new Zend_Db_Expr("''"),
                    'subject_id' => new Zend_Db_Expr("0"),
                    'user_id' => 'su.user_id',
                ]
            )
            ->joinInner(['su' => 'at_session_users'], 's.session_id = su.session_id', [])
            ->where('s.begin_date is not null')
            ->where('su.user_id = ?', $currentUserId)
            ->where('s.begin_date <> 0');
        $sessionEvent = $sessionEventSelect->query()->fetchAll();

        $events = array_merge($scheduleEvents, $subjectEvent, $sessionEvent);
        $datesWithEvents = [];
        $lessonsIds = [0];

        foreach ($events as $event) {
            if(HM_EventDate_EventDateModel::EVENT_TYPE_LESSON == $event['type']) {
                $lessonsIds[] = $event['id'];
            }
        }

        foreach ($events as &$event) {
            $eventDate = new HM_Date($event['begin_date']);
            $eventDate = $eventDate->toString('YYYY-MM-dd');
            $event['begin_date'] = $eventDate;

            switch ($event['type']) {
                case HM_EventDate_EventDateModel::EVENT_TYPE_LESSON:
                    $viewUrl = $this->getService('Lesson')->getExecuteUrl($event['id'], $event['subject_id']);
                    break;
                case HM_EventDate_EventDateModel::EVENT_TYPE_AT_SESSION:
                    $viewUrl = $this->getService('AtSession')->getDefaultUri($event['id']);
                    break;
                case HM_EventDate_EventDateModel::EVENT_TYPE_SUBJECT:
                    $viewUrl = $this->getService('Subject')->getViewUrl($event['id']);
                    break;
            }

            $event['view_url'] = is_array($viewUrl) ? $this->view->url($viewUrl) : $viewUrl;

            $datesWithEvents[$eventDate][] = $event;
        }

        $this->view->data = HM_Json::encodeErrorSkip($datesWithEvents);

        $content = $this->view->render('trainingsCalendarBlock.tpl');
        return $this->render($content);
    }
}