<?php
class HM_Lesson_Log_LogService extends HM_Service_Abstract
{
    public function logCurrentUser($lessonId)
    {
        return $this->insert([
            'lesson_id' => $lessonId,
            'user_id' => $this->getService('User')->getCurrentUserId(),
            'date_start' => (string) new HM_Date(),
        ]);
    }
}
