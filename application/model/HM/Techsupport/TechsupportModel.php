<?php
class HM_Techsupport_TechsupportModel extends HM_Model_Abstract
{
    const STATUS_NEW      = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_RESOLVED = 3;
    const STATUS_REJECTED = 4;
    
    static public function getStatuses() {
        return array(
            self::STATUS_NEW      => _('Новый'),
            self::STATUS_ACCEPTED => _('В работе'),
            self::STATUS_RESOLVED => _('Решен'),
            self::STATUS_REJECTED => _('Отказ'),
        );
    }

    public function getCardFields()
    {
        $return = array (
            'theme' => _('Тема'),
            'problem_description' => _('Описание проблемы'),
            'wanted_result' => _('Ожидаемый результат'),
            'getviewPageUrl()' => '',
        );

        return $return;
    }

    public function getviewPageUrl()
    {
        $url = HM_View_Helper_Url::url(
            [
                'module' => 'techsupport',
                'controller' => 'ajax',
                'action' => 'view-page',
                'support_request_id' => $this->support_request_id
            ], null, true
        );

        return '<a href="' . $url . '">' . _('Войти от имени пользователя и посмотреть страницу с ошибкой') . '</a>';
    }
}