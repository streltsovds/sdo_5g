<?php
class HM_Tc_Provider_Room_RoomModel extends HM_Model_Abstract
{
    const ROOM_TYPE_LECTION  = 0;
    const ROOM_TYPE_COMPUTER = 1;

    protected $_primaryName = 'room_id';

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
        return 'TcProviderRoom';
    }

    public function getCardFields()
    {
        $fields = array(
            _('Название')            => $this->name,
            _('Учебный центр')       => $this->getProviderName(),
//            _('Тип')                 => $this->getType(),
            _('Описание')            => $this->description,
            _('Количество мест')     => $this->places,
        );
        return $fields;
    }

    public static function getTypes()
    {
        return array(
            self::ROOM_TYPE_LECTION  => _('Лекционный'),
            self::ROOM_TYPE_COMPUTER => _('Компьютерный'),
        );
    }

    public function getType()
    {
        $types = self::getTypes();
        return $types[$this->type];
    }

    public static function getTypeById($type)
    {
        $types = self::getTypes();
        return $types[$type];
    }
}