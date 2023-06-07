<?php
class Report_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->reports->constructor;
    }
    
    /**
     * Создание отчёта
     * 
     * @param stdObj $report
     * @return int ID созданного отчёта
     */
    public function create($report) 
    {
        try {
            $this->createEntity(array(
                'name' => $report->name,
                'domain' => array(
                    'type' => 'select',
                    'value' => $report->domain,
                ),
                'roles[]' => array( // все роли, так проще
                    'type' => 'checkbox',
                    'value' => true,
                ),
            ));
        } catch (Exception $e) {
            
        }
                
        $reportId = $this->grabEntityId('name', $report->name);
        
        $this->rollback('delete from reports where report_id = %d', $reportId);
        
        return $reportId;
    }
}