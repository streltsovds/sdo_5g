<?php
class HM_Meeting_Assign_AssignModel extends HM_Model_Abstract
{
    const PROGRESS_STATUS_NOSTART   = 0;
    const PROGRESS_STATUS_INPROCESS = 1;
    const PROGRESS_STATUS_DONE      = 2;

    public function getMeeting()
    {
        if (isset($this->meetings)) {
            return $this->meetings->current();
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
                                    ->fetchAllHybrid(array('MarkHistory','Meeting'),
    												 'Moderator',
    												 'MarkHistory',
                                                      $this->getService()
                                                           ->quoteInto('SSID=?', $this->SSID)));

        $scale = false;
        if (count($model->meetings)) {
            $scale = $model->meetings->current()->getScale();
        }
        if (count($model->moderators) && count($model->markHistory)){
            foreach ($model->markHistory as $key => $score) {
                $score->moderatorName = $model->moderators[$key]->getName();
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
                $text .= "<tr><td align='center'>{$historyItem->updated}</td><td align='center'>{$historyItem->mark}</td><td align='center'>{$historyItem->moderatorName}</td></tr>";
            }
            $text .= '</table>';
            return $text;
        }

        return _('Нет данных');
    }

    public function getServiceName()
    {
        return 'MeetingAssign';
    }
}