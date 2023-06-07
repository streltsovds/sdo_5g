<?php
class HM_User_Participant_Import_Manager
{
    protected $_existingPeople    = array();
    protected $_existingPeopleIds = array();
    protected $_existingGroups    = array();
    protected $_existingUserTags  = array();
    protected $_existingUserRole  = array();

    protected $_insertsPeople     = array();
    protected $_updatesPeople     = array();
    protected $_deletesPeople     = array();
    protected $_userGroups        = array();
    protected $_userTags          = array();
    protected $_userProjectRole   = array();

    protected $_notProcessed = array();

    const CACHE_NAME = 'HM_User_Participant_Import_Manager';

    private $_loginCount = 0;
    private $_restoredFromCache = false;

    const DEFAULT_PASSWORD = 'pass';

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $persons = $this->getService('User')->fetchAll();

        if (count($persons)) {
            foreach($persons as $person) {
                $this->_existingPeople[$person->MID] = $person;
                if (strlen($person->BirthDate)) {
                    $person->BirthDate = trim($person->BirthDate);
                    $this->_existingPeopleIds[$this->getFio($person) . '_' . date('d.m.Y', strtotime($person->BirthDate))] = $person->MID;
                }
            }
        }
        $this->_existingGroups   = $this->getService('StudyGroup')->fetchAll()->getList('name', 'group_id');
        $this->_existingUserTags = $this->getService('Tag')->getTagsCache(array_values($this->_existingPeopleIds), $this->getService('TagRef')->getUserType());
        $this->_existingUserRole = $this->getService('Participant')->fetchAll()->getList('MID', 'project_role');
    }

    protected function _needPersonUpdate($person)
    {
        $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$this->getFio($person) . '_' . date('d.m.Y', strtotime($person->BirthDate))]];

        $values = $person->getValues(null, array('mid_external', 'role', 'isAD', 'group', 'tags'));

        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingPerson->{$key} != $value) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function _needGroupUpdate($groupName, $userId)
    {
        if (isset($this->_existingGroups[$groupName])) {
            $groupId = $this->_existingGroups[$groupName];
        }
        else {
            $group = $this->getService('StudyGroup')->create($groupName, HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM);
            $groupId = $group->group_id;
            $this->_existingGroups[$group->name] = $groupId;
        }
        if (!$this->getService('StudyGroupUsers')->isGroupUser($groupId, $userId)) {
            return true;
        }
        return false;
    }

    protected function _needTagsUpdate($tags, $userId)
    {
        $tags = explode(',', $tags);
        if ((empty($this->_existingUserTags[$userId]) && count($tags)) ||
            count(array_diff($tags, $this->_existingUserTags[$userId]))
        ) {
            return true;
        }
        return false;
    }

    protected function _needProjectRoleUpdate($role, $userId)
    {
        $roles = HM_Role_ParticipantModel::getProjectRoles();
        foreach ($roles as $roleId => $roleName) {
            $roleName = strtolower(preg_replace('/\s+/', '', $roleName));
            $role     = strtolower(preg_replace('/\s+/', '', $role));
            if ($roleName == $role) $role = $roleId;
        }
        return ((empty($this->_existingUserRole[$userId])) || ($role == $this->_existingUserRole[$userId]));
    }

    public function getInsertsCount()
    {
        return count($this->_insertsPeople);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesPeople);
    }

    public function getDeletesCount()
    {
        return count($this->_deletesPeople);
    }

    public function getGroupsCount()
    {
        return count($this->_userGroups);
    }

    public function getUserTagsCount()
    {
        return count($this->_userTags);
    }

    public function getNotProcessedCount()
    {
        return count($this->_notProcessed);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount()
            + $this->getGroupsCount() + $this->getUserTagsCount();
    }

    public function getInserts()
    {
        return $this->_insertsPeople;
    }

    public function getUpdates()
    {
        return $this->_updatesPeople;
    }

    public function getDeletes()
    {
        return $this->_deletesPeople;
    }

    public function getGroups()
    {
        return $this->_userGroups;
    }

    public function getUserTags()
    {
        return $this->_userTags;
    }

    public function getNotProcessed()
    {
        return $this->_notProcessed;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_insertsPeople,
                 'updates' => $this->_updatesPeople,
                 'deletes' => $this->_deletesPeople,
                 'userGroups' => $this->_userGroups,
                 'userTags' => $this->_userTags,
                 'userProjectRole' => $this->_userProjectRole,
                 'notProcessed' => $this->_notProcessed
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_insertsPeople = $actions['inserts'];
            $this->_updatesPeople = $actions['updates'];
            $this->_deletesPeople = $actions['deletes'];
            $this->_userGroups = $actions['userGroups'];
            $this->_userTags = $actions['userTags'];
            $this->_userProjectRole = $actions['userProjectRole'];
            $this->_notProcessed = $actions['notProcessed'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function getFio($item)
    {
        return $item->LastName . ' ' . $item->FirstName . ' ' . $item->Patronymic;
    }

    public function init($items)
    {
        $this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                if (empty($item->EMail) && empty($item->BirthDate)) {
                    $this->_notProcessed[] = $item;
                    continue;
                }
                if (!isset($this->_existingPeopleIds[$this->getFio($item) . '_' . $item->BirthDate])) {
                    $this->_insertsPeople[$this->getFio($item) . '_' . $item->BirthDate] = $item;
                } else {
                    if (isset($this->_updatesPeople[$this->getFio($item) . '_' . $item->BirthDate])) continue;
                    if (!isset($this->_existingPeople[$this->_existingPeopleIds[$this->getFio($item) . '_' . $item->BirthDate]])) continue;

                    $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$this->getFio($item) . '_' . $item->BirthDate]];

                    if (!empty($item->isTeacher)) {
                        $this->getService('User')->assignRole($existingPerson->MID, HM_Role_Abstract_RoleModel::ROLE_TEACHER);
                    }
                    unset($item->isTeacher);
                    unset($item->Password);
                    if ($this->_needPersonUpdate($item)) {
                        $item->MID = $existingPerson->MID;
                        $this->_updatesPeople[$this->getFio($item) . '_' . $item->BirthDate] = array('source' => $existingPerson, 'destination' => $item);
                    }
                    if (!empty($item->group) && $this->_needGroupUpdate($item->group, $existingPerson->MID)) {
                        $this->_userGroups[$existingPerson->MID] = $this->_existingGroups[$item->group];
                    }
                    if (!empty($item->tags) && $this->_needTagsUpdate($item->tags, $existingPerson->MID)) {
                        $this->_userTags[$existingPerson->MID] = explode(',', $item->tags);
                    }
                    if (!empty($item->project_role) && $this->_needProjectRoleUpdate($item->project_role, $existingPerson->MID)) {
                        $this->_userProjectRole[$existingPerson->MID] = $item->project_role;
                    }

                    unset($this->_existingPeople[$existingPerson->MID]);
                }
            }
        }

        $this->saveToCache();
    }

    protected function _generateLogin()
    {
        if ($this->_loginCount == 0) {
    		$user = $this->getService('User')->getOne($this->getService('User')->fetchAll($this->getService('User')->quoteInto("Login LIKE '?%'", new Zend_Db_Expr(HM_User_UserService::NEW_LOGIN_PREFIX)), 'MID DESC', 1));
    		if ($user) {
    			$this->_loginCount = (int) substr($user->Login, strlen(HM_User_UserService::NEW_LOGIN_PREFIX));
    		}
        }
        while(true) {
            $login = HM_User_UserService::NEW_LOGIN_PREFIX.str_pad((string) $this->_loginCount, 4, "0", STR_PAD_LEFT);
            $collection = $this->getService('User')->fetchAll($this->getService('User')->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $this->_loginCount++;
                continue;
            } else {
                $this->_loginCount++;
                return $login;
            }
        }
    }

    public function import()
    {
        $teachers = array();

        if (count($this->_insertsPeople)) {

            foreach($this->_insertsPeople as $id => $insert) {

                if (!isset($insert->Login)) {
                    $insert->Login = $this->_generateLogin();
                }

                if (empty($insert->Password)) {
                    $insert->Password = self::DEFAULT_PASSWORD;
                }
                $insert->Password = new Zend_Db_Expr("PASSWORD('".$insert->Password."')");

                $insert->BirthDate = date('Y-m-d', strtotime($insert->BirthDate));

                // Если не заполнены поля e-mail, lastname, firstname, то просить заполнить при первом логине
                if (!strlen($insert->EMail) || (!strlen($insert->LastName) && !strlen($insert->FirstName))) {
                    $insert->need_edit = HM_User_UserModel::NEED_EDIT_AFTER_FIRST_LOGIN;
                }

                $user = $this->getService('User')->insert($insert->getValues(null, array('isTeacher', 'group', 'tags', 'project_role')));
                if ($user) {
                    if (!empty($insert->isTeacher)) {
                        $teachers[] = $user->MID;
                    }
                    if (!empty($insert->group) && $this->_needGroupUpdate($insert->group, $user->MID)) {
                        $this->_userGroups[$user->MID] = $this->_existingGroups[$insert->group];
                    }
                    if (!empty($insert->tags) && $this->_needTagsUpdate($insert->tags, $user->MID)) {
                        $this->_userTags[$user->MID] = explode(',', $insert->tags);
                    }
                    $this->_existingPeopleIds[$this->getFio($user) . '_' . date('d.m.Y', strtotime($user->BirthDate))] = $user->MID;
                }

                if (!empty($insert->project_role) && $this->_needProjectRoleUpdate($insert->project_role, $user->MID)) {
                    $this->_userProjectRole[$user->MID] = $insert->project_role;
                }
            }
        }

        if (count($this->_updatesPeople)) {
            foreach($this->_updatesPeople as $id => $update) {
                $existingPerson  = $update['source'];
                $destinationUser = $update['destination'];
                $this->getService('User')->update($destinationUser->getValues(null, array('Password', 'isTeacher', 'group', 'tags', 'project_role')));

                if (!empty($destinationUser->project_role) && $this->_needProjectRoleUpdate($destinationUser->project_role, $existingPerson->MID)) {
                    $this->_userProjectRole[$existingPerson->MID] = $destinationUser->project_role;
                }
            }
        }

        if (count($this->_userGroups)) {
            $this->_assignPeopleToGroup();
        }

        if (count($this->_userTags)) {
            $this->_assignTags();
        }

        if (count($this->_userProjectRole)) {
            $this->_assignProjectRole();
        }

        if (count($teachers)) {
            foreach($teachers as $userId ) {
                $this->getService('User')->assignRole($userId, HM_Role_Abstract_RoleModel::ROLE_TEACHER);
            }
        }

        if (count($this->_deletesPeople)) {
            foreach($this->_deletesPeople as $id => $delete) {
                if (strlen($delete->EMail)) {
                    $this->getService('User')->update(array('MID' => $delete->MID, 'blocked' => 1));
                }
            }
        }
    }

    protected function _assignPeopleToGroup()
    {
        foreach ($this->_userGroups as $userId => $groupId) {
            $this->getService('StudyGroupUsers')->addUser($groupId, $userId);
        }
    }

    protected function _assignTags()
    {
        foreach ($this->_userTags as $userId => $tags) {
            $tagsCache = (isset($this->_existingUserTags[$userId])) ? $this->_existingUserTags[$userId] : array();
            $this->getService('Tag')->updateTags(array_merge($tagsCache, $tags), $userId,
                $this->getService('TagRef')->getUserType());
        }
    }

    protected function _assignProjectRole()
    {
        foreach ($this->_userProjectRole as $userId => $project_role) {
            $cid = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('project_id', 0);

            $roles = HM_Role_ParticipantModel::getProjectRoles();
            foreach ($roles as $roleId => $roleName) {
                $roleName     = strtolower(preg_replace('/\s+/', '', $roleName));
                $project_role = strtolower(preg_replace('/\s+/', '', $project_role));
                if ($roleName == $project_role) $project_role = $roleId;
            }

            $participant = $this->getService('Participant')->getOne(
                $this->getService('Participant')->fetchAll(
                    $this->getService('Participant')->quoteInto(
                        array('MID = ? AND ', 'CID = ?'),
                        array($userId, $cid)
                    )
                )
            );
            if ($participant) {
                $this->getService('Participant')->updateWhere(
                    array(
                        'project_role' => $project_role,
                    ),
                    array('MID = ?' => $userId)
                );
            } else {
                $this->getService('Participant')->insert(
                    array(
                        'MID' => $userId,
                        'CID' => $cid,
                        'project_role' => $project_role,
                        'time_registered' => date('Y-m-d H:i:s')
                    )
                );
            }
        }
    }
}