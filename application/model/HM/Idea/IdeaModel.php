<?php
class HM_Idea_IdeaModel extends HM_Model_Abstract
{
    const STATE_NEW         = 0;
    const STATE_SUPPORTED   = 1;
    const STATE_APPROVED    = 2;
    const STATE_DECLINED    = 3;
    const STATE_ARCHIVED    = 4;

//    static public $events = null;

    static public function getStates()
    {
        return array(
            self::STATE_NEW     => _('Новая'),
            self::STATE_SUPPORTED    => _('Поддержанная'),
            self::STATE_APPROVED   => _('Одобренная'),
            self::STATE_DECLINED  => _('Отклоненная'),
            self::STATE_ARCHIVED      => _('Архивная'),
        );
        
    }
}