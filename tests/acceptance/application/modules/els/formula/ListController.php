<?php
class Formula_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->formulas;
    }
    
    /**
     * Создание формулы на курсе
     * 
     * @param stdObj $formula
     * @return int ID созданной формулы
     */
    public function create($formula, $subject, $openExtendedPage = true) 
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        
        try {
            $data = [
                'name' => $formula->name,
                'type' => [
                    'type' => 'select',
                    'value' => $formula->type,
                ],
                'formula' => str_replace(';', ";\r\n", $formula->text),
            ];
            $this->createEntity($data, false);
            
        } catch (Exception $e) {
            
        }
                
        $formulaId = $this->grabEntityId('name', $formula->name);
        
        $this->rollback('delete from formula where id = %d', $formulaId);
        
        return $formulaId;
    }
}