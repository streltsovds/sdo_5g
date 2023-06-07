<?php
class Assign_CuratorController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $service     = 'Project';
    protected $idParamName = 'project_id';
    protected $idFieldName = 'projid';
    protected $id          = 0;

    protected $_responsibilities = null;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $projectId = (int) $this->_getParam('project_id', 0);
            if ($projectId) { // Делаем страницу расширенной
                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $project = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'projectName' => $this->service,
                        'projectId' => $this->id,
                        'projectIdParamName' => $this->idParamName,
                        'projectIdFieldName' => $this->idFieldName,
                        'project' => $project
                    )
                );
            }
        }


    }

    public function indexAction()
    {
        $courseId = (int) $this->_getParam('subject_id', 0);
        $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC', 'notempty DESC');

        $select = $this->getService('User')->getSelect();

        if ($switcher) {
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login'
                )
            )->joinLeft(
                array('t2' => 'curators'),
                't1.MID = t2.MID',
                array(
                    //'role' => 'r.role',
                    'status' => 't1.blocked',
                    'responsibility' => new Zend_Db_Expr('GROUP_CONCAT(t2.project_id)')
                )
            )->joinLeft(
                array('dr' => 'curators_responsibilities'),
                't1.MID  = dr.user_id',
                array()
            )->joinLeft(
                array('org' => 'classifiers'),
                'dr.classifier_id  = org.classifier_id',
                array('orgStruct' => new Zend_Db_Expr('GROUP_CONCAT(org.name)'), 'assigned' => 't2.MID')

            )->group(array(
                't1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic'/*, 'role'*/, 't1.blocked', 't1.Login', 't2.MID'
            ));
        } else {
            $select->from(
                array('t1' => 'People'),
                array(
                    'MID',
                    'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    'login' => 't1.Login'
                )
            )
                ->joinInner(
                    array('t2' => 'curators'),
                    't1.MID = t2.MID',
                    array(
                        // 'role'           => 'r.role',
                        'status'         => 't1.blocked',
                        'responsibility' => new Zend_Db_Expr('GROUP_CONCAT(t2.project_id)')
                    )
                )->joinLeft(
                    array('dr' => 'curators_responsibilities'),
                    't1.MID  = dr.user_id',
                    array()
                )->joinLeft(
                    array('org' => 'classifiers'),
                    'dr.classifier_id  = org.classifier_id',
                    array('orgStruct' => new Zend_Db_Expr('GROUP_CONCAT(org.name)'))
                )
                ->group(array(
                    't1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', /*'role',*/ 't1.blocked', 't1.Login')
                );
        }

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'notempty' => array('hidden' => true),
                'employer' => array('title' => _('Место работы')),
                'login' => array('title' => _('Логин')),
                'role' => array('title' => _('Роли')),
                'status' => array('title' => _('Статус')),
                 'assigned' => array('title' => _('Назначен')),
                'responsibility' => array('title' => _('Области ответственности по конкурсам')),
                'orgStruct' => array('title' => _('Области ответственности по оргструктуре')),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'gridmod' => null,'user_id' => '')).'{{MID}}',_('Карточка пользователя')).'<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => '')) . '{{MID}}'.'">'.'{{fio}}</a>'),
            ),
            array(
                'fio' => null,
                'login' => null,
//                    'assigned' => array(
//                            'values' => array(
//                                '*' => _('Да'),
//                                'ISNULL' => _('Нет')
//                            )
//                        ),
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
            'title' => _('Показать всех менеджеров конкурсов'),
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
        ]);

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

        $grid->updateColumn('role',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateRole'),
                    'params' => array('{{role}}')
                )
            )
        );
        $grid->updateColumn('assigned',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateAssigned'),
                    'params' => array('{{assigned}}')
                )
            )
        );
        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('responsibility',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResponsibilityWithSelect'),
                    'params' => array('{{responsibility}}', '{{MID}}',$select, '{{assigned}}')
                )
            )
        );

        $grid->updateColumn('orgStruct',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateResponsibilityOrg'),
                    'params' => array('{{orgStruct}}', '{{MID}}')
                )
            )
        );

        if ($switcher) $grid->setClassRowCondition("'{{assigned}}' != ''", "success");
        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "selected");

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
    }


    public function assignAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $errors=false;
                foreach($ids as $id) {
                    if (method_exists($this, '_preAssign')) {
                        $this->_preAssign($id, $projectId);
                    }


                    $fetch = $this->getService('Curator')->fetchAll(array('MID = ?' => $id));
                    try{
                        if(count($fetch) == 0){
                            $this->getService('Curator')->insert(
                                array(
                                    'MID' => $id,
                                    'project_id' => $projectId
                                )
                            );
                        }
                    }catch (Zend_Db_Exception  $e){
                        $errors=true;
                    }


                    if (method_exists($this, '_postAssign')) {
                        $this->_postAssign($id, $projectId);
                    }
                }


                if($errors==false){
                    $this->_flashMessenger->addMessage(_('Пользователи успешно назначены'));
                }else{
                    $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли несущественные ошибки.'));
                }

            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите конкурс'));
        }

        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $this->_redirector->gotoSimple('index', null, null, array('project_id' => $projectId));
    }

    public function unassignAction()
    {
        $projectId = 0;

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    if (method_exists($this, '_preUnassign')) {
                        $this->_preUnassign($id, $projectId);
                    }
                    $this->getService('Curator')->deleteBy(
                    sprintf("%s = %d AND %s = %d", 'MID', $id, 'project_id', 0)
                    );
                    if (method_exists($this, '_postUnassign')) {
                        $this->_postUnassign($id, $projectId);
                    }
                }
                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            }
        } else {
            $this->_flashMessenger->addMessage(_('Пожалуйста выберите пользователей и укажите конкурс'));
        }

        if (method_exists($this, '_finishUnassign')) {
            $this->_finishUnassign();
        }
        $this->_redirector->gotoSimple('index', null, null, array('project_id' => $projectId));
    }

    protected function _postAssign($id, $projectId)
    {
    }


    public function updateResponsibilityWithSelect($responsibility, $userId, $select, $assigned)
    {

        if($assigned == '' || $responsibility == ''){
            //return _('Нет');
        }

        if($this->_responsibilities == null){
            $fetch = $select->query();
            $fetch = $fetch->fetchAll();

            $tempProjects = array();

            foreach($fetch as $value){
                $tempProjects = array_merge($tempProjects, explode(',', $value['responsibility']));
            }

            $tempProjects = array_unique($tempProjects);
            sort($tempProjects);
            $result = $this->getService('Project')->fetchAll(array('projid IN (?)' => $tempProjects));

            foreach($result as $value){
                $this->_responsibilities[$value->projid] = $value->name;
            }
        }

        $options = $this->getService('Curator')->getResponsibilityOptions($userId);
        if($options['unlimited_projects'] == 1){
            return _('Без ограничений');
        }

        $fields = array();
        $responsibilities = explode(',', $responsibility);
        $responsibilities = array_unique($responsibilities);

        foreach($responsibilities as $param){
            if(!empty($this->_responsibilities[$param])){
                $fields[]=  $this->_responsibilities[$param];
            }
        }

        // #5337 - сворачивание высоких ячеек
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Project')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $result[] = "<p>{$value}</p>";
        }
        if($result)
            return implode($result);
        else
            return _('Нет');
    }

    public function updateResponsibilityOrg($responsibility, $userId)
    {
        $options = $this->getService('Curator')->getResponsibilityOptions($userId);
        if ($options['unlimited_classifiers'] == 1) {
            return _('Без ограничений');
        } elseif($responsibility == '') {
            return _('Нет');
        } else {
            // #5337 - сворачивание высоких ячеек
            $fields = array_unique(explode(",", $responsibility));
            $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Orgstructure')->pluralFormCount(count($fields)) . '</p>') : array();
            foreach($fields as $value){
                $result[] = "<p>{$value}</p>";
            }
            if($result)
                return implode($result);
            else
                return _('Нет');
        }
    }

    protected function _preAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    protected function _unassign($personId, $courseId){}
    protected function _preUnassign($personId, $courseId){}
}