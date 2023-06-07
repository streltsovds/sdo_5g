<?php
class Orgstructure_ListController extends Codeception_Controller_Action
{
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->orgstructure;
    }
    
    /**
     * Создание подразделения
     * из-за tree и фильтра ШЕ практически всё приходится делать руками  
     * 
     * @param stdObj $department
     * @param stdObj $parentDepartment родитель, внутри которого создавать; если нет  - в корне
     * @return int ID созданного подразделения
     */
    public function create($department, $parentDepartment = false) 
    {
        $this->openMenuMain($this->page->menu);
        
        if ($parentDepartment) {
            $this->I->click($parentDepartment->name, '.dynatree-container');
            $this->waitForGrid();
        }
        
        $this->I->click($this->page->links->create);
        $this->I->submitForm('.els-content form', array(
            'name' => $department->name,
        ));
        $this->I->see($this->pageCommon->grid->messages->success);        

        $this->filterEntity('so_name', $department->name);
        
        $types = HM_Orgstructure_OrgstructureModel::getTypes();
        $this->I->selectOption('#filter_gridtype', $types[HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT]);
        $this->I->click(Codeception_Registry::get('config')->pages->common->grid->filter->apply);
        // @todo: рефакторить wait
        $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
        
        $departmentId = $this->I->grabValueFrom('td.checkboxes input');
                
        $this->rollback('delete from structure_of_organ where soid = %d', $departmentId);
        
        return $departmentId;        
    }

}