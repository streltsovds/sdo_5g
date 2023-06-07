<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 22.09.2014
 * Time: 15:19
 */

class HM_Session_Grid_SessionGrid extends HM_Grid
{

    protected static $_defaultOptions = array(
        'sessionId'    => 0,
        'defaultOrder' => 'session_id_ASC',

    );

    public function init($source = null)
    {

        parent::init($source);
        /*$this->_grid->setDefaultFiltersValues(
            array('status' => HM_Tc_Session_SessionModel::GOING)
        );*/
        /*$statusFilter = $this->_grid->getParam('status', null);
        if (!isset($statusFilter) && !$this->isAjaxRequest()) {
            $this->_grid->setParam('status', HM_Tc_Session_SessionModel::GOING);
        }*/
        if ($this->getCourseId()) {
            $this->_grid->setClassRowCondition("'{{session_id}}' != ''", "success");
        }

    }

    protected function _getDefaultFilterValues()
    {
        /*
         * return array(
         *     'field_name' => 'filter_value'
         * );
         */
        return array('status' => HM_Tc_Session_SessionModel::GOING);
    }

    protected function _initColumns()
    {
        $view       = $this->getView();

        $numberFormat   = new HM_Grid_ColumnCallback_Other_NumberFormat($this);

        $sessionViewUrl = $view->url(array(
            'module'      => 'session',
            'controller'  => 'list',
            'action'      => 'view',
            'session_id' => ''
        ));
        $sessionViewUrl .= '{{session_id}}';

        $this->_columns = array(
            'session_id' => array('hidden'=> true),
            'responsible_id' => array('hidden'=> true),
            'workflow_id' => array(
                'title' => _('Бизнес-процесс'), // бизнес проуцесс
                'callback' => array(
                    'function' => array($this, 'printWorkflow'),
                    'params' => array('{{workflow_id}}', _('Бизнес-процесс сессии планирования')),
                ),
                'position' => 1,
            ),
            'name' => array(
                'title'=> _('Название'),
                'decorator' => '<a href="'.$sessionViewUrl.'">{{name}}</a>',
                'position' => 2,
            ),
            'cycleid' => array(
                'title'=> _('Период планирования'),
                'decorator' => '{{cycle}}',
                'position' => 3,
            ),
            'cycle' => array('hidden' => true),
            'norm' => $this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))
                ? array('hidden' => true)
                : array(
                    'title'    => _('Сумма'),
                    'style'    => "text-align:right",
                    'callback' => $numberFormat->getCallback('{{norm}}', 0, '0'),
                    'position' => 4,
            ),
            'summ' => $this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))
                ? array('hidden' => true)
                : array(
                    'title'    => _('Сумма'),
                    'style'    => "text-align:right",
                    'position' => 5,
                    //'callback' => $numberFormat->getCallback('{{norm}}', 0, '0')
            ),
            'responsible' => array('title' => _('Ответственный специалист'),
                'callback' => array(
                    'function'=> array($this, 'updateResponsible'),
                    'params'=> array('{{responsible_id}}', '{{responsible}}', $view)),
                'position' => 6,

            ),
            'status' => array('title'=> _('Статус'),
                'callback' => array(
                    'function'=> array($this, 'updateStatus'),
                    'params'=> array('{{status}}')),
                'position' => 7,
            ),
            'department_all'   => array('hidden' => true),
            'department_count' => $this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))
                ? array('hidden' => true)
                : array('title'  => _('Количество подразделений'), 'position' => 8)
        );

        $processType = $this->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_TC_SESSION);
//        foreach ($processType->states['state'] as $state) {
//            if ($state['name']) {
//                $this->_columns[$state['id'].'_mydep'] = array('hidden' => true);
//
//                $this->_columns[$state['id']] = array(
//                    'title' => $state['name'],
//                    'callback' => array(
//                        'function'=> array($this, 'updateDepartmentsCount'),
//                        'params'=> array('{{'.$state['id'].'}}', '{{'.$state['id'].'_mydep}}'))
//
//                );
//            }
//        }


    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'name' => null,
            'status' => array(
                'values' => HM_Tc_Session_SessionModel::getStatuses(),
            ),
            'cycleid' => array(
                'values' => $this->getService('Cycle')->fetchAll($this->getService('Cycle')->quoteInto('type=?',HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING))->getList('cycle_id', 'name')

            ),
            'norm'  => array('render' => 'Number'),
            'department_count' => array('render' => 'Number'),
        ));

        /*$processType = $this->getService('Process')->getStaticProcess(HM_Process_ProcessModel::PROCESS_TC_SESSION);
        foreach ($processType->states['state'] as $state) {
            if ($state['name']) {
                $filters->add(array(
                    $state['id'] => array(
                        'render' => 'Number',
                        'callback' => array(
                            'function' => array($this, 'filterSop'),
                            'params'   => array()
                        )),
                    ));
            }
        }*/
    }

    protected function _initSwitcher(HM_Grid_Switcher $switcher)
    {

    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        $actions
            ->add('edit', array(
                'module'     => 'session',
                'controller' => 'list',
                'action'     => 'edit',
            ))
            ->setParams(array(
                'session_id'
            ));

        $actions
            ->add('delete', array(
                'module'     => 'session',
                'controller' => 'list',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'session_id'
            ));

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $actions
                ->add('import', array(
                    'module'     => 'session',
                    'controller' => 'import',
                    'action'     => 'index',
                    'source'     => 'template',
                ))
                ->setParams(array(
                    'session_id'
                ))
                ->setTitle(_('Импортировать план обучения'));
        }

//        $actions
//            ->add('rollback', array(
//                'module'     => 'session',
//                'controller' => 'list',
//                'action'     => 'rollback',
//            ))
//            ->setParams(array(
//                'session_id'
//            ))
//            ->setTitle(_('Вернуть на предыдущий этап'));
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            return;
        }

        $massActions->add(
            array(
                'module'     => 'session',
                'controller' => 'list',
                'action'     => 'delete-by',
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
        $massActions->add(
            array(
                'module'     => 'session',
                'controller' => 'list',
                'action'     => 'finish-by',
            ),
            _('Отменить сессии планирования'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        $menu->addItem(array(
            'urlParams' => array(
                'module' => 'session',
                'controller' => 'list',
                'action' => 'new'
            ),
            'title' => _('Создать сессию планирования')
        ));
        $menu->addItem(array(
            'urlParams' => array(
                'module' => 'session',
                'controller' => 'import',
                'action' => 'index',
                'source' => 'template'
            ),
            'title' => _('Создать сессию на основе годового плана обучения')
        ));
    }

    public function getCourseId()
    {
        return $this->_options['sessionId'];
    }

    public function getGridId()
    {
        $gridId = parent::getGridId();
        $courseId = $this->getCourseId();

        if (!$courseId) {
            return $gridId;
        }

        return $gridId.$courseId;

    }

    public function updateStatus($status) {
        $statuses = HM_Tc_Session_SessionModel::getStatuses();
        if (isset($statuses[$status])) {
            return $statuses[$status];
        }
        return $status;
    }

    public function updateResponsible($responsibleId, $responsible, $view)
    {
        return ($responsibleId) ? $view->cardLink(
            $view->url(array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'view',
                'user_id' => ''
            ), null, true) . $responsibleId,_('Карточка пользователя')).
        '<a href="'.$view->url(array(
            'module' => 'user',
            'controller' => 'edit',
            'action' => 'card',
            'user_id' => ''), null, true) . $responsibleId.'">' . $responsible . '</a>' : '';
    }

    public function printWorkflow($sessionId)
    {
        static $sessionsCache = null;
        if ($sessionsCache === null) {
            $sessionsCache = array();
            $collection = $this->getService('TcSession')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $sessionsCache[$item->session_id] = $item;
                }
            }
        }
        if(intval($sessionId) > 0 && count($sessionsCache) && array_key_exists($sessionId, $sessionsCache)){
            $model = $sessionsCache[$sessionId];
            $this->getService('Process')->initProcess($model);
            return $this->getView()->workflowBulbs($model);
        }
        return '';
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        static $sessionStateCache = null;
        if ($sessionStateCache === null) {
            $result = $this->_grid->getResult();
            $tmp = array();
            foreach ($result as $item) {
                $tmp[] = $item['session_id'];
            }
            $tmp = array_unique($tmp);
            /** @var HM_Tc_Session_SessionService $sessionService */
            $sessionService = $this->getService('TcSession');
            /** @var HM_Process_ProcessService $processService */
            $processService = $this->getService('Process');

            $sessions = $sessionService->fetchAll($sessionService->quoteInto(
                'session_id IN (?)', $tmp
            ));
            $sessionStateCache = array();
            foreach ($sessions as $session) {
                $state = $processService->getCurrentState($session);
                $sessionStateCache[$session->session_id] = $state;
            }
        }
        if (!$sessionStateCache[$row['session_id']] ||
            !$sessionStateCache[$row['session_id']] instanceof HM_Tc_Session_State_Agreement ||
            !$this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_DEAN))
        ) {
            $actions->setInvisibleActions(array(
                'rollback',
            ));
        }
    }

    public function updateDepartmentsCount($depCount, $myDepCount)
    {
        $myDepCount = (int)$myDepCount;
        if (empty($myDepCount)) {
            return $depCount;
        } else {
            return $depCount . '*';// <span style="font-size: 8px">(' . _('Вы здесь') . ')</span>';

        }
    }
    public function filterSop($data)
    {
        $select = $data['select'];
        $search = $data['value'];
        if ($search) {
        }
    }

} 