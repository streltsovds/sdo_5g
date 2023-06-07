<?php
class HM_Lesson_Assign_AssignModel extends HM_Model_Abstract implements HM_Rest_Interface
{
    const PROGRESS_STATUS_NOSTART   = 0;
    const PROGRESS_STATUS_INPROCESS = 1;
    const PROGRESS_STATUS_DONE      = 2;

    protected $_primaryName = 'SSID';

    public function getLesson()
    {
        if (isset($this->lessons)) {
            return $this->lessons->current();
        }

        return false;
    }

    /**
     * Получаем массив "ключ=>заголовок" статусов прохождения занятия
     * @static
     * @return array
     */
    static public function getProgressStatuses()
    {
        return array(
            self::PROGRESS_STATUS_NOSTART   => _('Не начат'),
            self::PROGRESS_STATUS_INPROCESS => _('В процессе'),
            self::PROGRESS_STATUS_DONE      => _('Пройден')
        );
    }

    /**
     * Возвращает заголовок статуса прохождения по его ключу
     * @static
     * @param $statusKey
     * @return string
     */
    static public function getProgressStatusName($statusKey)
    {
        $statuses = self::getProgressStatuses();
        return (isset($statuses[$statusKey]))? $statuses[$statusKey]: '';
    }

    public function getScore()
    {
        return $this->V_STATUS;
    }

    public function getComment()
    {
        return $this->comments;
    }

    public function getScoreHistory()
    {
        $model = $this->getService()
                      ->getOne($this->getService()
                                    ->fetchAllHybrid(array('MarkHistory','Lesson'),
    												 'Teacher',
    												 'MarkHistory',
                                                      $this->getService()
                                                           ->quoteInto('SSID=?', $this->SSID)));

        $scale = false;
        if (count($model->lessons)) {
            $scale = $model->lessons->current()->getScale();
        }
        if (count($model->teachers) && count($model->markHistory)){
            foreach ($model->markHistory as $key => $score) {
                $score->teacherName = $model->teachers[$key]->getName();
                $score->mark = HM_Scale_Value_ValueModel::getTextStatus($scale, $score->mark);
                $score->updated = date('d.m.Y H:i:s', strtotime($score->updated)); // как правильно?
            }
            return $model->markHistory;
        }

        return false;
    }

    public function getScoreHistoryTable()
    {
        $data = $this->getScoreHistory();
        if ( count($data) ) {
            $text = '<table cellspacing=10><tr><th>' . _('Дата') . '</th><th>' . _('Оценка') . '</th><th>' . _('Кто выставил') . '</th></tr>';
            foreach ($data as $historyItem) {
                $text .= "<tr><td align='center'>{$historyItem->updated}</td><td align='center'>{$historyItem->mark}</td><td align='center'>{$historyItem->teacherName}</td></tr>";
            }
            $text .= '</table>';
            return $text;
        }

        return _('Нет данных');
    }

    public function getServiceName()
    {
        return 'LessonAssign';
    }

    public function getCachedLaunchCondition()
    {
        if (!$lesson = $this->getLesson()) return false;

        $relationTitle = $this->getCachedValue('lessonId2Title', $lesson->cond_sheid);
        if (!$relationTitle) return false;

        return $lesson->formatCondition($relationTitle);
    }

    public function getCachedTeacherUser()
    {
        if ($user = $this->getCachedValue('lessonId2TeacherUser', $this->SHEID)) {
            return $user;
        }
        return false;
    }

    public function getCachedLogEnabled()
    {
        if (!$lesson = $this->getLesson()) return false;

        if ($lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
            if ($questSettings = $this->getCachedValue('lessonId2QuestSettings', $lesson->SHEID)) {
                return isset($questSettings['show_log']) ? $questSettings['show_log'] : false;
            }
        }

        return false;
    }

    public function getCachedMarkHistory()
    {
        if ($markHistory = $this->getCachedValue('lessonId2MarkHistory', $this->SHEID)) {
            if (isset($markHistory[$this->SSID])) {
                return $markHistory[$this->SSID];
            }
        }
        return false;
    }

    public function getBeginEnd()
    {
        $beginDate = HM_Model_Abstract::date($this->begin_personal);
        $endDate = HM_Model_Abstract::date($this->end_personal);

        $beginTime = $this->timeWithoutSeconds($this->begin_personal);
        $endTime = $this->timeWithoutSeconds($this->end_personal);

        if($beginTime !== false) {
            $beginDate = sprintf(_("%s %s"), $beginDate, $beginTime);
            $endDate = sprintf(_("%s %s"), $endDate, $endTime);
        }

        $hasBeginPersonal = (strtotime($this->begin_personal) && strtotime($this->begin_personal) > 0);
        $hasEndPersonal = (strtotime($this->end_personal) && strtotime($this->end_personal) > 0);

        if (!$hasBeginPersonal && !$hasEndPersonal) {
            $return = false; // не ограничено
        } elseif (!$hasBeginPersonal) {
            $return['end'] = $endDate; // по
        } elseif (!$hasEndPersonal) {
            $return['begin'] = $beginDate; // c
        } elseif ($beginDate == $endDate)	{
            $return['begin'] = $return['end'] = $beginDate; // один день
        } else {
            $return = [
                'begin' => $beginDate,
                'end' => $endDate
            ];
        }
        return $return;
    }

    public function getBeginTime()
    {
        $hasBeginPersonal = (strtotime($this->begin_personal) && strtotime($this->begin_personal) > 0);
        $beginTime = HM_Model_Abstract::time($this->begin_personal);

        if(!$hasBeginPersonal) {
            return false;
        } else {
            return $beginTime;
        }
    }

    public function getEndTime()
    {
        $endTime = HM_Model_Abstract::time($this->end_personal);
        $hasEndPersonal = (strtotime($this->end_personal) && strtotime($this->end_personal) > 0);

        if(!$hasEndPersonal) {
            return false;
        } else {
            return $endTime;
        }
    }

    // DEPRECATED!!!
    // для vue нужны структурированные данные, не строка
    // используйте getBeginEnd()
    public function formatBeginEnd()
    {
        $beginDate = HM_Model_Abstract::date($this->begin_personal);
        $endDate = HM_Model_Abstract::date($this->end_personal);
        $hasBeginPersonal = (strtotime($this->begin_personal) && strtotime($this->begin_personal) > 0);
        $hasEndPersonal = (strtotime($this->end_personal) && strtotime($this->end_personal) > 0);

        if (!$hasBeginPersonal && !$hasEndPersonal) {
            $return = _('Не ограничено');
        } elseif (!$hasBeginPersonal) {
            $return = sprintf(_("по %s"), $endDate);
        } elseif (!$hasEndPersonal) {
            $return = sprintf(_("с %s"), $beginDate);
        } elseif ($beginDate == $endDate)	{
            $return = sprintf(_("%s"), $beginDate);
        } else {
            $return = sprintf(_("с %s по %s"), $beginDate, $endDate);
        }
        return $return;
    }


    public function getRestDefinition()
    {
        /** @var HM_Lesson_LessonModel $lesson */
        $lesson = $this->getLesson();
        return [
            'subjectAssignmentId' => $this->SHEID,
            'title' => $lesson->title,
            'datetime_begin' => $lesson->getBegin(true),
            'datetime_end' => $lesson->getEnd(true),
            'status' => $this->getRestStatus(),
            'result' => '__todo__'
        ];
    }

    private function getRestStatus($status = null)
    {
        $statuses = self::getRestStatuses();

        if ($status == NULL) {
            $status = $this->period;
        }

        return $statuses[$status];
    }

    static public function getRestStatuses()
    {
        return [
            self::PROGRESS_STATUS_NOSTART  => 'future',
            self::PROGRESS_STATUS_INPROCESS => 'active',
            self::PROGRESS_STATUS_DONE => 'passed'
        ];
    }
}
