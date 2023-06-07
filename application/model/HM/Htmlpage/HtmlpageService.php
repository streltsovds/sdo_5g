<?php
class HM_Htmlpage_HtmlpageService extends HM_Service_Abstract
{
    const MODE_ALL_PAGES = 0;
    const MODE_COMMON_PAGES = 1;
    const MODE_ROLE_PAGES = 2;

    public function delete($id){

        $page = $this->getOne($this->find($id));
        parent::delete($id);
        
        // файл удаляется здесь, а генерится в 1main.php при первом последующем запуске
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file))
            unlink($file);

        $groupId = $page->group_id;
        if($groupId)
            if(!$this->countAll($this->quoteInto('group_id = ?', $groupId))){
                $this->getService('HtmlpageGroup')->delete($groupId);
            }
    }

    /**
     * Выводит коллекцию страниц для указанной роли (или для роли текущего пользователя, если роль не указана)
     * @param null $role
     * @return mixed
     */
    public function getAllPages($role = null)
    {
        if (null === $role ) {
            $role = $this->getService('User')->getCurrentUserRole();
        }

        if ($this->getService('Acl')->inheritsRole($role, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) $role = HM_Role_Abstract_RoleModel::ROLE_ENDUSER;

        $select = $this->getSelect();
        $select->from(
            array('a' => 'htmlpage')
        )->joinLeft(
            array('b' => 'htmlpage_groups'),
            "a.group_id = b.group_id",
            array()
        )->where(
            "(b.role = ?", $role
        )->orWhere(
            "a.group_id = 0 OR a.group_id IS NULL)"
        )->where(
            "a.visible = 1"
        )->order(array("b.ordr", "a.ordr", "a.page_id"));

        return $this->getMapper()->fetchAllFromArray($select->query()->fetchAll());
    }


	public function insert($data, $unsetNull = true)
    {
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
    
        return parent::insert($data, $unsetNull);
    }
    
    public function update($data, $unsetNull = true)
    {
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
    
        return parent::update($data, $unsetNull);
    }


    /**
     * DEPRECATED!!!
     *
     * @param null $role
     * @return mixed
     */
    public function getPages($role = null, $mode = self::MODE_ALL_PAGES)
    {
        if (null === $role ) {
            $role = $this->getService('User')->getCurrentUserRole();
        }

        if ($this->getService('Acl')->inheritsRole($role, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) $role = HM_Role_Abstract_RoleModel::ROLE_ENDUSER;

        $select = $this->getSelect();
        $select->from(
            array('a' => 'htmlpage')
        )->joinLeft(
            array('b' => 'htmlpage_groups'),
            "a.group_id = b.group_id",
            array(
                'group_name' => 'b.name',
                'group_role' => 'b.role',
            )
        );


        if (self::MODE_ALL_PAGES == $mode or
            self::MODE_COMMON_PAGES == $mode
        ) {
            $select->orWhere("a.group_id = 0");
        }

        if (self::MODE_ALL_PAGES == $mode or
            self::MODE_ROLE_PAGES == $mode
        ) {
            $select->orWhere("b.role = ?", $role);
        }

        $select->order(array("b.ordr", "a.ordr", "a.page_id"));

        return $this->getMapper()->fetchAllFromArray($select->query()->fetchAll());
    }

    public function getIndexNavigation()
    {
        $role = $this->getService('User')->getCurrentUserRole();
        $groups = $this->getService('HtmlpageGroup')->fetchAllDependence('Htmlpage', ['role = ?' => $role], 'ordr');

        $navigation[$role] = [
            'type' => 'HM_Navigation_Page_Mvc',
            'uri' => '',
            'pages' => []
        ];

        if (count($groups)) {
            foreach ($groups as $group) {

                if (count($group->pages)) {

                    if ($group->is_single_page) {

                        $page = $group->pages->current();

                        if (!$page->visible) continue;

                        $menuGroup = [
                            'type' => 'HM_Navigation_Page_Mvc',
                            'label' => $page->name,
                            'module' => 'htmlpage',
                            'controller' => 'index',
                            'action' => 'view',
                            'params' => [
                                'htmlpage_id' => $page->page_id,
                            ],
                        ];

                    } else {

                        $menuGroup = [
                            'type' => 'HM_Navigation_Page_Mvc',
                            'label' => $group->name,
                            'uri' => '',
                            'pages' => []
                        ];

                        $pages = $group->pages->asArrayOfObjects();
                        usort($pages, function($page1, $page2) {return $page1->ordr <=> $page2->ordr;});

                        foreach ($pages as $page) {
                            if (!$page->visible) continue;
                            $menuGroup['pages'][] = [
                                'type' => 'HM_Navigation_Page_Mvc',
                                'label' => $page->name,
                                'module' => 'htmlpage',
                                'controller' => 'index',
                                'action' => 'view',
                                'params' => [
                                    'htmlpage_id' => $page->page_id,
                                ],
                            ];
                        }
                    }

                    $navigation[$role]['pages'][] = $menuGroup;
                }
            }
        }

        return count($navigation[$role]['pages']) ? $navigation : [];
    }

    public function getFooterNavigation()
    {
        $navigation = array();

        $select = $this->getSelect();
        $select->from(
            array('a' => 'htmlpage')
        )->joinLeft(
            array('b' => 'htmlpage_groups'),
            "a.group_id = b.group_id",
            array(
                'group_name' => 'b.name',
                'group_role' => 'b.role',
            )
        )->where("a.group_id = 0 OR a.group_id IS NULL")
        ->order(array("b.ordr", "a.ordr", "a.page_id"));

        $pages = $this->getMapper()->fetchAllFromArray($select->query()->fetchAll());

        if (count($pages)) {

            foreach ($pages as $page) {

                $groupRole = $page->group_role ? : 'all';
                if (!isset($navigation[$groupRole])) $navigation[$groupRole] = array(
                    'uri' => '',
                    'label' => /*$page->group_name ? : */ '',
                    'pages' => array(),
                );

                $navigation[$groupRole]['pages'][] = array(
                    'type' => 'HM_Navigation_Page_Mvc',
                    'label' => $page->name,
                    'module' => 'htmlpage',
                    'controller' => 'index',
                    'action' => 'view',
                    'htmlpage_id' => $page->page_id,
                );
            }
        }

        return $navigation;
    }


    public function roleToFrontendData($role, $field)
    {
        return array_filter([
            'active' => true,
            'expand' => false,
            'isFolder' => true,
            'isLazy' => false,
            'key' => (string) $role,
            'keyType' => $field,
            'title' => HM_Role_Abstract_RoleModel::getRoleTitle($role),
        ]);
    }
}