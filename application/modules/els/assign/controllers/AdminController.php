<?php

class Assign_AdminController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $service = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id = 0;

    protected $_responsibilities = null;


    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $subjectId = (int)$this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->id = (int)$this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $subject
                    )
                );
            }
        }


    }

    public function indexAction()
    {
        $courseId = (int)$this->_getParam('subject_id', 0);
        $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC', 'notempty DESC');

        if ($this->_request->getParam("assigned{$this->gridId}", false)) {
            $this->_request->setParam("assigned{$this->gridId}", urldecode(urldecode($this->_request->getParam("assigned{$this->gridId}"))));
        }
        $select = $this->getService('User')->getSelect();
        if ($switcher) {
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login',
                    'status' => 't1.blocked'
                )
            )->joinLeft(array('a' => 'admins'),
                'a.MID = t1.MID',
                array('assigned' => 'a.MID')
            )->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.blocked', 't1.Login', 'a.MID'));
        } else {
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login',
                    'status' => 't1.blocked'
                )
            )->joinInner(array('a' => 'admins'),
                'a.MID = t1.MID',
                array()
            )->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.blocked', 't1.Login'));
        }

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'fio' => array(
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink($this->view->url(array(
                                'module' => 'user',
                                'controller' => 'list',
                                'action' => 'view',
                                'gridmod' => null,
                                'user_id' => '')) . '{{MID}}', _('Карточка пользователя')) . '<a href="' . $this->view->url(array(
                            'module' => 'user',
                            'controller' => 'edit',
                            'action' => 'card',
                            'report' => 1,
                            'gridmod' => null,
                            'user_id' => '')) . '{{MID}}' . '">' . '{{fio}}</a>',
                    'callback' => array(
                        'function' => array($this, 'updateFio'),
                        'params' => array('{{fio}}', '{{MID}}')
                    )
                ),
                'notempty' => array('hidden' => true),
                'login' => array('title' => _('Логин')),
                'assigned' => array(
                    'title' => _('Назначен'),
                    'hidden' => $switcher == 0,
                    'callback' =>
                        array(
                            'function' => array($this, 'updateAssigned'),
                            'params' => array('{{assigned}}')
                        )
                ),
                'status' => array(
                    'title' => _('Статус'),
                    'callback' =>
                        array(
                            'function' => array($this, 'updateStatus'),
                            'params' => array('{{status}}')
                        )
                ),
            ),
            array(
                'fio' => null,
                'login' => null,
                'assigned' => array(
                    'values' => array(
                        '*' => _('Да'),
                        'ISNULL' => _('Нет')
                    )
                ),
                'status' => array(
                    'values' => array(
                        '0' => _('Активный'),
                        '1' => _('Заблокирован')
                    )
                )
            )
        );

        $grid->setGridSwitcher([
            'label' => _('Показать всех'),
            'title' => _('Показать всех администраторов'),
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
        ]);

        if ($switcher) $grid->setClassRowCondition("'{{assigned}}' != ''", "success");
        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "success");

        $url = array('action' => 'assign');
        $grid->addMassAction(
            $url,
            _('Назначить роль'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $url = array('action' => 'unassign');
        $grid->addMassAction(
            $url,
            _('Удалить роль'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addAction(array('module' => 'message',
            'controller' => 'send',
            'action' => 'index'),
            array('MID'),
            _('Отправить сообщение'));
        $grid->addMassAction(array('module' => 'message',
            'controller' => 'send',
            'action' => 'index'),
            _('Отправить сообщение'));

        $grid->setHeadCheckbox('all', _('Отображать пользователей только данной роли'), 1);

        $grid->addFixedRows(
            $this->_getParam('module'),
            $this->_getParam('controller'),
            $this->_getParam('action'),
            't1.MID');
        $grid->updateColumn('fixType', array('hidden' => true));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
    }


    public function assignAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $errors = false;
                foreach ($ids as $id) {
                    if (method_exists($this, '_preAssign')) {
                        $this->_preAssign($id, $courseId);
                    }


                    $fetch = $this->getService('Admin')->fetchAll(array('MID = ?' => $id));
                    try {
                        if (count($fetch) == 0) {
                            $this->getService('Admin')->insert(
                                array(
                                    'MID' => $id
                                )
                            );
                        }
                    } catch (Zend_Db_Exception  $e) {
                        $errors = true;
                    }


                    if (method_exists($this, '_postAssign')) {
                        $this->_postAssign($id, $courseId);
                    }
                }


                if ($errors == false) {
                    $this->_flashMessenger->addMessage(_('Пользователи успешно назначены'));
                } else {
                    $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли несущественные ошибки.'));
                }

            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }

    public function unassignAction()
    {
        //$subjectId = 0;

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    if ($this->getService('User')->getCurrentUserId() == $id) {
                        $this->_flashMessenger->addMessage(_('Вы не можете удалить себя'));
                        continue;
                    }
                    if (method_exists($this, '_preUnassign')) {
                        $this->_preUnassign($id, $courseId);
                    }
                    $this->getService('Admin')->deleteBy(
                        sprintf("%s = %d", 'MID', $id)
                    );
                    if (method_exists($this, '_postUnassign')) {
                        $this->_postUnassign($id, $courseId);
                    }
                }
                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array('subject_id' => $subjectId));
    }


    protected function _postAssign($id, $subjectId)
    {

    }


    protected function _preAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _preUnassign($personId, $courseId){}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    protected function _unassign($personId, $courseId){}
}