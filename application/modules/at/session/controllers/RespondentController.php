<?php
class Session_RespondentController extends HM_Controller_Action_Session
{
    use HM_Controller_Action_Trait_Grid;

    public function listAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'fio_ASC');
        }

        $this->_request->setParam('decimalPlaces', 0);

        $sessionModel = $this->getService('AtSession')->find($this->_request->getParam('session_id'))->current();
        $subSelectAbsence = $this->getService('Absence')->getSelect();
        $subSelectAbsence->from(array('a' => 'absence'), array(
                'user_id' => new Zend_Db_Expr('DISTINCT(a.user_id)'),
                'is_absent' => new Zend_Db_Expr('1'))
        )->where(
            '(DATEDIFF(day, \'' . $sessionModel->end_date   . '\', a.absence_begin) <= 0) AND
                 (DATEDIFF(day, \'' . $sessionModel->begin_date . '\', a.absence_end  ) >= 0)'
        )->group(array('a.user_id', 'a.absence_begin', 'a.absence_end'));

        $select = $this->getService('AtSessionRespondent')->getSelect();

        $select->from(
            array(
                'asr' => 'at_session_respondents'
            ),
            array(
                'MID' => 'p.MID',
                'session_respondent_id',
                'ase.respondent_id',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'department' => 'so.owner_soid',
                'position' => 'so.soid',
                'events' => new Zend_Db_Expr("COUNT(ase.session_event_id)"),
                'events_total' => new Zend_Db_Expr("COUNT(ase.session_event_id)"),
                'events_filled' => new Zend_Db_Expr("SUM(CASE WHEN ase.status = 1 THEN 1 ELSE 0 END)"),
                'progress' => new Zend_Db_Expr("100 * (SUM(CASE WHEN ase.status = 1 THEN 1 ELSE 0 END))/(COUNT(ase.session_event_id))"),
                'is_absent' => new Zend_Db_Expr("CASE WHEN sub.is_absent IS NULL THEN 0 ELSE sub.is_absent END")
//                 'profile' => 'ap.name',
//                 'evaluation_types' => new Zend_Db_Expr("GROUP_CONCAT(CONCAT(aet.method, aet.relation_type))"),
            )
        );

        $select
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.soid = asr.position_id', array())
            ->join(array('p' => 'People'), 'p.MID = asr.user_id', array())
//             ->join(array('ap' => 'at_profiles'), 'ap.profile_id = so.profile_id', array())
//             ->join(array('aet' => 'at_evaluation_type'), 'aet.profile_id = ap.profile_id', array())
            ->join(array('ase' => 'at_session_events'), 'ase.session_id = asr.session_id AND ase.respondent_id = p.MID', array())
            ->joinLeft(array('sub' => $subSelectAbsence),
                'sub.user_id = p.MID',
                array()
            )
            ->where('asr.session_id = ?', $this->_session->session_id);

        if (isset($this->_currentPosition) &&
            $this->_currentPosition &&
            $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            )) {
            $select->where('so.owner_soid = ?', $this->_currentPosition->owner_soid); // @todo: нужно ещё всех вложенных
        }
        
        $select->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'ase.respondent_id', 'asr.session_respondent_id', 'so.owner_soid', 'so.soid', 'progress', 'sub.is_absent'));

//        exit ($select->__toString());
        $grid = $this->getGrid($select, array(
            'MID' => array('hidden' => true),
            'session_respondent_id' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'is_absent' => array('hidden' => true),
            'respondent_id' => array('hidden' => true),
            'events_total' => array('hidden' => true),
            'events_filled' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)))
                    ? $this->view->cardLink($this->view->url([
                            'baseUrl' => '',
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'view',
                            'gridmod' => null,
                            'report' => 1,
                            'user_id' => ''
                        ], null, true) . '{{MID}}')
                    . '<a href="' . $this->view->url(
                        [
                            'baseUrl' => '',
                            'module' => 'user',
                            'controller' => 'report',
                            'action' => 'index',
                            'gridmod' => null,
                            'report' => 1,
                            'user_id' => ''
                        ], null, true) . '{{MID}}' . '">' . '{{fio}}</a>'
                    : null,
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
            'events' => array(
                'title' => _('Количество оценочных форм'),
                'callback' => array(
                    'function'=> array($this, 'updateEvents'),
                    'params' => array('{{events}}', '{{fio}}')
                )
            ),
            'progress' => array(
                'title' => _('Прогресс заполнения, %'),
//                'callback' => array(
//                    'function'=> array($this, 'updateProgress'),
//                    'params' => array('{{events_filled}}', '{{events_total}}')
//                )
            ),
        ),
        array(
            'fio' => null,
            'department' =>  array(
                'render' => 'department'
            ),
            'position' =>  array(
                'callback' => array(
                    'function'=>array($this, 'orgFilter'),
                    'params'=>array()
                )
            ),
            'progress' => null,
        ));
        
        $grid->addAction(array(
            'baseUrl' => '',
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index',
            'session_id' => $this->_session->session_id
        ),
           array('MID'),
           _('Отправить сообщение')
        );
               
        $grid->addAction(array(
            'baseUrl' => '',
            'module' => 'user',
            'controller' => 'list',
            'action' => 'login-as'
        ),
            array('MID'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );

        $grid->addMassAction($this->view->url(array(
                'baseUrl' => '',
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index',
                'session_id' => $this->_session->session_id
            )),
            _('Отправить сообщение')
        );

        $grid->setClassRowCondition("{{is_absent}} > 0",'highlighted');
        
        $this->view->grid = $grid;
    }

    public function updateEvents($events, $fio)
    {
        $url = $this->view->url(array(
            'action' => 'list',
            'controller' => 'event',
            'module' => 'session',
            'gridmod' => 'ajax', // только так фильтр устанавливается; надо бы убрать этот костыль
            'respondentgrid' => $fio
        ));
        $title = _('Список анкет');
        return "<a href='{$url}' title='{$title}'>{$events}</a>";
    }

    public function updateStatus($status)
    {
        return HM_At_Session_Respondent_RespondentModel::getStatus((int)$status);
    }

    public function updateProgress($filled, $total)
    {
        return $total ? floor(100 * $filled/$total) . '%' : '';
    }

// столбец с методами не совсем нужен - их можно понять из БП
//     public function updateMethod($methods)
//     {
//         $result = array();
//         foreach (explode(',', $methods) as $method) {
//             $relationType = false;
//             if (strpos($method, HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE) !== false) {
//                 $relationType = str_replace(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, '', $method);
//             }
//             $result[] = HM_At_Evaluation_EvaluationModel::getMethodTitle($method, $relationType);
//         }
//     }
}
