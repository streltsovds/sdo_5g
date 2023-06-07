<?php

class HM_Integration_Task_Positions_Adapter extends HM_Integration_Abstract_Adapter implements HM_Integration_Interface_Adapter
{
//    protected $_keyExternal = 'soid_external';

    protected $_mapping = array(
        'EmployeeID'    => 'soid_external',
        'PositionName'  => 'name',
        'PersonID'      => 'mid',
        'DepartmentId'  => 'owner_soid',
        'PositionId'    => 'profile_id',
        'DateOfEmployment'  => 'position_date',
        'EmploymentType'  => 'employment_type',
        'EmployeeStatus'  => 'employee_status',
    );

    protected $_defaults = array(
        'type'    => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
    );

    // ID категорий должностей, для которых ставим признак руководителя подразделения
//    static public $managerCategoryIds = array(
//        '7948cc6d-fc08-11e6-acd2-005056a46a7a', // Менеджеры
//        '6e6c81dd-fc08-11e6-acd2-005056a46a7a', // Ведущие менеджеры
//        '4a113257-fc08-11e6-acd2-005056a46a7a', // Высшие менеджеры
//    );

    // ID категорий должностей, для которых ставим признак руководителя подразделения
    static public $managerExcludeNames = array(
//        'Заместитель', // не исключаем заместителей
    );

    public function convert(Array $item)
    {
        $this->_model->setExternalId($item['EmployeeID']);

        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('profileIdExternal2isManager')) {
            $masterManager->initCache('profileIdExternal2isManager');
        }
        if ($cachedValue = $masterManager->getCachedValue($item['PositionId'], 'profileIdExternal2isManager')) {
            $this->_model->setAttribute('is_manager', $cachedValue);
        }

        return parent::convert($item);
    }

    protected function _convertPersonID($value)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('midExternal2mid')) {
            $masterManager->initCache('midExternal2mid');
        }

        if ($cachedValue = $masterManager->getCachedValue($value, 'midExternal2mid')) {
            return $cachedValue;
        }

        // либерализация интеграции
        // throw new HM_Integration_Exception(sprintf('Ошибка: неизвестный человек %s назначается на должность', $value));
    }

    protected function _convertDepartmentId($value)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('soidExternal2soid')) {
            $masterManager->initCache('soidExternal2soid');
        }

        if ($cachedValue = $masterManager->getCachedValue($value, 'soidExternal2soid')) {
            return $cachedValue;
        }

        throw new HM_Integration_Exception(sprintf('Ошибка: создаётся должность в неизвестном подразделении %s', $value));
    }

    protected function _convertPositionId($value)
    {
        $masterManager = Zend_Registry::get('integrationManager');

        if (!$masterManager->cacheExists('profileIdExternal2profileId')) {
            $masterManager->initCache('profileIdExternal2profileId');
        }

        if (!$masterManager->cacheExists('profileId2baseProfileId')) {
            $masterManager->initCache('profileId2baseProfileId');
        }

        if ($profileId = $masterManager->getCachedValue($value, 'profileIdExternal2profileId')) {

            // подсовываем базовый профиль
            if ($baseProfileId = $masterManager->getCachedValue($profileId, 'profileId2baseProfileId')) {
                $profileId = $baseProfileId;
            }

            return $profileId;
        }

        // либерализация интеграции
        // throw new HM_Integration_Exception(sprintf('Ошибка: создаётся должность с неизвестным профилем %s', $value));
    }

    protected function _convertDateOfEmployment($value)
    {
        return parent::_convertDate($value);
    }

    protected function _convertEmployeeStatus($value)
    {
        $inactiveStatuses = array(
            'Отпуск по беременности и родам',
            'Отпуск по уходу за ребенком',
        );
        return in_array($value, $inactiveStatuses) ? 1 : 0;
    }

}