<?php

class HM_Mailer extends Zend_Mail
{

    private $priority = 0;

    public function setPriorityMail($priority)
    {
        $this->priority = $priority;
    }

    //Реальная отправка на почту
    public function send_real()
    {
        parent::send();
    }

    public function send($transport = null)
    {

        $config = Zend_Registry::get('config');

        if (!$config->mailer->cron->use || $this->priority) {
            return parent::send($transport);
        }

        $subject = iconv('windows-1251', 'UTF-8', mb_decode_mimeheader($this->getSubject()));
        $recipients = $this->getRecipients();
        $data = array(
            'created' => date('Y-m-d H:i:s'),
            'subject' => $subject,
            'recipient' => $recipients[0],
            'data' => serialize($this),
            'sended' => 0
        );

        Zend_Registry::get('serviceContainer')->getService('Mailqueue')->insert($data);
    }
}
