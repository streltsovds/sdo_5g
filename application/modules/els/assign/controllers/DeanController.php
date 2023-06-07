<?php

class Assign_DeanController extends HM_Controller_Action_Assign
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
        $switcher = $this->getSwitcherSetOrder($courseId, 'fio_ASC', 'notempty DESC');

        $select = $this->getService('User')->getSelect();
        if ($switcher) {

            $select
                ->from(array('t1' => 'People'), array(
                    'MID',
                    'user_id' => 't1.MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login',
                    'status' => 't1.blocked',
                    'assigned' => 't2.MID',
                    'orgStruct' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT r.item_id)'),
                    // 'orgStruct' => new Zend_Db_Expr('GROUP_CONCAT(r.item_id)'),
                    'responsibility_type' => new Zend_Db_Expr('MAX(r2.item_type)'),
                    'responsibility_count' => new Zend_Db_Expr('COUNT(DISTINCT r2.item_id)'),
                ))
                ->joinLeft(
                    array('t2' => 'deans'),
                    't1.MID = t2.MID',
                    array()
                )
                ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.Login', 't1.blocked', 't2.MID'));
        } else {

            $select
                ->from(array('t1' => 'People'), array(
                    'MID',
                    'user_id' => 't1.MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login',
                    'status' => 't1.blocked',
                    'orgStruct' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT r.item_id)'),
                    // 'orgStruct' => new Zend_Db_Expr('GROUP_CONCAT(r.item_id)'),
                    'responsibility_type' => 'r2.item_type',
                    'responsibility_count' => new Zend_Db_Expr('COUNT(DISTINCT r2.item_id)'),
                ))
                ->joinInner(
                    array('t2' => 'deans'),
                    't1.MID = t2.MID',
                    array()
                )
                ->group(array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.Login', 't1.blocked', 't2.MID', 'r2.item_type'));
        }

        $select
            ->joinLeft(array('r' => 'responsibilities'),
                't1.MID = r.user_id AND r.item_type=' . HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE,
                array())
            ->joinLeft(array('r2' => 'responsibilities'),
                't1.MID = r2.user_id AND r2.item_type=' . HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT,
                array()
            );

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'user_id' => array('hidden' => true),
                'notempty' => array('hidden' => true),
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
                    'callback' =>
                        array('function' => array($this, 'updateFio'),
                            'params' => array('{{fio}}', '{{MID}}')
                        )
                ),
                'login' => array('title' => _('Логин')),
                'status' => array(
                    'title' => _('Статус'),
                    'callback' => array(
                        'function' => array($this, 'updateStatus'),
                        'params' => array('{{status}}')
                    )
                ),
                'assigned' => array(
                    'title' => _('Назначен'),
                    'hidden' => $switcher == 0,
                    'callback' =>
                        array(
                            'function' => array($this, 'updateAssigned'),
                            'params' => array('{{assigned}}')
                        )
                ),
                'orgStruct' => array(
                    'title' => _('Области ответственности по оргструктуре'),
                    'callback' =>
                        array(
                            'function' => array($this, 'updateResponsibilityOrg'),
                            'params' => array('{{orgStruct}}', $select)
                        )
                ),
                'responsibility_type' => array('hidden' => true),
                'responsibility_count' => array(
                    'title' => _('Области ответственности по курсам'),
                    'callback' =>
                        array(
                            'function' => array($this, 'updateResponsibility'),
                            'params' => array('{{responsibility_type}}', '{{responsibility_count}}')
                        )
                ),
            ),
            array(
                'fio' => null,
                'login' => null,
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
            'title' => _('Показать всех менеджеров по обучению'),
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

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'responsibility',
            'action' => 'assign',
            'dean' => 1
        ),
            array('user_id'),
            _('Редактировать область ответственности')
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

//        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
//        $this->view->grid = $grid;
        $this->view->grid = $grid;
        Zend_Registry::get('session_namespace_default')->userCard['returnUrl'] = $_SERVER['REQUEST_URI'];
    }


    public function assignAction()
    {
        $subjectId = (int)$this->_getParam('subject_id', 0);

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $errors = false;
                foreach ($ids as $id) {
                    if (method_exists($this, '_preAssign')) {
                        $this->_preAssign($id, $subjectId);
                    }

                    $fetch = $this->getService('Dean')->fetchAll(array('MID = ?' => $id));
                    try {
                        if (count($fetch) == 0) {

                            $this->getService('Responsibility')->resetResponsibility($id);

                            $this->getService('Dean')->insert(
                                array(
                                    'MID' => $id,
                                    'subject_id' => $subjectId
                                )
                            );
                        }
                    } catch (Zend_Db_Exception  $e) {
                        $errors = true;
                    }

                    if (method_exists($this, '_postAssign')) {
                        $this->_postAssign($id, $subjectId);
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

        $this->_redirector->gotoSimple('index', null, null, array('Dean' => $subjectId));
    }

    public function unassignAction()
    {
        $subjectId = 0;

        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        if (count($ids)) {
            foreach ($ids as $value) {
                $this->getService('User')->removalRole($value, HM_Role_Abstract_RoleModel::ROLE_DEAN);
            }
            $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите курс'));
        }

        $this->_redirector->gotoSimple('index', null, null, array('Dean' => $subjectId));
    }


    protected function _postAssign($id, $subjectId)
    {

    }

    public function updateDate($date)
    {

        if ($date == "") {
            return _('Нет');
        } else {
            $date = new Zend_Date($date);

            if ($date instanceof Zend_Date) {
                return $date->toString(HM_Locale_Format::getDateFormat());
            } else {
                return _('Нет');
            }

        }

    }

    /**
     * @param string $field Поле для обработки
     * @param string $separator Разделитель
     * @return string
     */
    public function updateRole($field, $separator = ', ')
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles();
        if ($field == '') return $roles['user'];
        $str = str_replace(array_keys($roles), array_values($roles), $field);
        $str = str_replace(',', $separator, $str);
        return $str;
    }

    protected function _preAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    protected function _unassign($personId, $courseId){}
    protected function _preUnassign($personId, $courseId){}
}