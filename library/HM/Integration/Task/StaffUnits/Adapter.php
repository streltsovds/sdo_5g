<?php

class HM_Integration_Task_StaffUnits_Adapter extends HM_Integration_Abstract_Adapter implements HM_Integration_Interface_Adapter
{
    protected $_keyExternal = 'staff_unit_id_external';

    protected $_mapping = array(
        'IDStaffUnit' => 'staff_unit_id_external',
        'IDParentSubdivision' => 'soid',
        'IDPosition' => 'profile_id', // @todo: логика профилей поменялась
        'NumberOfStaffUnits'  => 'quantity',
        'NumberOfStaffUnitsText'  => 'quantity_text',
    );

    protected $_defaults = array(
//        'type'    => HM_Orgstructure_OrgstructureModel::TYPE_VACANCY,
    );

    protected $_externals = array(
    );

    public function convert(Array $item)
    {
        $this->_model->setExternalId($item['IDStaffUnit']);
        return parent::convert($item);
    }

    protected function _convertIDParentSubdivision($value)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('soidExternal2soid')) {
            $masterManager->initCache('soidExternal2soid');
        }

        if ($cachedValue = $masterManager->getCachedValue($value, 'soidExternal2soid')) {
            return $cachedValue;
        }

        throw new HM_Integration_Exception(sprintf('Ошибка: создаётся ШЕ в неизвестном или заблокированном подразделении %s', $value));
    }

    protected function _convertIdPosition($value)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('profileIdExternal2profileId')) {
            $masterManager->initCache('profileIdExternal2profileId');
        }

        if ($value = $masterManager->getCachedValue($value, 'profileIdExternal2profileId')) {
            return $value;
        }

        // либерализация интеграции
        // throw new HM_Integration_Exception(sprintf('Ошибка: создаётся ШЕ с неизвестным профилем %s', $value));
    }

    protected function _convertNumberOfStaffUnits($value)
    {
        return ceil(str_replace(',', '.', $value));
    }

}