<?php

class HM_Integration_Task_Orgstructure_Adapter extends HM_Integration_Abstract_Adapter implements HM_Integration_Interface_Adapter
{
//    protected $_keyExternal = 'soid_external';

    protected $_mapping = array(
        'ID'    => 'soid_external',
        'Name'  => 'name',
    );

    protected $_externals = array(
    );

    protected $_defaults = array(
        'type'    => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
    );

    public function convert(Array $item)
    {
        $this->_model->setExternalId($item['ID'])
            ->setParentExternalId($item['idParentSubdivision']);
        return parent::convert($item);
    }
}