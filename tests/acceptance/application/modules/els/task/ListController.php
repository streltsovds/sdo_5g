<?php
class Task_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->tasks;
    }
    
    /**
     * Создание задания в курсе
     * 
     * @param stdObj &$task
     * @param stdObj $subject
     * @return int ID созданного задания
     */
    public function createTask($task, $subject, $openExtendedPage = true)
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        
        $data = [
            'title' => $task->title,
        ];

        $this->createEntity($data, false);
        $taskId = $this->grabEntityId('title', $task->title);
        $variantIds = array();

        if (isset($task->variants)) {

            $this->page = Codeception_Registry::get('config')->pages->task->variants;
            $this->I->click('td.grid-title a');
            $this->I->waitForText($task->title, Codeception_Registry::get('config')->global->timeToWait);

            foreach ($task->variants as $variant) {
                $data = [
                    'qdata' => [
                        'type' => 'wysiwyg',
                        'value' => $variant->qdata
                    ],
                    'files' => [
                        'type' => 'file',
                        'value' => $variant->files
                    ],
                ];
                $variantIds[] = $this->createEntity($data, false); // @todo: не работает
            }
        }

        $this->rollback('delete from tasks where task_id = %d', $taskId);
        if (count($variantIds)) $this->rollback("delete from list where kod IN ('%s')", implode("','", $variantIds));

        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->tasks;

        return $taskId;
    }
}