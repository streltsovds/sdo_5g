<?php
class HM_Crontask_Task_Integration extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'integration';
    }

    public function run()
    {
        $importManager = new HM_Integration_Manager();
        $importManager->updateAll();
    }
}
