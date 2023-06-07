<?php
class HM_Tc_Provider_ProviderModel extends HM_Model_Abstract
{

    const TYPE_NONE     = 2; // используется для поля provider_type сабжектов
    const TYPE_PROVIDER = 0;
    const TYPE_STUDY_CENTER = 1;

    protected $_primaryName = 'provider_id';

    const STATUS_NOT_PUBLISHED = 0;
    const STATUS_PUBLISHED = 1;


    const HARDCODED_ID_INTERNAL_STUDY = 1;

    public function getCardFields()
    {
        $fields = array(
            _('Название') =>$this->name,
            _('Краткое описание') => $this->description,
            _('Статус') => self::getStatus($this->status),
            _('Юридический адрес') => $this->address_legal,
            _('Почтовый адрес') => $this->address_postal,
            _('ИНН') => $this->inn,
            _('КПП') => $this->kpp,
            _('БИК') => $this->bik,
            _('ФИО подписанта') => $this->subscriber_fio,
            _('Должность подписанта') => $this->subscriber_position,
            _('Основание для подписанта') => $this->subscriber_reason,
            _('Номер счета') => $this->account,
            _('Номер кор.счета') => $this->account_corr,
        );
        if (!strlen($this->description)) {
            unset($fields[_('Краткое описание')]);
        }

        return $fields;

    }

    public function getStudyCenterCardFields()
    {
        $fields = array(
            _('Название') =>$this->name,
            _('Краткое описание') => $this->description,
            _('Область ответственности (подразделение)') => $this->getDepartment(),
//            _('Лицензия') => $this->licence,
//            _('Регистрационный №') => $this->registration,
            _('Пропускная способность в месяц') => $this->pass_by,
        );
        if (!strlen($this->description)) {
            unset($fields[_('Краткое описание')]);
        }
        if (!strlen($this->licence)) {
            unset($fields[_('Лицензия')]);
        }
        if (!strlen($this->registration)) {
            unset($fields[_('Регистрационный №')]);
        }

        return $fields;

    }

    public function getDepartment()
    {
        $dep = $this->getService()->getDepartment($this->department_id);
        list($id, $name) = each($dep);
        return $name;
    }

    public function getCities($implode = false)
    {
        $cities = $this->getService()->getCities($this->provider_id);
        if ($implode){
            return implode(', ', $cities->getList('classifier_id', 'name'));
        }
        return $cities->getList('classifier_id', 'name');
    }

    public function getServiceName()
    {
        return 'TcProvider';
    }

    static public function getStatuses()
    {
        return array(
            self::STATUS_NOT_PUBLISHED => _('Не утвержден'),
            self::STATUS_PUBLISHED => _('Утвержден'),
        );
    }

    static public function getStatusesShort()
    {
        return array(
            self::STATUS_NOT_PUBLISHED => _('Нет'),
            self::STATUS_PUBLISHED => _('Да'),
        );
    }

    static public function getStatus($status)
    {
        $states = self::getStatuses();
        return ($states[$status]) ? $states[$status] : '';
    }

    static public function getStatusShort($status)
    {
        $states = self::getStatusesShort();
        return ($states[$status]) ? $states[$status] : '';
    }

    public function getDescription()
    {
        return $this->description;
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_PROVIDER     => _('Провайдеры'),
            self::TYPE_STUDY_CENTER => _('Учебные центры'),
        );
    }
}