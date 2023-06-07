<?php

use HM_Grid_ColumnCallback_Tc_SubjectCardLink      as SubjectCardLink;
use HM_Grid_ColumnCallback_Els_UserCardLink        as UserCardLink;
use HM_Role_Abstract_RoleModel                      as Roles;

class HM_SessionQuarter_Grid_StudentsGrid extends HM_Grid
{

    protected static $_defaultOptions = array(
        'sessionQuarterId'    => 0,
    );

    public function init($source = null)
    {
        $this->setClassRowCondition("{{is_absent}} > 0",'highlighted');

        parent::init($source);
    }


    protected function _initColumns()
    {

        $subjectCardLink  = new SubjectCardLink();
        $this->_columns = array(
            'application_id' => array('hidden'=> true),
            'MID' => array('hidden'=> true),
            'is_absent' => array('hidden' => true),
            'has_student' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{fio}}', '{{MID}}', '{{application_id}}'))
            ),
//            'position_full' => array('title'=> _('Должность')),
            'department' =>  array(
                'title'=> _('Подразделение'), 'hidden'=> true
            ),
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
            'subject_name' => array(
                'title'=> _('Курс'),
                'callback' => $subjectCardLink->getCallback('{{subjectId}}', '{{subject_name}}'),
            ),
            'subjectId' => array('hidden'=> true),
            'comment' => array(
                'title'=> _('Комментарий'),
                'callback' => array(
                    'function' => array($this, 'updateComment'),
                    'params'   => array('{{comment}}'))
            ),
            'period' => array(
                'title'=> _('Запланировано на'),
                'callback' => array(
                    'function' => array($this, 'monthDate'),
                    'params'   => array('{{period}}'))
            ),
            'study_status' => array(
                'title'=> _('Статус назначения'),
                'callback' => array(
                    'function' => array($this, 'updateStudyStatus'),
                    'params'   => array('{{study_status}}'))
            ),
            'price' => array(
                'title' => _('Стоимость'),
            ),
            /*'notified' => array(
                'title' => _('Уведомление'),
                'callback' => array(
                    'function'=> array($this, 'updateNotified'),
                    'params'=> array('{{notified}}'))
            ),*/
        );
    }



    public function positionFullFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){

            $value = '%' . $value . '%';

            $select->where("(so.name LIKE ?", $value);
            $select->orWhere("so2.name LIKE ?", $value);
            $select->orWhere("so3.name LIKE ?)", $value);
        }

    }

    public function updateComment($comment)
    {
        return substr($comment, 0, 100);
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        if (empty($row['has_student'])) {
            $actions->setInvisibleActions(array(
                _('Ввести комментарий')
            ));
        }
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'fio' =>  null,
//            'department' =>  null,
            'position' =>  array('render' => 'department'),
//            'position_full' =>array(
//                'callback' => array(
//                    'function'=>array($this, 'positionFullFilter'),
//                    'params'=>array()
//                )
//            ),
            'subject_name' =>  null,
            'comment' =>  null,
            'price' =>  null,
            'period' => array('render' => 'Date'),
            'study_status' => array(
                'values' => HM_Tc_Application_ApplicationModel::getStudyStatuses(),
            ),
            //'notified' => array('values' => array('не отправлено', 'отправлено'))
        ));
    }

    protected function _initSwitcher(HM_Grid_Switcher $switcher)
    {

    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if ($this->currentUserIs(array(Roles::ROLE_DEAN, Roles::ROLE_DEAN_LOCAL))) {
            $actions->add(_('Войти от имени пользователя'), array(
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'list',
                'action' => 'login-as'
            ))
            ->setParams(array('MID'));

            $actions->add(_('Заменить пользователя'), array(
                'baseUrl' => '',
                'module' => 'assign',
                'controller' => 'student',
                'action' => 'change-student',
                'model' => 'Student'
            ))
            ->setParams(array('MID', 'subjectId', 'application_id'));

            $actions->add(_('Ввести комментарий'), array(
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'student',
                'action' => 'set-comment',
            ))
            ->setParams(array('MID' => 'user_id', 'subjectId'));
        }
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        $massActions->add(
            array(
                'module'     => 'session-quarter',
                'controller' => 'subject',
                'action'     => 'assign-sessions',
                'session_quarter_id' => $this->getController()->_sessionQuarterId
            ),
            _('Назначение на сессии'),
            _('Вы действительно хотите назначить отмеченных пользователей на учебные сессии?')
        );

        $massActions->add(
            array(
                'module' => 'session-quarter',
                'controller' => 'subject',
                'action' => 'send-notifications',
                'simple' => '1',
                'session_quarter_id' => $this->getController()->_sessionQuarterId
            ),
            _('Отправить уведомление'),
            _('Вы действительно хоите отправить уведомления отмеченным пользователям? Продолжить?')
        );


    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {

    }

    public function getCourseId()
    {
        return $this->_options['sessionQuarterId'];
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

    public function updateStatus($status)
    {
        $statuses = HM_Tc_Session_SessionModel::getStatuses();
        if (isset($statuses[$status])) {
            return $statuses[$status];
        }
        return $status;
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

    public function updateNotified($notified)
    {
        return ($notified == 1) ? 'отправлено' : 'не отправлено';
    }

    function updateStudyStatus($studyStatus)
    {
        $statuses = HM_Tc_Application_ApplicationModel::getStudyStatuses();
        return $statuses[$studyStatus];
    }

    public function monthDate($date, $checkSession = false)
    {
        $tst = strtotime($date);
        if (!$date || !$tst || (date('Y-m-d', $tst) == '1900-01-01')) {
            return '';
        }

        return month_name((int) date('m', $tst)) . " " . date('Y', $tst);
    }


} 