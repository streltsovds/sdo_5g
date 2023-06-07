<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_DepartmentsCache extends HM_DataGrid_Column_Callback_Abstract
{
    use HM_Grid_ColumnCallback_Trait_Common;

    private $departmentsCache;

    public function callback(...$args)
    {
        list($dataGrid, $field, $isPosition) = func_get_args();
        $key = $isPosition ? 'positions' : 'departments';

        if(!isset($this->departmentsCache[$key])) {
            $extraCond = $isPosition ? 'type IN (1,2)' : 'type=0';

            $select = $dataGrid->getServiceContainer()->getService('Orgstructure')->getSelect();
            $select->from('structure_of_organ', array(
                'soid',
                'name',
                'is_manager'
            ));
            $select->where($extraCond);
            $deps = $select->query()->fetchAll();
            $index = array();
            foreach ($deps as $dep) {
                $index[$dep['soid']] = array('name' => $dep['name'], 'is_manager' => $dep['is_manager']);
            }
            $this->departmentsCache[$key] = $index;
        }

        $fields = array_filter(array_unique(explode(',', $field)));
        $pluralForm = $isPosition ? 'pluralFormPositionsCount' : 'pluralFormCount';
        $cache = &$this->departmentsCache[$key];


        if ($isPosition && is_array($fields) && (count($fields) == 1)) {
            // Если данные представляют собой одну-единственную должность
            $value = $fields[0];
            return $this->updatePositionName(
                $dataGrid,
                isset($cache[$value]['name']) ? $cache[$value]['name'] : '',
                $value,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                isset($cache[$value]['is_manager']) ? $cache[$value]['is_manager'] : ''
            );

        } else {
            // Во всех остальных случаях (т.е. нет данных или несколько должностей или подразделений)
            // Делаем, как было раньше
            $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Orgstructure')->$pluralForm(count($fields)) . '</p>') : array();

            foreach($fields as $value){
                if (isset($cache[$value])) {
                    $result[] = "<p>{$cache[$value]['name']}</p>";
                }
            }

            if ($result) {
                return implode('', $result);
            } else {
                return _('Нет');
            }
        }
    }

    /*
     * @TODO Переделать на декоратор
     */
    public function updatePositionName($dataGrid, $name, $soid, $type, $isManager)
    {
        if (empty($name)) return '';

        if ($dataGrid->getServiceContainer()->getService('Acl')->inheritsRole(
            $dataGrid->getServiceContainer()->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER
        )) return $name;

        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            $name = '<a href="'.$dataGrid->getView()->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $soid), null, true).'">'.$name.'</a>';
        }

        // отключил карточки
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
