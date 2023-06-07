<?php
class HM_Programm_ProgrammModel extends HM_Model_Abstract
{
//     const TYPE_MIXED      = 0;
    const TYPE_ELEARNING  = 0; // els
    const TYPE_ASSESSMENT = 1; // регулярные оценки
    const TYPE_RECRUIT    = 2; // программы отбора
    const TYPE_ADAPTING   = 3; // программы адаптации
    const TYPE_AGREEMENT_CLAIMANTS   = 4; // программы согласования заявок на обучение
    const TYPE_ROTATION   = 5; // программы ротации
    const TYPE_RESERVE    = 6; // программы резерва

    // для тех программ, которые связаны 1-1 с каким-то объектом
    const ITEM_TYPE_CATEGORY = 0; // категория доложности
    const ITEM_TYPE_PROFILE = 1; // профиль должности 
    const ITEM_TYPE_VACANCY = 2; // сессия подбора
    const ITEM_TYPE_NEWCOMER = 3; // сессия адаптации
    const ITEM_TYPE_SUBJECT = 4; // учебный курс/сессия (в случае TYPE_AGREEMENT_CLAIMANTS; не путать с TYPE_ELEARNING, там программа не связана с каким-либо курсом, а состоит из курсов)
    const ITEM_TYPE_RESERVE = 5; // сессия кадрового резерва

    const MODE_STRICT_OFF = 0; // i.e. arbitrary
    const MODE_STRICT_ON = 1;

    const MODE_FINALIZE_OFF = 0; // do not add final event
    const MODE_FINALIZE_ON = 1;

    const BUILTIN_TYPE_ASSESSMENT = 1; // дефолтная программа оценки - автоматически назначается всем профилям

    const THUMB_WIDTH = 640;
    const THUMB_HEIGHT = 320;

    protected $_primaryName = 'programm_id';

    // программы профиля; напр., программа согласования заявок здесь не должна быть
    static public function getTypes($onlyEvaluation = true)
    {
        $return = array(
            self::TYPE_ASSESSMENT => _('Программа регулярной оценки'),
            self::TYPE_ADAPTING => _('Программа адаптации'),
            self::TYPE_RECRUIT => _('Программа подбора'),
            self::TYPE_RESERVE => _('Программа оценки КР'),
        );

        if (!$onlyEvaluation) {
            $return[self::TYPE_ELEARNING] = _('Программа обучения');
        }
        return $return;
    }

    static public function getProgrammTitle($programmType, $itemType, $itemTitle)
    {
        $titles = array(
            self::TYPE_AGREEMENT_CLAIMANTS => array(
                HM_Programm_ProgrammModel::ITEM_TYPE_SUBJECT => _('Cогласование заявок по курсу "%s"'),
            ),
            self::TYPE_ELEARNING => array(
                HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY => _('Программа начального обучения категории "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE => _('Программа начального обучения профиля "%s"'),
            ),
            self::TYPE_ASSESSMENT => array(
                HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY => _('Программа оценки категории "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE => _('Программа оценки профиля "%s"'),
            ),
            self::TYPE_ADAPTING => array(
                HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY => _('Адаптация категории "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE => _('Адаптация профиля "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER => _('Адаптации должности "%s"'),
            ),
            self::TYPE_RECRUIT => array(
                HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY => _('Подбор категории "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE => _('Подбор профиля "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY => _('Подбора на должность "%s"'),
            ),
            self::TYPE_RESERVE => array(
                HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY => _('Оценка КР категории "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE => _('Оценка КР профиля "%s"'),
                HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE => _('Оценка КР %s'),
            ),
        );
        return sprintf($titles[$programmType][$itemType], $itemTitle);
    }

    static public function getItemAttribute($itemType)
    {
        switch ($itemType) {
            case self::ITEM_TYPE_CATEGORY:
                return 'category_id';
                break;
            case self::ITEM_TYPE_PROFILE:
                return 'profile_id';
                break;
            case self::ITEM_TYPE_VACANCY:
                return 'vacancy_id';
                break;
            case self::ITEM_TYPE_NEWCOMER:
                return 'newcomer_id';
                break;
            case self::ITEM_TYPE_SUBJECT:
                return 'subid';
                break;
        }
    }

    public function isEvaluation()
    {
        return in_array($this->programm_type, array(
            HM_Programm_ProgrammModel::TYPE_ASSESSMENT,
            HM_Programm_ProgrammModel::TYPE_RECRUIT,
            HM_Programm_ProgrammModel::TYPE_ADAPTING,
            HM_Programm_ProgrammModel::TYPE_RESERVE,
        ));
    }

    public function getProgrammName()
    {

        $result = $this->name;
        if (null !== $this->item_id) {

            $itemTitle = null;
            if ($this->item_type == static::ITEM_TYPE_CATEGORY) {
                $service = Zend_Registry::get('serviceContainer')->getService('AtCategory');
                $obj = $service->getOne($service->find($this->item_id));
                if (false !== $obj) $itemTitle = $obj->name;
            } elseif ($this->item_type == static::ITEM_TYPE_PROFILE) {
                $service = Zend_Registry::get('serviceContainer')->getService('AtProfile');
                $obj = $service->getOne($service->find($this->item_id));
                if (false !== $obj) $itemTitle = $obj->name;
            } elseif ($this->item_type == static::ITEM_TYPE_NEWCOMER) {

            } elseif ($this->item_type == static::ITEM_TYPE_SUBJECT) {
                $service = Zend_Registry::get('serviceContainer')->getService('Subject');
                $obj = $service->getOne($service->find($this->item_id));
                if (false !== $obj) $itemTitle = $obj->name;
            } elseif ($this->item_type == static::ITEM_TYPE_VACANCY) {

            }

            if (null !== $itemTitle) $result = static::getProgrammTitle($this->programm_type, $this->item_type, $itemTitle);
        }
        return $result;
    }


    // тонкая настройка критериев через карандашик доступна только на уровне вакансии; 
    // на уровне профиля набор критериев определяется на отдельных страницах
    public function isEditCriteriaFromProgramm()
    {
        if ($this->item_type == self::ITEM_TYPE_VACANCY) return true;
        return false;
    }

    public function getIcon()
    {
        if ($icon = $this->getUserIcon()) {
            return $icon;
        } else {
            return $this->getDefaultIcon();
        }
    }

    public function getUserIcon($full = false)
    {
        $full = $full ? '-full' : '';
        $path = HM_Programm_ProgrammModel::getIconFolder($this->programm_id) . $this->programm_id . $full . '.jpg';
        if (is_file($path)) {
            return preg_replace('/^.*public\//', Zend_Registry::get('config')->url->base, $path) . '?_=' . @filemtime($path);
        }
        return null;
    }

    public function getDefaultIcon() {
        if ($this->programm_type == HM_Programm_ProgrammModel::TYPE_ELEARNING) {
            if ($this->item_type == static::ITEM_TYPE_PROFILE) {
                $service = Zend_Registry::get('serviceContainer')->getService('AtProfile');
                $profile = $service->getOne($service->find($this->item_id));
                return $profile->getUserIcon() ?: Zend_Registry::get('config')->url->base.'images/icons/library.svg';
            }
        }
        return Zend_Registry::get('config')->url->base.'images/icons/library.svg';
    }

    public function getIconHtml()
    {
        $result = '';
        if (!$icon = $this->getUserIcon()) {
            $icon = $this->getDefaultIcon();
            $result.= '<v-img src="'.$icon.'" :width="\'100%\'" :height="\'150\'" color="primary" class="primary default-subject-icon"></v-img>';
        } else {
            $result.='<v-img src="'.$icon.'" :width="\'100%\'" :height="\'150\'"></v-img>';
        }

        return $result;
    }

    public static function getIconFolder($programId = 0)
    {
        $folder = Zend_Registry::get('config')->path->upload->program;
        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;
        $folder = $folder . floor($programId / $maxFilesPerFolder) . '/';

        if (!is_dir($folder)) {
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }

        return $folder;
    }
}