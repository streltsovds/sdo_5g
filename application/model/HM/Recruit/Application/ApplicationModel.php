<?php
class HM_Recruit_Application_ApplicationModel extends HM_Model_Abstract
{
    CONST STATUS_NEW       = 1;
    CONST STATUS_INWORK    = 2;
    CONST STATUS_CREATED   = 3;
    CONST STATUS_COMPLETED = 4;
    CONST STATUS_STOPPED   = 5;
    CONST STATUS_CLOSED    = 6;


    public static function getAttributeLabels()
    {
        return array(
            'recruit_application_id' => 'Id',
            'department' => _('Подразделение'),
            'department_path' => _('Подразделение'),
            'fio' => _('Инициатор'),
            'fio_manager' => _('Руководитель'),
            'vacancy_name' => _('Название вакансии'),
            'vacancy_description' => _('Описание вакансии'),
            'rv_name' => _('Сессия подбора'),
            'recruiter_fio' => _('Специалист по подбору'),
            'status' => _('Статус')
        );
    }

    public static function getLabel($label)
    {
        $labels = self::getAttributeLabels();
        if (isset($labels[$label])) {
            return $labels[$label];
        }
        return null;
    }

    public static function getStatuses()
    {
        return array(
            self::STATUS_NEW       => _('Новая'),
            self::STATUS_INWORK    => _('Принята в работу'),
            self::STATUS_CREATED   => _('Принята в работу'),
            self::STATUS_COMPLETED => _('Завершена'),
//            self::STATUS_STOPPED   => _('Приостановлена'),
//            self::STATUS_CLOSED    => _('Закрыта'),
        );
    }
    
    public static function getStatus($status)
    {
        $stauses = self::getStatuses();
        if (isset($stauses[$status])) {
            return $stauses[$status];
        }
        return null;
    }
}