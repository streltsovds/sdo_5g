<?php
class Newcomer_KpiController extends HM_Controller_Action_Newcomer
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $form = new HM_Form_Kpi();
        $this->_setForm($form);

        parent::init();
    }

    public function indexAction()
    {
        if (count($this->_newcomer->cycle)) {
            $cycleId = $this->_newcomer->cycle->current()->cycle_id;
        }
        
        $select = $this->getService('AtKpi')->getSelect();
        $select->from(
            array(
                'uk' => 'at_user_kpis'
            ),
            array(
                'uk.user_kpi_id',
                'kpi_name' => 'k.name',
                'uk.value_plan',
                'uk.value_fact',
//                'uk.weight',
                'uk.begin_date',
                'uk.end_date',
            )
        );

        $select
            ->join(array('k' => 'at_kpis'), 'uk.kpi_id = k.kpi_id', array())
            ->join(array('c' => 'cycles'), 'uk.cycle_id = c.cycle_id', array())
            ->where('c.cycle_id = ?', $cycleId)
            ->where('uk.user_id = ?', $this->_newcomer->user_id)
            ->group(array(
                'uk.user_kpi_id',
                'k.kpi_id',
                'k.name',
                'c.cycle_id',
                'uk.value_plan',
                'uk.value_fact',
//                'uk.weight',
                'uk.begin_date',
                'uk.end_date',
            ));
        ;

        $fields = array(
            'user_kpi_id' => array('hidden' => true),
            'kpi_name' => array(
                'title' => _('Задача на испытательный срок'),
            ),
            'value_plan' => array(
                'title' => _('Плановое значение'),
            ),
            'value_fact' => array(
                'title' => _('Фактическое значение'),
            ),
//            'weight' => array(
//                'title' => _('Вес'),
//            ),
            'begin_date' => array(
                'title' => _('Начало'),
                'format' => 'Date'
            ),
            'end_date' => array(
                'title' => _('Плановое завершение'),
                'format' => 'Date'
            ),
        );
        
        $grid = $this->getGrid($select, $fields,
            array(
                'kpi_name' => null,
                'value_plan' => null,
                'value_fact' => null,
//                'weight' => null,
                'begin_date' => array('render' => 'DateSmart'),
                'end_date' => array('render' => 'DateSmart'),
            )
        );
        
        $result = $this->getService('State')->getOne($this->getService('State')->fetchAll(array('process_type = ?'=>HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING, 'item_id = ?'=>$this->_newcomer->newcomer_id)));
        $isKpiPassed = !in_array($result->current_state, array(HM_Recruit_Newcomer_State_Plan, HM_Recruit_Newcomer_State_Open));

        if (!$isKpiPassed && $this->getService('Acl')->isCurrentAllowed('mca:newcomer:kpi:edit')) {
             $grid->addAction(array(
                 'module' => 'newcomer',
                 'controller' => 'kpi',
                 'action' => 'edit'
             ),
                 array('user_kpi_id'),
                 $this->view->svgIcon('edit', 'Редактировать')
             );
            
            $grid->addAction(array(
                    'module' => 'newcomer',
                    'controller' => 'kpi',
                    'action' => 'delete'
                ),
                array('user_kpi_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );
    
            $grid->addMassAction(
                array(
                    'module' => 'newcomer',
                    'controller' => 'kpi',
                    'action' => 'delete-by',
                ),
                _('Удалить задачу'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }
        
        $this->view->grid = $grid;

        $this->view->editable = !$isKpiPassed;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();

        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
            $this->getService('User')->isRoleExists($this->getService('User')->getCurrentUserId(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)
        ) {
            // если потенциально имеет роль - переключаем автоматом
            $this->view->switchRole = HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR;
        }
    }

    public function create($form)
    {
        $data = $form->getValues();
        
        $dataKpi = array(
            'name' => $data['name'],        
//            'kpi_cluster_id' => $data['kpi_cluster_id'],        
            'kpi_unit_id' => $data['kpi_unit_id'],        
            'is_typical' => $data['is_typical'],  
        );
        
        $dataKpiUser = array(
            'weight' => $data['weight'],
            'value_plan' => $data['value_plan'],
            'value_fact' => $data['value_fact'],
            'user_id' => $this->_newcomer->user_id,       
            'cycle_id' => count($this->_newcomer->cycle) ? $this->_newcomer->cycle->current()->cycle_id : 0,
            'begin_date' => empty($data['begin_date']) ? null : date ("Y-m-d H:i:s", strtotime($data['begin_date'])),
            'end_date'   => empty($data['end_date']) ? null : date ("Y-m-d H:i:s", strtotime($data['end_date'])),
            'value_type' => $data['value_type'],      
        );
        
        $kpi = $this->getService('AtKpi')->insert($dataKpi);
        $dataKpiUser['kpi_id'] = $kpi->kpi_id;

        $this->getService('AtKpiUser')->insert($dataKpiUser);        
    }

    public function update($form)
    {
        $data = $form->getValues();
        
        if ($kpiUser = $this->getService('AtKpiUser')->getOne($this->getService('AtKpiUser')->find($data['user_kpi_id']))) {
            
            if ($kpi = $this->getService('AtKpi')->getOne($this->getService('AtKpi')->find($kpiUser->kpi_id))) {
            
                $kpi->name = $data['name'];
                $kpi->kpi_cluster_id = $data['kpi_cluster_id'];
                $kpi->kpi_unit_id = $data['kpi_unit_id'];
            
                $this->getService('AtKpi')->update($kpi->getValues());
            }
            
            $begin = new HM_Date($data['begin_date']);
            $end = new HM_Date($data['end_date']);

            $kpiUser->weight = $data['weight'];
            $kpiUser->value_plan = $data['value_plan'];
            $kpiUser->value_fact = $data['value_fact'];
            $kpiUser->begin_date = $begin->toString('yyyy-MM-dd');
            $kpiUser->end_date = $end->toString('yyyy-MM-dd 23:59:59');
            $kpiUser->value_type = $data['value_type'];

            $this->getService('AtKpiUser')->update($kpiUser->getValues());
        }
    }

    public function deleteAction()
    {
    	$id = (int) $this->_getParam('user_kpi_id', 0);
    	if ($id) {
    		$this->delete($id);
    		$this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
    	}
    	$this->_redirectToIndex();
    }
    
    
    public function delete($id) {
        $this->getService('AtKpiUser')->delete($id);
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'kpi', 'newcomer', array('newcomer_id' => $this->_newcomer->newcomer_id));
    }    
    
    public function setDefaults(Zend_Form $form)
    {
        if ($userKpiId = $this->_getParam('user_kpi_id', 0)) {
            $userKpi = $this->getService('AtKpiUser')->findDependence('Kpi', $userKpiId)->current();
            $userKpiData = $userKpi->getData();
            $kpiData = (count($userKpi->kpi)) ? $userKpi->kpi->current()->getData() : array();
            $form->populate($userKpiData + $kpiData);
        }
    }
}
