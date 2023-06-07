<?php
class HM_Tc_Application_ApplicationModel extends HM_Model_Abstract
{
    protected $_primaryName = 'application_id';

    //Для очных занятий: Обязательное/Дополнительное/Корпоративное
    const CATEGORY_REQUIRED   = 1;
    const CATEGORY_ADDITION   = 2;
    const CATEGORY_RECOMENDED = 3;

    const STATUS_INACTIVE = 0; // создано, но никем не согласовано (напр., рекомендованное)
    const STATUS_ACTIVE   = 1; // подтвержденное рекомендованное, либо созданная вручную заявка
    const STATUS_COMPLETE = 2; // когда согласована вся конс.заявка

    const STUDY_STATUS_NONE = 0;
    const STUDY_STATUS_SESSION   = 1;
    const STUDY_STATUS_COMPLETE = 2;

    const DEFAULT_COST_ITEM = 1;
    const CONSULT_COST_ITEM = 2;
    const CULTURE_COST_ITEM = 3;
    const PROFCOM_COST_ITEM = 4; // PROFCOM = PROFESSIONAL COMPETITIONS
    const ANOTHER_COST_ITEM = 5;

    const COST_ITEM_STUDY = 1;
    const COST_ITEM_CONSULT = 2;
    const COST_ITEM_CULTURE = 3;
    const COST_ITEM_COMPET = 4;
    const COST_ITEM_MISC = 5;

    // типы финансирования
    const PAYMENT_COMPANY  = 0;
    const PAYMENT_EMPLOYEE = 1;
    const PAYMENT_PARTIAL  = 2;

    public function getServiceName()
    {
        return 'TcApplication';
    }

    public static function getStatuses()
    {
        return array(
            self::STATUS_INACTIVE => _('Неактивна'),
            self::STATUS_ACTIVE => _('Активна')
        );
    }

    public static function getPaymentTypes()
    {
        return array(
            self::PAYMENT_COMPANY  => _('за счёт компании'),
            self::PAYMENT_EMPLOYEE => _('за свой счёт'),
            self::PAYMENT_PARTIAL  => _('частично за свой счёт')
        );
    }

    public static function getApplicationCategory($apCategory,$application = 'tc')
    {
        switch ($application) {
            case 'sc':
                $categories = self::getScApplicationCategories();
                break;
            default:
                $categories = self::getApplicationCategories();
        }
        return isset($categories[$apCategory]) ? $categories[$apCategory] : "";
    }

    public static function getApplicationCategories()
    {
        return array(
            self::CATEGORY_REQUIRED   => _('Обязательное'),
//            self::CATEGORY_RECOMENDED => _('Рекомендованное'), // по большому счёту неважно, рекомендовала ли его система или вручную
            self::CATEGORY_ADDITION   => _('Инициативное'),
        );
    }

    public static function getStudyStatuses() {

        return array(
            self::STUDY_STATUS_NONE   => _('Сессия не назначена'),
            self::STUDY_STATUS_SESSION => _('Сессия назначена'),
            self::STUDY_STATUS_COMPLETE   => _('Обучение пройдено'),
        );
    }

    public static function getCostItems($full = true)
    {
        $result = array(
            self::COST_ITEM_STUDY  => _('Аттестация и обучение персонала'),
            self::COST_ITEM_CONSULT  => _('Консультационные услуги'),
        );
        if ($full) {
            $result += array(
                self::COST_ITEM_CULTURE  => _('Культурно-массовые мероприятия'),
                self::COST_ITEM_COMPET  => _('Проф.соревнования'),
                self::COST_ITEM_MISC  => _('Прочие расходы'),
            );
        }
        return $result;
    }

    public static function getCostItemsSubject()
    {
        return array(
            self::COST_ITEM_STUDY => self::COST_ITEM_STUDY,
            self::COST_ITEM_CONSULT => self::COST_ITEM_CONSULT,
        );
    }

    public static function getCostItemsNonSubject()
    {
        return array(
            self::COST_ITEM_CULTURE => self::COST_ITEM_CULTURE,
            self::COST_ITEM_COMPET => self::COST_ITEM_COMPET,
            self::COST_ITEM_MISC => self::COST_ITEM_MISC,
        );
    }

    public static function getWorkerCostItems()
    {
        return array(3,5,9,12);
    }

    public static function getCostItemsTree()
    {
        return array(
            1 => array(
                'number'    => _('1.1'),
                'name'      => _('Ротация и стажировки'),
                'top_level' => 1,
                'costItems' => array(1)
            ),
            2 => array(
                'number'    => _('1.2'),
                'name'      => _('Подготовка и обучение кадров, в т.ч.:'),
                'top_level' => 1,
                'costItems' => array(2,3,4,5,6,7,8,9,10,11,12,13)
            ),
            3 => array(
                'number'    => _('1.2.1'),
                'name'      => _('Обязательное обучение, в т.ч.:'),
                'top_level' => 0,
                'costItems' => array(2,3,4,5)
            ),
            4 => array(
                'number'    => _('1.2.1.1'),
                'name'      => _('РСС'),
                'top_level' => 0,
                'costItems' => array(2,4)
            ),
            5 => array(
                'number'    => _('1.2.1.2'),
                'name'      => _('Рабочие'),
                'top_level' => 0,
                'costItems' => array(3,5)
            ),
            6 => array(
                'number'    => _('1.2.2'),
                'name'      => _('Корпоративное обучение'),
                'top_level' => 0,
                'costItems' => array(6,7,8,9)
            ),
            7 => array(
                'number'    => _('1.2.3'),
                'name'      => _('Развитие кадрового резерва'),
                'top_level' => 0,
                'costItems' => array(10)
            ),
            8 => array(
                'number'    => _('1.2.4'),
                'name'      => _('Дополнительное обучение'),
                'top_level' => 0,
                'costItems' => array(11,12)
            ),
            9 => array(
                'number'    => _('1.2.5'),
                'name'      => _('Развитие и вовлечение молодых специалистов'),
                'top_level' => 0,
                'costItems' => array(13)
            ),

            10 => array(
                'number'    => _('1.3'),
                'name'      => _('Взаимодействие с вузами, в т.ч.:'),
                'top_level' => 1,
                'costItems' => array(14,15)
            ),
            11 => array(
                'number'    => _('1.3.1'),
                'name'      => _('Благотворительность'),
                'top_level' => 0,
                'costItems' => array(14)
            ),
            12 => array(
                'number'    => _('1.3.2'),
                'name'      => _('Разное'),
                'top_level' => 0,
                'costItems' => array(15)
            ),
            13 => array(
                'number'    => _('1.4'),
                'name'      => _('Конкурс "Лучший по профессии"'),
                'top_level' => 1,
                'costItems' => array(16)
            ),
        );
    }

    public static function getCostItem($costItem)
    {
        $costItems = self::getCostItems();
        return isset($costItems[$costItem]) ? $costItems[$costItem] : "";
    }

    public static function getPaymentType($type)
    {
        $types = self::getPaymentTypes();
        return isset($types[$type]) ? $types[$type] : "";
    }

}