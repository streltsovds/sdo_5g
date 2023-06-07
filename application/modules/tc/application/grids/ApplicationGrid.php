<?php

use HM_Grid_ColumnCallback_Els_UserCardLink        as UserCardLink;
use HM_Grid_ColumnCallback_Tc_SubjectCardLink      as SubjectCardLink;
use HM_Grid_ColumnCallback_Tc_ProviderCardLink     as ProviderCardLink;
use HM_Grid_ColumnCallback_Other_NumberFormat      as NumberFormat;
use HM_Role_Abstract_RoleModel as Roles;
use HM_Tc_Application_ApplicationModel as TcApplicationModel;

class HM_Application_Grid_ApplicationGrid extends HM_Grid {

    protected static $_defaultOptions = array(
        'applicationId'       => 0,
        'departmentId'        => 0,
        'sessionId'           => 0,
        'sessionDepartmentId' => 0,
        'status'              => TcApplicationModel::STATUS_ACTIVE,
        'defaultOrder'        => 'fio_ASC',
        'showButton'          => false
    );

    public function init($source = null)
    {
        $this->setClassRowCondition(
            "'{{subject_status}}' == '0' || '{{provider_status}}' == '0'",
            "highlighted"
        );
        parent::init($source);
    }

    protected function _initColumns()
    {
        $numberFormat   = new NumberFormat($this);
        $providerCardLink = new ProviderCardLink();

        $this->_columns = array(
            'application_id' => array('hidden'=> true),
            'subject_id' => array('hidden'=> true),
            'provider_id' => array('hidden'=> true),
            'subject_status' => array('hidden'=> true),
            'provider_status' => array('hidden'=> true),
            'MID' => array('hidden'=> true),
            'fio' => array(
                'title' => _('ФИО'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{fio}}', '{{MID}}', '{{application_id}}'))
            ),
//            'position_full' => array('title' => _('Должность')),
            'position_id' => array('hidden' => true),
            'is_manager' => array('hidden' => true),
            'position' => array(
                'title' => _('Должность'),
                'position' => 2,
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
                'title'=> _('Курс'),
                'callback' => array(
                    'function'=> array($this, 'updateSubject'),
                    'params'=> array('{{subject_id}}', '{{subject}}'))
            ),
            'price' => array(
                'title'=> _('Стоимость'),
                'style'    => "text-align:right",
                'callback' => $numberFormat->getCallback('{{price}}')
            ),
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
            'category' => array(
                'title'=> _('Тип обучения'),
                'callback' => array(
                    'function'=> array($this, 'updateCategory'),
                    'params'=> array('{{category}}'))
            ),
            'department_name' => array(
                'title'=> _('Подразделение'), 'hidden'=> true
            ),
            'department' => array(
                'title'  => _('Департамент'), 'hidden'=> true,
                'position' => 4,
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
            'previous_period' => array(
                'title'  => _('Прошлый период'),
                'callback' => array(
                    'function'=> array($this, 'updatePreviousPeriod'),
                    'params'=> array('{{application_id}}')),
                'position' => 7,
            ),
            'cost_item' => array(
                    'title'=> _('Статья расходов'),
                    'callback' => array(
                        'function'=> array($this, 'updateCostItem'),
                        'params'=> array('{{cost_item}}'))
                ),
            'event_name' => array('hidden'=> true),
       );
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        $session = $this->getController()->getSession();
        if (!$this->getService('TcSession')->isApplicable($session)) return;

        $actions
            ->add('edit', array(
                'module'     => 'application',
                'controller' => 'list',
                'action'     => 'edit',
                'session_id' => $session->session_id//null
            ))
            ->setParams(array(
                'application_id',
            ));

        $actions
            ->add('delete', array(
                'module'     => 'application',
                'controller' => 'list',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'application_id'
            ));
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        $session = $this->getController()->getSession();
        $createAllowed = $this->getService('TcSession')->isApplicable($session);

        // @todo: лучше бы это зарулить на ACL
        if ($this->currentUserIs(array(Roles::ROLE_DEAN, Roles::ROLE_DEAN_LOCAL))) {
            $massActions->add(
                $this->getView()->url(array(
                    'module' => 'application',
                    'controller' => 'list',
                    'action' => 'set-cost-item-by',
                )),
                _('Назначить статью расхода'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            )->addSelect(
                'costItem',
                TcApplicationModel::getCostItems(false)
            );

            if ($createAllowed) {
                $massActions->add(
                    $this->getView()->url(array(
                        'module' => 'application',
                        'controller' => 'list',
                        'action' => 'delete-by',
                        'grid' => $this->getGridId()
                    )),
                    _('Удалить'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }
        }
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'fio' => null,
//            'position' => null,
            'position_full' =>array(
                'callback' => array(
                    'function'=>array($this, 'positionFullFilter'),
                    'params'=>array()
                )
            ),
            'subject' => null,
            'price' => null,
            'provider_name' => null,
            'initiator' => null,
            'period' => array('render' => 'Date'),
            'position' => array('render' => 'department'),
//            'department' => array('values' => $this->getDepartmentsNames()),
            'category' => array('values' => TcApplicationModel::getApplicationCategories()),
//            'department_name' => array('render' => 'department'),
            'payment_type' => array(
                'values' => TcApplicationModel::getPaymentTypes()
            ),
            'cost_item' => array(
                'values' => TcApplicationModel::getCostItems(false)
            ),
        ));
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
            $select->orWhere("son.name LIKE ?)", $value);


        }

    }

    protected function getDepartmentsNames()
    {
        $select = $this->getService('TcApplication')->getClaimantListSource(
            array(
                'sessionId' => $this->_options['sessionId'],
                'status'    => HM_Tc_Application_ApplicationModel::STATUS_COMPLETE
            )
        );
        $results = array();
        foreach ($select->query()->fetchAll() as $row) {
            if ($row['department'] && !in_array($row['department'], $results)) $results[$row['department']] = $row['department'];
        }

        asort($results);
        return $results;
    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        if ($this->_options['sessionDepartmentId']) {
            $model = $this->getService('TcSessionDepartment')->getOne(
                $this->getService('TcSessionDepartment')->find($this->_options['sessionDepartmentId'])
            );
            $state = $this->getService('Process')->getCurrentState($model);

            if ($state) {

                if ($this->currentUserIs(Roles::ROLE_SUPERVISOR) && !($state instanceof HM_Tc_Session_Department_State_Open)) {
                    return;
                }

                if ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) {
                    return;
                }

                $className = $state->getNextState();

                $sendForAgreement = ($className == 'HM_Tc_Session_Department_State_Agreement') &&
                    $this->currentUserIs(array(Roles::ROLE_SUPERVISOR));

                if (!$className) {
                    return;
                } elseif ($sendForAgreement) {
                    $message = _('Отправить на согласование');
                } elseif ($className == 'HM_Tc_Session_Department_State_Complete') {
                    $message = _('Завершить согласование консолидированной заявки');
                } else {
                    $next = new $className();
                    $message = _('Перевести на этап: ') . $next->getCurrentStateMessage();
                }

                $actionLink = array(
                    'urlParams' => array(
                        'module' => 'session',
                        'controller' => 'consolidated',
                        'action' => 'change-state',
                        'session_department_id' => $this->_options['sessionDepartmentId'],
                        'state' => HM_State_Abstract::STATE_STATUS_CONTINUING
                    ),
                    'title' => $message,
                    'class' => 'gridMenuCustom',
                );

                if ($sendForAgreement) {
                    $actionLink['onclick'] = 'return confirm(\'' . _("Вы действительно желаете отправить консолидированную заявку на согласование менеджером по обучению? Дальнейшее изменение параметров заявки будет невозможно. Продолжить?") . '\')';
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
        return $this->_options['sessionId'].$this->_options['departmentId'];
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

    public function updateName($fio, $MID)
    {
        $result = '';

        if (strlen($fio) && $MID) {
            $userCardLink     = new UserCardLink();
            $result = $userCardLink($MID, $fio);
        }

        return $result;
    }

    public function updateSubject($subject_id, $subjectName)
    {
        if ($subject_id) {
            $subjectCardLink     = new SubjectCardLink();
            return $subjectCardLink($subject_id, $subjectName);
        }
        return '';
    }

    public function updateCostItem($item)
    {
        static $costItems = null;
        if ($costItems === null){
            $costItems = TcApplicationModel::getCostItems();
        }

        return isset($costItems[$item]) ? $costItems[$item] : '--не назначена--';
    }

    public function updatePaymentType($type)
    {
        static $paymentTypes = null;
        if ($paymentTypes === null){
            $paymentTypes = TcApplicationModel::getPaymentTypes();
        }

        return isset($paymentTypes[$type]) ? $paymentTypes[$type] : '--не указано--';
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
        // нельзя удалять обязательно и уже согласованное обучение
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

    public function updatePreviousPeriod($applicationId)
    {
        $sessionId = $this->getRequest()->getParam('session_id');
        $session = Zend_Registry::get('serviceContainer')->getService('TcSession')->getOne(
            Zend_Registry::get('serviceContainer')->getService('TcSession')->find($sessionId)
        );

        $currentCycle = Zend_Registry::get('serviceContainer')->getService('Cycle')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Cycle')->find($session->cycle_id)
        );

        $previousCycle = Zend_Registry::get('serviceContainer')->getService('Cycle')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Cycle')->fetchAll(
                Zend_Registry::get('serviceContainer')->getService('Cycle')->quoteInto(
                    array(' year = ? AND ', ' (quarter IS NULL OR quarter = 0) AND type = \'tc\' '),
                    array($currentCycle->year - 1)
                )
            )
        );

        $previousSessions = $result = Zend_Registry::get('serviceContainer')->getService('TcSession')->fetchAll(
            Zend_Registry::get('serviceContainer')->getService('TcSession')->quoteInto(
                array(' cycle_id = ? '),
                array($previousCycle->cycle_id)
            )
        )->getList('session_id');

        $application = Zend_Registry::get('serviceContainer')->getService('TcApplication')->getOne(
            Zend_Registry::get('serviceContainer')->getService('TcApplication')->find($applicationId)
        );

        $employeeId = $application->user_id;

        $result = Zend_Registry::get('serviceContainer')->getService('TcApplication')->fetchAll(
            Zend_Registry::get('serviceContainer')->getService('TcApplication')->quoteInto(
                array(
                    ' user_id = ? AND ',
                    ' session_id IN (?) AND ',
                    ' category = ? AND ',
                    ' status = ? '
                ),
                array(
                    $employeeId,
                    count($previousSessions) ? $previousSessions : array(0),
                    HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION,
                    HM_Tc_Application_ApplicationModel::STATUS_COMPLETE
                )
            )
        )->getList('application_id');

        $url = Zend_Registry::get('view')->url(
            array(
                'module' => 'session',
                'controller' => 'list',
                'action' => 'past',
                'employee' => $employeeId
            )
        );
        return count($result) ? '<a href="'. $url .'">' . count($result) . ' ' . $this->getCaseFor('курс', count($result)) . '</a>' : 0 . ' ' . $this->getCaseFor('курс', 0);
    }

    private function getCaseFor($word, $number)
    {
        if ($number == 1 || ($number%10 == 1 && $number%100 != 11)) {
            return $word;
        } else if (in_array($number, array(2, 3, 4)) || in_array($number%10, array(2, 3, 4))) {
            return $word . 'а';
        } else {
            return $word . 'ов';
        }
    }
}