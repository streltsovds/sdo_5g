<?php

class HM_Mailqueue_MailqueueTable extends HM_Db_Table
{
    protected $_name = "mail_queue";
    protected $_primary = "id";
    protected $_sequence = "S_55_1_MAILQ";


    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('mail_queue.created ASC');
    }
}