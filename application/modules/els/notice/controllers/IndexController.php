<?php
class Notice_IndexController extends HM_Controller_Action
{
	use HM_Controller_Action_Trait_Grid;

	public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'id_ASC');
        }

        $clusters = HM_Notice_NoticeModel::getClusters();

        $select = $this->getService('Notice')->getSelect();
        $select->from(
            'notice',
            array(
                'id',
                'cluster',
                'event',
                'receiver',
                'title',
                'message',
                'enabled',
                'priority'
            ))
            ->where('type != ?', HM_Notice_NoticeModel::TEMPLATE_SENDALL)
            ->where('cluster IS NULL OR cluster IN (?)', array_keys($clusters));

        $grid = $this->getGrid(
            $select,
            array(
                'id' => array('hidden' => true),
                'cluster' => array(
                    'title' => _('Группа сообщений'),
                    'callback' =>
                        array(
                            'function' => array($this, 'updateClusters'),
                            'params' => array('{{cluster}}')
                        )
                ),
                'event' => array('title' => _('Событие')),
                'receiver' => array('title' => _('Адресат'), 'helper' => array('name' => 'receiverType', 'params' => array('{{receiver}}'))),
                'title' => array('title' => _('Заголовок')),
                'enabled' => array('title' => _('Активность')),
                'priority' => array('title' => _('Немедленная отправка')),
                'message' => array('hidden' => true)
            ),
            array(
                'event' => null,
                'cluster' => array('' => _('Все')) + array('values' => $clusters),
                'title'   => null,
                'receiver' => array('values' => HM_Notice_NoticeModel::getReceivers()),
                'message' => null
            ),
            'grid'
        );

        $grid->updateColumn('enabled',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateEnabled'),
                    'params' => array('{{enabled}}')
                )
            )
        );

        $grid->updateColumn('priority',
            array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateEnabled'),
                        'params' => array('{{priority}}')
                    )
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            //$this->view->isModerator = $isModerator = $this->getService('Notice')->isCurrentUserActivityModerator();
            $isModerator = true;

            if ($isModerator) {

                $grid->addAction(array(
                    'module' => 'notice',
                    'controller' => 'index',
                    'action' => 'edit'
                ),
                    array('id'),
                    $this->view->svgIcon('edit', 'Редактировать')
                );
            }

            $grid->addMassAction(array('action' => 'enable'), _('Включить'));
            $grid->addMassAction(array('action' => 'disable'), _('Выключить'));
        }
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function enableAction(){
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $array = array('enabled' => 1);
        $res = $this->getService('Notice')->updateWhere($array, array('id IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Сообщения включены!'));
            $this->_redirector->gotoSimple('notice', '', '');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время включения сообщений!'));
            $this->_redirector->gotoSimple('notice', '', '');
        }
        
    }
    
    public function disableAction(){
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $array = array('enabled' => 0);
        $res = $this->getService('Notice')->updateWhere($array, array('id IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Сообщения выключены!'));
            $this->_redirector->gotoSimple('notice', '', '');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время выключения сообщений!'));
            $this->_redirector->gotoSimple('notice', '', '');
        }
    }
    
    
    public function editAction()
    {
        $notice_id = (int) $this->_getParam('id', 0);

//        if (!$this->getService('Notice')->isCurrentUserActivityModerator()) {
//            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
//            $this->_redirector->gotoSimple('index', 'index', 'notice', array('subject' => $subjectName, 'subject_id' => $subjectId));
//        }

        $form = new HM_Form_Notice();
        $form->setAction($this->view->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'edit')));

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $this->getService('Notice')->update(array(
                    'title' => strip_tags($form->getValue('title')),
                    'message' => $form->getValue('message'),
                    'id' => $form->getValue('id'),
                    'enabled' => $form->getValue('enabled'),
                    'priority' => $form->getValue('priority')
                ));

                $this->_flashMessenger->addMessage(_('Оповещение успешно изменено'));
                $this->_redirector->gotoSimple('index', 'index', 'notice');

            }
        } else {
            if ($notice_id) {
                $notice = $this->getOne($this->getService('Notice')->find($notice_id));
                $values = array();
                if ($notice) {
                    $values = $notice->getValues();
                }
                $values['receiver'] = HM_Notice_NoticeModel::getReceiver($values['receiver']);
                $form->setDefaults($values);
            }
        }

        $this->view->setBackUrl($this->view->url([
            'module' => 'notice',
            'controller' => 'index',
            'action' => 'index',
        ], null, true));

        $this->view->form = $form;

    }

    public function updateClusters($cluster)
    {
        $clusters = HM_Notice_NoticeModel::getClusters();
        return $clusters[$cluster];
    }

    public function updateEnabled($param){
        if($param == 1)
            return _('Да');
        return _('Нет');
    }


}