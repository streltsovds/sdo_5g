<?php
class HM_Crontask_Task_MailQueue extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'sendMailQueue';
    }

    public function run()
    {
        $config = Zend_Registry::get('config');
        if(!$config->mailer->cron->use) {
            return;
        }

        function microtime_float()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }

        $serviceMailQueue = Zend_Registry::get('serviceContainer')->getService('Mailqueue');

        $neededQty = $config->mailer->cron->task_interval * $config->mailer->cron->mail_per_minute;
        $mails = $serviceMailQueue->getSelect()
            ->from(
                array( 'm' => 'mail_queue'),
                array(
                    'id' => 'm.id',
                    'data' => 'm.data',
                )
            )
            ->where('m.sended = ?', 0)
            ->order('created ASC')
            ->limit($neededQty)
            ->query()
            ->fetchAll();

        $timeStart = time();
        $delaySec = $config->mailer->cron->task_interval * 60 / $config->mailer->cron->mail_per_minute;

        foreach ($mails as $mail) {
            if ((time()-$timeStart) > ($config->mailer->cron->task_interval * 60) * 0.9)  {
                //Эффективное время = 0.8-0.9 от выделенного, чтобы не перекрывались задачи
                break;
            }

            $mailSender = @unserialize($mail['data']);
            $deltaSend = 0;
            if ($mailSender) {
                $startSend = microtime_float();
                try {
                    $mailSender->send_real();
                    $serviceMailQueue->delete($mail['id']);

                } catch (Exception $e) {
                    Zend_Registry::get('log_mail')->debug($e->getMessage().'\n'.$e->getTraceAsString());
                }

                $deltaSend = microtime_float() - $startSend;
            }

            $delay = (int)(($delaySec - $deltaSend)*1000*1000); //Вычитаем из плановой задержки реальное время на отправку
            if ($delay>0) {
                usleep($delay);
            }
        }
    }
}

