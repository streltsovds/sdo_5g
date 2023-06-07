<?php
class Group_IndexController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Grid_ColumnCallback_Trait_Common;

    public function indexAction()
    {

        // Получаем ид курса
        $subjectId = $this->_request->getParam('subject_id',0);
        $this->view->subjectId = $subjectId;
        $select = $this->getService('Group')->getSelect();
        $select->from(
                    array('groupname'),
                    array('gid', 'name'))
               ->joinLeft(
                    array('groupuser'),
                    'groupname.gid = groupuser.gid',
                    array('students' => 'COUNT(groupuser.mid)')
                )
                ->where('groupname.cid = ?', $subjectId)
                ->group(array('groupname.gid', 'groupname.name'));
                
        // hack
        $grid = $this->getGrid(
            $select,
            array(
                'gid' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    // 'decorator' =>                               //! Декоратор под карточку
                    //     $this->view->cardLink($this->view->url(array('action' => 'card', 'group_id' => '')).'{{gid}}', _('Карточка подгруппы')).' {{name}}'
                ),
                'students' => array('title' => _('Количество слушателей'))
            ),
            array(
                'name' => null,
                'students' => null
            )
        );

        $grid->addMassAction(array(
            'module' => 'group',
            'controller' => 'index',
            'action' => 'delete-by'), _('Удалить'), _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        
        $grid->addAction(array(
                'module' => 'group',
                'controller' => 'index',
                'action' => 'edit',
                'subject_id' => array(
                    'subject_id' => $subjectId)), array(
                'gid'), $this->view->svgIcon('edit', 'Редактировать'));
        
        
        
        $grid->addAction(array(
                'module' => 'group',
                'controller' => 'index',
                'action' => 'delete',
                'subject_id' => array(
                    'subject_id' => $subjectId)), array(
                'gid'), $this->view->svgIcon('delete', 'Удалить'));
        
        $grid->updateColumn('name', array(
            'callback' => array(
                'function' => array(
                    $this,
                    'updateNameColumn'),
                'params' => array(
                    '{{name}}',
                    '{{gid}}',
                    $subjectId))));
        
        
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function newAction()
    {
        
        $subjectId=$this->_request->getParam('subject_id');
        $form = new HM_Form_Group();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                
                
                $group = $this->getService('Group')->insert(
                    array(
                        'name' => $form->getValue('name'),
                        'cid' => $subjectId
                    )
                );
              
                $this->_flashMessenger->addMessage(_('Подгруппа успешно создана'));
                $this->_redirector->gotoSimple('edit-members', 'index', 'group',array('subject_id'=>array('subject_id'=>$subjectId),'groupId'=>array('groupId'=>$group->gid)))
                ;
            }
        }
        $this->view->form = $form;
         
    }

    /**
     * Экшн для изменения состава группы
     */
    public function editMembersAction()
    {
        $subjectId = $this->_request->getParam('subject_id', 0);
        $gid = $this->_request->getParam('groupId', 0);
        $gridId = ($gid) ? "grid{$gid}" : 'grid';
        $default = new Zend_Session_Namespace('default');

        if ($subjectId && !isset($default->grid['group-index-edit-members'][$gridId])) {
            $default->grid['group-index-edit-members'][$gridId]['filters']['groupp'] = $gid;
        }

        /** @var HM_Group_GroupService $groupService */
        $groupService = $this->getService('Group');

        $group = $this->getOne($groupService->find($gid));        
        if ($group) {
            $this->view->setHeader($group->name);
        }

        $backUrl = [
            'module' => 'group',
            'controller' => 'index',
            'action' => 'index',
        ];

        if ($subjectId)
            $backUrl['subject_id'] = $this->_subjectId;

        $this->view->setBackUrl($this->view->url($backUrl, null, true));

        // Получаем массив с отмеченными галочками
        $this->view->courseId = $subjectId;
        $this->view->groupId = $gid;

        $select = $groupService->getSelect();

        $select->from(
            ['p' => 'People'],
            [
                'MID' => 'p.mid',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'department' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT d2.name)'),
                'groups' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT(sgu.group_id))'),
                'time_registered' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT s.time_registered)'),
                'tags' => 'p.MID',
                'groupp' => 'g.gid',
                'groupshotcut' => 'g.gid'
            ]
        )
            ->join(['s' => 'Students'], 's.mid = p.mid', [])
            ->joinLeft(['d' => 'structure_of_organ'], 'd.mid          = p.MID', [])
            ->joinLeft(['d2' => 'structure_of_organ'], 'd.owner_soid   = d2.soid', [])
            ->joinLeft(['sgu' => 'study_groups_users'], 'sgu.user_id      = p.MID', [])
            ->where('s.cid = ?', $subjectId)
            ->group(['p.mid', "CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)", 'g.gid']);


        $subSelectGroup = $groupService->getSelect();
        $subSelectGroup->from('groupuser')->where('groupuser.gid = ?', $gid);

        $switcher = $this->getSwitcherSetOrder();
        if ($switcher == self::FILTER_STRICT) {
            $select->joinInner(['g' => $subSelectGroup], 's.mid = g.mid', []);
        } else {
            $select->joinLeft(['g' => $subSelectGroup], 's.mid = g.mid', []);
        }

        $fields = [
            'MID' => ['hidden' => true],
            'groupshotcut' => ['hidden' => true],
            'fixType' => ['hidden' => true],
            'fio' => [
                'title' => _('ФИО'),
                'decorator' => $this->view->cardLink($this->view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => '']) . '{{MID}}') . '{{fio}}'
            ],
            'department' => [
                'title' => _('Подразделение')
            ],
            'groups' => [
                'title' => _('Учебные группы'),
                'callback' => [
                    'function'=> [$this, 'groupsCache'],
                    'params'=> ['{{groups}}', $select]
                ],
                'color' => HM_DataGrid_Column::colorize('groups')
            ],
            'time_registered' => [
                'title' => _('Дата начала обучения'),
                'callback' => [
                    'function'=> [$this, 'updateDate'],
                    'params'=> ['{{time_registered}}']
                ],
            ],
            'tags' => [
                'title' => _('Метки'),
                'callback' => [
                    'function'=> [$this, 'displayTags'],
                    'params'=> ['{{tags}}', HM_Tag_Ref_RefModel::TYPE_USER, true]
                ],
                'color' => HM_DataGrid_Column::colorize('tags')
            ],
            'groupp' => [
                'title' => _('Состоит в этой подгруппе')
            ]
        ];

        $filters = [
            'fio' => null,
            'department' => null,
            'groups' => ['callback' => ['function' => [$this, 'groupsFilter']]],
            'time_registered' => ['render' => 'DateSmart'],
            'tags' => ['callback' => ['function' => [$this, 'filterTags']]],
        ];

        $grid = $this->getGrid(
            $select,
            $fields,
            $filters,
            $gridId
        );

        if ($switcher == self::FILTER_ALL)
            $grid->setClassRowCondition("'{{groupp}}' == {$gid}", 'success');

        if ($gid) {
            $grid->setGridSwitcher([
                'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
                'param' => self::SWITCHER_PARAM_DEFAULT,
                'label' => _('Показать всех'),
                'title' => _('Показать всех слушателей курса'),
            ]);
        }

        $grid->addMassAction(
            [
                'module' => 'group',
                'controller' => 'index',
                'action' => 'add-member-by'
            ],
            _('Включить в подгруппу')
        );

        $grid->addMassAction(
            [
                'module' => 'group',
                'controller' => 'index',
                'action' => 'delete-member-by'
            ],
            _('Исключить из подгруппы'),
            _('Вы действительно хотите исключить отмеченных пользователей из подгруппы?')
        );

        $grid->updateColumn('groupp',
            [
                'callback' => [
                    'function' => [
                        $this,
                        'updateGroupColumn'
                    ],
                    'params' => [
                        '{{groupp}}', $gid
                    ]
                ]
            ]
        );

        /** @var Zend_Controller_Request_Abstract $request */
        $request = $this->getRequest();

        $grid->addFixedRows($request->getModuleName(), $request->getControllerName(), $request->getActionName(), 'p.mid');
        $grid->updateColumn('fixType', ['hidden' => true]);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    /**
     * Экшн для массового добавления
     */
    public function addMemberByAction()
    {

        $gid = $this->_getParam('groupId', '');
        $postMassIds = $this->_getParam('postMassIds_grid'.$gid, '');
        $subjectId = $this->_getParam('subject_id', '');
       
        if (strlen($postMassIds))
        {
            $ids = explode(',', $postMassIds);
            
            if (count($ids))
            {
                
               
                foreach ( $ids as $id )
                {
                    
                    $select = $this->getService('GroupAssign')->getSelect();
                    $select->from('groupuser')->where('mid = ?', $id)->where('gid = ?', $gid);
                    $adapter = $select->getAdapter();
                    //echo $select;
                    $data = $adapter->fetchRow($select);
                    
                    if (empty($data))
                    {
                        $select = $this->getService('GroupAssign')->insert(array(
                            'mid' => $id,
                            'cid' => $subjectId,
                            'gid' => $gid));
                    }
                    
                   
                    
                }
                
                $this->_flashMessenger->addMessage(_('Пользователи успешно добавлены.'));
            }
        }
        $this->_redirector->gotoSimple('edit-members', 'index', 'group', array(
            'subject_id' => array(
                'subject_id' => $subjectId),
            'groupId' => array(
                'groupId' => $gid)));
    
    }

    /**
     * Тоглер, для шотката, если входит в множество, то сет, если не входит то Unset
     */
    public function toggleMemberAction()
    {

        $id = $this->_request->getParam('idrow');
        $gid = $this->_request->getParam('groupId');
        $subjectId = $this->_request->getParam('subject_id');
        
        $select = $this->getService('GroupAssign')->getSelect();
        $select->from('groupuser')->where('mid = ?', $id)->where('gid = ?', $gid);
        $adapter = $select->getAdapter();
        //echo $select;
        $data = $adapter->fetchRow($select);
        //echo $data;
        if (empty($data))
        {
            $this->getService('GroupAssign')->insert(array(
                'mid' => $id,
                'cid' => $subjectId,
                'gid' => $gid));
            echo 'set';
        }else{
            $nn=$this->getService('GroupAssign')->deleteBy($this->getService('GroupAssign')->quoteInto('gid = ?', $gid).' AND '.$this->getService('GroupAssign')->quoteInto('cid = ?', $subjectId).' AND '.$this->getService('GroupAssign')->quoteInto('mid = ?', $id));
            
            if($nn >0){
                echo 'unset';
            }
            else{
                echo 'set';
            }
        }
        exit();
    
    }
    
    
    
    /**
     * Экшн для массового удаления
     */
    public function deleteMemberByAction()
    {

        $gid = $this->_getParam('groupId', '');
        $postMassIds = $this->_getParam('postMassIds_grid'.$gid, '');
        $subjectId = $this->_getParam('subject_id', '');
        
        if (strlen($postMassIds))
        {
            $ids = explode(',', $postMassIds);
            
            if (count($ids))
            {
                array_walk($ids, 'intval');
                $in = implode(',', $ids);
                $adapter = $this->getService('GroupAssign')->getSelect()->getAdapter();
                
                $n = $adapter->delete('groupuser', 'mid IN (' . $in . ') AND gid = ' . $adapter->quote($gid, 'INTEGER'));
                
                if ($n == count($ids))
                {
                    $this->_flashMessenger->addMessage(_('Пользователи успешно удалены из подгруппы'));
                } else
                {
                    $this->_flashMessenger->addMessage(_('Пользователи успешно удалены из подгруппы'));
                }
            }
            $this->_redirector->gotoSimple('edit-members', 'index', 'group', array(
                'subject_id' => array(
                    'subject_id' => $subjectId),
                'groupId' => array(
                    'groupId' => $gid)));
        
        }
    }
    /**
     * Экшн для редактирования только названия
     */
    public function editAction()
    {
        $form = new HM_Form_Group();
        $groupId = (int) $this->_getParam('gid', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                                
                $group = $this->getService('Group')->update(array(
                    'gid' => $form->getValue('gid'),
                    'name' => $form->getValue('name'))
                    );

                $this->_flashMessenger->addMessage(_('Подгруппа успешно изменена'));
                $this->_redirector->gotoSimple('index', 'index', 'group',array('subject_id'=>array('subject_id'=>$subjectId)));
            }
        } else {
            $group = $this->getService('Group')->getOne($this->getService('Group')->find($groupId));
            if ($group) {
                $form->populate(
                    array(
                        'name' => $group->name,
                        'gid' => $group->gid,
                         )
                );
            }
        }
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $groupId = (int) $this->_getParam('gid', 0);
        if ($groupId) {
            $this->getService('Group')->delete($groupId);
            $this->_flashMessenger->addMessage(_('Подгруппа успешно удалена'));
        }
        $this->_redirector->gotoSimple('index', 'index', 'group',array('subject_id' =>array('subject_id' => $this->_request->getParam('subject_id'))));
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Group')->delete($id);
                }
                $this->_flashMessenger->addMessage(_('Подгруппы успешно удалены'));
            }
        }
        $this->_redirector->gotoSimple('index', 'index', 'group',array('subject_id' =>array('subject_id' => $this->_request->getParam('subject_id'))));
    }

    public function list1OptionsAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $searchString = trim(iconv('UTF-8', Zend_Registry::get('config')->charset, $this->_getParam('searchString', '*')));
        $searchString = '%'.str_replace('*', '%', $searchString).'%';
        $groupId = $this->_getParam('gid', 0);

        $where = $this->getService('User')->quoteInto('LOWER(LastName) LIKE LOWER(?)', $searchString);
        $where .= " OR ".$this->getService('User')->quoteInto('LOWER(FirstName) LIKE LOWER(?)', $searchString);
        $where .= " OR ".$this->getService('User')->quoteInto('LOWER(Login) LIKE LOWER(?)', $searchString);
        $collection = $this->getService('User')->fetchAllDependence(array('Student', 'Group_Assign'), $where, array('LastName ASC', 'FirstName ASC', 'Patronymic ASC', 'Login ASC'));

        $students = array();
        if (count($collection)) {
            $peopleFilter = $this->getService('Unmanaged')->getPeopleFilter();
            foreach($collection as $user) {
                if ($user->isStudent() && !$user->isGroupUser($groupId) && $peopleFilter->is_filtered($user->MID)) {
                    $students[] = $user;
                }
            }
        }

        $this->view->students = $students;
    }

    public function list2OptionsAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
                
        $groupId = $this->_getParam('gid', 0);

        $students = array();
        if ($groupId) {
            $collection = $this->getService('User')->fetchAllDependence(array('Student', 'Group_Assign'), null, array('LastName ASC', 'FirstName ASC', 'Patronymic ASC', 'Login ASC'));

            if (count($collection)) {
                $peopleFilter = $this->getService('Unmanaged')->getPeopleFilter();
                foreach($collection as $user) {
                    if ($user->isStudent() && $user->isGroupUser($groupId) && $peopleFilter->is_filtered($user->MID)) {
                        $students[] = $user;
                    }
                }
            }
        }

        $this->view->students = $students;
    }


    public function validateFormAction($form = null)
    {
        $form = new HM_Form_Group();
        parent::validateFormAction($form);
    }

    /**
     * Метод для update названия на ссылку
     * 
     * @param unknown_type $field
     */
    public function updateNameColumn($field, $gid, $subjectId)
    {
        if (isset($field) && isset($gid))
        {
            
            return '<a href="'.$this->view->serverUrl().'/group/index/edit-members/subject_id/'.$subjectId.'/groupId/'.$gid.'/" label="'.$field.'">'.$field.'</a>';
        
        } else
        {
            return $field;
        }
    
    }

    public function cardAction()
    {
        $groupId = (int) $this->_getParam('group_id', 0);

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
        $this->view->disableExtendedFile();
        $groupId = (int) $this->_getParam('group_id', 0);
        $this->view->group = false;
        $this->view->group = $this->getService('Group')->getOne(
            $this->getService('Group')->find($groupId)
        );

    }
    

}