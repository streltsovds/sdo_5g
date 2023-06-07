<?php

class Report_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    private $_reportConfig = null;

    public function init()
    {
        $this->_form = new HM_Form_Domain();
        $this->_setForm($this->_form);

        return parent::init();
    }

    public function indexAction()
    {
        $this->_reportConfig = new HM_Report_Config();

        $order = $this->_request->getParam("ordergrid");
        if ($order == "") {
            $this->_request->setParam("ordergrid", $order = 'name_ASC');
        }

        $select = $this->getService('Report')->getSelect();
        $select->from(['r' => 'reports'], [])
            ->joinLeft(['rr' => 'reports_roles'], 'r.report_id = rr.report_id', [
                'report_id' => 'r.report_id',
                'name' => 'r.name',
                'domain' => 'r.domain',
                'input' => 'r.name',
                //'status' => 'r.status', 
                'role' => new Zend_Db_Expr('GROUP_CONCAT(rr.role)'),
            ])
            ->group(['r.report_id', 'r.name', 'r.domain', 'r.status']);
        $grid = $this->getGrid($select,
            [
                'report_id' => ['hidden' => true],
                'name' => [
                    'title' => _('Название'),
                    'decorator' => ' <a href="' . $this->view->url(['module' => 'report', 'controller' => 'generator', 'action' => 'construct', 'report_id' => '{{report_id}}'], null, true, false) . '">{{name}}</a>'
                ],
                'domain' => [
                    'title' => _('Область данных'),
                    'callback' => [
                        'function' => [$this, 'updateDomainField'],
                        'params' => ['{{domain}}']
                    ]
                ],
                'input' => [
                    'title' => _('Входные параметры'),
                    'callback' => [
                        'function' => [$this, 'updateInputField'],
                        'params' => ['{{input}}']
                    ]
                ],
                'role' => [
                    'title' => _('Роли'),
                    'callback' => [
                        'function' => [$this, 'updateRole'],
                        'params' => ['{{role}}']
                    ],
                    'color' => HM_DataGrid_Column::colorize('roles')
                ],
            ],
            [
                'name' => null,
                'domain' => ['values' => $this->_reportConfig->getDomains()]
            ]
        );

        $grid->addAction([
            'module' => 'report',
            'controller' => 'list',
            'action' => 'edit'
        ],
            ['report_id'],
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction([
            'module' => 'report',
            'controller' => 'list',
            'action' => 'delete'
        ],
            ['report_id'],
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            ['module' => 'report', 'controller' => 'list', 'action' => 'delete-by'],
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->addMassAction(['action' => 'assign'], _('Открыть для роли'));
        $grid->addMassAction(['action' => 'unassign'], _('Закрыть для роли'));

        $roles = HM_Report_ReportModel::getReportRoles();

        $grid->addSubMassActionSelect([$this->view->url(['action' => 'assign'])],
            'role',
            $roles);

        $grid->addSubMassActionSelect([$this->view->url(['action' => 'unassign'])],
            'role',
            $roles);

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function editAction()
    {
        $reportId = $this->_getParam('report_id', 0);

        if ($this->_request->isPost()) {

            if ($this->_form->isValid($this->_request->getPost())) {

                $name = $this->_getParam('name', '');
                $domain = $this->_getParam('domain', '');
                $status = $this->_getParam('status', 0);

                if ($reportId) {
                    $this->getService('Report')->update(['report_id' => $reportId, 'name' => $name, 'domain' => $domain, 'status' => $status]);
                    $this->getService('ReportRole')->removalAllRoles($reportId);
                    $this->getService('ReportRole')->assignRole($reportId, $this->_getParam('roles', []));
                    $this->_redirector->gotoSimple('index', 'list', 'report');
                } else {
                    $report = $this->getService('Report')->insert(['name' => $name, 'domain' => $domain, 'status' => $status]);
                    $this->getService('ReportRole')->assignRole($report->report_id, $this->_getParam('roles', []));
                    $this->_redirector->gotoSimple('construct', 'generator', 'report', ['report_id' => $report->report_id]);
                }
            }

        } elseif ($reportId) {
            if ($report = $this->getService('Report')->getOne($this->getService('Report')->find($reportId))) {
                $roles = $this->getService('ReportRole')->fetchAll($this->getService('ReportRole')->quoteInto('report_id = ?', $reportId))->getList('role');

                $rolesArray = [];
                foreach ($roles as $role) {
                    $rolesArray[] = $role;
                }
                $this->_form->setDefaults([
                    'name' => $report->name,
                    'domain' => $report->domain,
                    'status' => $report->status,
                    'roles' => $rolesArray
                ]);
            }
        }

        $this->view->form = $this->_form;

    }

    public function updateDomainField($domain)
    {
        $domains = $this->_reportConfig->getDomains();
        return $domains[$domain];
    }

    public function updateStatusField($status)
    {
        return $status ? _('Опубликован') : '<span class="nowrap">' . _('Не опубликован') . '</span>';
    }

    public function updateInputField($input)
    {
        return '';
    }

    public function deleteAction()
    {
        $reportId = (int)$this->_getParam('report_id', 0);
        $this->delete($reportId);
        $this->_flashMessenger->addMessage(_('Отчёт успешно удалён'));
        $this->_redirector->gotoSimple('index', 'list', 'report', []);
    }

    public function delete($reportId)
    {
        $this->getService('Report')->delete($reportId);
        return true;
    }

    public function treeAction()
    {
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->tree = $this->getService('Report')->getTreeContent(new HM_Report_Config());
    }


    public function getTreeBranchAction()
    {
        if ($this->_request->getParam("key", 0)) die('[]');

        $tree = $this->getService('Report')->getTreeContent(new HM_Report_Config());
        echo HM_Json::encodeErrorSkip($tree);
        exit;
    }


    public function updateRole($field, $separator = ',')
    {
        if ($field == '') return _('Нет');
        $roles = HM_Role_Abstract_RoleModel::getBasicRoles(true, false);

        $reportRoles = explode($separator, $field);

        $result = (is_array($reportRoles) && (count($reportRoles) > 1))
            ? ['<p class="total">' . Zend_Registry::get('serviceContainer')->getService('User')->pluralFormRolesCount(count($reportRoles)) . '</p>']
            : [];

        foreach ($reportRoles as $value) {
            $result[] = "<p>{$roles[$value]}</p>";
        }

        if ($result)
            return implode('', $result);
        else
            return _('Нет');
    }

    public function unassignAction()
    {
        $arRoles = HM_Role_Abstract_RoleModel::getBasicRoles();
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $roles = $this->_request->getParam('role', []);

        /** @var HM_Report_Role_RoleService $service */
        $service = $this->getService('ReportRole');

        foreach ($ids as $report_id) {
            foreach ($roles as $role) {
                if (array_key_exists($role, $arRoles)) {
                    $service->removalRole($report_id, $role);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Отчетные формы закрыты для данных ролей'));
        $this->_redirector->gotoSimple('index', 'list', 'report');
    }


    public function assignAction()
    {

        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $role = $this->_request->getParam('role');

        /** @var HM_Report_Role_RoleService $service */
        $service = $this->getService('ReportRole');

        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $report_id) {
            $res = $service->assignRole($report_id, $role);
            if ($res === false) {
                $error = true;
            }
        }
        if ($error === true) {
            $this->_flashMessenger->addMessage(_('Некоторым отчетные формы уже были открыты для данных ролей'));
        } else {
            $this->_flashMessenger->addMessage(_('Отчетные формы успешно открыты для данных ролей'));
        }

        $this->_redirector->gotoSimple('index', 'list', 'report');
    }
}