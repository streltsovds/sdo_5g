<?php

class HM_Grid_ColumnCallback_Tc_ProviderContactCardLink extends HM_Grid_ColumnCallback_AbstractCardLink
{
    protected function _getCardTitle()
    {
        return _('Карточка контактного лица');
    }

    protected function _getViewUrl($id)
    {
        return $this->_url(array(
            'baseUrl'    => 'tc',
            'module'     => 'provider',
            'controller' => 'list',
            'action'     => 'contact-card',
            'contact_id' => $id
        ));
    }
}