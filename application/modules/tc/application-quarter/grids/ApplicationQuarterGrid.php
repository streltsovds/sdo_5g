<?php
use HM_Grid_ColumnCallback_Els_UserCardLink        as UserCardLink;
use HM_Grid_ColumnCallback_Tc_SubjectCardLink      as SubjectCardLink;
use HM_Grid_ColumnCallback_Tc_ProviderCardLink     as ProviderCardLink;
use HM_Grid_ColumnCallback_Other_NumberFormat      as NumberFormat;
use HM_Role_Abstract_RoleModel as Roles;
use HM_Tc_Application_ApplicationModel as TcApplicationModel;

class HM_ApplicationQuarter_Grid_ApplicationQuarterGrid extends HM_Grid {

    protected static $_defaultOptions = array(
        'applicationId'    => 0,
        'departmentId'     => 0,
        'sessionQuarterId'        => 0,
        'sessionDepartmentId' => 0,
        'status'           => TcApplicationModel::STATUS_ACTIVE,
        'defaultOrder' => 'fio_ASC',
        'showButton'          => false
    );

    protected function _initColumns()
    {
        $numberFormat   = new NumberFormat($this);
        $providerCardLink = new ProviderCardLink();

        $this->_columns = array(
            'application_id' => array('hidden'=> true),
            'session_quarter_id2' => array('hidden'=> true),
            'subjectId' => array('hidden'=> true),
            'subject_status' => array('hidden'=> true),
            'provider_status' => array('hidden'=> true),
            'MID' => array('hidden'=> true),
            'fio' => array(
                'title' => _('ФИО'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{fio}}', '{{MID}}', '{{application_id}}'))
            ),
//            'position_full' => array('title'=> _('Должность')),
            'user_city' => array('hidden'=> true),
            'position_id' => array('hidden'=> true),
            'is_manager' => array('hidden'=> true),
            'position' => array(
                'title' => _('Должность'),
                'callback' => array(
                    'function' => array($this, 'updatePositionName'),
                    'params' => array(
                        '{{position}}',
                        '{{position_id}}',
                        HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                        '{{is_manager}}'
                    )
                )
            ),
            'subject' => array(
                'title'=> _('Курс / мероприятие'),
                'callback' => array(
                    'function'=> array($this, 'updateSubject'),
                    'params'=> array('{{subjectId}}', '{{subject}}', '{{event_name}}'))
            ),
            'subject_city' => array('hidden'=> true),
            'price' => array(
                'title'=> _('Стоимость'),
                'style'    => "text-align:right",
                'callback' => $numberFormat->getCallback('{{price}}')
            ),
            'format' => array('hidden'=> true),
            'provider_id' => array('hidden'=> true),
            'provider_name' => array(
                'title'=> _('Провайдер'),
                'callback' => $providerCardLink->getCallback('{{provider_id}}', '{{provider_name}}'),
            ),
            'period' => array(
                'title'=> _('Срок проведения'),
                'callback' => array(
                    'function' => array($this, 'monthDate'),
                    'params'   => array('{{period}}'))
            ),
            'longtime' => array('title'=> _('Длительность курса, дней')),
            'department_goal' => array('hidden'=> true), //array('title'=> _('Цель подразделения')),
            'education_goal' => array('hidden'=> true), //array('title'=> _('Цель обучения')),
            'category' => array(
                'title'=> _('Тип обучения'),
                'callback' => array(
                    'function'=> array($this, 'updateCategory'),
                    'params'=> array('{{category}}'))
            ),
            'department_name' => array(
                'title'=> _('Подразделение'), 'hidden'=> true
            ),
            'initiator' => array(
                'title'  => _('Инициатор заявки'),
                'callback' => array(
                    'function'=> array($this, 'updateInitiator'),
                    'params'=> array('{{initiator}}')),
                'position' => 5,
            ),
            'payment_type' => array(
                'title'  => _('Тип финансирования'),
                'callback' => array(
                    'function'=> array($this, 'updatePaymentType'),
                    'params'=> array('{{payment_type}}')),
                'position' => 6,
            ),
            'manager_id' => array('hidden'=> true),
            'cost_item' => $this->currentUserIs(Roles::ROLE_SUPERVISOR)
                ? array('hidden'=> true)
                : array(
                'title'=> _('Статья расходов'),
                'callback' => array(
                    'function'=> array($this, 'updateCostItem'),
                    'params'=> array('{{cost_item}}'))
            ),
            'event_name' => array('hidden'=> true),
            'application_status' => array(
                'title'=> _('Статус консолидированной заявки'),
                'callback' => array(
                    'function' => array($this, 'updateApplicationStatus'),
                    'params'   => array('{{application_status}}'))
            ),
        );

        if ($this->_options['sessionDepartmentId'] || $this->currentUserIs(Roles::ROLE_SUPERVISOR)) {
            $this->_columns['department_name']  = array('hidden'=> true);
            $this->_columns['manager_fio'] = array('hidden'=> true);
        } else {
            $this->_columns['longtime']         = array('hidden'=> true);

        }
    }


    public function positionFullFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){

            $value = '%' . $value . '%';

            $select->where("(sop.name LIKE ?", $value);
            $select->orWhere("sod.name LIKE ?", $value);
            $select->orWhere("sod2.name LIKE ?)", $value);


        }

    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        $session = $this->getController()->getSessionQuarter();
        if (!$this->getService('TcSessionQuarter')->isApplicable($session)) return;

        $actions
            ->add('edit', array(
                'module'     => 'application-quarter',
                'controller' => 'list',
                'action'     => 'edit',
                'session_quarter_id' => $session->session_quarter_id
            ))
            ->setParams(array(
                'application_id',
            ));

        if ($this->_options['status'] != HM_Tc_Application_ApplicationModel::STATUS_COMPLETE) 
		{
            $actions
                ->add('delete', array(
                    'module'     => 'application-quarter',
                    'controller' => 'list',
                    'action'     => 'delete',
                    'session_quarter_id' => $session->session_quarter_id
                ))
                ->setParams(array(
                    'application_id'
                ));
        }
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        $session = $this->getController()->getSessionQuarter();
        $createAllowed = $this->getService('TcSessionQuarter')->isApplicable($session);

        if ($this->currentUserIs(array(Roles::ROLE_DEAN)) && $createAllowed) {
            $massActions->add(
                $this->getView()->url(array(
                    'module' => 'application-quarter',
                    'controller' => 'list',
                    'action' => 'delete-by',
                    'grid' => $this->getGridId()
                )),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

        }
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'fio' => null,
            'position' => array('render' => 'department'),
//            'position_full' =>array(
//                'callback' => array(
//                    'function'=>array($this, 'positionFullFilter'),
//                    'params'=>array()
//                )
//            ),
            'subject' => null,
            'price' => null,
            'provider_name' => null,
            'initiator' => null,
            'period' => array('render' => 'Date'),
            'longtime' => null,
            'department_goal' => null,
            'education_goal' => null,
            'category' => array(
                'values' => TcApplicationModel::getApplicationCategories(),
            ),
//            'department_name' => array('render' => 'department'),
            'manager_fio' => null,
            'cost_item' => array(
                'values' => TcApplicationModel::getCostItems()
            ),
            'payment_type' => array(
                'values' => TcApplicationModel::getPaymentTypes()
            ),
            'application_status' => array(
                'values' => array(
                    HM_Tc_Application_ApplicationModel::STATUS_ACTIVE => _('Не согласована'),
                    HM_Tc_Application_ApplicationModel::STATUS_COMPLETE => _('Согласована'),
                ),
            ),
        ));

        $filters->addFromColumnCallbacks(array(
            'user_city',
            'subject_city'
        ));

    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        if ($this->_options['sessionDepartmentId']) {
            $model = $this->getService('TcSessionDepartment')->getOne(
                $this->getService('TcSessionDepartment')->find($this->_options['sessionDepartmentId'])
            );
            $state = $this->getService('Process')->getCurrentState($model);

            if ($state) {

                if ($this->currentUserIs(Roles::ROLE_SUPERVISOR) && !($state instanceof HM_Tc_SessionQuarter_Department_State_Open)) {
                    return;
                }

                if ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) {
                    return;
                }

                $className = $state->getNextState();

                $sendForAgreement = ($className == 'HM_Tc_SessionQuarter_Department_State_Agreement') &&
                    $this->currentUserIs(array(Roles::ROLE_SUPERVISOR));

                $message = '';
                if (!$className) {
                    return;
                } elseif ($sendForAgreement) {
                    $message = _('Отправить на согласование');
                } elseif ($className == 'HM_Tc_SessionQuarter_Department_State_Complete') {
                    $message = _('Завершить согласование консолидированной заявки');
                } else {
                    $next = new $className();
                    $message = _('Перевести на этап: ') . $next->getCurrentStateMessage();
                }

                $actionLink = array(
                    'urlParams' => array(
                        'module' => 'session-quarter',
                        'controller' => 'consolidated',
                        'action' => 'change-state',
                        'session_department_id' => $this->_options['sessionDepartmentId'],
                        'state' => HM_State_Abstract::STATE_STATUS_CONTINUING
                    ),
                    'title' => $message,
                    'class' => 'gridMenuCustom',
                );

                if ($sendForAgreement) {
                    $actionLink['onclick'] = 'return confirm(\'' . _("Вы действительно желаете отправить консолидированную заявку на согласование менеджером по обучению? Дальнейшее изменение параметров заявки будет невозможно, указанные пользователи будут назначаться менеджером на учебные курсы. Продолжить?") . '\')';
                }

                if ($this->_options['showButton']) $menu->addItem($actionLink);

            }
        }
    }

    protected function _getDefaultFilterValues()
    {
        $filters = array();
        if ($this->currentUserIs(array(Roles::ROLE_DEAN, Roles::ROLE_DEAN_LOCAL))) {
            $department = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->find($this->_options['departmentId'])
            );
            if ($department) {
                $filters['department'] = $department->name;
            }
        }
        return $filters;
    }

    public function getCourseId()
    {
        return $this->_options['sessionQuarterId'].$this->_options['departmentId'];
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

    public function updateName($fio, $MID, $applicationId)
    {
        $result = _("Заявка [{$applicationId}]");

        if (strlen($fio) && $MID) {
            $userCardLink     = new UserCardLink();
            $result = $userCardLink($MID, $fio);
        }

        return $result;
    }

    public function updateInitiator($initiatorId)
    {
        $result = '';

        $user = $this->getService('User')->getOne(
            $this->getService('User')->find($initiatorId)
        );

        $fio = $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic;

        if (strlen($fio) && $initiatorId) {
            $userCardLink     = new UserCardLink();
            $result = $userCardLink($initiatorId, $fio);
        }

        return $result;
    }

    public function updatePaymentType($type)
    {
        static $paymentTypes = null;
        if ($paymentTypes === null){
            $paymentTypes = TcApplicationModel::getPaymentTypes();
        }

        return isset($paymentTypes[$type]) ? $paymentTypes[$type] : '--не указано--';
    }

    public function updateSubject($subjectId, $subjectName, $eventName)
    {
        if ($subjectId) {
            $subjectCardLink     = new SubjectCardLink();
            $result = $subjectCardLink($subjectId, $subjectName);
        } else {
            $result = $eventName;
        }
        return $result;


    }

    public function updateCostItem($item)
    {
        static $costItems = null;
        if ($costItems === null){
            $costItems = TcApplicationModel::getCostItems();
        }

        return isset($costItems[$item]) ? $costItems[$item] : '';
    }

    public function updateCategory($categoryId)
    {
        return TcApplicationModel::getApplicationCategory($categoryId);
    }

    public function monthDate($date, $checkSession = false)
    {
        $tst = strtotime($date);
        if (!$date || !$tst || (date('Y-m-d', $tst) == '1900-01-01')) {
            return '';
        }

        return month_name((int) date('m', $tst)) . " " . date('Y', $tst);
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        if (
            ($row['category'] == HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY) &&
            $this->currentUserIs(Roles::ROLE_SUPERVISOR)
        ) {
            $actions->setInvisibleActions(array(
                'delete',
            ));
        }
        if (
            ($this->_options['status'] == HM_Tc_Application_ApplicationModel::STATUS_COMPLETE)
        ) {
            $actions->setInvisibleActions(array(
                'delete',
            ));
        }
    }

    function updateApplicationStatus($applicationStatus)
    {
        $statuses = array(
            HM_Tc_Application_ApplicationModel::STATUS_ACTIVE => _('Не согласована'),
            HM_Tc_Application_ApplicationModel::STATUS_COMPLETE => _('Согласована'),
        );
        return $statuses[$applicationStatus];
    }
}