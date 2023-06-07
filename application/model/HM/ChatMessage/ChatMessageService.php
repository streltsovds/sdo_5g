<?php

class HM_ChatMessage_ChatMessageService extends HM_Service_Abstract
{    
    public function getMessages($namespace, $page = 1, $itemsPerPage = HM_ChatMessage_ChatMessageModel::ITEMS_PER_PAGE)
    {
        $offset = ($page - 1) * $itemsPerPage;
        return $this->fetchAllDependence('Author', array('namespace = ?' => $namespace), 'created_at ASC', $itemsPerPage, $offset);
    }
}
