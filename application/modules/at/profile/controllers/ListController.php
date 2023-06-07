<?php
class Profile_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid {
        editAction as editActionTraitGrid;
    }

    protected $_profilesCache = array();
    protected $_profile = array();

    public function init()
    {
        $form = new HM_Form_Profiles();
        $this->_setForm($form);
        
        if ($profileId = $this->_getParam('profile_id')) {
            $this->_profile = $this->getOne($this->getService('AtProfile')->find($profileId));        
        }
        
        parent::init();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'profile_ASC');
        }
        
        $select = $this->getService('AtProfile')->getSelect();

        $select->from(
            array(
                'p' => 'at_profiles'
            ),
            array(
                'p.profile_id',
                'p.profile_id_external',
                'profile' => 'p.name',
                'category' => 'ac.name',
                'profile_children' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT p_child.profile_id)'),
                'positions' => new Zend_Db_Expr('COUNT(DISTINCT s.soid)'),
            )
        );

        $select
            ->joinLeft(array('ac' => 'at_categories'), 'ac.category_id = p.category_id', array())
            ->joinLeft(array('p_child' => 'at_profiles'), 'p.profile_id = p_child.base_id', array())
            ->joinLeft(array('s' => 'structure_of_organ'), 'p.profile_id = s.profile_id', array())
            ->where('p.user_id IS NULL')
            ->where('p.base_id IS NULL OR p.base_id = 0')
            ->group(array(
                'p.profile_id',
                'p.profile_id_external',
                'ac.name',
                'p.name',
                'p.department_path',
            ));
        ;

        $grid = $this->getGrid($select, array(
            'profile_id' => array('hidden' => true),
            'profile_id_external' => array('hidden' => true),
            'title' => array('hidden' => true),
            'profile' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{profile_id}}', '{{profile}}')
                ),
            ),
            'category' => array('title' => _('Категория должности'),),
            'positions' => array(
                'title' => _('Должности в оргструктуре'),
                'callback' => array(
                    'function'=> array($this, 'updatePositions'),
                    'params'=> array('{{positions}}')
                ),
            ),
        ),
            array(
                'category' => null,
                'profile' => null,
                'department' => null,
                'profile_children' => null,
                'positions' => null,
            )
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{profile_id_external}}')
            )
        );

        $grid->addAction(array(
            'module' => 'profile',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('profile_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'profile',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('profile_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );


        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
          /*  $grid->addMassAction(
                array(
                    'module' => 'profile',
                    'controller' => 'list',
                    'action' => 'link-by'
                ),
                _('Назначить базовый профиль'),
                _('Вы действительно желаете назначить базовый профиль для отмеченных профилей? При этом все должности будут автоматически привязаны к базовому профилю и будут назначены соответствующие программы подбора, обучения и т.д. Продолжить?')
            );

            $grid->addSubMassActionSelect(
                array($this->view->url(
                    array(
                        'module' => 'profile',
                        'controller' => 'list',
                        'action' => 'link-by'
                    )
                )),
                'base_id',
                HM_At_Profile_ProfileModel::getNotLinkedYetProfiles(),
                false
            );

            $grid->addMassAction(
                array(
                    'module' => 'profile',
                    'controller' => 'list',
                    'action' => 'unlink-by'
                ),
                _('Отменить назначение базого профиля')
            );
          */

            $grid->addMassAction(
                array(
                    'module' => 'profile',
                    'controller' => 'list',
                    'action' => 'delete-by',
                ),
                _('Удалить профиль'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function linkByAction()
    {
        $baseProfileId = $this->_getParam('base_id');
        $profileIds = explode(',', $this->_getParam('postMassIds_grid', ''));

        if (count($profileIds)) {
            $profiles = $this->getService('AtProfile')->fetchAll(array('profile_id IN (?)' => $profileIds));

            if (count($profiles)) {
                foreach ($profiles as $profile) {
                    if ($profile->profile_id == $baseProfileId) continue;
                    $this->getService('AtProfile')->setBaseProfile($profile, $baseProfileId);
                }
                $this->_flashMessenger->addMessage(_('Базовый профиль успешно назначен'));
            }
        }

        $this->_redirector->gotoUrl($this->view->url(array(
            'baseUrl' => 'at',
            'module' => 'profile',
            'controller' => 'list',
            'action' => 'index'
        )), array('prependBase' => false));
    }

    public function unlinkAction()
    {
        $profileId = $this->_getParam('profile_id');
        $profile = $this->getService('AtProfile')->findOne($profileId);

        if ($profile) {
            $this->getService('AtProfile')->unsetBaseProfile($profile);
            $this->_flashMessenger->addMessage(_('Базовый профиль успешно отменен'));
        }

        $this->_redirector->gotoUrl($this->view->url(array(
            'baseUrl' => 'at',
            'module' => 'profile',
            'controller' => 'report',
            'action' => 'index'
        )), array('prependBase' => false));
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['profile_id']);
        unset($values['icon']);
        $res = $this->getService('AtProfile')->insert($values);
        if ($form->getValue('icon') != null) {
            HM_At_Profile_ProfileService::updateIcon($res->profile_id, $form->getElement('icon'));
        } else {
            HM_At_Profile_ProfileService::updateIcon($res->profile_id, $form->getElement('server_icon'));
        }
    }

    public function update($form)
    {
        $values = $form->getValues();
        unset($values['icon']);

        $res = $this->getService('AtProfile')->update($values);
        if ($form->getValue('icon') != null) {
            HM_At_Profile_ProfileService::updateIcon($res->profile_id, $form->getElement('icon'));
        } else {
            HM_At_Profile_ProfileService::updateIcon($res->profile_id, $form->getElement('server_icon'));
        }
    }

    public function delete($id) {
        $this->getService('AtProfile')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $profileId = $this->_getParam('profile_id', 0);
        $profile = $this->getService('AtProfile')->find($profileId)->current();

        $imageUrl = $this->view->publicFileToUrlWithHash($profile->getUserIcon());

        if ($imageUrl) {
            $iconElement = $form->getElement('icon');
            $iconElement->setPreviewImg($imageUrl);
        }

        $data = $profile->getData();
        $form->populate(array('cancelUrl' => $this->view->url(array('controller' => 'report', 'action' => 'index'))));
        $form->populate($data);
    }

    public function updateName($profileId, $str)
    {
        return '<a href="' . $this->view->url(array('controller' => 'report', 'action' => 'index', 'profile_id' => $profileId)) . '">' . $this->view->escape($str) . '</a>';
    }

    public function updateChildren($profileIds)
    {
        $profileIds = explode(',', $profileIds);

        if (empty($this->_profilesCache)) {
            $this->_profilesCache = $this->getService('AtProfile')->fetchAll(array(
                'base_id IS NOT NULL' => null
            ), 'name')->getList('profile_id', 'name');

        }

        $result = (is_array($profileIds) && (($count = count($profileIds)) > 1))
            ? array('<p class="total">' . sprintf(_n('профиль plural', '%s профиль', $count), $count) . '</p>')
            : array();

        foreach($profileIds as $profileId){
            $url = $this->view->url(array('controller' => 'report', 'action' => 'index', 'profile_id' => $profileId));
            $name = isset($this->_profilesCache[$profileId]) ? $this->_profilesCache[$profileId] : '';
            $result[] = "<p><a href='{$url}'>{$name}</a></p>";
        }

        if (count($result))
            return implode(' ', $result);
        else
            return '';
    }

    public function profileProgrammEventsCache($allNames, $programmType)
    {
        $eventNames = array();
        $allNames = explode(',', $allNames);

        foreach ($allNames as $name) {
            $parts = explode('-', $name);
            if ($parts[0] == $programmType) {
                $eventNames[] = $parts[1] ? : _('<нет названия>');
            }
        }

        sort($eventNames);

        $result = (is_array($eventNames) && (($count = count($eventNames)) > 1)) ?
            array('<p class="total">' . sprintf(_n('мероприятия во множественном числе', '%s мероприятие', $count), $count) . '</p>') : array();
        foreach($eventNames as $eventName){
            $result[] = "<p>{$eventName}</p>";
        }

        if (count($result))
            return implode(' ', $result);
        else
            return '';
    }

    public function updatePositions($positions)
    {
        return $this->getService('Orgstructure')->pluralFormPositionsCount($positions);
    }

    public function updateActions($profileExternalId, $actions)
    {
        if (!empty($profileExternalId)) {
            $this->unsetAction($actions, array('module' => 'profile', 'controller' => 'list', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'profile', 'controller' => 'list', 'action' => 'delete'));
        }
        return $actions;
    }

    public function editAction()
    {
        $this->view->setBackUrl($_SERVER['HTTP_REFERER']);
        $this->view->setHeader($this->_profile->name);
        $this->editActionTraitGrid();
    }
}
