<?php

class HM_Grid_ColumnCallback_Tc_ProviderCardLink extends HM_Grid_ColumnCallback_AbstractCardLink
{
    protected function _getCardTitle()
    {
        return _('Карточка провайдера');
    }

    protected function _getViewUrl($id)
    {
        return $this->_url(array(
            'module'      => 'provider',
            'controller'  => 'list',
            'action'      => 'view',
            'provider_id' => $id
        ));
    }

    protected function _getCardUrl($id)
    {
        return $this->_url(array(
            'baseUrl'     => 'tc',
            'module'      => 'provider',
            'controller'  => 'list',
            'action'      => 'card',
            'provider_id' => $id
        ));
    }
}