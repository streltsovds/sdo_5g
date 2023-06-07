<?php
class HM_Tc_Subject_StudyCenter_SubjectModel extends HM_Tc_Subject_SubjectModel
{
    // Тип обучения
    const TYPE_FULLTIME = 0; //Очное
    const TYPE_DISTANCE = 1; // Дистанционное
    const TYPE_MIXED    = 2; // Смешанное
    //Для очных занятий: Обязательное/Дополнительное/Корпоративное
    const FULLTIME_CATEGORY_NECESSARY = 1;
    const FULLTIME_CATEGORY_ADDITION  = 2;
    const FULLTIME_CATEGORY_CORPORATE = 3;
    const FULLTIME_CATEGORY_PRIMARY = 4; //первичное обучение
    const FULLTIME_CATEGORY_TRAINING = 5; // повышение квалификации

    const FULLTIME_PRIMARY_BOTH      = 1;
    const FULLTIME_PRIMARY_PRIMARY   = 2;
    const FULLTIME_PRIMARY_SECONDARY = 3;

    const FULLTIME_CRITERION_TYPE_CRITERION = 1;
    const FULLTIME_CRITERION_TYPE_CRITERION_TEST = 2;

    const FULLTIME_STATUS_NOT_PUBLISHED = 0;
    const FULLTIME_STATUS_PUBLISHED     = 1;

    const FULLTIME_FORMAT_SEMINAR = 1;
    const FULLTIME_FORMAT_TRAINING = 2;
    const FULLTIME_FORMAT_LECTURE = 3;
    const FULLTIME_FORMAT_MASTER_CLASS = 4;
    const FULLTIME_FORMAT_BUSINESS_SIMULATION = 5;

    public function getDefaultUri($force = false)
    {
        $aclService = Zend_Registry::get('serviceContainer')->getService('Acl');
        $currentUserRole = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
        $isEnduser = $aclService->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        if (!empty($this->default_uri) && $isEnduser) {
                // dirty hack
            $uri = str_replace(
                array('lesson/list/index',),
                array('lesson/list/my',),
                $this->default_uri
            );

            return $uri;

        } else {
            $view = Zend_Registry::get('view');
            return $view->url(array(
                'module'     => 'subject',
                'controller' => 'fulltime',
                'action'     => 'view',
                'baseUrl'    => 'sc',
                'subject_id' => $this->subid
            ));
        }
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_FULLTIME => _('Очный'),
            self::TYPE_DISTANCE => _('Дистанционный'),
            self::TYPE_MIXED    => _('Смешанный')
        );

    }

    public function getServiceName()
    {
        return 'TcSubject';
    }

    public function getTcProvider($asObject = false)
    {
        return $this->getService()->getStudyCenter($this->subid, $asObject);
    }

    static public function getVariant($variantId, $method)
    {
        $method = 'get' . $method . 'Variants';
        $variants = self::$method();
        if (isset($variants[$variantId])) {
            return $variants[$variantId];
        }
        return false;
    }

    static public function getVariants($method)
    {
        $method = 'get' . $method . 'Variants';
        return self::$method();
    }

    static public function getFulltimeCategoriesVariants()
    {
        return array(
            self::FULLTIME_CATEGORY_PRIMARY => _('Первичное'),
            self::FULLTIME_CATEGORY_TRAINING => _('Повышение квалификации'),
        );
    }

    static public function getFulltimeCategoriesSimpleVariants()
    {
        return array(
            self::FULLTIME_CATEGORY_NECESSARY => _('Обязательное'),
            self::FULLTIME_CATEGORY_ADDITION  => _('Дополнительное'),
            //self::FULLTIME_CATEGORY_CORPORATE => _('Корпоративное'),
        );
    }

    public function getFulltimeCategory()
    {
        return $this->getVariant($this->category, 'FulltimeCategories');
    }

    public function getFormat()
    {
        return $this->getVariant($this->format, 'FulltimeFormates');
    }

    public function getEducationType()
    {
        return $this->getVariant($this->education_type, 'FulltimeEducationTypes');
    }



    static public function getFulltimeFormatesVariants()
    {
        return array(
            self::FULLTIME_FORMAT_SEMINAR => _('Семинар'),
            self::FULLTIME_FORMAT_TRAINING => _('Тренинг'),
            self::FULLTIME_FORMAT_LECTURE => _('Лекция'),
            self::FULLTIME_FORMAT_MASTER_CLASS => _('Мастер-класс'),
            self::FULLTIME_FORMAT_BUSINESS_SIMULATION => _('Бизнес-симуляция')
        );
    }

    static public function getFulltimeStatesVariants()
    {
        return array(
            self::FULLTIME_STATUS_NOT_PUBLISHED => _('Не опубликован'),
            self::FULLTIME_STATUS_PUBLISHED => _('Опубликован'),
        );
    }

    static public function getFulltimeStatesSimpleVariants()
    {
        return array(
            self::FULLTIME_STATUS_NOT_PUBLISHED => _('Нет'),
            self::FULLTIME_STATUS_PUBLISHED => _('Да'),
        );
    }

    static public function getFulltimeCheckFormesVariants()
    {
        return array(
            1 => _('Тестирование'),
            2 => _('Опрос'),
        );
    }

    public function getCriterionName()
    {
        if (($this->criterion_type == self::FULLTIME_CRITERION_TYPE_CRITERION) && $this->criterion) {
            return $this->criterion->current()->name;
        }
        if (($this->criterion_type == self::FULLTIME_CRITERION_TYPE_CRITERION_TEST) && $this->criterionTest) {
            return $this->criterionTest->current()->name;
        }

        return '';
    }

    public function getPrice()
    {
        return number_format($this->price, 0, '.', ' ');
    }

    public function getIcon()
    {
        $path = parent::getIcon();
        if (!$path) {
            $path = ($this->isSession()) ?
                Zend_Registry::get('config')->url->base . 'images/subject-icons/fulltime-session.png' :
                Zend_Registry::get('config')->url->base . 'images/subject-icons/fulltime.png';
        }
        return $path;
    }

    public function getPrefix()
    {
        $prefix =  $this->getService()->getOne(
            $this->getService()->getService('TcPrefix')->find($this->prefix_id)
        );
        return ($prefix && $prefix->name) ? $prefix->name : '';
    }

    public function getCardValues()
    {
        $values = array(
            'getType()'             => _('Тип курса'),
            'getFulltimeCategory()' => _('Категория обучения'),
            'getStatus()'           => _('Статус'),
            'getFormat()'           => _('Формат'),
            //'getEducationType()'    => _('Тип обучения'),
            'getPrice()'            => _('Стоимость'),
            'getPrefix()'           => _('Префикс курса'),
            'longtime'              => _('Длительность курса, дней'),
            //        'getColorField()'       => _('Цвет в календаре'),
            //'description'           => _('Описание'),
        );

        return $values;
    }

    public function getTeachers()
    {

    }

    public function checkRequiredLessons($userId)
    {
        if($this->requiredLessons == null ) {
            $this->requiredLessons = $this->getService()->getRequiredLessons($this->subid);
        }
        return (count($this->requiredLessons)) ?
            $this->getService()->getService('LessonAssign')->countAll($this->getService()->quoteInto(
                array('SHEID IN (?) ', ' AND V_STATUS = ?', ' AND MID = ?'),
                array($this->requiredLessons->getList('SHEID','SHEID'), -1, $userId)
            ))
            : 0 ;
    }


    /**
     * проверяет превышение размера группы
     * @param $count - количество назначаемых
     * @return false|int
     *   false - если нет превышения || это не сессия
     */

    public function isOverLimitUsers($count)
    {
        if($this->getBaseType() != HM_Tc_Subject_StudyCenter_SubjectModel::BASETYPE_SESSION) {
            return false;
        }
        if($this->students === null) {
            $this->students = $this->getService()->getService('Student')->fetchAll(
                $this->getService()->quoteInto('CID = ?', $this->subid)
            );
        }
        return ($this->max_users < (count($this->students) + $count)) ? (int)($this->max_users - count($this->students)) : false;
    }

    public function getWrongProviderUsers($usersStudyCenters)
    {
        $wrongProviderUsers = array();
        $subjectStudyCenterId = $this->getTcProvider(true);

        foreach ($usersStudyCenters as $userId => $userStudyCenter) {
            if ($userStudyCenter != $subjectStudyCenterId->provider_id) {
                $wrongProviderUsers[] = $userId;
            }
        }
        if (!count($wrongProviderUsers)) {
            $wrongProviderUsers[] = -1;
        }
        $this->wrongProviderUsers = $this->getService()->getService('User')->fetchAll(
            $this->getService()->quoteInto('MID IN (?)', $wrongProviderUsers)
        )->asArrayOfObjects();
        return $wrongProviderUsers;
    }
}
