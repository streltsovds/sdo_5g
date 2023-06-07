<?php

class HM_MessageItem_MessageItemTable extends HM_Db_Table
{
    protected $_name = "chat_message_items";
    protected $_primary = "message_item_id";
    protected $_sequence = "S_41_1_CHAT_MESSAGE_ITEMS";

  

    public function getDefaultOrder()
    {
        return array('chat_message_items.message_item_id DESC');
    }
}