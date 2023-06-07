<?php

class HM_ChatMessage_ChatMessageTable extends HM_Db_Table
{
    protected $_name = "chat_messages";
    protected $_primary = "message_id";
    protected $_sequence = "S_41_1_CHAT_MESSAGES";

    protected $_referenceMap = array(
        'Author' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
    );


    public function getDefaultOrder()
    {
        return array('chat_messages.created_at DESC');
    }
}