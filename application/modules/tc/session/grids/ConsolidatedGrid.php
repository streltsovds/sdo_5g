<?php
use HM_Role_Abstract_RoleModel as Roles;
use HM_Grid_ColumnCallback_Els_UserCardLink        as UserCardLink;

class HM_Session_Grid_ConsolidatedGrid extends HM_Grid {

    protected static $_defaultOptions = array(
        'sessionId' => 0,
        'sessionDepartmentId'    => 0,
        'defaultOrder' => 'session_id_ASC',
    );

    public function init($source = null)
    {

        parent::init($source);
        if ($this->getCourseId()) {
            $this->_grid->setClassRowCondition("'{{session_department_id}}' != ''", "success");
        }
    }

    protected function _initColumns()
    {
        $view       = $this->getView();

        $sessionViewUrl = $view->url(array(
            'module'      => 'application',
            'controller'  => 'list',
            'action'      => 'index',
            'session_department_id' => ''
            )
        );
        $sessionViewUrl .= '{{session_department_id}}';
        if (!$this->_options['sessionId']) {
            $sessionViewUrl .= '/session_id/{{tcsession}}/';
        }

        $this->_columns = array(
            'session_department_id' => array('hidden'=> true),
            'tcsession' => array('hidden'=> true),
            'department_id' => array('hidden'=> true),
            'workflow_id' => $this->_options['sessionId']
                    ? array(
                        'title' => _('Бизнес-процесс'), // бизнес проуцесс
                        'callback' => array(
                            'function' => array($this, 'printWorkflow'),
                            'params' => array('{{workflow_id}}', _('Бизнес-процесс сессии планирования')),
                        ),
                    )
                    : array('hidden'=> true),
            'cycle_id' => $this->_options['sessionId']
                    ? array('hidden'=> true)
                    : array(
                        'title'=> _('Период планирования'),
                        'callback' => array(
                            'function' => array($this, 'updateCycle'),
                            'params' => array('{{cycle}}'),
                        ),
                    ),
            'dzo_department' => $this->_options['sessionId']
                    ? array('hidden'=> true)
                    : array('title'=> _('ДЗО')),
            'level2_department' => $this->_options['sessionId']
                    ? array('hidden'=> true)
                    : array('title'=> _('Подразделение 2-го уровня')),
            'cycle' => array('hidden'=> true),
            'department' => array('title'=> _('Подразделение'),
                'decorator' => '<a href="'.$sessionViewUrl.'">{{department}}</a>',
            ),
            'department_manager_user_ids' => array(
                'title' => _('Руководитель'),
                'callback' => array(
                    'function' => array($this, 'updateManagers'),
                    'params' => array('{{department_manager_user_ids}}'),
                ),
            ),
            'fact_count' => array('title'=> _('Кол-во персональных заявок')),
            'summ' => array(
                'title'=> _('Сумма'),
                'style'    => "text-align:right",
            ),
            'sop_status' => array('hidden' => true),
            'state' => array('hidden' => true)
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'cycle_id' => array(
                'values' => $this->getService('Cycle')
                        ->fetchAll(array('type=?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING), 'begin_date')
                        ->getList('cycle_id', 'name')
            ),
            'workflow_id' => array(
                'render' => 'process',
                'values' => Bvb_Grid_Filters_Render_Process::getStates(HM_Tc_Session_Department_DepartmentModel, 'session_department_id'),
                'field4state' => 'sp.current_state',
            ),
            'department' => null, // здесь нужен именно поиск по строке // array('render' => 'department'),
            'department_manager_fio' => null,
            'dzo_department' => array('values' => $this->getController()->getLevel1()),
            'level2_department' => array('values' => $this->getController()->getLevel2($this->getGridId())),

            'fact_count' => null,

        ));
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if ($this->currentUserIs(array(Roles::ROLE_DEAN, Roles::ROLE_DEAN_LOCAL))) {
            $actions->add(_('Войти от имени руководителя'), array(
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'list',
                'action' => 'login-as'
            ))
            ->setParams(array('MID'));
        }
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if ($this->getService('TcSession')->applicationsStatus($this->_options['sessionId']) != HM_Tc_Session_SessionModel::STATE_ACTUAL) {
            return;
        }

        if ($this->currentUserIs(array(Roles::ROLE_DEAN, Roles::ROLE_DEAN_LOCAL))) {
            $massActions->add(
                array(
                    'module'     => 'session',
                    'controller' => 'consolidated',
                    'action'     => 'change-state-by',
                ),
                _('Перевести на следующий этап Бизнес-процесса'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
/*            $massActions->add(
                array(
                    'module'     => 'session',
                    'controller' => 'consolidated',
                    'action'     => 'agreement-by',
                ),
                _('Согласовать групповые заявки'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
*/
            // #18157 : просто закомменчу, мб какому-нибудь клиенту надо будет включить
            /*$massActions->add(
                $this->getView()->url(array(
                    'module' => 'session',
                    'controller' => 'consolidated',
                    'action' => 'rollback-by',
                )),
                _('Вернуть групповые заявки на этап согласования'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );*/
        }
    }

    public function getCourseId()
    {
        return $this->_options['sessionDepartmentId'];
    }

    public function getGridId()
    {
        $gridId = parent::getGridId();
        $courseId = $this->getCourseId();
        $sessionId = $this->_options['sessionId'];

        if (!$courseId && !$sessionId) {
            return $gridId;
        }

        return $gridId.$courseId.$sessionId;

    }

    public function printWorkflow($sessionDepartmentId)
    {
        static $consolidatedsCache = null;
        if ($consolidatedsCache === null) {
            $consolidatedsCache = array();
            $collection = $this->getService('TcSessionDepartment')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $consolidatedsCache[$item->session_department_id] = $item;
                }
            }
        }
        if(intval($sessionDepartmentId) > 0 && count($consolidatedsCache) && array_key_exists($sessionDepartmentId, $consolidatedsCache)){
            $model = $consolidatedsCache[$sessionDepartmentId];
            $this->getService('Process')->initProcess($model);
            return $this->getView()->workflowBulbs($model);
        }
        return '';
    }

    public function updatePlan($normCount)
    {
        if (!$normCount) {
            return 0;
        }
        static $norm = null;
        if ($norm === null) {
            $norm = $this->getService('Option')->getOption('standard');
        }
        return number_format($norm*$normCount, 0, '.', ' ');
    }

    public function updateFact($factCost)
    {
        return ($factCost) ? number_format($factCost, 0, '.', ' ')  : '0';
    }

    public function updateState($state, $status)
    {

        if ($status == HM_Process_Abstract::PROCESS_STATUS_FAILED) {
            return _('Групповая заявка отменена');
        }

        static $states = null;
        if ($states === null)
        {
            $allStates = $this->getService('TcSessionDepartment')->getProcessStates();
            foreach($allStates['classes'] as $key => $item) {
                $states[$item] = $allStates['names'][$key];
            }
        }

        return (isset($states[$state])) ? _($states[$state])  : _('Бизнес-процесс не начат');
    }

    public function filterState($data)
    {
        $value = $data['value'];
        $states = $this->getService('TcSessionDepartment')->getProcessStates();
        $select = $data['select'];
        if ($value && $states['classes'][$value]) {
            $select->where('sp.current_state = ?', $states['classes'][$value]);
        }
    }

    public function updateCycle($cycleName)
    {
        return $cycleName;
    }

    public function updateName($name, $userId)
    {
        $name = trim($name);
        if (!strlen($name)) {
            if ($userId) {
                $name = sprintf(_('Пользователь #%d'), $userId);
            }
            else {
                $name = '';
            }
        }
        return $name;
    }

    public function updateManagers($userIds)
    {
        static $managersCache;

        if (!is_array($managersCache)) {
            $collection = $this->getService('User')->fetchAllDependenceJoinInner('Position', $this->getService('User')->quoteInto('Position.is_manager = ?', 1));
            if (count($collection)) {
                foreach ($collection as $row) {
                    $managersCache[$row->MID] = $row;
                }
            }
        }

        $userCardLink     = new UserCardLink();
        $userIds = explode(',', $userIds);

        $return = (is_array($userIds) && count($userIds) > 1) ? array('<p class="total">' . $this->getService('User')->pluralFormUsersCount(count($userIds)) . '</p>') : array();

        foreach ($userIds as $userId) {
            if (isset($managersCache[$userId])) {
                $user = $managersCache[$userId];
                $return[] = '<p>' . $userCardLink($user->MID, implode(' ', array($user->LastName, $user->FirstName, $user->Patronymic))) . '</p>';
            }
        }

        if ($return)
            return implode('', $return);
        else
            return _('Нет');
    }

    public function updateCostItem($item)
    {
        static $costItems = null;
        if ($costItems === null){
            $costItems = HM_Tc_Application_ApplicationModel::getCostItems();
        }

        return isset($costItems[$item]) ? $costItems[$item] : _('Не назначена');
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        if (!$row['MID']) {
            $actions->setInvisibleActions(array(
                _('Войти от имени руководителя')
            ));
        }
    }
} 