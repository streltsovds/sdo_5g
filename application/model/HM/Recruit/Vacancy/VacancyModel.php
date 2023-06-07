<?php 
class HM_Recruit_Vacancy_VacancyModel extends HM_Model_Abstract implements HM_Process_Model_Interface
{
    const DELIMETER = 100;
//     Агрегатные состояния вакансии полностью совпадают с оц. сессиями
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;  
    const STATE_CLOSED = 2;    
    const STATE_EXTERNAL = 3;    
    
    protected $_primaryName = 'vacancy_id';
    
    public function getServiceName()
    {
        return 'RecruitVacancy';
    }    
    
    public function getName()
    {
//        return sprintf(_('Подбор кандидатов на вакансию &laquo;%s&raquo;'), $this->name);
        return $this->name;
    }

    // @todo: нужна своя иконка
    public function getIcon()
    {
        return Zend_Registry::get('config')->url->base . "images/session-icons/session-" . HM_Programm_ProgrammModel::TYPE_RECRUIT . ".png";
    }

    public function getIconHtml()
    {
        $icon = $this->getIcon();
        $defaultIconClass = 'hm-subject-icon-default';
        $defaultIconStyle = sprintf('background-color: #%s;', $this->base_color ? $this->base_color : '555');

        return sprintf('<div style="background-image: url(%s); %s; background-repeat: no-repeat; background-size: cover;   background-position: center; height: 120px; " class="hm-subject-icon %s" title="%s"></div>', $icon, $defaultIconStyle, $defaultIconClass, $this->name);
    }

    static public function getStates()
    {
        return array(
            self::STATE_PENDING => _('Ожидание'),
            self::STATE_ACTUAL => _('В работе'),
            self::STATE_CLOSED => _('Закончена'),
            self::STATE_EXTERNAL => _('Не обработана'),
        );
    }

    static public function getStatesCustom()
    {
        return array(
            self::STATE_EXTERNAL => _('Не начата'),
            self::STATE_ACTUAL => _('Идёт'),
            self::STATE_CLOSED => _('Закончена'),
            self::STATE_EXTERNAL => _('Внешняя'),
        );
    }

    static public function getStateTitle($index)
    {
        $states = HM_Recruit_Vacancy_VacancyModel::getStates();
        return $states[$index];
    } 

    public function getCreator()
    {
        $user = Zend_Registry::get('serviceContainer')->getService('User')->find($this->recruiter_user_id)->current();
        if($user){
            return $user->getName();
        }else{
            return false;
        }
    }

    public function getParentPositionName()
    {
        $position = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($this->parent_position_id)->current();
        if($position){
            return $position->name;
        }else{
            return false;
        }
    }


    public function getOpenDate()
    {
        return date('d.m.Y', strtotime($this->open_date));
    }

   public function getCloseDate()
    {
        return strtotime($this->close_date) ? date('d.m.Y', strtotime($this->close_date)) : '';
    }

    public function getStateSwitcher()
    {
        // дабы не копипастить
        $vacancySession         = new HM_At_Session_SessionModel($this->getValues());
        $vacancySession->state = (int) $vacancySession->status;
        return $vacancySession->getStateSwitcher();
    }
    
    static public function getVariant($variantId, $method)
    {
        $variants = self::$method();
        if (isset($variants[$variantId])) {
            return $variants[$variantId];
        }
        return false;        
    }        

    static public function getReasonVariants()
    {
        return array(
           1 => _('Вакансия в связи с добавлением ставки'), 
           2 => _('Вакансия в связи с увольнением основного пользователя'),
           3 => _('Резерв'),
           4 => _('Срочный ТД на период отсутствия основного пользователя'),
        );
    }

    static public function getStaffCountVariants()
    {
        return array(
           1 => _(sprintf(_('от %s до %s'), 1, 5)), 
           2 => _(sprintf(_('от %s до %s'), 6, 10)), 
           3 => _(sprintf(_('от %s до %s'), 11, 50)), 
           4 => _(sprintf(_('более %s'), 50)), 
        );
    }

    static public function getWorkModeVariants()
    {
        return array(
           1 => _('Пятидневка'), 
           2 => _('Сменный график'), 
           3 => _('Другое'), 
        );
    }

    static public function getBusinessTripVariants()
    {
        return array(
           1 => _('Не требуется'), 
           2 => _('Требуется один-два раза в месяц'), 
           3 => _('Требуется один-два раза в год'), 
           4 => _('Разъездной характер работы'), 
        );
    }

    // DEPRECATED! теперь список тянется с HH динамически и кэшируется в classifiers
    static public function getExperienceVariants()
    {
        return array(
            7 => _('Автомобильный бизнес'),
            4 => _('Административный персонал'),
            5 => _('Банки, инвестиции, лизинг'),
            8 => _('Безопасность'),
            2 => _('Бухгалтерия, управленческий учет, финансы предприятия'),
            9 => _('Высший менеджмент'),
            16 => _('Государственная служба, некоммерческие организации'),
            10 => _('Добыча сырья'),
            27 => _('Домашний персонал'),
            26 => _('Закупки'),
            25 => _('Инсталляция и сервис'),
            1 => _('Информационные технологии, интернет, телеком'),
            11 => _('Искусство, развлечения, масс-медиа'),
            12 => _('Консультирование'),
            3 => _('Маркетинг, реклама, PR'),
            13 => _('Медицина, фармацевтика'),
            14 => _('Наука, образование'),
            15 => _('Начало карьеры, студенты'),
            17 => _('Продажи'),
            18 => _('Производство'),
            29 => _('Рабочий персонал'),
            24 => _('Спортивные клубы, фитнес, салоны красоты'),
            19 => _('Страхование'),
            20 => _('Строительство, недвижимость'),
            21 => _('Транспорт, логистика'),
            22 => _('Туризм, гостиницы, рестораны'),
            6 => _('Управление персоналом, тренинги'),
            23 => _('Юристы'),
        );
    }
    
    static public function getContractVariants()
    {
        return array(
            1 => _('Бессрочный'),
            2 => _('Срочный'),
            3 => _('Подрядный'),
        );
    }
    
    static public function getSearchChannelVariants()
    {
        return array(
            'search_channels_corporate_site'=> _('На корпоративном сайте'),
            'search_channels_recruit_sites' => _('На сайтах для поиска работы'),
            'search_channels_papers' => array(
                _('В газетах'),
                array('search_channels_papers_list' => _('газеты:')),
            ),
            'search_channels_universities' => array(
                _('В учебных заведениях города'),
                array('search_channels_universities_list' => _('учебные заведения:')),
            ),
            'search_channels_email' => _('Рассылка электронного письма пользователям'),
            'search_channels_inner' => _('Поиск кандидатов среди пользователей Предприятия'),
            'search_channels_outer' => _('Поиск кандидатов среди пользователей других компаний'),
        );         
    }
}