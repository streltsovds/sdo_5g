<?php
class HM_Mail_Hold_HoldService extends HM_Service_Abstract
{
    public function getSendList()
    {
        $select = $this->getSelect()
            ->from(array('hm' => 'hold_mail')
            )
            ->joinLeft(array('p' => 'People'),
                'hm.receiver_MID = p.MID',
                array('MID')
            )
            ->where('p.EMail <> ?', '');

        return $select->query()->fetchAll();
    }

}