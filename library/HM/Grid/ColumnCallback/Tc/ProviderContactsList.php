<?php

use HM_Grid_ColumnCallback_Tc_ProviderContactCardLink as ProviderContactCardLink;

class HM_Grid_ColumnCallback_Tc_ProviderContactsList extends HM_Grid_ColumnCallback_AbstractListOfCardLinks
{
    protected $_defaultServiceName = 'TcProviderContact';

    protected function _createCardLinkCallback()
    {
        return new ProviderContactCardLink();
    }

}