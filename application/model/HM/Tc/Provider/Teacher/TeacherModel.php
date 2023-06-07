<?php
class HM_Tc_Provider_Teacher_TeacherModel extends HM_Model_Abstract
{
    protected $_primaryName = 'teacher_id';

    /**
     * @return HM_Tc_Provider_ProviderModel
     */
    public function getProvider()
    {
        /** @var HM_Tc_Provider_ProviderService $providerService */
        $providerService = Zend_Registry::get('serviceContainer')->getService('TcProvider');

        return $providerService->getOne($providerService->find($this->provider_id));

    }

    public function getProviderName()
    {
        $provider = $this->getProvider();

        if (!$provider) {
            return '';
        }

        return $provider->name;

    }

    public function getServiceName()
    {
        return 'TcProviderTeacher';
    }

    public function getCardFields()
    {
        $fields = array(
            _('ФИО')                 => $this->name,
            _('Провайдер обучения') => $this->getProviderName(),
            _('Контактные данные')   => $this->contacts,
            _('Описание')            => $this->description,
        );
        return $fields;
    }

    public function getStudyCenterCardFields()
    {
        $fields = array(
            _('ФИО')                 => $this->name,
            _('Учебный центр')       => $this->getProviderName(),
        );

        if ($this->contacts) {
            $fields[_('Контактные данные')] = $this->contacts;
        }
        if ($this->description) {
            $fields[_('Описание')] = $this->description;
        }

        return $fields;
    }
}