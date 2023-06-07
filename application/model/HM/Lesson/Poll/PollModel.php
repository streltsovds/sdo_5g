<?php
/*
 * Опрос
 */

class HM_Lesson_Poll_PollModel extends HM_Lesson_LessonModel
{

	// от старых опросов, не используется
    const NOTICE_NONE     = 0;
    const NOTICE_ASSIGNED = 1;
    const NOTICE_MARKED   = 2;
    const NOTICE_DATE     = 3;
    const NOTICE_REPEAT   = 4;
    // при назначении, - с даты доступности опроса, - не отправлять, - повторять уведомление каждые _ дней;

    static public function getNotices()
    {
        return array(
            self::NOTICE_NONE     => _('Не отправлять'),
            self::NOTICE_ASSIGNED => _('При назначении опроса'),
            //self::NOTICE_MARKED   => _('При выставлении итоговой оценки'),
            self::NOTICE_REPEAT   => _('Повторять уведомление каждые N дней с даты назначения опроса'), // @TODO Сколько дней??
            self::NOTICE_DATE     => _('Повторять уведомление каждые N дней с даты доступности опроса'),
        );
    }

    public function getType()
    {
        return HM_Event_EventModel::TYPE_POLL;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/poll.svg";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
        $path = isset($redirectUrl['path']) ? $redirectUrl['path'] : '';
        $query = isset($redirectUrl['query']) ? $redirectUrl['query'] : '';
        $redirectUrl = $path . '?' . $query;

        $params = [
            'module' => 'quest',
            'controller' => 'lesson',
            'action' => 'info',
            'quest_id' => $this->getModuleId(),
            'lesson_id' => $this->SHEID,
            'redirect_url' => urlencode($redirectUrl)
        ];
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params, null, true));
    }

    public function getQuestContext()
    {
        return array(
            'context_type' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING, 
            'context_event_id' => $this->SHEID
        );
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params));

    }
    
    public function isResultInTable()
    {
        return true;
    }
    
    
    public function isFreeModeEnabled()
    {
        return false;
    }

    public function isExecutable($fromQuest = false)
    {
        if (!parent::isExecutable()) {
            return false;
        }
        
        if (!$quest = Zend_Registry::get('serviceContainer')->getService('Quest')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $this->getModuleId())
        )) {
            throw new HM_Exception(_('Опрос не найден'));
            return false;
        }
        return true;
    }
    
}