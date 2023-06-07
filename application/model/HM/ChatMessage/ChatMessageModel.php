<?php
class HM_ChatMessage_ChatMessageModel extends HM_Model_Abstract
{
    const ROOM_TYPE_LESSON = 'lesson';
    const ITEMS_PER_PAGE = 10;

    protected $_primaryName = 'chat_messages';
}