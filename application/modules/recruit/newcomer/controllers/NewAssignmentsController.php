<?php
class Newcomer_NewAssignmentsController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    //ограничение по кол-ву месяцев нахождения в должности
    const POS_DURATION = 6;

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
                'rn.newcomer_id',
                'org_id' => 'so.soid',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'department' => 'so2.name',
                'name' => 'so.name',
                'is_manager' => 'so.is_manager',
                'position_date'=>'so.position_date',
                'vacancy_id'=>'rv.vacancy_id',
                'vacancy'=>'rv.name',
                'duplicate' => new Zend_Db_Expr('CASE WHEN (p.duplicate_of = 0) THEN 0 ELSE 1 END'),
            )
        );

        $select
            ->joinInner(array('so' => 'structure_of_organ'), 'so.mid = p.MID', array())
            ->joinInner(array('so2' => 'structure_of_organ'), 'so2.soid = so.owner_soid', array())
            ->joinLeft(array('ap' => 'at_profiles'), 'so.profile_id = ap.profile_id', array())
            ->joinLeft(array('rn' => 'recruit_newcomers'), 'rn.position_id = so.soid', array())
            ->joinLeft(array('rvc' => 'recruit_vacancy_candidates'), 'rvc.user_id = p.MID AND rvc.result = ' . HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS, array())
            ->joinLeft(array('rv' => 'recruit_vacancies'), 'rv.vacancy_id = rvc.vacancy_id AND rv.position_id = so.soid', array())
            ->where('p.blocked = ?',  0)
            ->where('so.blocked = ?',  0)
            ->where('so.type = ?',  1)
            ->where('so.is_first_position = ?',  1)
//            ->where('rn.newcomer_id is null')
            ->where('so.position_date > ?', date("Y-m-d H:i:s", strtotime("-".Newcomer_NewAssignmentsController::POS_DURATION." months")))
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
                'rn.newcomer_id',
                'ap.profile_id',
                'so2.name',
                'rv.vacancy_id',
                'rv.name',
            ))
            ->order('position_date DESC');

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            // все по области ответственности, даже не назначенные
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("so.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $grid = $this->getGrid($select, array(
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'newcomer_id' => array('hidden' => true),
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
            'position_date' => array(
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
                'position_date' => array('render' => 'DateSmart'),
                'duplicate' => array(
                    'values'     => array(
                        1 => _('Да'),
                        0 => _('Нет')
                    ),
                    'searchType' => '='
                ),
            ));

        $grid->addAction(array(
            'module' => 'newcomer' ,
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
                'module' => 'newcomer',
                'controller' => 'new-assignments',
                'action' => 'create-adaptation-sessions',
            ),
            _('Создать сессии адаптации')
        );

        $grid->setClassRowCondition("{{duplicate}} > 0", 'highlighted');

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{duplicate}}', '{{profile_id}}', '{{newcomer_id}}')
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
                $repeatErrorShown = false;
                $wasSuccess = false;

                foreach($ids as $id) {
                    $hasError = false;
                    if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->fetchAllDependence(array('Parent', 'User'), array('mid = ?' => $id)))){

                        $collection = $this->getService('RecruitNewcomer')->fetchAll(array('position_id = ?' => $position->soid));
                        if (count($collection)) {
                            foreach($collection as $newcomer) {
                                $this->getService('Process')->initProcess($newcomer);
                                $status = $newcomer->getProcess()->getStatus();
                                if (in_array($status, array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING))) {

                                    if(!$repeatErrorShown) {
                                        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации повторно')));
                                        $repeatErrorShown = true;
                                    }

                                    $hasError = true;
                                }
                            }
                        }

                        if(!$hasError) {
                            $this->getService('RecruitNewcomer')->createByPosition($position);
                            $wasSuccess = true;
                        }
                    }
                }

                if($wasSuccess) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации успешно создана')));
                }
            }
            if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'newcomer') {
                $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
            } else {
                $this->_redirector->gotoSimple('index', 'new-assignments', 'newcomer');
            }
        }
        $this->_redirectToIndex();
    }

    public function updateDuplicate($duplicate) 
    {
        return $duplicate ? _('Да') : _('Нет');
    }

    public function updateActions($duplicate, $profileId, $newcomerId, $actions)
    {
        if ($duplicate != _('Да')) {
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'duplicate-merge', 'baseUrl' => ''));
        }
        if (!$profileId || $newcomerId) {
            $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'list', 'action' => 'create-from-structure'));
        }
        return $actions;
    }
}