<?php

class HM_Integration_Task_Profiles_Adapter extends HM_Integration_Abstract_Adapter implements HM_Integration_Interface_Adapter
{
    protected $_keyExternal = 'profile_id_external';

    protected $_mapping = array(
        'ID' => 'profile_id_external',
        'PositionName' => 'name',
        'PositionShortname' => 'shortname',
        'PositionId' => 'position_id_external',
        'DepartmentId' => 'department_id',
        'CategoryId' => 'category_id',
        'Category' => 'category_id',
    );

    protected $_defaults = array(
        'blocked'    => 0,
    );

    protected function _convertCategoryId($value, $extraValues)
    {
        $masterManager = Zend_Registry::get('integrationManager');
        if (!$masterManager->cacheExists('categoryIdExternal2categoryId')) {
            $masterManager->initCache('categoryIdExternal2categoryId');
        }

        if ($value = $masterManager->getCachedValue($value, 'categoryIdExternal2categoryId')) {
            return $value;
        } else {
            if (!empty($extraValues['Category'])) {
                $category = Zend_Registry::get('serviceContainer')->getService('AtCategory')->insert(array(
                    'category_id_external' => $extraValues['CategoryId'],
                    'name' => $extraValues['Category'],
                ));

                $masterManager->setCachedValue($category->category_id_external, $category->category_id, 'categoryIdExternal2categoryId');
                return $category->category_id;
            }

            return null;
        }
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

        // либерализация интеграции
        // throw new HM_Integration_Exception(sprintf('Ошибка: создаётся профиль в неизвестном подразделении %s', $value));
    }

}