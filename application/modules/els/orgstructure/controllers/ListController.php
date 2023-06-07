<?php
class Orgstructure_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    private $_item;
    private $_relationsCache;
    private $_profilesCache;

    protected $emptyDepartmentsCache;

    public function init()
    {
        if ($this->_request->getParam('item', 'department') == 'position') {
            $form = new HM_Form_Position();
        } else {
            $form = new HM_Form_Department();
        }

        $orgId = (int) $this->_getParam('org_id', 0);
        if ($orgId) {
            $this->_item = $this->getOne($this->getService('Orgstructure')->find($orgId));
            if ($this->_item && $this->_item->type != HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                $form = new HM_Form_Position();
            }
        }

        $this->_setForm($form);
        parent::init();

        $this->gridId = 'grid';
    }

    public function supervisorAction()
    {
        $this->index();
    }

    public function indexAction()
    {
        $this->index();
    }

    public function index()
    {
        $this->gridId = 'grid';
        $userService = $this->getService('User');
        $userRole = $userService->getCurrentUserRole();
        $isAdmin = $this->getService('Acl')->inheritsRole($userRole, array(
            HM_Role_Abstract_RoleModel::ROLE_ADMIN
        ));

        /** @var HM_Orgstructure_OrgstructureService $orgstructureService */
        $orgstructureService = $this->getService('Orgstructure');
        try {
            $defaultParent = $orgstructureService->getDefaultParent();
        } catch (HM_Responsibility_ResponsibilityException $e) {

            $this->_flashMessenger->addMessage(array(
                'message' => $e->getMessage(),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR
            ));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        // возможно, этот код дублирует логику выше
        if ($defaultParent && isset($defaultParent->soid)) {
            $defaultParentSoid = $defaultParent->soid;
        } elseif ($this->getService('Acl')->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Ваша учетная запись не связана ни с одним подразделением оргструктуры')
            ));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        /** @var Zend_Session_Namespace $session */
        $session = new Zend_Session_Namespace('default');

        $pageSess = sprintf('%s-%s-%s',
            $this->getRequest()->getModuleName(),
            $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName()
        );

        $sessOrgId = $session->orgstructure_id;
        $orgId = (int) $this->_getParam('key', $sessOrgId ?: $defaultParentSoid);
        $session->orgstructure_id = $orgId;

        $paramExp = $this->_getParam('_exportTogrid', '0');
        $paramAll = ($this->_getParam('all', $session->grid['switchOrg'])) ;
        $session->grid[$pageSess][$this->gridId]['all'] = $paramAll;

        if (!empty($paramExp) && (int) $paramAll == self::FILTER_ALL){

            $session->grid['switchOrg'] = self::FILTER_ALL;
            $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC', 'is_vacant ASC');

        } else {

            $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC', 'is_vacant ASC');
            // $session->grid['switchOrg'] = $switcher;
            $session->grid['switchOrg'] = ($switcher) ? 1 : 0; //$session->grid[$pageSess][$this->gridId]['all'];
            $paramAll = $session->grid['switchOrg'];

        }

        $this->view->treeajax = $this->_getParam('treeajax', 'none');
        $this->view->all = $switcher;

        $select = $orgstructureService->getSelect();
        $select->from(array('so' => 'structure_of_organ'), array(
            'so.soid',
            'org_id' => 'so.soid',
            'so_name' => 'so.name',
            'so.type',
            'type_id' => 'so.type',
            'so.is_manager',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'profile_id' => 'ap.profile_id',
            'profile_user_id' => 'ap.user_id',
            'profile' => 'ap.name',
            'department_path' => 'ap.department_path',
            'kpis' => new Zend_Db_Expr("CASE WHEN so.type != 0 THEN COUNT(DISTINCT uk.user_kpi_id) ELSE '' END"),
            'is_vacant' => new Zend_Db_Expr("CASE WHEN so.type != 0 AND so.mid=0 THEN 1 ELSE 0 END"),
            'MID' => 'so.mid',
            'user_id' => 'so.mid',
        ));

        $cycleId = 0;
        if ($cycle = $this->getService('Cycle')->getCurrent()) {
            $cycleId = $cycle->cycle_id;
        }

        $select->joinLeft(array('p' => 'People'), 'so.mid = p.MID', array())
            ->joinLeft(array('s' => 'Students'), 's.MID = p.MID', array('courses' => new Zend_Db_Expr('GROUP_CONCAT(s.CID)')))
            ->joinLeft(array('ap' => 'at_profiles'), 'so.profile_id = ap.profile_id', array())
            ->joinLeft(array('uk' => 'at_user_kpis'), "so.mid = uk.user_id AND uk.cycle_id = {$cycleId}", array())
            ->joinLeft(
                array('v' => 'recruit_vacancies'),
                '(v.position_id = so.soid) AND (v.deleted IS NULL OR v.deleted != 1) AND (v.status != ' . HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED . ')',
                array('vacancy_id' => new Zend_Db_Expr('MAX(v.vacancy_id)')) // если открыли несколько вакансий; вообще, такого быть не должно
            )
            ->joinLeft(
                array('n' => 'recruit_newcomers'),
                'n.position_id = so.soid AND n.status != ' . HM_Recruit_Newcomer_NewcomerModel::STATE_CLOSED,
                array('newcomer_id' => new Zend_Db_Expr('MAX(n.newcomer_id)')) // если открыли несколько адаптаций; вообще, такого быть не должно
            );

        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            $this->getService('ClassifierLink')->quoteInto('cl.item_id = so.soid AND cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_STRUCTURE),
            array() //array('classifiers' => new Zend_Db_Expr('GROUP_CONCAT(c.classifier_id)'))
        );

        $select->joinLeft(
            array('c' => 'classifiers'),
            'c.classifier_id = cl.classifier_id',
            array('classifiers' => new Zend_Db_Expr('GROUP_CONCAT(c.name)'))
        );

        // отсечь левые висящие в воздухе, попавшие в диапазон lft-rgt
        $select->join(array('sop' => 'structure_of_organ'), 'sop.soid = so.owner_soid', array());

        $select->where('p.blocked = 0 OR p.blocked IS NULL');

        if ($this->_request->getParam('typegrid', -1) == -1) {
            $select->where('so.type IN (?)', array(
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_OrgstructureModel::TYPE_VACANCY,
            ));
            $this->_request->setParam('typegrid', null);
        }

        if ( (!empty($paramExp)) && ($paramAll) ){
            $swr = $session->grid['switchOrg'];
        } else {
            $swr = ($session->grid[$pageSess][$this->gridId]['all']) ? : 0;
        }

        if ($orgId > 0) {
            $orgUnit = $orgstructureService->find($orgId)->current();

            if ( (int) $swr == self::FILTER_ALL ) {
                $select->where('so.lft > ?', $orgUnit->lft);
                $select->where('so.rgt < ?', $orgUnit->rgt);
            } else {
                $select->where('so.lft > ?', $orgUnit->lft);
                $select->where('so.rgt < ?', $orgUnit->rgt);
                $select->where('so.level = ?', $orgUnit->level + 1);
            }
        } else {
            if ( (int) $swr == self::FILTER_STRICT) {
                $select->where('so.level = ?', 0);
            } 
        }

        $select->where('so.blocked = ?', 0);
        //        $select->where('so.soid NOT IN (?)', (!is_null($this->emptyDepartmentsCache) && ! empty($this->emptyDepartmentsCache)) ? $this->emptyDepartmentsCache : array(0));
        //         $select->where('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION);
        $select->group(array('so.soid', 'so.soid_external', 'p.mid', 'p.mid_external', 'so.name', 'so.type', 'so.mid', 'so.is_manager', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'p.EMail', 'ap.name', 'ap.profile_id', 'ap.user_id', 'ap.department_path'));
        $select->order('so.type');

        $columns = array(
            'soid' => array('hidden' => true),
            'org_id' => array('hidden' => true),
            'type_id' => array('hidden' => true),
            'profile_id' => array('hidden' => true),
            'profile_user_id' => array('hidden' => true),
            'is_manager' => array('hidden' => true),
            'is_vacant' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'newcomer_id' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'so_name' => array(
                'title' => _('Название'),
                'callback' => array('function' => array($this, 'updatePositionName'), 'params' => array('{{so_name}}', '{{org_id}}', '{{type}}', '{{is_manager}}', '{{MID}}'))
            ),
            'type' => array(
                'title' => _('Тип'),
                'callback' => array('function' => array($this, 'updateType'), 'params' => array('{{type}}'))
            ),
            'fio' => array(
                'title' => _('Пользователь')
            ),
            'classifiers' => array('hidden' => true),
            'profile' => array(
                'title' => _('Профиль должности'),
                // @todo: не работает $this->view->url(array('module' => 'profile', 'controller' => 'report', 'action' => 'index', 'gridmod' => null, 'profile_id' => ''), null, true)
                //'decorator' => '<a href="/at/profile/report/index/profile_id/{{profile_id}}'.'">'. '{{profile}}</a>'
                'callback' => array('function' => array($this, 'updateProfile'), 'params' => array('{{profile_id}}', '{{profile}}', '{{MID}}', '{{profile_user_id}}'))
            ),
            'department_path' => array('hidden' => true),
//			array(
//                'title' => _('Подразделение профиля'),
//                'callback' => array(
//                    'function' => array($this, 'updateDepartmentPath'),
//                    'params' => array('{{department_path}}')
//                ),
//            ),
            'kpis' => array('hidden' => true),
            //				array(
            //                 'title' => _('Показатели эффективности'),
            //                 'decorator' => '<a href="'.$this->view->url(array('module' => 'kpi', 'controller' => 'user', 'action' => 'index', 'gridmod' => null, 'user_id' => '', 'baseUrl' => 'at'), null, true) . '{{MID}}'.'">'. '{{kpis}}</a>'
            //             ),
            //            'classifiers' => array('hidden' => true),
            //             array(
            //                    'title' => _('Классификация')
            //                ),
            'courses' => array('hidden' => true),
            //             array(
            //                 'title' => _('Курсы'),
            //                 'callback' => array(
            //                 'function' => array($this, 'coursesCache'),
            //                 'params' => array('{{courses}}', $select))
            //             ),
        );

        if (
            $this->getService('Acl')->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
            !$this->getService('User')->isManager()
        ) {
            // hide for enduser
            $columns['profile'] = array('hidden' => true);
            $columns['kpis'] = array('hidden' => true);
            $columns['classifiers'] = array('hidden' => true);
            $columns['courses'] = array('hidden' => true);
        }

        //exit($select->__toString());

        $types = HM_Orgstructure_OrgstructureModel::getTypes();
        unset($types[HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT]);
        $types = array(-1 => _('Все')) + $types;

        $grid = $this->getGrid(
            $select,
            $columns,
            array(
                'so_name' => null,
                'type' => array(
                    'values'        => $types,
                    'removeShowAll' => true
                ),
                'fio' => null,
                'classifiers' => null,
                'profile' => null,
                'department_path' => null,
                'courses' => null,
            ),
            $this->gridId
        );

        $grid->updateColumn(
            'classifiers',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateClassifiers'),
                    'params'   => array('{{classifiers}}', $select)
                )
            )
        );

        $grid->updateColumn(
            'fio',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateFio'),
                    'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

        $grid->addAction(
            array(
                'module' => 'orgstructure',
                'controller' => 'list',
                'action' => 'edit'
            ),
            array('org_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(
            array(
                'module' => 'orgstructure',
                'controller' => 'list',
                'action' => 'delete'
            ),
            array('org_id'),
            $this->view->svgIcon('delete', 'Удалить'),
            _('Вы уверены, что хотите удалить данный элемент оргструктуры без сохранения истории?')
        );

        $grid->addAction(
            array(
                'module' => 'orgstructure',
                'controller' => 'list',
                'action' => 'archive'
            ),
            array('org_id'),
            _('Архивировать'),
            _('Вы уверены, что хотите перенести данную должность в архив? Это может понадобиться, в частности, при увольнении работника. Продолжить?')
        );

        $grid->addAction(
            array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'edit'
            ),
            array('MID'),
            _('Редактировать учетную запись пользователя')
        );

        $grid->addAction(
            array(
                'module' => 'orgstructure',
                'controller' => 'list',
                'action' => 'edit-respondents'
            ),
            array('org_id', 'MID'),
            _('Индивидуально настроить список респондентов')
        );

        $grid->addAction(
            array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'login-as'
            ),
            array('MID'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );

        if ($this->getService('Acl')->inheritsRole($userRole, array(
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ))) {
            $grid->addAction(
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'application',
                    'controller' => 'list',
                    'action' => 'new',
                    'create-from-structure' => 1,
                    'ordergrid' => null,
                    'gridmod' => null,
                    'all' => null,
                    'key' => null,
                    'treeajax' => null,
                ),
                array('soid'),
                _('Создать заявку на подбор')
            );
        }

        $grid->addAction(
            array(
                'baseUrl' => 'recruit',
                'module' => 'vacancy',
                'controller' => 'report',
                'action' => 'card',
                'key' => null,
                'treeajax' => null,
            ),
            array('vacancy_id'),
            _('Просмотр сессии подбора')
        );

        $grid->addAction(
            array(
                'baseUrl' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'create-from-structure',
                'key' => null,
                'treeajax' => null,
            ),
            array('org_id'),
            _('Создать сессию адаптации')
        );

        $grid->addAction(
            array(
                'baseUrl' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'report',
                'action' => 'index',
                'key' => null,
                'treeajax' => null,
            ),
            array('newcomer_id'),
            _('Просмотр сессии адаптации')
        );

        $grid->addAction(
            array(
                'baseUrl' => 'hr',
                'module' => 'rotation',
                'controller' => 'list',
                'action' => 'new',
                'key' => null,
                'treeajax' => null,
            ),
            array('MID', 'org_id'),
            _('Создать сессию ротации')
        );

        $grid->addMassAction(
            array(
                'controller' => 'list',
                'module' => 'orgstructure',
                'action' => 'delete-by',
                'parent' => $orgId
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        // профили назначаются в процессе синхронизации с 1С

        $grid->addMassAction(
            array(
                'controller' => 'list',
                'module' => 'orgstructure',
                'action' => 'assign-profile',
                'parent' => $orgId
            ),
            _('Назначить профиль должности'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'),
            array('multiple' => false)
        );

        $grid->addSubMassActionSelect(
            array(
                $this->view->url(
                    array('action' => 'assign-profile', 'parent' => $orgId)
                )
            ),
            'profile_id',
            HM_At_Profile_ProfileModel::getNotLinkedYetProfiles()
        );


        $grid->addMassAction(
            array(
                'controller' => 'list',
                'module' => 'orgstructure',
                'action' => 'unassign-profile',
                'parent' => $orgId
            ),
            _('Отменить назначение профиля должности'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $userId = $userService->getCurrentUserId();

        //для назначения на курсы должны отображать список активных курсов, для удаления - список всех курсов
        if ($this->getService('Acl')->inheritsRole($userRole, HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $collection = $this->getService('Dean')->getActiveSubjectsResponsibilities($userId);
            $fullCollection = $this->getService('Dean')->getSubjectsResponsibilities($userId);
        } else {
            $collection = $this->getService('Subject')->fetchAll(['
                (period IN (1,2) OR end > NOW() OR end IS NULL) AND 
                (is_labor_safety = 0) AND 
                (type != ' . HM_Subject_SubjectModel::TYPE_FULLTIME . ')
            '], 'name');
            $fullCollection = $this->getService('Subject')->fetchAll(['
                (is_labor_safety = 0) AND 
                (type != ' . HM_Subject_SubjectModel::TYPE_FULLTIME . ')
            '], 'name');
        }
        if (count($collection)) {
            $grid->addMassAction(
                array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'do-soids',
                    'do' => 'assign',
                ),
                _('Hазначить учебные курсы'),
                _('Вы уверены, что хотите назначить выбранные учебные курсы пользователям?')
            );

            $grid->addSubMassActionSelect(
                $this->view->url(
                    array(
                        'module' => 'assign',
                        'controller' => 'student',
                        'action' => 'do-soids',
                        'do' => 'assign',
                    )
                ),
                'subjectId',
                $collection->getList('subid', 'name')
            );

            $grid->addMassAction(
                array(
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'do-soids',
                    'do' => 'unassign',
                ),
                _('Отменить назначение учебных курсов'),
                _('Вы уверены, что хотите отменить назначение учебных курсов пользователям отмеченных подразделений, включая все уровни вложенности?')
            );

            if (count($fullCollection)) {
                $grid->addSubMassActionSelect(
                    $this->view->url(
                        array(
                            'module' => 'assign',
                            'controller' => 'student',
                            'action' => 'do-soids',
                            'do' => 'unassign',
                        )
                    ),
                    'subjectId',
                    $fullCollection->getList('subid', 'name')
                );
            }
        }

        $grid->addMassAction(
            array(
                'baseUrl' => 'at',
                'controller' => 'list',
                'module' => 'session',
                'action' => 'create-from-structure'
            ),
            _('Создать оценочную сессию'),
            _('Вы уверены, что хотите создать оценочную сессию для выбранных подразделений? При этом пользователям подразделений будут автоматически назначены оценочные мероприятия согласно настроенным профилям должностей.')
        );

        $grid->setGridSwitcher([
            'label' => _('Показать все вложенные'),
            'title' => _('Показать все вложенные записи'),
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'modes' => [
                self::FILTER_STRICT,
                self::FILTER_ALL,
            ],
        ]);

        if (!$this->isAjaxRequest()) {

            $openedParents = [0]; // 0 и 1 уровень открыты, остальные - Lazy
            if ($orgId && ($current = $orgstructureService->findOne($orgId))) {
                $parents = $orgstructureService->fetchAll([
                    'lft < ?' => $current->lft,
                    'rgt > ?' => $current->rgt,
                ]);
                $openedParents = $parents->getList('owner_soid');
            }

            $tree = $orgstructureService->getTreeContent($defaultParent->soid, true, $orgId, null, $openedParents);

            $tree = array(
                0 => array(
                    'title' => $defaultParent->name,
                    'count' => 0,
                    'key' => $defaultParent->soid,
                    'isLazy' => true,
                    'isFolder' => true,
                    'expand' => true
                ),
                1 => $tree
            );
            $this->view->tree = $tree;

            $gridUrl = $this->view->url(
                [
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'index',
                    'gridmod' => 'ajax',
                    'treeajax' => 'true'
                ],
                null,
                true
            );

            $rubricatorUrl = $this->view->url(
                [
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'get-tree-branch'
                ],
                null,
                true
            );

            $rubricatorValue = isset($orgUnit)
                ? $orgstructureService->orgUnitToFrontendData(
                    $orgUnit,
                    $defaultParent->soid,
                    true,
                    $orgId
                )
                : null;

            /** @see HM_View_Helper_VueRubricatorGridButton */
            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Оргструктура'),
                $rubricatorValue,
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                    'isAdmin' => $isAdmin,
                ]
            );
        }

        $grid->setActionsCallback(
            array(
                'function' => array($this, 'updateActions'),
                'params'   => array('{{type_id}}', '{{profile_id}}', '{{MID}}', '{{vacancy_id}}', '{{newcomer_id}}')
            )
        );

        $this->view->orgId = $orgId;
        $this->view->gridId = $grid->getGridId();
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function assignProgrammAction()
    {
        $programmId = $this->_getParam('programm_id', 0);

        if ($programmId > 0) {
            $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $orgElements = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $ids));

                    $this->getService('Lesson')->beginProctoringTransaction();
                    foreach($orgElements as $element){
                        if($element->mid > 0){
                            $this->getService('Programm')->assignToUser($element->mid, $programmId);
                        }
                    }
                    $this->getService('Lesson')->commitProctoringTransaction();
                    $this->_flashMessenger->addMessage(_('Программа успешно назначена'));
                    $this->_redirectToIndex();
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Программа не выбрана'));
        $this->_redirectToIndex();
    }

    public function assignProfileAction()
    {
        $profileId = $this->_getParam('profile_id', 0);

        if ($profileId) {
            $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $positions = $this->getService('Orgstructure')->fetchAll(array(
                        'soid IN (?)' => $ids,
                        'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    ));

                    if (count($positions)) {
                        if (is_array($profileId)) {
                            foreach ($profileId as $pId) {
                                $this->getService('AtProfile')->assign($pId, $positions->getList('soid'));
                            }
                        } else {
                            $this->getService('AtProfile')->assign($profileId, $positions->getList('soid'));
                        }
                    }
                    $this->_flashMessenger->addMessage(_('Профиль успешно назначен'));
                    $this->_redirectToIndex();
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Профиль не выбран'));
        $this->_redirectToIndex();
    }

    public function unassignProfileAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $this->getService('AtProfile')->unassign($ids);
                $this->_flashMessenger->addMessage(_('Назначение профиля отменено'));
                $this->_redirectToIndex();
            }
        }
    }

    public function assignSessionAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {

                $session = $this->getService('AtSession')->insert(
                    array(
                        'initiator_id' => $this->getService('User')->getCurrentUserId(),
                        'name' => 'Session 1',
                        'cycle_id' => 1,
                        'begin_date' => '2012-03-26',
                        'end_date' => '2012-03-26'
                    )
                );

                $orgElements = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $ids));

                foreach ($orgElements as $element) {
                    if ($element->mid > 0) {

                        $employee = $this->getService('User')->find($element->mid)->current();


                        //AtSessionUser
                        $link = $this->getService('AtSessionUser')->insert(
                            array(
                                'session_id' => $session->session_id,
                                'user_id'    => $element->mid
                            )
                        );

                        $chief = $this->getService('Orgstructure')->find($element->owner_soid)->current();
                        $chiefId = 0;
                        if ($chief) {
                            $chiefId = $chief->mid;
                        }

                        $programms = $this->getService('ProgrammUser')->fetchAll(array('user_id = ?' => $element->mid));
                        foreach ($programms as $programm) {
                            $events = $this->getService('Programm')->getEvents($programm->programm_id);

                            // @todo: рефакторить
                            // логика создания session_event'ов должна быть внутри методики
                            // например, в оценке 360 град. создается сразу много event'ов (самому пользователю, его руководителю и т.д.)
                            foreach ($events as $event) {
                                if (empty($event->evaluation)) continue;
                                $this->getService('AtSessionEvent')->insert(array(
                                    'session_user_id' => $link->session_user_id,
                                    'evaluation_id'   => $event->item_id,
                                    'user_id'         => $element->mid,
                                    'chief_id'        => $chiefId,
                                    'status'          => 0,
                                    'programm_id'     => $programm->programm_id,
                                    'name'            => $event->name . ' ' . _('по пользователю') . ' ' . $employee->getName(),
                                    'method'          => $this->getService('AtEvaluation')->getOne($event->evaluation)->method
                                ));
                            }
                        }
                        //$this->getService('AtEvaluationSession')->assignToUser($element->mid, $programmId);
                    }
                }
                $this->_flashMessenger->addMessage(_('Сессия успешно создана'));
                $this->_redirectToIndex();
            }
        }
    }


    public function classifierAction()
    {

        $request = $this->getRequest();
        $orgId = (int) $request->getParam('org_id', 0);

        $form = new HM_Form_Classifier();

        if ($request->isPost() && $form->isValid($params = $request->getParams())) {
            $this->getService('Orgstructure')->setClassifiers(
                $orgId,
                $form->getSubForm('classifierStep2')->getClassifierTypes(),
                $form->getSubForm('classifierStep2')->getClassifierValues()
            );
            $this->_flashMessenger->addMessage(_('Классификация успешно изменена'));
            $this->_redirector->gotoSimple('index');
        }

        $this->view->form = $form;
    }


    public function updateClassifiers($classifiers)
    {
        $classifiers = array_unique(explode(',', $classifiers));
        $classifiers = array_unique($classifiers);
        $classifiers = implode(', <br/>', $classifiers);
        return $classifiers;
    }

    public function updateType_($type, $vacancyId, $isManager)
    {
        if ($vacancyId) {
            $url = $this->view->url(array('module' => 'vacancy', 'controller' => 'list', 'action' => 'index', 'vacancy_id' => $vacancyId, 'baseUrl' => 'recruit'));
            return "<a class='vacancy' href='{$url}/?page_id=m6801'>" . _('Вакансия') . "</a>";
        }
        $types = HM_Orgstructure_OrgstructureModel::getTypes();
        return $types[$type] . ($isManager ? ' (' . _('рук.') . ')' : '');
    }

    public function updateType($type)
    {
        $types = HM_Orgstructure_OrgstructureModel::getTypes();
        return $types[$type];
    }

    public function updateProfile($profileId, $profile, $mid, $profileUserId)
    {
        $return = '';
        if ($this->_relationsCache === null) {
            $this->_relationsCache = $this->getService('AtRelation')->fetchAll()->getList('user_id');
        }
        if ($this->_profilesCache === null) {
            $this->_profilesCache = $this->getService('AtProfile')->fetchAll()->getList('profile_id');
        }
        if ($profileId) {
            //if ($this->getService('Acl')->isCurrentAllowed('mca:profile:report:index')) {
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_HR,
                HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            ))) {
                $return = "<a href='/at/profile/report/index/profile_id/{$profileId}'>{$profile}</a>";
            } else {
                $return = $profile;
            }
            //             if (!empty($profileUserId)) {
            //                 $return .= HM_View_Helper_Footnote::marker(1);
            //                 $this->view->footnote(_('Установлены индивидуальные настройки профиля должности'), 1);
            //             }
            if (
                in_array($profileId, $this->_profilesCache) &&
                in_array($mid, $this->_relationsCache) &&
                $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))
            ) {
                $return .= HM_View_Helper_Footnote::marker(1);
                $this->view->footnote(_('Установлены индивидуальные настройки списка респондентов'), 1);
            }
        }
        return $return;
    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', $this->getService('Orgstructure')->getDefaultParent()->soid);

        $children = $this->getService('Orgstructure')->getTreeContent($key, false);

        echo HM_Json::encodeErrorSkip($children);
        exit;
    }

    public function cardAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $orgId = (int) $this->_getParam('org_id', 0);
        $position = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->find($orgId)
        );

        $profileName =
            $userName    = '';
        if ($profileId = $position->getValue('profile_id')) {
            $profile = $this->getService('AtProfile')->getOne(
                $this->getService('AtProfile')->find($profileId)
            );
            if ($profile) {
                $profileName  = $profile->getValue('name');
            }
        }

        if ($userId = $position->getValue('mid')) {
            $user = $this->getService('User')->getOne(
                $this->getService('User')->find($userId)
            );
            if ($user) {
                $userName  = $user->getName();
            }
        }

        $data = [];
        $data['cardTitle']  = ($position->getType() == HM_Orgstructure_OrgstructureModel::TYPE_POSITION) ? _('Карточка должности') : _('Карточка подразделения');
        $data['name']       = ['key' => _('Название'),     'value' => $position->getValue('name')];
        $data['profile']    = ['key' => _('Профиль'),      'value' => $profileName];
        $data['department'] = ['key' => _('Входит в'),     'value' => $position->getOrgPath()];
        $data['user']       = ['key' => _('В должности'),  'value' => $userName];

        $this->view->fields  = array_values($data);

        // todo: удалить когда во Vue-компоненте будут ислользоваться только данные из $data
        $this->view->title = $data['cardTitle'];
    }

    // ВНИМАНИЕ!!! здесь определяется только unset-логика, не связанная с ролями
    // unset в зависимости от ролей - в ACL
    public function updateActions($type, $profileId, $userId, $vacancyId, $newcomerId, $actions)
    {
        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {

            $this->unsetAction($actions, array('controller' => 'list', 'action' => 'edit-respondents'));
            $this->unsetAction($actions, array('module' => 'vacancy', 'controller' => 'list', 'action' => 'create-from-structure'));
            $this->unsetAction($actions, array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card'));
            $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'list', 'action' => 'create-from-structure'));
            $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'report', 'action' => 'index'));
            $this->unsetAction($actions, array('module' => 'rotation', 'controller' => 'list', 'action' => 'new'));
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'login-as'));
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'report', 'action' => 'index'));
        } else {

            if ($this->_profilesCache === null) {
                $this->_profilesCache = $this->getService('AtProfile')->fetchAll()->getList('profile_id');
            }

            // если нет профиля
            if (!in_array($profileId, $this->_profilesCache)) {
                $this->unsetAction($actions, array('controller' => 'list', 'action' => 'edit-respondents'));
                $this->unsetAction($actions, array('module' => 'vacancy', 'controller' => 'list', 'action' => 'create-from-structure'));
                $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'list', 'action' => 'create-from-structure'));
                $this->unsetAction($actions, array('module' => 'rotation', 'controller' => 'list', 'action' => 'new'));
            }

            // если нет юзера
            if (empty($userId)) {
                $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'edit'));
                $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'login-as'));
                $this->unsetAction($actions, array('baseUrl' => 'recruit', 'module' => 'newcomer', 'controller' => 'list', 'action' => 'create-from-structure'));
                $this->unsetAction($actions, array('baseUrl' => 'hr', 'module' => 'rotation', 'controller' => 'list', 'action' => 'new'));
            }

            // просмотр либо создание сессии адаптации
            if (empty($newcomerId)) {
                $this->unsetAction($actions, array(
                    'module' => 'newcomer',
                    'controller' => 'report',
                    'action' => 'index'
                ));
            } else {
                $this->unsetAction($actions, array(
                    'module' => 'newcomer',
                    'controller' => 'list',
                    'action' => 'create-from-structure'
                ));
            }

            // просмотр либо создание сессии подбора
            if (empty($vacancyId)) {
                $this->unsetAction($actions, array(
                    'module' => 'vacancy',
                    'controller' => 'report',
                    'action' => 'index'
                ));
            } else {
                $this->unsetAction($actions, array(
                    'controller' => 'list',
                    'module' => 'vacancy',
                    'action' => 'create-from-structure'
                ));
            }
        }

        return $actions;
    }

    public function updateName($name, $orgId, $type, $isManager)
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) return $name;

        // не показываем ссылку пока там не будет нормального аккордеона
        //         if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
        //             $name = '<a href="'.$this->view->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $orgId), null, true).'">'.$name.'</a>';
        //         }

        return $this->view->cardLink(
            $this->view->url(
                array(
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'card',
                    'org_id' => ''
                )
            ) . $orgId,
            HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
            'icon-custom',
            'pcard',
            'pcard',
            'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager)
        ) . $name;
    }

    public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!$userId) return $fio;

        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
            !$this->getService('User')->isManager() &&
            $this->getService('User')->getCurrentUserId() != $userId
        ) return $fio;


        return $this->view->cardLink(
            $this->view->url(array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'view',
                'user_id' => ''
            ), null, true) . $userId
        ) .
            '<a href="' . $this->view->url(array(
                'module' => 'user',
                'controller' => 'report',
                'action' => 'index',
                'user_id' => ''
            ), null, true) . $userId . '">' . $fio . '</a>';
    }

    protected function _redirectToIndex()
    {
        /** @var Zend_Session_Namespace $session */
        $session = new Zend_Session_Namespace('default');

        $orgId = (int) $this->_getParam('parent', $session->orgstructure_id);
        $this->_redirector->gotoSimple('index', 'list', 'orgstructure', array('key' => $orgId), null, true);
    }

    protected function _getMessages()
    {

        return array(
            self::ACTION_INSERT     => _('Элемент успешно создан'),
            self::ACTION_UPDATE     => _('Элемент успешно обновлён'),
            self::ACTION_DELETE     => _('Элемент успешно удалён'),
            self::ACTION_DELETE_BY  => _('Элементы успешно удалены'),
            self::ACTION_ARCHIVE    => _('Элемент успешно архивирован'),
        );
    }

    public function setDefaults(Zend_Form $form)
    {
        $orgId = (int) $this->_getParam('org_id', 0);
        $item = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->find($orgId)
        );
        $values = $item->getValues();

        $mid = $values['mid'];
        if ($mid) {
            //$values['mid'] = array($values['mid'] => '');
            $values['mid'] = $values['mid'] = [[
                'key' => '',
                'value' => $mid
            ]];
            $user = $this->getOne(
                $this->getService('User')->find($mid)
            );
            if ($user) {
                //$values['mid'][$mid] = $user->getName();
                $values['mid'] = [[
                    'key' => $user->getName(),
                    'value' => $mid
                ]];
            }
        } else {
            $values['mid'] = [];
        }

        $form->setDefaults($values);
    }

    public function create(Zend_Form $form)
    {

        $values = array(
            'name' => $form->getValue('name'),
            'is_manager' => $form->getValue('is_manager'),
            'type' => $form->getValue('type'),
            'code' => $form->getValue('code'),
            'info' => $form->getValue('info'),
            'owner_soid' => $form->getValue('owner_soid'),
            'created_at' => HM_Date::now()->toString(HM_Date::SQL),
        );

        if ($form->getElement('mid')) {
            $values['mid'] = $form->getValue('mid');

            if (is_array($values['mid'])) {
                if (count($values['mid'])) {
                    $values['mid'] = $values['mid'][0];
                } else {
                    $values['mid'] = 0;
                }
            }
            $values['position_date'] = date('Y-m-d');
        }

        $this->getService('Orgstructure')->insert(
            $values,
            $form->getValue('owner_soid')
        );
    }

    public function update(Zend_Form $form)
    {
        // это зачем-то продублировано еще внутри формы
        $orgId = (int) $this->_getParam('org_id', 0);
        if ($orgId) {
            $item = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->find($orgId));
            $midBeforeUpdate = $item->mid;
        }

        date('Y-m-d');

        $values = array(
            'soid' => $form->getValue('soid'),
            'is_manager' => $form->getValue('is_manager'),
            'name' => $form->getValue('name'),
            'code' => $form->getValue('code'),
            'info' => $form->getValue('info'),
        );

        if ($form->getElement('mid')) {
            $values['mid'] = $form->getValue('mid');

            if (is_array($values['mid']) && count($values['mid'])) {
                $values['mid'] = $values['mid'][0];
            } else {
                $values['mid'] = 0;
            }

            if ($values['mid'] && ($values['mid'] != $midBeforeUpdate)) {
                $values['position_date'] = date('Y-m-d');
                $values['is_first_position'] = 1;
            }
        }

        $position = $this->getService('Orgstructure')->update($values);


        // в ГСП не назначались программы при редактировании; назначались при создании адаптации

        if ($profileId = $form->getValue('profile_id')) {
            if ($values['mid']) {
                $this->getService('AtProfile')->assignPosition($profileId, $position);
            } else {
                $this->getService('AtProfile')->unassignUser(array($midBeforeUpdate), $profileId);
            }
        }

        $this->_setParam('parent', $form->getValue('owner_soid'));
    }

    public function deleteAction()
    {
        $soid = $this->getRequest()->getParam('soid', $this->getRequest()->getParam('org_id', 0));
        $this->delete($soid);
        $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE));
        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $this->delete($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function delete($id)
    {
        $unit = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->find($id)
        );

        if ($unit) {
            $this->_request->setParam('parent', $unit->owner_soid);
        }
        return $this->getService('Orgstructure')->deleteNode($id, true);
    }

    public function archiveAction()
    {
        $soid = $this->getRequest()->getParam('soid', $this->getRequest()->getParam('org_id', 0));
        $orgstructureService = $this->getService('Orgstructure');

        $position = $orgstructureService->getOne(
            $orgstructureService->find($soid)
        );

        if ($position) {
            $this->_request->setParam('parent', $position->owner_soid);


            $parentBranch = $orgstructureService->getAllOwnersOnBranch($position->lft, $position->rgt);

            $ownersSoids = array();

            foreach ($parentBranch as $owner) {
                $ownersSoids[] = $owner['soid'];
            }

            $orgstructureHistoryService = $this->getService('OrgstructureHistory');
            $ownersHistorySoids = $orgstructureHistoryService->getSameOrgstructureBranch($ownersSoids);

            if (count($ownersHistorySoids)) {
                foreach ($parentBranch as $key => &$unit) {
                    foreach ($ownersHistorySoids as $soidHistory) {
                        if ($soidHistory['soid'] === $unit['soid']) {
                            unset($parentBranch[$key]); // Удаляем подразделение, которое есть в архиве
                            continue 2; // Для того, чтобы не попытаться добавить удаленное из массива подразделение
                        }
                    }

                    $unit['deleted_at'] = HM_Date::now()->toString(HM_Date::SQL);
                    $orgstructureHistoryService->archive($unit);
                }
                unset($unit); // Удаляем ссылку
            } else {
                foreach ($parentBranch as $key => &$unit) {
                    $unit['deleted_at'] = HM_Date::now()->toString(HM_Date::SQL);
                    $orgstructureHistoryService->archive($unit);
                }
                unset($unit); // Удаляем ссылку
            }

            $position->deleted_at = HM_Date::now()->toString(HM_Date::SQL);
            $position = $position->getValues();
            $orgstructureHistoryService->archive($position);
            $orgstructureHistoryService->repairStructure();
            $this->getService('User')->update(array(
                'MID' => $position['mid'],
                'blocked' => 1
            ));

            $this->delete($position['soid']); // Удаляем ТОЛЬКО(!) должность
            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_ARCHIVE));
        }

        $this->_redirectToIndex();
    }

    public function moveAction()
    {
        $parent = $this->_getParam('to', null);
        if (null !== $parent) {
            $postMassIds = $this->_getParam('postMassIds_' . $this->gridId, '');
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $errorFlag = false;
                    foreach ($ids as $id) {
                        $this->getService('Orgstructure')->updateNode(
                            array('owner_soid' => $parent),
                            $id,
                            $parent
                        );
                    }
                    $this->_flashMessenger->addMessage(_('Элемент успешно перемещён'));
                }
            }
            $this->_setParam('parent', $parent);
        }
        $this->_redirectToIndex();
    }

    public function editRespondentsAction()
    {
        $MID = $this->_getParam('MID', 0);
        $user = $this->getService('User')->find($MID)->current();

        $this->view->setSubHeader(_('Редактирование списка респондентов'));
        $this->view->setHeader(_("$user->LastName $user->FirstName $user->Patronymic"));

        $this->view->setBackUrl($this->view->url([
            'module' => 'orgstructure',
            'controller' => 'list',
            'module' => 'index',
            'key' => $this->_item->owner_soid,
            'gridmod' => null,
            'MID' => null,
        ]));

        $form = new HM_Form_Respondents();

        $userRelations = $this->getService('AtRelation')->fetchAll(array('user_id = ?' => $this->_item->mid))->getList('relation_type', 'respondents');

        // очень накладно иметь такой кэш
        //         $collection = $this->getService('User')->fetchAll();
        //         foreach ($collection as $user) {
        //             $usersCache[$user->MID] = $user;
        //         }

        $relationTypeRespondents = array();
        if (!empty($this->_item->profile_id)) {
            $profile = $this->getService('AtProfile')->findDependence('Evaluation', $this->_item->profile_id)->current();
            $evaluations = $this->getService('AtEvaluation')->fetchAll([
                'method = ?' => HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE,
                'relation_type IN (?) AND profile_id IS NULL AND category_id = 0' => [HM_At_Evaluation_EvaluationModel::RELATION_TYPE_PARENT,HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SIBLINGS, HM_At_Evaluation_EvaluationModel::RELATION_TYPE_CHILDREN]
            ]);
            foreach ($evaluations as $evaluation) {
                //if (($evaluation->method != HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE) || ($evaluation->relation_type == HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF)) continue;

//                if (!HM_At_Evaluation_Method_CompetenceModel::isCustomRespondentsEnabled($evaluation->relation_type)) continue;
                $relationTypeRespondents[$evaluation->relation_type] = array();
                if (count($respondents = $evaluation->getRespondents($this->_item))) {
                    foreach ($respondents as $respondent) {
                        // @todo: кэшировать, но не всех пиплов а только кого надо
                        if ($user = $this->getService('User')->getOne($this->getService('User')->find($respondent->mid))) {
                            $relationTypeRespondents[$evaluation->relation_type][$respondent->mid] = sprintf('%s (%s)', $user->getName(), $respondent->name);
                        }
                    }
                }
            }
        }

        // alter form
        foreach (array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()) as $relationType) {
            if (isset($relationTypeRespondents[$relationType])) {
                $element = $form->getElement('respondents_' . $relationType);
                if (count($relationTypeRespondents[$relationType])) {
                    $element->setOptions(array('MultiOptions' => $relationTypeRespondents[$relationType]));
                } else {
                    $element->setLabel(null); // не смущаем пользователя названием "Основной список", под которым ничего нет
                }
            } else {
                $form->removeElement('respondents_' . $relationType);
                $form->removeElement('respondents_custom_' . $relationType);
                $form->removeDisplayGroup('group_' . $relationType);
            }
        }

        $empty = true;
        foreach ($form->getElements() as $key => $element) {
            if (strpos($key, 'respondents_') !== false) {
                $empty = false;
                break;
            }
        }
        $this->view->empty = $empty;

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $oldValues = array();
                foreach (array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()) as $relationType) {
                    if (!HM_At_Evaluation_Method_CompetenceModel::isCustomRespondentsEnabled($relationType)) continue;


                    if (empty($userRelations[$relationType])) { // если для этого юзера не настроены custom'ные связи
                        $oldValues[$relationType] = array_keys($relationTypeRespondents[$relationType]); // check all
                    } else {

                        $oldValues[$relationType] = unserialize($userRelations[$relationType]);

                        $diff = array_diff(unserialize($userRelations[$relationType]), array_keys($relationTypeRespondents[$relationType]));
                        if (count($diff)) {
                            foreach ($diff as $userId) {
                                $oldValues[$relationType][] = $userId;
                            }
                        }
                    }
                }

                $values = $form->getValues();
                $newValues = array();
                $profile = $this->getService('AtProfile')->findDependence('Evaluation', $this->_item->profile_id)->current();
                foreach ($relationTypeRespondents as $relationType => $respondents) {

                    $checkedRespondents = is_array($values['respondents_' . $relationType]) ? $values['respondents_' . $relationType] : array();
                    $fcbkRespondents = is_array($values['respondents_custom_' . $relationType]) ? $values['respondents_custom_' . $relationType] : array();

                    $this->getService('AtRelation')->deleteBy(array(
                        'user_id = ?' => $this->_item->mid,
                        'relation_type = ?' => $relationType,
                    ));

                    if (count($fcbkRespondents) || (count($respondents) != count($checkedRespondents))) {
                        $this->getService('AtRelation')->insert(array(
                            'user_id' => $this->_item->mid,
                            'respondents' => serialize(array_merge($checkedRespondents, $fcbkRespondents)),
                            'relation_type' => $relationType,
                        ));
                    }

                    $fcbkValues[$relationType] = array();
                    $newValues[$relationType] = array();
                    if (count($fcbkRespondents)) {
                        foreach ($fcbkRespondents as $userId) {
                            if ($user = $this->getService('User')->getOne($this->getService('User')->find($userId))) {
                                $fcbkValues[$userId] = $user->getName();
                                $newValues[$relationType][] = $userId;
                            }
                        }
                    }

                    if (count($checkedRespondents)) {
                        foreach ($checkedRespondents as $userId) {
                            $newValues[$relationType][] = $userId;
                        }
                    }

                    $elementFcbk = $form->getElement('respondents_custom_' . $relationType);
                    $elementFcbk->setValue($fcbkValues);
                }

                $toAddUserIds = array();
                $toRemoveUserIds = array();

                $toAddUserCount = 0;
                $toRemoveUserCount = 0;

                foreach (array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()) as $relationType) {

                    $toAddUserIds[$relationType] = array();
                    $toRemoveUserIds[$relationType] = array();
                    foreach ($oldValues[$relationType] as $oldValue) {
                        if (!in_array($oldValue, $newValues[$relationType])) {
                            $toRemoveUserIds[$relationType][] = $oldValue;
                        }
                    }
                    foreach ($newValues[$relationType] as $newValue) {
                        if (!in_array($newValue, $oldValues[$relationType])) {
                            $toAddUserIds[$relationType][] = $newValue;
                        }
                    }

                    $toAddUserIds[$relationType] = array_unique($toAddUserIds[$relationType]);
                    $toRemoveUserIds[$relationType] = array_unique($toRemoveUserIds[$relationType]);


                    $toAddUserCount += count($toAddUserIds[$relationType]);
                    $toRemoveUserCount += count($toRemoveUserIds[$relationType]);
                }

                if ($toAddUserCount || $toRemoveUserCount) {

                    $user = $this->getService('User')->fetchOne($this->getService('User')->quoteInto(
                        array(
                            'mid = ?'
                        ),
                        array(
                            $this->_item->mid
                        )
                    ));

                    $sessionUsers = $this->getService('AtSessionUser')->fetchAll(
                        $this->getService('AtSessionUser')->quoteInto(
                            array(
                                'user_id = ?'
                            ),
                            array(
                                $this->_item->mid
                            )
                        )
                    );

                    foreach ($sessionUsers as $sessionUser) {
                        $session_id = $sessionUser->session_id;

                        $session = $this->getService('AtSession')->fetchOne($this->quoteInto(
                            array(
                                'session_id = ?'
                            ),
                            array(
                                $session_id
                            )
                        ));

                        if ($session->programm_type != HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
                            // ПОКА ПРОПУСКАЕМ ДРУГИЕ ТИПЫ СЕССИ!
                            continue;
                        }

                        $evaluationContainer = $this->getService('AtSession')->getProfileEvaluations($profile);
                        // для рег.оценки даты событий отсчитываются от даты начала сессии

                        $programm = $this->getService('Programm')->getOne(
                            $this->getService('Programm')->fetchAllDependence(
                                array('Event', 'EventUser'),
                                $this->quoteInto(
                                    array('item_id = ?', ' AND programm_type = ?'),
                                    array($this->_item->profile_id, $session->programm_type)
                                )
                            )
                        );

                        if (count($evaluationContainer)) {
                            foreach ($evaluationContainer as $evaluation) {
                                if ($evaluation->programm_type != $session->programm_type) continue;
                                //if (isset($this->_restrictions['method']) && ($this->_restrictions['method'] != $evaluation->method)) continue;

                                if (count($toAddUserIds[$evaluation->relation_type])) {
                                    foreach ($toAddUserIds[$evaluation->relation_type] as $newUserId) {
                                        $sessionRespondent = $this->getService('AtSessionRespondent')->safeAddRespondentToSession($newUserId, $session->session_id);
                                        $sessionEvent = $this->getService('AtSessionEvent')->addEventToSession($session, $user, $sessionUser, $sessionRespondent, $evaluation, $programm);
                                    }
                                }

                                if (count($toRemoveUserIds[$evaluation->relation_type])) {
                                    foreach ($toRemoveUserIds[$evaluation->relation_type] as $removeUserId) {
                                        $sessionEvent = $this->getService('AtSessionEvent')->removeEventFromSession($session, $user, $sessionUser, $removeUserId, $evaluation, $programm);
                                    }
                                }
                            }
                        }
                        $this->getService('AtSessionRespondent')->deleteRespondentsWithoutEvents($session_id);
                    }
                }

                $this->_flashMessenger->addMessage(_('Список респондентов успешно изменен.'));
                $this->_redirector->gotoSimple('edit-respondents', 'list', 'orgstructure', array('org_id' => $this->_item->soid));
            }
        } else {
            // populate
            foreach (array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()) as $relationType) {
                if (!HM_At_Evaluation_Method_CompetenceModel::isCustomRespondentsEnabled($relationType)) continue;

                if (isset($relationTypeRespondents[$relationType])) { // если это relation_type используется профилем
                    $element = $form->getElement('respondents_' . $relationType);
                    if (empty($userRelations[$relationType])) { // если для этого юзера не настроены custom'ные связи
                        $element->setValue(array_keys($relationTypeRespondents[$relationType])); // check all
                    } else {
                        $respondents = unserialize($userRelations[$relationType]);
                        if ($respondents && count($respondents)) {
                            $element->setValue($respondents);
                        }

                        $fcbkValues = array();
                        $diff = array_diff($respondents, array_keys($relationTypeRespondents[$relationType]));
                        if (count($diff)) {
                            foreach ($diff as $userId) {
                                if ($user = $this->getService('User')->getOne($this->getService('User')->find($userId))) {
                                    $fcbkValues[$userId] = [[
                                        'key' => $user->getName(),
                                        'value' => $userId
                                    ]];
                                }
                            }
                        }
                        $elementFcbk = $form->getElement('respondents_custom_' . $relationType);
                        $elementFcbk->setValue($fcbkValues);
                    }
                }
            }
        }

        $this->view->form = $form;
        if (!count($form->getDisplayGroups())) {
            $this->view->message = _('Нет данных для отображения');
        }
    }

    public function editUserProfileAction()
    {
        $profileId = $this->_getParam('profile_id', 0);
        $userId = $this->_getParam('MID', 0);

        // @todo: использовать gotoSimple
        $this->_redirector->gotoUrl("/at/profile/method/index/profile_id/{$profileId}/user_id/{$userId}");
    }

    public function reassignAction()
    {
        $profileId = $this->_getParam('profile_id', 0);
        $soid = $this->_getParam('soid', 0);

        $this->getService('AtProfile')->unassign(array($soid));
        $this->getService('AtProfile')->assign($profileId, array($soid));
        exit('ok');
    }

    //
    //  Вызов (1): $this->_redirector->gotoSimple('check', 'list', 'orgstructure', array('from' => 'import'));
    //  Вызов (2): /orgstructure/list/check/from/{import|origin|all}/for/{profile|date|all}/mode/{show|edit}
    //
    public function checkAction(){

        $userService = $this->getService('User');
        $userRole = $userService->getCurrentUserRole();
        $isAdmin = $this->getService('Acl')->inheritsRole($userRole, array(
            HM_Role_Abstract_RoleModel::ROLE_ADMIN
        ));
        if (!$isAdmin){
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        }
        
        $from = mb_strtolower(htmlspecialchars($this->_getParam('from', 0)));
        
        if (!in_array( $from, ['all', 'import', 'origin'] )) {
            $this->_flashMessenger->addMessage(_('Не указана группа для проверки профиля должности'));
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        }
        
        $for = mb_strtolower(htmlspecialchars($this->_getParam('for', 0)));
        if (!in_array( $for, ['all', 'date', 'profile'] )) {
            $for = 'all';
        }

        $mode = mb_strtolower(htmlspecialchars($this->_getParam('mode', 0)));
        if (!in_array( $mode, ['show', 'edit'] )) {
            $mode = 'show';
        }

        $count = [];
        $count = $this->checkProfiles( $from, $for, $mode );
        
        if ( $mode == 'edit' && ($count[0] || $count[1])) {
            
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' =>_(  
                    ( ($count[0] && $mode == 'edit' && in_array( $for, ['all', 'profile'])) ? 'Oбновленных профилей должности: ' . $count[0] . '. ' :'') .
                    ( ($count[1] && $mode == 'edit' && in_array( $for, ['all', 'date'])) ? 'Oбновленных дат: ' . $count[1] . '. ' : '')
            )));
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');        // return $count;
        } 
        if ( (!$mode || $mode == 'show') && ($count[0] || $count[1]) ) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' =>_(  
                    ( ($count[0]) ? 'Пропущенных профилей должности: ' . $count[0] . '. ' :'') .
                    ( ($count[1])  ? 'Пропущенных дат: ' . $count[1] . '. ' : '')
            )));
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');        // return $count;
        } 
        
        // $this->_flashMessenger->addMessage(_('Профилей для обновления должностей нет!'));
        $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        
    }

    public function checkProfiles( string $from = 'import', string $for = 'all', string $mode = 'show' ) {

        // pr(($this->_request->module));

        $editProfile = false;
        $editDate = false;
        
        if ($for == 'all') {
            $editProfile = true;
            $editDate = true;
        } elseif ($for == 'profile') {
            $editProfile = true;
        } elseif ($for == 'date') {
            $editDate = true;
        } else {
            $editProfile = false;
            $editDate = false;
        }

        if ($mode == 'edit') {
            $modeEdit = true;
        } else {
            $modeEdit = false;
        }
        
        // $items = $orgService->fetchAll(array(
        //     'blocked = ?' => 0,
        //     'soid_external IS NOT NULL',
        //     'type = ?' => 1
        // ));

        $whereIs = [];
        $whereIs['blocked = ?'] =  0;
        $whereIs['type = ?'] = 1;
        
        switch ($from) {

        case 'import':
            $whereIs['soid_external IS NOT NULL'] = '';
            break;
        case 'origin':
            $whereIs['soid_external IS NULL'] = '';
            break;            
        default:
            break;
        }
        
        $orgService = $this->getService('Orgstructure');
        $items = $orgService->fetchAll($whereIs);

        $profService = $this->getService('AtProfile');
        $profs = $profService->fetchAll();
    
        $countProf = 0;
        $countDate = 0;

        if (count($items) && count($profs)) {
            foreach($items as $item) {
                $isChanged = false;
                if (! $item->position_date ) {
                    if ($modeEdit && $editDate) {
                        $item->position_date = date('Y-m-d');
                        $isChanged = true;
                    }
                    $countDate++;
                }
                foreach($profs as $prof) {
                    if ( mb_strtolower(trim($prof->name)) == mb_strtolower(trim($item->name)) 
                    && ( $item->profile_id <> $prof->profile_id) ){

                        if ($modeEdit && $editProfile) {
                            $item->profile_id = $prof->profile_id;
                            $isChanged = true;
                        }
                        $countProf++;
                        continue;    
                    }
                    if ($isChanged && $modeEdit) {                  
                        $orgService->update($item->getValues());
                    }
                }
            }
        }
        return [$countProf, $countDate];
    }

    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getParams())) {
            $this->update($form);

            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_UPDATE));
            $this->_redirectToIndex();
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }
}
