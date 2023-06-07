<?php

/**
 *
 */
class HM_User_DataGrid_Callback_UpdateEmail extends HM_DataGrid_Column_Callback_Abstract
{
    public function callback(...$args)
    {
        list($dataGrid, $email, $emailConfirmed) = func_get_args();
        if (!$email) return '';
        $validateEmailEnabled = $this->getService('Option')->getOption('regValidateEmail');
        return ($emailConfirmed || !$validateEmailEnabled) ?
            $email : '<span class="unconfirmed" title="' . _('Email не подтверждён пользователем') . '">' . $email . '</span>';
    }
}