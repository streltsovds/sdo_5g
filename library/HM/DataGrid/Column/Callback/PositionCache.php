<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_PositionCache extends HM_DataGrid_Column_Callback_Abstract
{
    use HM_Grid_ColumnCallback_Trait_Common;

    public function callback(...$args)
    {
        list($dataGrid, $name, $soid, $type, $isManager) = func_get_args();

        if (empty($name)) return '';

        if ($dataGrid->getServiceContainer()->getService('Acl')->inheritsRole(
            $dataGrid->getServiceContainer()->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER
        )) return $name;

        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            $name = '<a href="'.$dataGrid->getView()->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $soid), null, true).'">'.$name.'</a>';
        }

        // карточки не работают
        // временно отключил
        return $name;

        $positionCardLink = $dataGrid->getView()->url([
            'module' => 'orgstructure',
            'controller' => 'list',
            'action' => 'card',
            'org_id' => '',
            'baseUrl' => ''
        ]) . $soid;

        return $dataGrid->getView()->cardLink(
            $positionCardLink,
            HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
            'icon-svg', // type
            'pcard', // className (not used)
            'pcard', // relName
//            'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager),
            'address-book', // iconType
            [
                'iconVueColor' => 'colors.iconGray',
                'class' => 'hm-card-link-departments-cache',
            ] // additional params
        ) . $name;
    }
}
