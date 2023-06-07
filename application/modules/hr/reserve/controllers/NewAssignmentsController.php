<?php
class Reserve_NewAssignmentsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    //ограничение по кол-ву месяцев нахождения в должности
    const POS_DURATION = 3;

    public function indexAction()
    {
        $select = $this->getService('User')->getSelect();
        $select->from(
            array(
                'p' => 'People'
            ),
            array(
                'MID' => 'p.MID',
                'user_id' => 'p.MID',
                'profile_id' => 'ap.profile_id',
                'hr.reserve_id',
                'org_id' => 'so.soid',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'department' => 'so2.name',
                'name' => 'so.name',
                'is_manager' => 'so.is_manager',
                'pos_date'=>'so.position_date',
                'vacancy_id'=>'rv.vacancy_id',
                'vacancy'=>'rv.name',
                'duplicate' => new Zend_Db_Expr('CASE WHEN (p.duplicate_of = 0) THEN 0 ELSE 1 END'),
            )
        );

        $select
            ->joinInner(array('so' => 'structure_of_organ'), 'so.mid = p.MID', array())
            ->joinInner(array('so2' => 'structure_of_organ'), 'so2.soid = so.owner_soid', array())
            ->joinLeft(array('ap' => 'at_profiles'), 'so.profile_id = ap.profile_id', array())
            ->joinLeft(array('hr' => 'hr_reserves'), 'hr.position_id = so.soid', array())
            ->joinLeft(array('rvc' => 'recruit_vacancy_candidates'), 'rvc.user_id = p.MID AND rvc.result = ' . HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS, array())
            ->joinLeft(array('rv' => 'recruit_vacancies'), 'rv.vacancy_id = rvc.vacancy_id AND rv.position_id = so.soid', array())
            ->where('p.blocked = ?',  0)
            ->where('so.blocked = ?',  0)
            ->where('so.type = ?',  1)
            ->where('hr.reserve_id is null')
            ->where('so.position_date > ?', date("Y-m-d H:i:s", strtotime("-".Reserve_NewAssignmentsController::POS_DURATION." months")))
            ->group(array(
                'p.MID',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'p.duplicate_of',
                'so.soid',
                'so.name',
                'so.position_date',
                'so.is_manager',
                'hr.reserve_id',
                'ap.profile_id',
                'so2.name',
                'rv.vacancy_id',
                'rv.name',
            ))
            ->order('pos_date DESC');

        $grid = $this->getGrid($select, array(
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'reserve_id' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'profile_id' => array('hidden' => true),
            'org_id' => array('hidden' => true),
            'is_manager' => array('hidden' => true),
            'params' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' =>  $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{MID}}') . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{MID}}' . '">' . '{{fio}}</a>'
            ),
            'department' => array(
                'title' => _('Подразделение'),
            ),
            'name' => array(
                'title' => _('Должность'),
                'callback' => array('function' => array($this, 'updatePositionName'), 'params' => array('{{name}}', '{{org_id}}', HM_Orgstructure_OrgstructureModel::TYPE_POSITION, '{{is_manager}}'))
            ),
            'vacancy' => array(
                'title' => _('Сессия подбора'),
                'decorator' =>  '<a href="' . $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'gridmod' => null, 'vacancy_id' => ''), null, true) . '{{vacancy_id}}' . '">' . '{{vacancy}}</a>'
            ),
            'pos_date' => array(
                'title' => _('Дата приема'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
            ),
            'duplicate' => array(
                'title' => _('Дубл.'),
                'callback' => array(
                    'function' => array($this, 'updateDuplicate'),
                    'params' => array('{{duplicate}}')
                )
            ),
        ),
            array(
                'name' => null,
                'fio' => null,
                'department' => array('render' => 'department'),
                'vacancy' => null,
                'pos_date' => array('render' => 'SubjectDate'),
                'duplicate' => array(
                    'values'     => array(
                        1 => _('Да'),
                        0 => _('Нет')
                    ),
                    'searchType' => '='
                ),
            ));

        $grid->addAction(array(
            'module' => 'reserve' ,
            'controller' => 'list',
            'action' => 'create-from-structure',
            'key' => null,
            'treeajax' => null,
        ),
            array('org_id'),
            _('Создать сессию адаптации')
        );

        $grid->addAction(array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'duplicate-merge',
                'from' => 'new-assignments',
                'baseUrl' => '',
            ),
            array('MID'),
            _('Объединение дубликатов')
        );

        $grid->addMassAction(
            array(
                'module' => 'reserve',
                'controller' => 'new-assignments',
                'action' => 'create-adaptation-sessions',
            ),
            _('Создать сессии адаптации')
        );

        $grid->setClassRowCondition("{{duplicate}} > 0", 'highlighted');

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{duplicate}}', '{{profile_id}}')
            )
        );

        $this->view->grid = $grid;
    }

    public function createAdaptationSessionsAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->fetchAllDependence(array('Parent', 'User'), array('mid = ?' => $id)))){

                        $collection = $this->getService('HrReserve')->fetchAll(array('position_id = ?' => $position->soid));
                        if (count($collection)) {
                            foreach($collection as $reserve) {
                                $this->getService('Process')->initProcess($reserve);
                                $status = $reserve->getProcess()->getStatus();
                                if (in_array($status, array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING))) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации повторно')));
                                }
                            }
                        }

                        $this->getService('HrReserve')->createByPosition($position);
                    }
                }
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации успешно создана')));
            }
            if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'reserve') {
                $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
            } else {
                $this->_redirector->gotoSimple('index', 'new-assignments', 'reserve');
            }
        }
        $this->_redirectToIndex();
    }

    public function updateDuplicate($duplicate) 
    {
        return $duplicate ? _('Да') : _('Нет');
    }

    public function updateActions($duplicate, $profileId, $actions)
    {
        if ($duplicate != _('Да')) {
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'duplicate-merge', 'baseUrl' => ''));
        }
        if (!$profileId) {
            $this->unsetAction($actions, array('module' => 'reserve', 'controller' => 'list', 'action' => 'create-from-structure'));
        }
        return $actions;
    }
}