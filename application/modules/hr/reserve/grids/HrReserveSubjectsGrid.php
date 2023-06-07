<?php

use HM_Role_Abstract_RoleModel as Roles;
use HM_Hr_Application_ApplicationModel as Model;

class HM_Reserve_Grid_HrReserveSubjectsGrid extends HM_Grid
{
    protected static $_defaultOptions = array();

    protected function _initCols(HM_Grid_Columns $columns)
    {
        $columns->add(array(
            'subid' => array('hidden' => true),
            'isLaborSafety' => array('hidden' => true),
            'name' => array(
                'title' => _('Название курса'),
                'callback' => array(
                    'function'=> array($this, 'updateTitle'),
                    'params'=> array('{{subid}}', '{{name}}', '{{isLaborSafety}}')
                ),
            ),
            'mark' => array(
                'title' => _('Итоговая оценка')
            ),

        ));
    }

    public function updateTitle($subjectId, $name, $isLaborSafety)
    {
        $allow = $isLaborSafety && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL));

        $url = $this->getView()->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'subject_id' => $subjectId), null, true);
        return $allow ? "<a href='{$url}'>{$name}</a>" : $name;
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR))) {
            return;
        }


    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_HR))) {
            return;
        }


    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        if (!$this->currentUserIs(array(Roles::ROLE_HR, Roles::ROLE_HR_LOCAL, Roles::ROLE_SUPERVISOR))) {
            return;
        }
    }



}