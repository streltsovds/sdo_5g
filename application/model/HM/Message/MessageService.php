<?php
class HM_Message_MessageService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        $data['created'] = $this->getDateTime();
        return parent::insert($data);
    }
}