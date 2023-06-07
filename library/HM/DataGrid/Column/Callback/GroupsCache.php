<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_GroupsCache extends HM_DataGrid_Column_Callback_Abstract
{
    private $departmentCache;

    public function callback(...$args)
    {
        list($dataGrid, $field, $isPosition) = func_get_args();
        if(!isset($this->departmentCache['groups'])) {
            $this->departmentCache['groups'] = $dataGrid->getServiceContainer()->getService('StudyGroup')->fetchAll()->asArrayOfObjects();
        }

        $fields = array_filter(array_unique(explode(',', $field)));
        $result = (is_array($fields) && count($fields) > 1) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('StudyGroup')->pluralFormCount(count($fields)) . '</p>') : array();

        foreach ($fields as $value) {
            if (count($this->departmentCache['groups'])) {
                $tempModel = $this->departmentCache['groups'][$value];
                if ($tempModel) {
                    $result[] = '<p><a href="' . $dataGrid->getView()->url(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'index', 'group_id' => ''), null, true) . $tempModel->group_id . '">' . $tempModel->name . '</a></p>';
                }
            }
        }

        if ($result)
            return implode('', $result);
        else
            return _('Нет');
    }
}