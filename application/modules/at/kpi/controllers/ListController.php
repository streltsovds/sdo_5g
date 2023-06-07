<?php
class Kpi_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    private $_profileId;
    private $_profile;

    public function init()
    {
        if ($this->_profileId = $this->_getParam('profile_id', 0)) {

            $this->_profile = $profile = $this->getService('AtProfile')->find($this->_profileId)->current();

            $this->initContext($this->_profile);
            $this->view->addSidebar('profile', [
                'model' => $this->_profile,
                
               
            ]);

            $form = new HM_Form_ProfileKpi();
        } else {
            $form = new HM_Form_Kpi();
        }
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $gridId = ($this->_profileId) ? "grid{$this->_profileId}" : 'grid';
        
        $default = new Zend_Session_Namespace('default');
     	if ($this->_profileId && !isset($default->grid['kpi-list-index'][$gridId])) {
     		$default->grid['kpi-list-index'][$gridId]['filters']['profile'] = $this->_profileId; // по умолчанию показываем только назначенные
     	}
        $order = $this->_request->getParam("order{$gridId}");
        
        if ($order == ''){
            $this->_request->setParam("order{$gridId}", $this->_profileId ? 'profile_DESC' : 'name_ASC');
        }
        
        $fields = array(
            'k.kpi_id',
            'k.name',
            'cluster' => 'kc.name',
        );
        
        if ($this->_profileId) {
            $fields['value_plan'] = 'kp.value_plan';
            $fields['unit'] = 'ku.name';
            $fields['profile'] = 'kp.profile_id';
        } else {
            $fields['users'] = new Zend_Db_Expr('COUNT(DISTINCT uk.user_id)');
            $fields['unit'] = 'ku.name';
        }
        
        $select = $this->getService('AtKpi')->getSelect();
        $select->from(
            array(
                'k' => 'at_kpis'
            ),
            $fields
        );

        $groupFields = $fields;
        unset($groupFields['users']);
        $select
            ->joinLeft(array('kc' => 'at_kpi_clusters'), 'kc.kpi_cluster_id = k.kpi_cluster_id', array())
            ->joinLeft(array('ku' => 'at_kpi_units'), 'ku.kpi_unit_id = k.kpi_unit_id', array())
            ->where('k.is_typical = ?', HM_At_Kpi_KpiModel::TYPICAL)
            ->group($groupFields);
        
        if ($this->_profileId) {
            $select->joinLeft(array('kp' => 'at_profile_kpis'), 'kp.kpi_id = k.kpi_id', array());
        } else {
            $select->joinLeft(array('uk' => 'at_user_kpis'), 'uk.kpi_id = k.kpi_id', array());
        }     
        
        $kpiUseClusters = $this->getService('Option')->getOption('kpiUseClusters') || $this->getService('Option')->getOption('kpiUseClusters', HM_Option_OptionModel::MODIFIER_RECRUIT);
        $grid = $this->getGrid($select, array(
            'kpi_id' => array('hidden' => true),
            'profile' => $this->_profileId ? array(
                'title' => _('Назначен'),
                'callback' => array(
                    'function'=> array($this, 'updateStatus'),
                    'params'=> array('{{profile}}')
                ),
            ) : array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
            ),
            'cluster' => $kpiUseClusters ? array(
                'title' => _('Кластер'),
            ) : array('hidden' => true),
            'unit' => array(
                'title' => _('Единица измерения'),
            ),
            'users' => $this->_profileId ? array('hidden' => true) : array(
                'title' => _('Назначен пользователям'),
                'callback' => array(
                    'function'=> array($this, 'updateUsers'),
                    'params'=> array('{{name}}', '{{users}}')
                )
            ),
            'value_plan' => !$this->_profileId ? array('hidden' => true) : array(
                'title' => _('Плановое значение'),
            ),
        ),
            array(
                'name' => null,
                'cluster' => null,
                'unit' => null,
            ),
            $gridId            
        );

        $grid->addAction(array(
            'module' => 'kpi',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('kpi_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'kpi',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('kpi_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );
        
        if ($this->_profileId) {
            
            $grid->addMassAction(
                array(
                    'module' => 'kpi',
                    'controller' => 'list',
                    'action' => 'assign',
                ),
                _('Назначить показатель эффективности профилю'),
                _('Вы уверены, что хотите назначить данные типовые показатели эффективности профилю? При этом они будут автоматически добавлены на текущий оценочный период всем пользователям, которым назначен данный профиль.')
            );
            
            $grid->addMassAction(
                array(
                    'module' => 'kpi',
                    'controller' => 'list',
                    'action' => 'unassign',
                ),
                _('Отменить назначение показателей эффективности'),
                _('Вы уверены, что хотите отменить назначение данных типовых показателей эффективности? При этом они будут автоматически аннулированы на текущий оценочный период всем пользователям, которым назначен данный профиль.')
            );
            
        } else {

            $grid->addMassAction(
                array(
                    'module' => 'kpi',
                    'controller' => 'list',
                    'action' => 'delete-by',
                ),
                _('Удалить типовые показатели эффективности'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }       

        if ($this->_profileId) {
	        $grid->setGridSwitcher(array(
	  			array('name' => 'local', 'title' => _('используемые в данном профиле'), 'params' => array('profile' => $this->_profileId)),
	  			array('name' => 'global', 'title' => _('все типовые показатели'), 'params' => array('profile' => null), 'order' => 'profile', 'order_dir' => 'DESC'),
	  		));
        }
        
        if ($this->_profileId) $grid->setClassRowCondition("'{{profile}}' == {$this->_profileId}", "success");

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_UPDATE));
                $this->_redirectToIndex();
            } else {
                // чтобы единицы измерения не сбрасывались после неуспешной валидации
                $post = $this->_request->getParams();
                if (isset($post['kpi_unit'][0]))
                    $form->populate(array('kpi_unit' => $post['kpi_unit']));
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function create($form)
    {
        $dataKpi = $form->getValues();
        unset($dataKpi['kpi_id']);
        unset($dataKpi['profile_id']);
        unset($dataKpi['profile_kpi_id']);
        
        $dataKpiProfile = array(
            'weight' => $dataKpi['weight'],        
            'value_plan' => $dataKpi['value_plan'],        
        );
        unset($dataKpi['weight']);
        unset($dataKpi['value_plan']);

        if (isset($dataKpi['kpi_unit'])) {
            $unitId = $this->getService('AtKpiUnit')->insertUnit(array('name' => $dataKpi['kpi_unit'][0]));
            $dataKpi['kpi_unit_id'] = $unitId;
            unset($dataKpi['kpi_unit']);
        }
        
        $kpi = $this->getService('AtKpi')->insert($dataKpi);
        
        if ($this->_profileId) {
            $dataKpiProfile['kpi_id'] = $kpi->kpi_id;  
            $dataKpiProfile['profile_id'] = $this->_profileId;  
            $kpiProfile = $this->getService('AtKpiProfile')->insert($dataKpiProfile);
        }
    }

    public function update($form)
    {
        $dataKpi = $form->getValues();

        $unitId = $this->getService('AtKpiUnit')->insertUnit(array('name' => $dataKpi['kpi_unit'][0]));
        // убираем за собой мусор
        $this->getService('AtKpiUnit')->clearUnits();
        $dataKpi['kpi_unit_id'] = $unitId;
        unset($dataKpi['kpi_unit']);

        if ($this->_profileId) {
            unset($dataKpi['is_typical']);
            unset($dataKpi['name']);
            unset($dataKpi['kpi_cluster_id']);
            unset($dataKpi['kpi_unit_id']);
            $this->getService('AtKpiProfile')->update($dataKpi);
        } else {
            $this->getService('AtKpi')->update($dataKpi);
        }
    }

    public function delete($id) {
        $this->getService('AtKpi')->delete($id);
        // убираем за собой мусор
        $this->getService('AtKpiUnit')->clearUnits();
    }
    
    protected function _redirectToIndex()
    {
        if ($this->_profileId) {
            $this->_redirector->gotoSimple('index', 'list', 'kpi', array('profile_id' => $this->_profileId, "ordergrid{$this->_profileId}" => 'profile_DESC'));
        } else {
            $this->_redirector->gotoSimple('index');
        }
    }    

    public function setDefaults(Zend_Form $form)
    {
        $kpiId = $this->_getParam('kpi_id', 0);
        $kpi = $this->getService('AtKpi')->find($kpiId)->current();
        $data = $kpi->getData();
        $dataProfile = array();
        if ($this->_profileId) {
            $kpiProfile = $this->getService('AtKpiProfile')->fetchAll(array(
                'kpi_id = ?' => $kpiId,        
                'profile_id = ?' => $this->_profileId,        
            ))->current();
            if( $kpiProfile && $kpiProfile->getData()) {
                $dataProfile = $kpiProfile->getData();
            }
        }
        $form->populate($data + $dataProfile);
    }

    public function updateUsers($name, $users)
    {
        if ($users == '0') return $users;
        $url = $this->view->url(array(
            'action' => 'index',
            'controller' => 'user',
            'module' => 'kpi',
            'kpi_namegrid' => $name
        ));
        $title = _('Список пользователей');
        return "<a href='{$url}/?page_id=m6902' title='{$title}'>{$users}</a>";
    }

    public function newDefaultAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $result = false;
        $defaults = array(
            'name' => $this->_getParam('title'),
            'is_typical' => HM_At_Kpi_KpiModel::NOT_TYPICAL
        );
        if (strlen($defaults['name'])) {
            if ($kpi = $this->getService('AtKpi')->insert($defaults)) {
                $result = $kpi->kpi_id;
            }
        }
        exit(HM_Json::encodeErrorSkip($result));
    }
    
    public function assignAction()
    {
    	$gridId = ($this->_profileId) ? "grid{$this->_profileId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $kpiId) {
                    $this->getService('AtKpiProfile')->assign($this->_profileId ,$kpiId);
                }
                $this->getService('AtKpiProfile')->assignUserKpisByProfile($this->_profileId);
                $this->_flashMessenger->addMessage(_('Показатели эффективности успешно назначены профилю'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
    	$gridId = ($this->_profileId) ? "grid{$this->_profileId}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
    	if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('AtKpiProfile')->unassign($this->_profileId ,$id);
                }
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
    }
    
    public function updateStatus($profileId)
    {
        return ($profileId == $this->_profileId) ?  _('Да') : _('Нет');
    }    
}