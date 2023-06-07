<?php
class Htmlpage_ListController extends HM_Controller_Action {

    use HM_Controller_Action_Trait_Grid {
        newAction as protected gridTraitNewAction;
    }

    protected $field;

    protected $key;

    public function init()
    {
        $this->_setForm(new HM_Form_Page());
        parent::init();

        $this->gridId = 'htmlpageGrid';

        // $key может быть ролью или group_id при $type=true и page_id при $type=false
        // теперь $key может быть ролью при текстовом виде, group_id или page_id при числовом
        // если роль - выводим список групп,
        // если группа - список страниц,
        // если страница - форму редактирования
        $type = $this->_getParam('type', 1);

        $session = new Zend_Session_Namespace('default');
        $sessionKey = $session->htmlpage_key;

        $this->key = $this->_getParam('key', $sessionKey ?: 0);
        $this->field = (!is_numeric($this->key)) ? 'role' : (($type != 'false') ? 'group_id' : 'page_id');
    }

    public function indexAction()
    {
        // нельзя вложенные группы и группы без роли
        $key = $this->key;
        $field = $this->field;

        if (empty($key) || ($field == 'group_id')) {
            $this->view->unsetActionById('mca:htmlpage:group:index:new');
        }

        switch ($field) {
            case 'group_id': $grid = $this->getPagesGrid(); break;
            case 'role':     $grid = $this->getGroupsGrid(); break;
        }

        $session = new Zend_Session_Namespace('default');

        $sessionRole = $session->htmlpage_key;
        $sessionField = $session->htmlpage_field;

        $key = $this->_getParam('key', $sessionRole ?: $key);
        $field = $this->_getParam('field', $sessionField ?: $field);

        $session->htmlpage_key = $key;

        if (!$this->isAjaxRequest()) {

            $tree = $this->getService('HtmlpageGroup')->getTreeContent();
            $tree = [
                0 => [
                    'title'    => _('Все роли'),
                    'count'    => 0,
                    'key'      => 0,
                    'isLazy'   => true,
                    'isFolder' => true,
                    'expand'   => true
                ],
                1 => $tree
            ];

            $rubricatorValue = !empty($key) && !empty($field)
                ? $this->getService('Htmlpage')->roleToFrontendData($key, $field)
                : null;

            /** @see HM_View_Helper_VueRubricatorGridButton */
            $rubricatorUrl = $this->view->url([
                'module' => 'htmlpage',
                'controller' => 'ajax',
                'action' => 'get-tree-branch'
            ]);

            $gridUrl = $this->view->url([
                'module' => 'htmlpage',
                'controller' => 'list',
                'action' => 'index',
                'gridmod' => 'ajax',
                'key' => null,
            ]);

            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Все роли'), // buttonLabel
                $rubricatorValue,
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                ],
                false // autoopen
            );
        }

        if ($grid) $this->view->grid = $grid;

        $this->view->field = $field;
        $this->view->key = $key;

        if (!$this->isAjaxRequest()) $this->view->tree = $tree;
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
    }

    public function getPagesGrid()
    {
        if ($this->field == 'group_id') {

            $select = $this->getService('Htmlpage')->getSelect();
            $select->from('htmlpage', ['page_id', 'name', 'ordr', 'visible', 'is_single_page' => 'page_id'])
                ->joinLeft('htmlpage_groups', 'htmlpage_groups.group_id = htmlpage.group_id', 'role');

            if ($this->key) {
                $select->where('htmlpage.group_id = ?', $this->key);
            } else {
                $select->where('htmlpage.group_id = 0 OR htmlpage.group_id IS NULL');
            }
        }

        $grid = $this->getGrid(
            $select,
            [
                'page_id' => ['hidden' => true],
                'name' => ['title' => _('Название')],
                'is_single_page' => ['title' => _('Тип')], // для единообразия гридов
                'ordr' => ['title' => _('Порядок следования')],
                'visible' => ['title' => _('Опубликована?')],
                'role' => [
                    'title' => _('Для роли')
                ],
            ],
            [
                'name' => null
            ]
        );

        $grid->addAction([
            'module' => 'htmlpage',
            'controller' => 'list',
            'action' => 'edit',
            'key' => null,
            'type' => null
        ],
            ['page_id'],
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction([
            'module' => 'htmlpage',
            'controller' => 'list',
            'action' => 'delete',
            'key' => null,
            'type' => null
        ],
            ['page_id'],
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction([
            'module' => 'htmlpage',
            'controller' => 'list',
            'action' => 'delete-by'
        ],
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->updateColumn('role', [
                'callback' => [
                    'function' => [$this, 'updateRole'],
                    'params' => ['{{role}}']
                ]
            ]
        );

        $grid->updateColumn('is_single_page', [
                'callback' => [
                    'function' => [$this, 'updateIsSinglePage'],
                    'params' => ['{{is_single_page}}']
                ]
            ]
        );

        $grid->updateColumn('visible', [
                'callback' => [
                    'function' => [$this, 'updateVisible'],
                    'params' => ['{{visible}}']
                ]
            ]
        );

        return $grid;
    }

    /**
     * @return Bvb_Grid
     */
    public function getGroupsGrid()
    {

        $select = $this->getService('HtmlpageGroup')->getSelect();
        $select->from('htmlpage_groups', ['group_id', 'name', 'is_single_page', 'ordr', 'role']);
        $select->where('role = ?', (string)$this->key);

        $grid = $this->getGrid(
            $select,
            [
                'group_id' => ['hidden' => true],
                'name' => ['title' => _('Название')],
                'is_single_page' => ['title' => _('Тип')],
                'ordr' => ['title' => _('Порядок следования')],
                'role' => ['title' => _('Для роли')],
            ],
            [
                'name' => null
            ]
        );

        $grid->addAction([
            'module' => 'htmlpage',
            'controller' => 'group',
            'action' => 'edit',
            'key' => null,
            'type' => null
        ],
            ['group_id'],
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction([
            'module' => 'htmlpage',
            'controller' => 'group',
            'action' => 'delete',
            'key' => null,
            'type' => null
        ],
            ['group_id'],
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction([
            'module' => 'htmlpage',
            'controller' => 'group',
            'action' => 'delete-by'
        ],
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->updateColumn('role', [
                'callback' => [
                    'function' => [$this, 'updateRole'],
                    'params' => ['{{role}}']
                ]
            ]
        );

        $grid->updateColumn('is_single_page', [
                'callback' => [
                    'function' => [$this, 'updateIsSinglePage'],
                    'params' => ['{{is_single_page}}']
                ]
            ]
        );

        $this->view->addAction = 1;
        return $grid;
    }

    public function updateIsSinglePage($isSinglePage)
    {
        return $isSinglePage ? _('Страница') : _('Группа страниц');
    }

    public function updateVisible($isVisible)
    {
        return $isVisible ? _('Да') : _('Нет');
    }

    public function updateRole($role)
    {
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, true);
        return array_key_exists($role, $roles) ? $roles[$role] : _('Для всех ролей (в footer)');
    }

    public function update(Zend_Form $form) {

        $pageId = $form->getValue('page_id');
        $page = $this->getService('Htmlpage')->getOne(
            $this->getService('Htmlpage')->findDependence('HtmlPage_Group', $pageId)
        );

        $form->saveIcon();

        $updateArr = [
            'page_id' => $pageId,
            'name' => $form->getValue('name'),
            'ordr' => $form->getValue('ordr'),
            'url' => $form->getValue('url'),
            'text' => $form->getValue('text'),
            'description' => $form->getValue('description'),
            'visible' => $form->getValue('visible'),
            'icon_url' => $form->getValue('icon_url'),
            'in_slider' => $form->getValue('in_slider')
        ];

        $this->getService('Htmlpage')->update( $updateArr );

        if (count($page->group)) {
            $group = $page->group->current();
            if ($group->is_single_page) {
                $this->getService('HtmlpageGroup')->update(
                    [
                        'group_id' => $group->group_id,
                        'name' => $form->getValue('name'),
                        'ordr' => $form->getValue('ordr'),
                    ]
                );
            }
        }
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'list', 'htmlpage');
    }

    public function setDefaults(Zend_Form $form)
    {
        $pageId = $this->_request->getParam('page_id', 0);
        if (is_array($pageId) && count($pageId)) $pageId = $pageId[0];
        $pageId = (int) $pageId;

        $page = $this->getService('Htmlpage')->getOne($this->getService('Htmlpage')->find($pageId));
        if ($page) {
            $values = $page->getValues();
            $form->populate($values);
        }
    }

    public function delete($id)
    {
        $this->getService('Htmlpage')->delete($id);
    }


    /**
     * @param Zend_Form $form
     */
    public function create(Zend_Form $form)
    {
        $groupId = $form->getValue('group_id');
        $role = $form->getValue('role');

        if (!empty($role)) {
            $group = $this->getService('HtmlpageGroup')->insert(
                [
                    'role' => $role,
                    'is_single_page' => 1,
                    'name' => $form->getValue('name'),
                    'ordr' => $form->getValue('ordr'),
                ],
                0
            );
        }

        $insertArr = [
            'group_id' => $groupId ? : $group->group_id,
            'name' => $form->getValue('name'),
            'ordr' => $form->getValue('ordr'),
            'url' => $form->getValue('url'),
            'text' => $form->getValue('text'),
            'description' => $form->getValue('description'),
            'visible' => $form->getValue('visible'),
            'in_slider' => $form->getValue('in_slider')
        ];
        $page = $this->getService('Htmlpage')->insert( $insertArr );

        $form->saveIcon($page->page_id);
        $this->getService('Htmlpage')->update([
            'page_id' => $page->page_id,
            'icon_url' => $form->getValue('icon_url')
        ]);
    }

    public function newAction()
    {
        $this->gridTraitNewAction();

        $form = $this->_getForm();
        $form->setDefault($this->field, $this->key);

        $this->view->form = $form;
    }
}