<?php

class HM_Crontask_CrontaskService extends HM_Service_Abstract
{
    private $_taskList = array();

    /**
     * Добавляет задание в список выполнения.
     * @param HM_Crontask_Task_Interface $task
     * @return HM_Crontask_CrontaskService
     */
    public function addTask($task)
    {
        if ($task instanceof HM_Crontask_Task_Interface) {
            $this->_taskList[] = $task;
        }
        return $this;
    }

    /**
     * Инит заданий
     */
    public function init()
    {
        $config = Zend_Registry::get('config');
        // Отложенная отправка системных сообщений
        $this->addTask(new HM_Crontask_Task_MailQueue($config->mailer->cron->task_interval));
//        // Отправка отложенных писем
//        $this->addTask( new HM_Crontask_Task_SendHeldEmails(1*30));
//
//        //интеграция с 1С
//        $this->addTask( new HM_Crontask_Task_Integration(1*60));
//
//        // интеграция с AD
//        $this->addTask( new HM_Crontask_Task_UsersSync(1*60));
//
//        //Оповещение о занятиях
//        $this->addTask( new HM_Crontask_Task_LessonNotification(24*60));
//
//        // перевод в прошедшие обучение раз в 4 часа
        $this->addTask(new HM_Crontask_Task_Graduate(4*60));
//
//        // Сообщение о назначении обратной связи
        $this->addTask(new HM_Crontask_Task_Feedback(24*60));
//        $this->addTask( new HM_Crontask_Task_CheckLessonsDatesForCacheCleaning(24*60));
//
//        // Сообщение за 21 день до конца адаптации
//        $this->addTask( new HM_Crontask_Task_AdaptingStart(24*60));
//
//        // Уведомление о необходимости заполнить оценочные формы
//        $this->addTask( new HM_Crontask_Task_FillFormsNotification(24*60));
//
//        // Уведомление о необходимости заполнить отчёт о ротации
//        $this->addTask( new HM_Crontask_Task_RotationReport(24*60));

        // Уведомление о необходимости заполнить отчёт о прохождении плана КР
//        $this->addTask( new HM_Crontask_Task_ReserveReport(24*60));

        return $this;
    }

    /**
     * Выполнение списка заданий
     */
    public function run($taskId = false)
    {
        $runnedTasks = $this->fetchAll()->getList('crontask_id', 'crontask_endtime');

        /** @var HM_Crontask_Task_TaskModel $task */
        foreach ($this->_taskList as $task) {

            if ($taskId && ($taskId != $task->getTaskId())) continue;

            // Если задание уже выполнялось и интервал еще не вышел, пропускаем
            if (array_key_exists($task->getTaskId(), $runnedTasks) &&
                ((time() - $runnedTasks[$task->getTaskId()]) < $task->getInterval(true))) continue;

            // Дата старта для предотвращения наложения заданий обновляем дату запуска сразу, перед выполнением
            $data = [
                'crontask_id' => $task->getTaskId(),
                'crontask_runtime' => time()
            ];

            if (array_key_exists($task->getTaskId(), $runnedTasks)) {
                $this->update($data);
            } else {
                $this->insert($data);
            }

            // Запуск задания
            $task->run();

            // В информативных целях записываем дату окончания задания,
            // как минимум чтобы убедиться что оно выполнено
            $data['crontask_endtime'] = time();
            $this->update($data);
        }
    }
}