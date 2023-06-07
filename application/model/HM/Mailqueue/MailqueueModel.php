<?php
class HM_Mailqueue_MailqueueModel extends HM_Model_Abstract
{
//    const STATUS_DISABLED = 0;

    protected $_primaryName = 'id';

    public function getServiceName()
    {
        return 'Mailqueue';
    }
/*
    static public function getStatuses()
    {
        return array(
            self::STATUS_DISABLED => _('Недоступна'),
            self::STATUS_ENABLED => _('Доступна')
        );
    }
*/
}