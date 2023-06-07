<?php
class HM_User_Import_Manager
{
    protected $_existingPeople = array();
    protected $_existingPeopleIds = array();
    protected $_existingGroups = array();
    protected $_existingUserTags = array();

    protected $_insertsPeople = array();
    protected $_updatesPeople = array();
    protected $_deletesPeople = array();
    protected $_userGroups = array();
    protected $_userTags = array();

    protected $_divisionId = null;

    protected $_notProcessed = array();

    const CACHE_NAME = 'HM_User_Import_Manager';

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
                if (strlen($person->mid_external)) {
                    $person->mid_external = trim($person->mid_external);
                    $this->_existingPeopleIds[$person->mid_external] = $person->MID;
                }
            }
        }
        $this->_existingGroups = $this->getService('StudyGroup')->fetchAll()->getList('name', 'group_id');;
        $this->_existingUserTags = $this->getService('Tag')->getTagsCache(array_values($this->_existingPeopleIds), $this->getService('TagRef')->getUserType());;
    }

    protected function _needPersonUpdate($person)
    {
        $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$person->mid_external]];

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

    public function getInsertsCount()
    {
        return count($this->_insertsPeople);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesPeople);
    }

    public function getUpdatesCountFull()
    {
        $updates = $this->_updatesPeople;
        $people = [];
        foreach ($updates as $person) {
            $people[] = $person['source']->MID;
        }
        $groups = array_keys($this->getGroups());
        $tags = array_keys($this->getUserTags());
        $unique = array_unique(array_merge($people, $groups, $tags));
        return count($unique);
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

    public function getInsertsPeopleCount()
    {
        return $this->getInsertsCount();
    }

    public function getUpdatesPeopleCount()
    {
        return $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_insertsPeople;
    }

    public function getUpdates()
    {
        return $this->_updatesPeople;
    }

    public function getUpdatesFull()
    {
        $result = [];
        foreach ($this->getGroups() as $mid => $group) {
            $result[$mid]['groups'][] = $group;
        }
        foreach ($this->getUserTags() as $mid => $tag) {
            $result[$mid]['tags'][] = $tag;
        }

        return [
            'people' => $this->getUpdates(),
            'additions' => $result
        ];
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
            $this->_notProcessed = $actions['notProcessed'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {
        $this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                if (empty($item->mid_external)) {
                    $this->_notProcessed[] = $item;
                    continue;
                }
                if (!isset($this->_existingPeopleIds[$item->mid_external])) {
                    $this->_insertsPeople[$item->mid_external] = $item;
                } else {
                    if (isset($this->_updatesPeople[$item->mid_external])) continue;
                    if (!isset($this->_existingPeople[$this->_existingPeopleIds[$item->mid_external]])) continue;

                    $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$item->mid_external]];

                    if (!empty($item->isTeacher)) {
                        $this->getService('User')->assignRole($existingPerson->MID, HM_Role_Abstract_RoleModel::ROLE_TEACHER);
                    }
                    unset($item->isTeacher);
                    unset($item->Password);
                    if ($this->_needPersonUpdate($item)) {
                        $item->MID = $existingPerson->MID;
                        $this->_updatesPeople[$item->mid_external] = array('source' => $existingPerson, 'destination' => $item);
                    }
                    if (!empty($item->group) && $this->_needGroupUpdate($item->group, $existingPerson->MID)) {
                        $this->_userGroups[$existingPerson->MID] = $this->_existingGroups[$item->group];
                    }
                    if (!empty($item->tags) && $this->_needTagsUpdate($item->tags, $existingPerson->MID)) {
                        $this->_userTags[$existingPerson->MID] = explode(',', $item->tags);
                    }

                    //unset($this->_existingPeopleIds[$existingPerson->mid_external]);
                    unset($this->_existingPeople[$existingPerson->MID]);
                }
            }
        }

        /*if (count($this->_existingPeople)) {
            $this->_deletesPeople = $this->_existingPeople;
        }*/

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

        $session = new Zend_Session_Namespace('default');
        $this->_divisionId = $session->orgstructure_id;

        $teachers = array();

        if (count($this->_insertsPeople)) {

            foreach($this->_insertsPeople as $id => $insert) {

                if (!isset($insert->Login)) {
                    $insert->Login = $this->_generateLogin();
                }

                if (empty($insert->Password)) {
                    $insert->Password = self::DEFAULT_PASSWORD;
                }
                $openPassword = $insert->Password; // для системного сообщения
                $insert->Password = new Zend_Db_Expr("PASSWORD('".$insert->Password."')");

                // Если не заполнены поля e-mail, lastname, firstname, то просить заполнить при первом логине
                if (!strlen($insert->EMail) || (!strlen($insert->LastName) && !strlen($insert->FirstName))) {
                    $insert->need_edit = HM_User_UserModel::NEED_EDIT_AFTER_FIRST_LOGIN;
                }

                $user = $this->getService('User')->insert($insert->getValues(null,array('isTeacher', 'group', 'tags', 'positionName')));
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
                    $this->_existingPeopleIds[$user->mid_external] = $user->MID;


                    // Здесь добавляем пользователя в оргструктуру, если необходимо
                    if (null !== $this->_divisionId) {
                        $positionName = 'Пользователь';
                        if ( isset($insert->positionName) && ! empty($insert->positionName)) $positionName = $insert->positionName;
                        $this->getService('Orgstructure')->assignUser($user->MID, $this->_divisionId, $positionName);
                    }

                    // Отправить сообщение о регистрации в системе
                    $messenger = $this->getService('Messenger');
                    $messenger->setOptions(
                        HM_Messenger::TEMPLATE_REG,
                        [
                            'fio' => $user->getNameCyr(),
                            'login' => $user->Login,
                            'password' => $openPassword
                        ]
                    );
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }
            }
        }

        if (count($this->_updatesPeople)) {
            foreach($this->_updatesPeople as $id => $update) {
                $this->getService('User')->update($update['destination']->getValues(null, array('Password', 'isTeacher', 'group', 'tags')));
            }
        }


        if (count($this->_userGroups)) {
            $this->_assignPeopleToGroup();
        }
        if (count($this->_userTags)) {
            $this->_assignTags();
        }
        if (count($teachers)) {
            foreach($teachers as $userId ) {
                $this->getService('User')->assignRole($userId, HM_Role_Abstract_RoleModel::ROLE_TEACHER);
            }
        }

        if (count($this->_deletesPeople)) {
            foreach($this->_deletesPeople as $id => $delete) {
                if (strlen($delete->mid_external)) {
                    $this->getService('User')->update(array('MID' => $delete->MID, 'blocked' => 1));
                }
            }
        }
    }


    protected function importPosition()
    {
        if (null === $this->_divisionId) return;

        foreach ($this->_insertsPeople as $people) {
            $mid = $people->MID;
            $positionName = 'Пользователь';
            if ( isset($people->positionName) && ! empty($people->positionName)) $positionName = $people->positionName;

            $values = array(
                'name' => $positionName,
                'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                'code' => '',
                'mid' => $mid,
                'info' => '',
                'owner_soid' => $this->_divisionId,
                'is_manager' => 0
            );

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
            $tagsIds = $this->getService('Tag')->updateTags(array_merge($tagsCache, $tags), $userId,
                $this->getService('TagRef')->getUserType());
            $this->getService('StudyGroup')->addUserByTags($userId, $tagsIds);
        }
    }

}