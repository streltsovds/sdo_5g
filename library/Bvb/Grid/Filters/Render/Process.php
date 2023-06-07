<?php   
class Bvb_Grid_Filters_Render_Process extends Bvb_Grid_Filters_Render_RenderAbstract
{
/*
Пример использования при включение фильтра в грид

        'workflow_id' => array(
            'render' => 'process',
            'values' => Bvb_Grid_Filters_Render_Process::getStates(HM_Recruit_Newcomer_NewcomerModel, 'newcomer_id'),
            'field4state' => 'state', --это поле в SQL-запросе, которое будет сравниваться со значением фильтра
        ),
*/

    function getFields ()
    {
        return true;
    }

    public function hasConditions()
    {
        return false;
    }


    public function render($table = null)
    {
        $id_field = $this->getFieldName();
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

        return '<div class="wrapFiltersInput maхWidthLimited">'.$this->getView()->formSelect($this->getFieldName(), $params[$id_field.$this->getGridId()]/*$this->getDefaultValue()*/, $this->getAttributes(),$this->getValues()).'<span class="clearFilterSpan">&nbsp;</span></div>';
     }

    static public function getStates($class, $key)
    {
        $model2 = HM_Model_Abstract::factory(array($key=>-1), $class);
        Zend_Registry::get('serviceContainer')->getService('Process')->initProcess($model2);
        $processAbstract = $model2->getProcess()->getProcessAbstract();
        $list = array();
        $i = 1;
        if (is_array($processAbstract->states['state'])) {
            foreach($processAbstract->states['state'] as $state) {
                $list[$state['class']] = (count($processAbstract->states['state'])==$i && !$state['name']) ? _('Завершен') : $state['name'];//$i
                $i++;
            }
        }
        return $list;
    }




    static public function getStatesProgramm($statePrefix, $programm_type, $item_type, $item_id)
    {
        $programm = Zend_Registry::get('serviceContainer')->getService('Programm')->getOne(Zend_Registry::get('serviceContainer')->getService('Programm')->getProgramms($item_type, $item_id, $programm_type));
        $list = array();
        foreach($programm->events as $state) {
            if (!$state->hidden) $list[$statePrefix.$state->programm_event_id] = $state->name;
        }
        
        return $list;
    }



    public function getConditions ()
    {
        return false;
    }

    public function buildQuery($filter, $grid)
    {
        if(!$filter) return;

        $fieldName  = $this->getFieldName();
        $filters  = $grid->getFilters();
        if(!isset($filters[$fieldName]['field4state'])) return;

        $like      = "{$filters[$fieldName]['field4state']}     LIKE '{$filter}%'";
        $notLike   = "{$filters[$fieldName]['field4state']} NOT LIKE '{$filter}%'";
        $failed    = "sop.status= " . HM_Process_Abstract::PROCESS_STATUS_FAILED;
        $notFailed = "sop.status!=" . HM_Process_Abstract::PROCESS_STATUS_FAILED;

        switch ($filter) {
            case 'HM_At_Session_State_Complete':
            case 'HM_Recruit_Vacancy_State_Complete':
            case 'HM_Recruit_Newcomer_State_Complete':
                $this->getSelect()->where($like . " OR (" . $notLike . " AND " . $failed .")");
                break;
            case 'HM_At_Session_State_Open':
            case 'HM_Recruit_Vacancy_State_Open':
            case 'HM_Recruit_Vacancy_State_Assessment':
            case 'HM_Recruit_Vacancy_State_Hire':
            case 'HM_Recruit_Newcomer_State_Welcome':
            case 'HM_Recruit_Newcomer_State_Open':
            case 'HM_Recruit_Newcomer_State_Plan':
            case 'HM_Recruit_Newcomer_State_Publish':
            case 'HM_Recruit_Newcomer_State_Result':
                $this->getSelect()->where($like . " AND ". $notFailed ."");
                break;
            default:
                $this->getSelect()->where($like);
        }

//        $this->getSelect()->where("{$filters[$fieldName]['field4state']}={$filter}");
    }

    public function transform($date, $key)
    {
    }
   
}