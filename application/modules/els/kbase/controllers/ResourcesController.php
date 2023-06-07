<?php
/*
 * 5G
 * Новая страница со списком инфоресурсов в БЗ
 */

class Kbase_ResourcesController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'resources', 'kbase');
    }

    public function indexAction()
    {
        $order = $this->_request->getParam("ordergrid");
        if ($order == "") {
            $this->_request->setParam("ordergrid", 'updated_DESC');
        }

        $select = $this->getService('Resource')->getSelect();
        $select->from(
            array('t' => 'resources'),
            array(
                'resource_id',
                'created_by',
                'title',
                'filetype',
                'filename',
                'activity_type',
                'volume',
                'public' => 'status',
                'updated',
                'statustemp' => 't.status',
                'type' => 't.type',
                'typetemp' => 't.type',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'tags' => 'resource_id'
            )
        )
            ->joinLeft(array('p' => 'People'), 'p.MID = t.created_by', array())
            ->where('subject_id IS NULL OR subject_id = ?', 0);

        $select->where('t.db_id IS NULL OR t.db_id = ?', '');
        $select->where('t.parent_id = 0 OR t.parent_id IS NULL');

        $grid = $this->getGrid(
            $select,
            [
                'resource_id' => array('title' => '#'),
                'created_by' => array('hidden' => true),
                'statustemp' => array('hidden' => true),
                'filetype' => array('hidden' => true),
                'filename' => array('hidden' => true),
                'activity_type' => array('hidden' => true),
                'typetemp' => array('hidden' => true),
                'title' => array(
                    'title' => _('Название'),
                    'callback' => array(
                        'function' => array($this, 'updateResourceName'),
                        'params' => array('{{resource_id}}', '{{title}}', '{{type}}', '{{filetype}}', '{{filename}}', '{{activity_type}}')
                    ),
                ),
                'type' => array(
                    'title' => _('Тип'),
                    'callback' => array(
                        'function' => array($this, 'updateType'),
                        'params' => array('{{type}}')
                    ),
                ),
                'updated' => array('title' => _('Дата последнего изменения')),
                'fio' => array('title' => _('Создан пользователем'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => '')) . '{{created_by}}', _('Карточка пользователя')) . '<a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => '')) . '{{created_by}}' . '">' . '{{fio}}</a>'),
                'public' => array('title' => _('Статус')),
                'tags' => array(
                    'title' => _('Метки'),
                    'callback' => array(
                        'function' => array($this, 'displayTags'),
                        'params' => array('{{tags}}', HM_Tag_Ref_RefModel::TYPE_RESOURCE)
                    ),
                    'color' => HM_DataGrid_Column::colorize('tags')
                )
            ],
            [
                'title' => null,
                'updated' => array('render' => 'Date'), // не работает,
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
                'type' => array('values' => HM_Resource_ResourceModel::getTypes()),
                'public' => array('values' => HM_Resource_ResourceModel::getStatuses()),
            ],
            'grid'
        );

        $grid->updateColumn('public',
            array('callback' =>
                array('function' =>
                    array($this, 'updatePublic'),
                    'params' => array('{{public}}')
                )
            )
        );

        $grid->updateColumn('updated', array(
                'callback' => array(
                    'function' => array(
                        new HM_Resource_ResourceModel(array()),
                        'dateTime'),
                    'params' => array(
                        '{{updated}}')))
        );

        if ($this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN])) {

            $grid->addAction(
                [
                    'module' => 'kbase',
                    'controller' => 'resource',
                    'action' => 'edit-card',
                    // resource_id чтобы сохранить /index/ в пути
                    'redirectUrl' => urlencode($this->view->url(['module' => 'kbase', 'controller' => 'resources', 'action' => 'index', 'resource_id' => 0], null, true))
                ],
                ['resource_id'],
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                ['module' => 'kbase', 'controller' => 'resource', 'action' => 'delete'],
                ['resource_id'],
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addAction(
                ['module' => 'kbase', 'controller' => 'resource', 'action' => 'edit'],
                ['resource_id'],
                $this->view->svgIcon('editContent', _('Редактировать содержимое'))
            );

//        $grid->addAction(
//            array('module' => 'kbase', 'controller' => 'resource', 'action' => 'download'),
//            array('resource_id'),
//            _('Скачать')
//        );

            $grid->addMassAction(
                ['module' => 'kbase', 'controller' => 'resource', 'action' => 'delete-by'],
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Ресурс успешно создан'),
            self::ACTION_UPDATE => _('Ресурс успешно обновлён'),
            self::ACTION_DELETE => _('Ресурс успешно удалён'),
            self::ACTION_DELETE_BY => _('Ресурсы успешно удалены')
        );
    }

    public function updateType($type)
    {
        $types = HM_Resource_ResourceModel::getTypes();
        return $types[$type];
    }

    public function updatePublic($status)
    {
        $statuses = HM_Resource_ResourceModel::getStatuses();
        return $statuses[$status];

    }
}
