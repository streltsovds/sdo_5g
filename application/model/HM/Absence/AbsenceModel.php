<?php
class HM_Absence_AbsenceModel extends HM_Model_Abstract
{
    const TYPE_SICK_LEAVE       = 0;
    const TYPE_VACATION         = 1;
    const TYPE_UNPAID_LEAVE     = 2;
    const TYPE_STUDY_LEAVE      = 3;
    const TYPE_MATERNITY_LEAVE  = 4;
    const TYPE_SOCIAL_LEAVE     = 5;
    const TYPE_PUBLIC_DUTIES    = 6;
    const TYPE_OUTAGE           = 7;
    const TYPE_HOOKY            = 8;
    const TYPE_TRAINING         = 9;
    const TYPE_BUSINESS_TRIP    = 10;

    const TYPE_WATCH            = 11; // вахта

    /**
     * Возвращает массив строковых представлений
     * типов отсутствий на рабочем месте для интерфейса пользователя
     * @return array
     */
    static public function getTypes()
    {
        return array(
            self::TYPE_SICK_LEAVE      => _('Больничный'),
            self::TYPE_VACATION        => _('Плановый отпуск'),
            self::TYPE_UNPAID_LEAVE    => _('Отпуск за свой сч.'),
            self::TYPE_STUDY_LEAVE     => _('Учебный отпуск'),
            self::TYPE_MATERNITY_LEAVE => _('Декретный отпуск'),
            self::TYPE_SOCIAL_LEAVE    => _('Социальный отпуск'),
            self::TYPE_PUBLIC_DUTIES   => _('Гос. обязанности'),
            self::TYPE_OUTAGE          => _('Простой'),
            self::TYPE_HOOKY           => _('Невыясненная причина'),
            self::TYPE_TRAINING        => _('Пов. квалификации'),
            self::TYPE_BUSINESS_TRIP   => _('Командировка'),
            self::TYPE_WATCH           => _('Межвахтовый отпуск')
        );
    }

    /**
     * Возвращает массив строковых представлений
     * типов отсутствий на рабочем месте для сопоставления в парсере
     * @return array
     */
    static public function getMapperTypes()
    {
        return array(
            self::TYPE_SICK_LEAVE      => _('Больничный'),
            self::TYPE_VACATION        => _('Плановый отпуск'),
            self::TYPE_UNPAID_LEAVE    => _('Отпуск за свой счет'),
            self::TYPE_STUDY_LEAVE     => _('Учебный отпуск'),
            self::TYPE_MATERNITY_LEAVE => _('Декретный отпуск'),
            self::TYPE_SOCIAL_LEAVE    => _('Социальный отпуск'),
            self::TYPE_PUBLIC_DUTIES   => _('Исполнение гос. обязанностей'),
            self::TYPE_OUTAGE          => _('Простой'),
            self::TYPE_HOOKY           => _('Отсутствие по невыясненной причине'),
            self::TYPE_TRAINING        => _('Повышение квалификации'),
            self::TYPE_BUSINESS_TRIP   => _('Командировка')
        );
    }

    /**
     * Возвращает наименование типа отсутствия по его typeId
     * @param $typeId
     * @return string
     */
    static public function getType($typeId)
    {
        $types = self::getTypes();
        return (isset($types[$typeId]))? $types[$typeId] : '';
    }

    /**
     * Возвращает typeId типа отсутствия по его наименованию
     * @param $typeName
     * @return int
     */
    static public function getTypeId($typeName)
    {
        $types = array_flip(self::getMapperTypes());
        return (isset($types[$typeName]))? $types[$typeName] : 0;
    }
}