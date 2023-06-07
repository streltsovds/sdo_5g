<?php
class HM_Tc_Provider_Contact_ContactService extends HM_Service_Abstract
{
    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('контактное лицо во множественном числе', '%s контактное лицо', $count), $count);
    }
}