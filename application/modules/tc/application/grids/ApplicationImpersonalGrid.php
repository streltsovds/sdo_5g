<?php

use HM_Grid_ColumnCallback_Tc_SubjectCardLink      as SubjectCardLink;
use HM_Grid_ColumnCallback_Tc_ProviderCardLink     as ProviderCardLink;
use HM_Grid_ColumnCallback_Other_NumberFormat      as NumberFormat;
use HM_Role_Abstract_RoleModel as Roles;
use HM_Tc_Application_ApplicationModel as TcApplicationModel;
use HM_Tc_ApplicationImpersonal_ApplicationImpersonalModel as TcApplicationImpersonalModel;

class HM_Application_Grid_ApplicationImpersonalGrid extends HM_Grid {

    protected static $_defaultOptions = array(
        'applicationId'    => 0,
        'departmentId'     => 0,
        'sessionId'        => 0,
        'sessionDepartmentId' => 0,
        'status'           => TcApplicationImpersonalModel::STATUS_ACTIVE,
        'defaultOrder' => 'application_impersonal_id_ASC',
    );

    protected function _initColumns()
    {
        $numberFormat   = new NumberFormat($this);
        $providerCardLink = new ProviderCardLink();

        $this->_columns = array(
            'application_impersonal_id' => array('hidden'=> true),
            'session_id2' => array('hidden'=> true),
            'subjectId' => array('hidden'=> true),
            'subject_status' => array('hidden'=> true),
            'provider_status' => array('hidden'=> true),
            'quarter' => array('hidden'=> true),
            'subject' => array(
                'title'=> _('Курс/мероприятие'),
                'callback' => array(
                    'function'=> array($this, 'updateSubject'),
                    'params'=> array('{{subjectId}}', '{{subject}}', '{{event_name}}'))
            ),
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
            'longtime' => array('hidden'=> true),
            'quantity' => array('title'=> _('Количество пользователей')),
            'department_goal' => array('hidden'=> true), //array('title'=> _('Цель подразделения')),
            'education_goal' => array('hidden'=> true), //array('title'=> _('Цель обучения')),
            'category' => array(
                'title'=> _('Тип обучения'),
                'callback' => array(
                    'function'=> array($this, 'updateCategory'),
                    'params'=> array('{{category}}'))
            ),
            'department_name' => array(
                'title'=> _('Подразделение'),
            ),
            'department' => array(
                'title'  => _('Департамент'),
                'position' => 4,
            ),
            'manager_id' => array('hidden'=> true),
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
                'controller' => 'impersonal',
                'action'     => 'edit',
                'session_id' => $session->session_id//null
            ))
            ->setParams(array(
                'application_impersonal_id',
            ));

        $actions
            ->add('delete', array(
                'module'     => 'application',
                'controller' => 'impersonal',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'application_impersonal_id'
            ));

    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        $session = $this->getController()->getSession();
        if (!$this->getService('TcSession')->isApplicable($session)) return;

        $massActions->add(
            $this->getView()->url(array(
                'module' => 'application',
                'controller' => 'impersonal',
                'action' => 'set-cost-item-by',
            )),
            _('Назначить статью расхода'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        )->addSelect(
            'costItem',
            TcApplicationModel::getCostItems(false)
        );

        $massActions->add(
            $this->getView()->url(array(
                'module' => 'application',
                'controller' => 'impersonal',
                'action' => 'delete-by',
                'grid' => $this->getGridId()
            )),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'subject' => null,
            'quantity' => null,
            'price' => null,
            'provider_name' => null,
            'period' => array('render' => 'Date'),
            'longtime' => null,
            'department' => array('values' => $this->getDepartmentsNames()),
            'department_name' => array('render' => 'department'),
            'manager_fio' => null,
            'cost_item' => array(
                'values' => TcApplicationModel::getCostItems(false)
            ),
        ));
    }

    protected function getDepartmentsNames()
    {
        $select = $this->getService('TcApplicationImpersonal')->getClaimantListSource(
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

        return isset($costItems[$item]) ? $costItems[$item] : '--не назначена--';
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
        if($row['category'] == HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY) {
            $actions->setInvisibleActions(array(
                'delete',
            ));
        }
    }
}