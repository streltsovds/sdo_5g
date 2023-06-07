<?php

class HM_View_Infoblock_ScheduleBlock extends HM_View_Infoblock_Abstract
{

    protected $id = 'schedule';

    public function scheduleBlock($param = null)
    {
        $begin = time();
        $end = $begin + 28 * 60*60*24;

        if (isset($options['begin'])) {
            $begin = $options['begin'];
        }

        if (isset($options['end'])) {
            $end = $options['end'];
        }

        $ajax = isset($options['ajax']);

        $currentUserId = (int) $this->getService('User')->getCurrentUserId();

        $select = $this->getService('User')->getSelect();

        $select->from(array('s' => 'schedule'), array(
                    's.*',
                    'subject' => 'subjects.name',
                    'regtime' => 'Students.time_registered',
                    'mark' => 'scheduleID.V_STATUS'
                ))
                ->joinInner('subjects', 's.CID = subjects.subid', array())
                ->joinInner('Students', 'subjects.subid = Students.CID', array())
                ->joinInner('scheduleID', 's.SHEID = scheduleID.SHEID')
                ->where('Students.MID = ?', $currentUserId)
                ->where('scheduleID.MID = ?', $currentUserId)
                ->where(
                    $this->getService('User')->quoteInto(
                        array(
                            '(s.timetype IN (?)',
                            ' OR (timetype IN (?)',
                            ' AND GREATEST(UNIX_TIMESTAMP(s.begin), ?)',
                            ' < LEAST(UNIX_TIMESTAMP(s.end), ?) ))'
                        ),
                        array(
                            array(HM_Lesson_LessonModel::TIMETYPE_FREE, HM_Lesson_LessonModel::TIMETYPE_RELATIVE),
                            array(HM_Lesson_LessonModel::TIMETYPE_TIMES, HM_Lesson_LessonModel::TIMETYPE_DATES),
                            $begin,
                            $end
                        )
                    )
                );

        $rows = $select->query()->fetchAll();

        $subjects = false;
        $sequence = array();
        if (count($rows)) {
            foreach($rows as $row) {

                $lesson = HM_Lesson_LessonModel::factory($row);
                if (!$lesson) continue;

                if ($lesson->isRelative()) {
                    if ((strtotime($lesson->getBeginDatetime($row['regtime'])) > $end)
                        || strtotime($lesson->getEndDatetime($row['regtime'])) < $begin) {
                        continue;
                    }
                }

                if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_FREE) {
                    $lesson->begin = date('Y-m-d H:i', $begin);
                    $lesson->end   = date('Y-m-d H:i', $end);
                }

                if ($lesson->isConditionalLesson()) {
                    if (!$this->getService('Lesson')->isLaunchConditionSatisfied($lesson->SHEID, $lesson, false)) {
                        continue;
                    }
                }

                $prefix = '';

                if ($lesson->recommend) {
                    $prefix = '9';
                }

                $lesson->overdue = false;
                if (($row['mark'] == -1) && ($lesson->vedomost) && !$lesson->recommend && (strtotime($lesson->end) < time())) {
                    $prefix = '0';
                    $lesson->overdue = true;
                }

                $lessons = array();
                if (isset($sequence[$row['CID']])) {
                    $lessons = $subjects[$sequence[$row['CID']]]['lessons'];
                    unset($subjects[$sequence[$row['CID']]]);
                }
                $key = $row['subject'].$row['begin'].$row['CID'];
                $sequence[$row['CID']] = $key;
                $subjects[$key]['title'] = $row['subject'];
                $subjects[$key]['subject_id'] = $row['CID'];
                $lessons[$prefix.$row['begin'].$row['SHEID']] = $lesson;
                $subjects[$key]['lessons'] = $lessons;
            }

            if (is_array($subjects)) {
                ksort($subjects);
            }
        }
        $this->view->ajax = $ajax;
        $this->view->begin = date('d.m.Y', $begin);
        $this->view->end = date('d.m.Y', $end);
        $this->view->subjects = $subjects;

        $content = $this->view->render('scheduleBlock.tpl');

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/schedule/style.css');

        return $this->render($content);
    }
}