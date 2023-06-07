<?php

/**
 *
 */
class HM_User_DataGrid_Callback_UpdateRole extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $mid) = func_get_args();

        static $rolesCache = null;
        static $basicRolesCache = null;

        if ($rolesCache === null) {
            $basicRolesCache = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);

            // оптимизация получения ролей
            $query= $dataGrid->getSelect()->query();
            $gridResult = $query->fetchAll();
            $mids = array();

            foreach ($gridResult as $raw) {
                $mids[$raw['MID']] = $raw['MID'];
            }

            $rolesCache = array();

            if (count($mids)) {
                $select = $this->getService('User')->getSelect();
                $select->from('roles', array('mid', 'role'));
                $select->where('mid IN (?)', $mids);
                $allUsersRoles = $select->query()->fetchAll();
                foreach ($allUsersRoles as $userRole) {
                    $rolesCache[$userRole['mid']] = explode(',', $userRole['role']);
                }
            }
        }

        $roles = $basicRolesCache;
        $userRoles = !empty($rolesCache[$mid]) ? $rolesCache[$mid] : array();
        $userRolesIndex = array(
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER => $roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER]
        );
        foreach ($userRoles as $userRole) {
            if (!isset($roles[$userRole])) {
                continue;
            }
            $userRolesIndex[$userRole] = $roles[$userRole];
        }

        $result[$roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER]] = "<p>" . $roles[HM_Role_Abstract_RoleModel::ROLE_ENDUSER] . "</p>";

        foreach($userRolesIndex as $value){
            $result[$value] = "<p>{$value}</p>";
        }
        $result = array_reverse($result);
        $roleCount = count($result);

        $result[] = ($roleCount > 1) ? '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount($roleCount) . '</p>' : '';

        $result = array_reverse($result);

        if($result) {
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }
}