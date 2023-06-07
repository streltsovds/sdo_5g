<?php
class Reserve_PositionController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_reservesCache = null;
    protected $_positionSoids = array();

    public function init()
    {
        $positions = $this->getService('Orgstructure')->fetchAll(array(
            'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
            'blocked = ?' => 0
        ))->getList('soid');
        foreach ($positions as $position) $this->_positionSoids[] = $position;

        $this->_defaultService = $this->getService('HrReserve');
        return parent::init();
    }

    public function indexAction()
    {
        $this->view->setHeader(_('Должности кадрового резерва'));

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'created_DESC');
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
            $default = Zend_Registry::get('session_namespace_default');
            $page = sprintf('%s-%s-%s', 'reserve', 'position', 'list');
            $filter = $this->_request->getParam("filter");
            if (empty($filter) && empty($default->grid[$page]['grid']['filters'])){
                $default->grid[$page]['grid']['filters']['recruiters'] = $this->getService('User')->getCurrentUser()->LastName;
            }
        }

        $select = $this->getService('HrReservePosition')->getSelect();
        $select->from(array('hrp' => 'hr_reserve_positions'),
            array(
                'reserve_position_id' => 'hrp.reserve_position_id',
                'name' => 'hrp.name',
//                'requirements' => 'hrp.requirements',
//                'formation_source' => 'hrp.formation_source',
                'position_id' => 'so.soid',
                'position' => 'so.name',
                'profile_id' => 'so.profile_id',
                'profile' => new Zend_Db_Expr("CASE WHEN so.profile_id IS NOT NULL THEN ap.name ELSE 'не указан' END"),
                'external_candidates_count' => new Zend_Db_Expr("COUNT(vc.reserve_position_id)"),
            )
        );
        $select->joinLeft(array('so' => 'structure_of_organ'), 'so.soid = hrp.position_id', array());
        $select->joinLeft(array('ap' => 'at_profiles'), 'ap.profile_id = so.profile_id', array());
        $select->joinLeft(array('vc' => 'recruit_vacancy_candidates'), 'vc.reserve_position_id = hrp.reserve_position_id', array());
        $select->group(array(
            'hrp.reserve_position_id',
            'hrp.name',
            'hrp.requirements',
            'hrp.formation_source',
            'so.name',
            'ap.name',
            'so.profile_id',
            'so.soid',
        ));

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
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

        $url = array('module' => 'reserve', 'controller' => 'position', 'action' => 'card', 'position_id' => '{{reserve_position_id}}');
        $columns = array(
            'reserve_position_id' => array('hidden' => true),
            'profile_id' => array('hidden' => true),
            'position_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'position' => 1,
//                'decorator' => $this->view->cardLink(
//                    $this->view->url(array('action' => 'card', 'modal' => 1, 'position_id' => '')) . '{{reserve_position_id}}', _('Карточка')
//                ) . ' <a href="' . $this->view->url($url, null, true, false) . '">{{name}}</a>'
            ),
            'profile' => array(
                'title' => _('Профиль должности'),
                'callback' => array(
                    'function'=> array($this, 'updateProfile'),
                    'params'=> array('{{profile_id}}', '{{profile}}')
                ),
                'position' => 2,
            ),
            'position' => array(
                'title' => _('Должность'),
                'decorator' => $this->view->cardLink(
                        $this->view->url(
                            array(
                                'module' => 'orgstructure',
                                'controller' => 'list',
                                'action' => 'card',
                                'baseUrl' => '',
                                'org_id' => ''
                            )
                        ) . '{{position_id}}',
                        HM_Orgstructure_OrgstructureService::getIconTitle(HM_Orgstructure_OrgstructureModel::TYPE_POSITION),
                        'icon-custom',
                        'pcard',
                        'pcard',
                        'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass(HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                    ) . ' {{position}}',
                'position' => 3,
            ),
            'external_candidates_count' => array(
                'title' => _('Количество внешних кандидатов'),
                'position' => 4,
            )
//            'formation_source' => array(
//                'title' => _('Источник формирования'),
//                'position' => 4,
//            ),
//            'requirements' => array(
//                'title' => _('Требования к кандидатам'),
//                'position' => 5,
//            ),
        );

        $filters = array(
            'name' => null,
            'profile' => null,
            'position' =>  array('render' => 'department'),
            'external_candidates_count' => null,
//            'formation_source' => null,
//            'requirements' => null,
        );

        $grid = $this->getGrid($select, $columns, $filters);

        $grid->updateColumn('external_candidates_count',
            array('callback' =>
                array('function' =>
                    array($this,'updateExternalCandidatesCount'),
                    'params' => array('{{reserve_position_id}}', '{{external_candidates_count}}')
                )
            )
        );

        $grid->addAction(array(
            'module' => 'reserve',
            'controller' => 'position',
            'action' => 'edit'
        ),
            array('reserve_position_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'reserve',
            'controller' => 'position',
            'action' => 'delete'
        ),
            array('reserve_position_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'reserve',
                'controller' => 'position',
                'action' => 'delete-by',
            ),
            _('Удалить должности кадрового резерва'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function updateExternalCandidatesCount($reservePositionId, $externalCandidatesCount)
    {
        $return = '<a href="' . $this->view->url(
            array(
                'action' => 'reserve-position-candidates',
                'controller' => 'list',
                'module' => 'candidate',
                'baseUrl' => 'recruit',
                'reserve_position_id' => $reservePositionId
            )
        ) . '" >' . $externalCandidatesCount . '</a>';

        return $externalCandidatesCount ? $return : 0;
    }

    public function descriptionAction()
    {
        $positionId = (int) $this->_getParam('position_id', 0);

        $this->view->positionId = $positionId;
        $this->view->userId = $this->getService('User')->getCurrentUserId();
        $this->view->position = $this->getService('HrReservePosition')->findDependence('ReserveRequest', $positionId)->current();
        $this->view->regText = _('Подать заявку');

        $this->view->setHeader($this->view->position->name);
    }

    public function newAction(){
        $this->view->setHeader(_('Новая должность КР'));
        $form = new HM_Form_ReservePosition();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $values = $form->getValues();

                if (!in_array((int)$values['position_id'], $this->_positionSoids)) {
                    $this->_flashMessenger->addMessage(_('Укажите должность. Возможно, Вы указали подразделение.'));
                    $this->_redirector->gotoSimple('new', 'position', 'reserve');
                }

                $icon = $values['icon'] ? $values['icon'] : $values['server_icon'];
                $values['app_gather_end_date'] = date('Y-m-d', strtotime($values['app_gather_end_date']));

                unset($values['icon']);
                unset($values['server_icon']);

                if (!empty($values['recruiters'])) {
                    $values['recruiters'] = serialize($values['recruiters']);
                }

                if (!empty($values['custom_respondents'])) {
                    $values['custom_respondents'] = serialize($values['custom_respondents']);
                }

                $position = $this->getService('HrReservePosition')->insert($values);

                if ($form->getValue('icon') != null) {
                    HM_Hr_Reserve_Position_PositionService::updateIcon($position->reserve_position_id, $form->getElement('icon'));
                } else {
                    HM_Hr_Reserve_Position_PositionService::updateIcon($position->reserve_position_id, $form->getElement('server_icon'));
                }

                $this->_flashMessenger->addMessage(_('Должность КР успешно создана'));
                $this->_redirectToIndex();
            }
        }

        $this->view->positionSoids = $this->_positionSoids;
        $this->view->form = $form;
    }

    public function editAction()
    {
        $form = new HM_Form_ReservePosition();
        $request = $this->getRequest();
        $reservePositionId = $request->getParam('reserve_position_id');
        $reservePosition = $this->getService('HrReservePosition')->findOne($reservePositionId);

        if ($request->isPost()) {

            $params = $request->getParams();
            if (!isset($params['in_slider']) || !(int)$params['in_slider']) {
                $form->getElement('app_gather_end_date')->setRequired(false);
            }

            if ($form->isValid($params)) {
                $data = $form->getValues();
                $data['reserve_position_id'] = $reservePositionId;

                $icon = $data['icon'] ? $data['icon'] : $data['server_icon'];
                $data['app_gather_end_date'] = date('Y-m-d', strtotime($data['app_gather_end_date']));

                $data['recruiters'] =
                    $data['recruiters'] ? serialize($data['recruiters']) : '';

                $data['custom_respondents'] =
                    $data['custom_respondents'] ? serialize($data['custom_respondents']) : '';

                unset($data['icon']);
                unset($data['server_icon']);

                if (!in_array((int)$data['position_id'], $this->_positionSoids)) {
                    $this->_flashMessenger->addMessage(_('Укажите должность. Возможно, Вы указали подразделение.'));
                    $this->_redirector->gotoSimple('edit', 'position', 'reserve');
                }

                $this->getService('HrReservePosition')->update($data);

                if ($form->getValue('icon') != null) {
                    HM_Hr_Reserve_Position_PositionService::updateIcon($reservePosition->reserve_position_id, $form->getElement('icon'));
                } else {
                    HM_Hr_Reserve_Position_PositionService::updateIcon($reservePosition->reserve_position_id, $form->getElement('server_icon'));
                }

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {

            if ($reservePosition) {
                $data = $customRespondents = array();

                $data['position_id']         = $reservePosition->position_id;
                $data['name']                = $reservePosition->name;
                $data['requirements']        = $reservePosition->requirements;
                $data['formation_source']    = $reservePosition->formation_source;
                $data['in_slider']           = $reservePosition->in_slider;
                $data['description']           = $reservePosition->description;

                if (strtotime($reservePosition->app_gather_end_date) > 0) {
                    $data['app_gather_end_date'] = date('d.m.Y', strtotime($reservePosition->app_gather_end_date));
                } else {
                    $data['app_gather_end_date'] = '';
                }

                if (!empty($reservePosition->custom_respondents)) {
                    $customRespondentIds = unserialize($reservePosition->custom_respondents);
                    foreach ($customRespondentIds as $id) {
                        $user = $this->getService('User')->find($id)->current();
                        $customRespondents[$id] = $user->getName();
                    }
                }
                $data['custom_respondents'] = $customRespondents;

                if (!empty($reservePosition->recruiters)) {
                    $recruiterIds = unserialize($reservePosition->recruiters);
                    foreach ($recruiterIds as $id) {
                        $user = $this->getService('User')->find($id)->current();
                        $recruiters[$id] = $user->getName();
                    }
                }
                $data['recruiters'] = $recruiters;

                $form->populate($data);
            }

            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function delete($id)
    {
        $this->getService('HrReservePosition')->delete($id);
    }

    public function updateProfile($profileId, $str)
    {
        return '<a href="' . $this->view->url(array('baseUrl' => 'at', 'module' => 'profile', 'controller' => 'report', 'action' => 'index', 'profile_id' => $profileId)) . '">' . $this->view->escape($str) . '</a>';
    }

    public function cardAction()
    {

    }
}