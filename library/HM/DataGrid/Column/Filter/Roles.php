<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_Roles
{
    private $value;

    public function __construct(HM_DataGrid $dataGrid)
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true,true);
        unset($roles[HM_Role_Abstract_RoleModel::ROLE_GUEST]);

        $this->value = [
            'values' => $roles,
            'callback' => [
                'function' => [$this, 'callback'],
                'params' => [$dataGrid]
            ]
        ];
    }

    static public function create($dataGrid)
    {
        $self = new self($dataGrid);
        return $self->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function callback($data)
    {
        $value = $data['value'];
        $select = $data['select'];

        if (!empty($value)) {
            $select->joinInner(['rs'=>'roles_source'], Zend_Registry::get('serviceContainer')->getService('User')->quoteInto('rs.user_id = t1.MID AND rs.role = ?', $value), []);
        }
    }
}