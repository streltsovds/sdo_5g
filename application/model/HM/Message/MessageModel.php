<?php
class HM_Message_MessageModel extends HM_Model_Abstract
{
    
    protected $_primaryName = 'message_id';

    public function getCardFields()
    {
        return [
            'getDate()' => _('Дата'),
            //'theme' => _('Тема'),
            'message' => _('Сообщение'),
        ];
    }

    public function getDate()
    {
        return $this->dateTime($this->created);
    }
}
