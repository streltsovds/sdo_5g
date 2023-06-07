<?php
class ReserveRequest_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction()
    {
        $this->view->setHeader(_('Заявки на участие в сессиях кадрового резерва'));
        
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'created_DESC');
        }

        $select = $this->getService('HrReserveRequest')->getSelect();
        $select->from(
            array(
                'r' => 'hr_reserve_requests'
            ),
            array(
                'r.reserve_request_id',
                'r.reserve_id',
                'MID' => 'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'r.request_date',
                'position' => 'rp.name',
                'status' => 'r.status',
                'status_int' => 'r.status',
            )
        );

        $select
            ->joinLeft(array('p' => 'People'), 'p.MID = r.user_id', array())
            ->joinLeft(array('rp' => 'hr_reserve_positions'), 'rp.reserve_position_id = r.position_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'rp.position_id = so.soid', array())
            ->group(
                array(
                    'r.reserve_request_id',
                    'p.MID',
                    'p.LastName',
                    'p.FirstName',
                    'p.Patronymic',
                    'r.request_date',
                    'rp.name',
                    'r.reserve_id',
                    'r.status',
                )
            );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
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

        $columns = array(
            'reserve_request_id' => array('hidden' => true),
            'reserve_id' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'status_int' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' =>  $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'view',
                            'gridmod' => null,
                            'baseUrl' => '',
                            'user_id' => ''
                        ),
                        null, true
                    ) . '{{MID}}') .
                    ' <a href="' .
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'edit',
                            'action' => 'card',
                            'gridmod' => null,
                            'baseUrl' => '',
                            'user_id' => ''
                        ),
                        null, true
                    ) .
                    '{{MID}}' . '">' . '{{fio}}</a>',
                'position' => 1,
            ),
            'position' => array(
                'title' => _('Должность КР'),
                'position' => 2,
            ),
            'request_date' => array(
                'title' => _('Дата подачи заявки'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 3,
            ),
            'status' => array(
                'title' => _('Статус заявки'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                ),
                'position' => 4,
            ),
        );

        $filters = array(
            'fio' => null,
            'position' => null,
            'request_date' => array('render' => 'Date'),
            'status' => array('values' => HM_Hr_Reserve_Request_RequestModel::getStatusTitles())
        );

        $grid = $this->getGrid($select, $columns, $filters);

        $grid->addAction(array(
            'module' => 'reserve',
            'controller' => 'list',
            'action' => 'new-from-request'
        ),
            array('reserve_request_id'),
            _('Принять')
        );

        $grid->addAction(array(
            'module' => 'reserve-request',
            'controller' => 'list',
            'action' => 'decline'
        ),
            array('reserve_request_id'),
            _('Отклонить')
        );

        $grid->addAction(array(
            'module' => 'reserve-request',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('reserve_request_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'reserve',
                'controller' => 'list',
                'action' => 'new-from-request',
            ),
            _('Принять'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array(
                'module' => 'reserve-request',
                'controller' => 'list',
                'action' => 'decline-by',
            ),
            _('Отклонить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(
            array(
                'module' => 'reserve-request',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{status_int}}')
            )
        );


        $this->view->grid = $grid;
    }

    public function createRequestAction()
    {
        $positionId  = $this->getRequest()->getParam('position_id');
        $this->makeRequest($positionId);

        $this->_flashMessenger->addMessage(_('Заявка успешно создана'));
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
            $this->_redirectToIndex();
        } else {
            $this->_redirect(Zend_Registry::get('baseUrl'));
        }
    }

    protected function makeRequest($positionId)
    {
        $userId      = $this->getService('User')->getCurrentUserId();
        $requestDate = date('Y-m-d');
        $this->getService('HrReserveRequest')->insert(
            array(
                'user_id' => $userId,
                'position_id' => $positionId,
                'request_date' => $requestDate
            )
        );
    }
    
    public function delete($id) 
    {
        $this->getService('HrReserveRequest')->delete($id);
    }

    public function updateStatus($status)
    {
        return HM_Hr_Reserve_Request_RequestModel::getStatusTitle($status);
    }

    public function declineAction()
    {
        $id = (int) $this->_getParam('reserve_request_id', 0);
        if ($id) {
            $this->getService('HrReserveRequest')->setStatus($id, HM_Hr_Reserve_Request_RequestModel::STATUS_DECLINED);
            $this->_flashMessenger->addMessage(_('Заявка успешно отклонена'));
        }
        $this->_redirectToIndex();
    }

    public function declineByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {

                    $reserveRequest = $this->getService('HrReserveRequest')->find($id)->current();
                    if ($reserveRequest->status != HM_Hr_Reserve_Request_RequestModel::STATUS_NEW) continue;

                    $this->getService('HrReserveRequest')->setStatus($id, HM_Hr_Reserve_Request_RequestModel::STATUS_DECLINED);
                }
                $this->_flashMessenger->addMessage(_('Новые заявки успешно отклонены'));
            }
        }
        $this->_redirectToIndex();
    }



    public function updateActions($status, $actions)
    {
        if ($status != HM_Hr_Reserve_Request_RequestModel::STATUS_NEW) {
            $this->unsetAction($actions, array('module' => 'reserve', 'controller' => 'list', 'action' => 'new-from-request'));
            $this->unsetAction($actions, array('module' => 'reserve-request', 'controller' => 'list', 'action' => 'decline'));
        }
        return $actions;
    }

}
