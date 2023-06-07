<?php
class HM_Room_RoomModel extends HM_Model_Abstract
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    const TYPE_LECTURE_ROOM = 0;
    const TYPE_SEMINAR_ROOM = 1;
    const TYPE_STUDY_CLASS = 2;
    const TYPE_LABORATORY = 3;
    const TYPE_WORK_ROOM = 4;

    protected $_primaryName = 'rid';

    public function getServiceName()
    {
        return 'Room';
    }

    static public function getStatuses()
    {
        return array(
            self::STATUS_DISABLED => _('Недоступна'),
            self::STATUS_ENABLED => _('Доступна')
        );
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_LECTURE_ROOM => _('Лекционная аудитория'),
            self::TYPE_SEMINAR_ROOM => _('Семинарская аудитория'),
            self::TYPE_STUDY_CLASS => _('Учебный класс'),
            self::TYPE_LABORATORY => _('Лаборатория'),
            self::TYPE_WORK_ROOM => _('Рабочее помещение')
        );
    }

    public function getStatus($status = null)
    {
        if (null == $status) {
            $status = $this->status;
        }
        $statuses = self::getStatuses();
        return $statuses[$status];
    }

    public function getType($type = null)
    {
        if (null == $type) {
            $type = $this->type;
        }
        $types = self::getTypes();
        return $types[$type];
    }
}