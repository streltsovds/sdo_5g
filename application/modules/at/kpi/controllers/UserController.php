<?php
class Kpi_UserController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    private $_userId;
    
    public function init()
    {
        $userId = $this->_getParam('user_id', 0);
        if ($userId && !is_array($userId)) {
            $this->_userId = $userId;
            $user = $this->getService('User')->find($this->_userId)->current();
            $this->view->setExtended(
                array(
                    'subjectName' => 'User',
                    'subjectId' => $this->_userId,
                    'subjectIdParamName' => 'user_id',
                    'subjectIdFieldName' => 'MID',
                    'subject' => $user
                )
            );  
            $this->getService('Unmanaged')->getController()->page_id = 'm00';
    
            // если пользователь не админ и смотрит не свою карточку
            // то скрываем меню "Редактирование учетной записи"
            // очень некрасиво скопировано из HM_Controller_Action_User
            
            if ( $userId != $this->getService('User')->getCurrentUserId() &&
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)
            //!in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN))
            ) {
                $this->view->addContextNavigationModifier(
                    new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page1_2')
                );
            }
            
            if (!count($this->_user->candidate) || empty($this->_user->candidate->current()->resume_external_url)) {
                $this->view->addContextNavigationModifier(
                    new HM_Navigation_Modifier_Remove_Page('resource', 'cm:user:page2_2')
                );
            }
        }
        $form = new HM_Form_UserKpi();
        $this->_setForm($form);
        parent::init();        
    }

    public function indexAction()
    {
        $select = $this->getService('AtKpi')->getSelect();

        $select->from(
            array(
                'uk' => 'at_user_kpis'
            ),
            array(
                'uk.user_kpi_id',
                'p.MID',
            	'department' => 'so.owner_soid',
            	'position' => 'so.soid',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'kpi_name' => 'k.name',
                'cycle_name' => 'c.name',
                'uk.value_plan',
                'uk.value_fact',
                'uk.weight',
            )
        );

        $select
            ->join(array('k' => 'at_kpis'), 'uk.kpi_id = k.kpi_id', array())
            ->join(array('c' => 'cycles'), 'uk.cycle_id = c.cycle_id AND c.newcomer_id IS NULL', array())
            ->join(array('p' => 'People'), 'p.MID = uk.user_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.mid = uk.user_id', array())
            ->group(array(
                'uk.user_kpi_id',
                'k.kpi_id',
                'p.MID',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'so.owner_soid',
                'so.soid',
                'k.name',
                'c.name',
                'uk.value_plan',
                'uk.value_fact',
                'uk.weight',
            ));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))) {
        
            if (count($responsibility = $this->getService('Responsibility')->get())) {
                $soid = array_shift($responsibility);  // сейчас нет возможности задать несколько responsibility
                if ($collection = $this->getService('Orgstructure')->find($soid)) {
                    $department = $collection->current();
                    $select->where('so.lft > ?', $department->lft)
                        ->where('so.rgt < ?', $department->rgt);
                }
            }
        }            
// exit($select->__toString());            
        $cycles = $this->getService('Cycle')->fetchAll()->getList('name');
        $fields = array(
            'user_kpi_id' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'cycle_name' => array(
                'title' => _('Оценочный период'),
            ),
            'kpi_name' => array(
                'title' => _('Показатель эффективности'),
            ),
            'value_plan' => array(
                'title' => _('Плановое значение'),
            ),
            'value_fact' => array(
                'title' => _('Фактическое значение'),
            ),
            'weight' => array(
                'title' => _('Вес'),
            ),
        );
        
        if ($this->_userId) {
            $extraFields = array(
                'fio' => array('hidden' => true),
                'department' => array('hidden' => true),
                'position' => array('hidden' => true),
            );
            $select->where('p.MID = ?', $this->_userId);
        } else {
            $extraFields = array(
                'fio' => array(
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => '', 'baseUrl' => ''), null, true) . '{{MID}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => '', 'baseUrl' => ''), null, true) . '{{MID}}'.'">'. '{{fio}}</a>'
                ),
                'department' => array(
                    'title' => _('Подразделение'),
                    'callback' => array(
                        'function'=> array($this, 'departmentsCache'),
                        'params' => array('{{department}}', $select)
                    )
                ),
                'position' => array(
                    'title' => _('Должность'),
                    'callback' => array(
                        'function'=> array($this, 'departmentsCache'),
                        'params' => array('{{position}}', $select, true)
                    )
                ),
            );                        
        }        
        
        $fields = $extraFields + $fields;
        
        $grid = $this->getGrid($select, $fields,
            array(
                'fio' => null,
                'department' => null,
                'position' => null,
                'cycle_name' => array('values' => $cycles),
                'kpi_name' => null,
                'value_plan' => null,
                'value_fact' => null,
                'weight' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'kpi',
            'controller' => 'user',
            'action' => 'edit'
        ),
         array('user_kpi_id'),
            $this->view->svgIcon('edit', 'Редактировать')    
        );

        $grid->addAction(array(
                'module' => 'kpi',
                'controller' => 'user',
                'action' => 'delete'
            ),
            array('user_kpi_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'kpi',
                'controller' => 'user',
                'action' => 'delete-by',
            ),
            _('Удалить показатели эффективности пользователей'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $data = $form->getValues();
        
        $dataKpi = array(
            'name' => $data['name'],        
            'kpi_cluster_id' => $data['kpi_cluster_id'],        
            'kpi_unit_id' => $data['kpi_unit_id'],        
            'is_typical' => $data['is_typical'],        
        );
        
        $dataKpiUser = array(
            'weight' => $data['weight'],
            'value_plan' => $data['value_plan'],
            'value_fact' => $data['value_fact'],
            'cycle_id' => $data['cycle_id'],        
        );
        
        $kpi = $this->getService('AtKpi')->insert($dataKpi);
        $dataKpiUser['kpi_id'] = $kpi->kpi_id;

        $kpiUser = $this->getRequest()->getParams();
        if ($this->_userId) {
            $dataKpiUser['user_id'] = $this->_userId;
        } else {
            $dataKpiUser['user_id'] = (is_array($kpiUser['user_id'])) ? array_shift($kpiUser['user_id']) : 0;
        }

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
        
            $kpiUser->weight = $data['weight'];
            $kpiUser->cycle_id = $data['cycle_id'];
            $kpiUser->value_plan = $data['value_plan'];
            $kpiUser->value_fact = $data['value_fact'];

        
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
        if ($this->_userId) {
            $this->_redirector->gotoSimple('index', 'user', 'kpi', array('user_id' => $this->_userId));
        } else {
            $this->_redirector->gotoSimple('index');
        }
    }    
    
    public function setDefaults(Zend_Form $form)
    {
        $userKpiId = $this->_getParam('user_kpi_id', 0);
        $userKpi = $this->getService('AtKpiUser')->findDependence(array('Kpi', 'User'), $userKpiId)->current();
        $userKpiData = $userKpi->getData();
        if (count($userKpi->user)) {
            $userKpiData['user'] = array($userKpiData['user_id'] => $userKpi->user->current()->getName());
        }
        $kpiData = (count($userKpi->kpi)) ? $userKpi->kpi->current()->getData() : array();

        $form->populate($userKpiData + $kpiData);
    }

    public function importAction()
    {
        $request = $this->getRequest();
        $typicalKpis = $this->getService('AtKpi')->getTypicalKpis()->getList('name', 'kpi_id');

        $kpis = $midsExternal = array();

        $form = new HM_Form_Import();
        if ($request->isPost() && $form->isValid($params = $request->getParams())) {
            if($form->data->isUploaded() && $form->data->receive() && $form->data->isReceived()){
                if ($fh = fopen($form->data->getFileName(), 'r')) {

//                     $seq = array(
//                         0 => 'fio',
//                         1 => 'mid_external',
//                         2 => 'kpi',
//                         3 => 'weight',
//                         4 => 'value_plan',
//                         5 => 'value_fact',
//                     );

                    setlocale(LC_ALL, 'ru_RU.utf8');
                    while(($row = fgetcsv($fh, 0, ';', '"')) !== false) {
                        $count++;
                        if ($count <= 1) continue;
                        if (!strlen($row[0])) continue;
                        $row = array_map('trim', $row);
                        $kpis[] = $row;
                        $midsExternal[] = $row[1];
                    }

                    $cycleId = $form->getValue('cycle_id');
                    $mids = $this->getService('User')->fetchAll($this->quoteInto('mid_external IN (?)', new Zend_Db_Expr(implode(',', $midsExternal))))->getList('mid_external', 'MID');
                    if (count($mids)) {
                        $this->getService('AtKpiUser')->deleteBy($this->quoteInto(array(
                            'user_id IN (?) AND ',
                            'cycle_id = ?'
                        ), array(
                            new Zend_Db_Expr(implode(',', $mids)),
                            $cycleId,
                        )));

                        $count = 0;
                        foreach ($kpis as $row) {

                            if (array_key_exists($row[2], $typicalKpis)) {
                                $kpiId = $typicalKpis[$row[2]];
                            } else {
                                $kpi = $this->getService('AtKpi')->insert(array(
                                    'name' => $row[2],
                                    'is_typical' => HM_At_Kpi_KpiModel::NOT_TYPICAL
                                ));
                                $kpiId = $kpi->kpi_id;
                            }

                            if ($this->getService('AtKpiUser')->insert(array(
                                'cycle_id' => $cycleId,
                                'kpi_id' => $kpiId,
                                'user_id' => $mids[$row[1]],
                                'weight' => $row[3],
                                'value_plan' => $row[4],
                                'value_fact' => $row[5],
                            ))) {
                                $count++;
                            }
                        }

                        if ($count) {
                            $this->_flashMessenger->addMessage(sprintf(_('Импортировано показателей: %s'), $count));
                            $this->_redirector->gotoSimple('index');
                        }
                    }
                }
            }
            $this->_flashMessenger->addMessage(array('message' => _('При загрузке данных произошла ошибка'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index');
        }

        $this->view->form = $form;
    }

    public function setCommentAction()
    {
        $userKpiId = $this->_getParam('id');
        $comment = $this->_getParam('comment');
        $this->getService('AtKpiUserResult')->setResult($userKpiId, array('comments' => $comment));
        exit(1);
    }
    
    public function setScoreAction()
    {
        $scores = $this->_getParam('score');

        if ($scores && !empty($scores) && is_array($scores))
        {
            foreach ($scores as $id => $score)
            {
                if (null === $score || '' === $score) {
                    $score = -1;
                }
                list(,$userKpiId) = explode("_", $id);
                $this->getService('AtKpiUserResult')->setResult($userKpiId, array('value_fact' => $score));
            }
        } else {
            $userKpiId = $this->_getParam('id');
            $score = $this->_getParam('score', -1);
            $this->getService('AtKpiUserResult')->setResult($userKpiId, array('value_fact' => $score));
        }

        echo count($scores);
        exit;
    }
    
    public function getUserProgressAction()
    {
        if ($this->isAjaxRequest()) {
            echo $this->getService('AtKpiUser')->getUserProgress($this->getService('User')->getCurrentUserId());
        exit();
        }
    }
}
