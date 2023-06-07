<?php
class HM_Orgstructure_OrgstructureService extends HM_Service_Nested
{
    protected $emptyDepartmentsCache;

    public function deleteNode($id, $recursive = false)
    {
        $data = $this->getOne($this->fetchAll($this->quoteInto('soid = ?', $id)));
        $managers = $this->fetchAll($this->quoteInto(
            array('lft >= ?', ' AND rgt <= ?', ' AND type = ?', ' AND is_manager = ?'),
            array($data->lft, $data->rgt, HM_Orgstructure_OrgstructureModel::TYPE_POSITION, 1)
        ));
        foreach($managers as $manager){
            if ($manager->mid) {
                $this->getService('Supervisor')->unassignDepartment($manager->mid, $manager->owner_soid);
            }
        }
       
        parent::deleteNode($id, $recursive);
    }
    
    public function delete($id)
    {
        $collection = $this->fetchAll(
            $this->quoteInto('owner_soid = ?', $id)
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $this->delete($item->soid);
            }
        }

        return parent::delete($id);
    }

    public function getUserPosition($userId){
        return $this->fetchRow(array('mid = ?' => $userId))->name;
    }

    public function orgUnitToFrontendData($unit, $parent = 0, $notEncodeTitle = true, $currentId = null)
    {
        return array_filter([
            'active' => $currentId == $unit->soid,
            'expand' => $parent != 0,
            'isFolder' => true,
            'isLazy' => ($parent == 0 ? false : true),
            'key' => (string) $unit->soid,
            'title' => (($notEncodeTitle === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $unit->name) : $unit->name),
        ]);
    }

    /**
     * Только на один уровень ниже
     */
    public function getTreeContent($parent = 0, $notEncodeTitle = true, $currentId = null, $type = null, $openedParents = [0])
    {
        $parent = (int) $parent;
        $tree = array();

        if($type == null){
            $type = array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
        }

        $collection = $this->fetchAll($this->quoteInto(
            array(
                'owner_soid = ?', ' AND type IN (?)', ' AND blocked = ?'
            ), array(
                $parent, $type, 0
            )), 
            false, null, array('type', 'name')
        );
        
        if (count($collection)) {
            foreach($collection as $unit) {
                if (!is_null($this->emptyDepartmentsCache) && in_array($unit->soid, $this->emptyDepartmentsCache)) continue;

                $treeItem = $this->orgUnitToFrontendData($unit, $parent, $notEncodeTitle, $currentId);
                $tree[] = $treeItem;

                if (in_array($parent, $openedParents)) {
                    /** recursive call */
                    $tree[] = $this->getTreeContent($unit->soid, $notEncodeTitle, null, null, $openedParents);
                }
            }
        }

        return $tree;
    }

    public function getDescendants($parent, $onlyDirectDescendants = false, $onlyType = false)
    {
        if(!is_array($parent)) $parent = array($parent);

        $descendants = array();
        $condition = array(
            'blocked = ?' => 0,
        );

        if(count($parent)) {
            $condition['owner_soid IN (?)'] = $parent;
        }

        if ($onlyType !== false) {
            $condition['type = ?'] = $onlyType;
        }
        $collection = $this->fetchAll($condition, 'name');
        if (count($collection)) {
            foreach($collection as $unit) {
                $descendants[] = (int)$unit->soid;
                if (!$onlyDirectDescendants) $descendants = array_merge($descendants, $this->getDescendants($unit->soid, $onlyDirectDescendants, $onlyType));
            }
        }
        array_unique($descendants);
        return $descendants;
    }


    public function isGrandOwner($itemSoid, $parentSoid)    
    {
        $soid = $itemSoid;
        while(count($collection = $this->fetchAll($this->quoteInto('soid = ?', $soid)))){

            if($collection->current()->soid==$parentSoid) return true; 
    
            $soid = $collection->current()->owner_soid;
    
            if($soid==0 && $parentSoid==0) return true; 
        }
        return false;
    }

    
    public function getDescendansForMultipleSoids($soids, $onlyType = false)
    {
        $descendants = array();
        if (count($soids)) {
            foreach ($soids as $soid) {
                // рекурсивно всем вложенным
                $descendants = array_merge($descendants, $this->getDescendants($soid, false, $onlyType));
            }
            if ($onlyType !== HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                $checkedPositions = $this->fetchAll($this->quoteInto(
                    array('soid IN (?) AND ', 'type = ?'),
                    array($soids, HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                ));
                foreach ($checkedPositions as $position) {
                    $descendants[] = (int)$position->soid;
                }  
            }
        }
        return $descendants;
    }

    public function getPositionsCodes()
    {
        $positions = array();
        $q = $this->getSelect()
            ->from(
                array('so' => 'structure_of_organ'),
                array(
                    'code' => 'DISTINCT(so.code)',
                    'so.name'
                )
            )
            ->where('so.code IS NOT NULL');
        //  print $q;exit;
        $res = $q->query()->fetchAll();
        foreach($res as $item) {
            $positions[$item['code']] = $item['name'];
        }
        return $positions;
    }

    public function pluralFormCount($count)
    {
        return !$count ? '' : sprintf(_n('подразделение plural', '%s подразделение', $count), $count);
    }

    public function pluralFormPositionsCount($count)
    {
        return !$count ? '' : sprintf(_n('должность plural', '%s должность', $count), $count);
    }

     /**
     * Возвращает массив с ID всех дочерних элементов структуры для указанного родительского
     * @param int $soid ID родительского элемента
     * @return array
     */
    public function getChildIDs($soid)
    {
        $arChilds = array();
        $items = $this->fetchAll();
        $arWork = $items->getList('soid','owner_soid');

        foreach ( $arWork as $id=>$parentID) {

            if (is_array($parentID)) {
                $parentID = $parentID['id'];
            }

            if (!is_array($arWork[$id])) {
                $arWork[$id] = array( 'id'        => $id,
                                      'childrens' => array()
                                    );
            }

            if (!is_array($arWork[$parentID])) {
                $arWork[$parentID] = array( 'id'        => $parentID,
                                            'childrens' => array()
                                    );
            }

            $arWork[$parentID]['childrens'][] = &$arWork[$id];
        }

        $needElement = (isset($arWork[$soid]))? $arWork[$soid]['childrens'] : array();

        array_walk_recursive($needElement, array($this,'walkRecursiveFunction'), $arChilds);

        return $arChilds;
    }

    public function walkRecursiveFunction($item, $key, &$arChilds)
    {
       $arChilds[] = $item;
    }

    
    // ВАЖНО! Всё назначение пиплов в уже существующие должности (с уже назначенными профилями) 
    // должно происходить через этот метод; иначе сломаются все программы   
    public function assignUserToPosition($userId, $positionId, $assignContextEvents = false)
    {
        if ($position = $this->getOne($this->find($positionId))) {

            $position->mid = $userId;
            $position->type = HM_Orgstructure_OrgstructureModel::TYPE_POSITION;

            $this->update($position->getValues());

            if ($position->profile_id) {
                $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $position->profile_id);
                foreach ($programms as $programm) {
                    $this->getService('Programm')->assignToUser($position->mid, $programm->programm_id, $assignContextEvents);
                }        
            }        
        }
    }
    
    /**
     * Делает то же самое, что insertUser - вставляет новую должность и назначает чела
     * $assignContextEvents - назначать или не назначать курсы в рамках программы нач.обучения (например)
     * 
     */
    public function assignUser($userId, $ownerSoid, $positionName, $profileId = null, $assignContextEvents = true)
    {
        $newPosition = null;

        $owner = $this->getOne($this->find($ownerSoid));
        
        $positionPrev = $this->getOne(
            $this->fetchAll(
                $this->quoteInto('mid = ?', $userId)
        ));
        
        if (empty($ownerSoid) || empty($owner)) $ownerSoid = 0; 
        if (empty($positionName)) $positionName = _('Пользователь');
        
        // если ничего не изменилось - ничего не делаем
        if (($positionPrev->name != $positionName) || ($ownerSoid != $positionPrev->owner_soid)) {
        
            if ($positionPrev) {
                // если это перемещение - удаляем и воссоздаём в другом месте
                $this->deleteBy(array('soid = ?' => $positionPrev->soid));
                $data = $positionPrev->getValues();
                unset($data['soid']);
            } else {
                // дефолтные параметры для нового пользователя
                $data = array(
                    'name'          => $positionName,
                    'mid'           => $userId,
                    'type'          => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'owner_soid'    => $ownerSoid,
                    'position_date' => HM_Date::now()->toString(HM_Date::SQL)
                );
            }
        
            $data['name'] = $positionName;
            $data['owner_soid'] = $ownerSoid;
            if ($profileId) {
                $profile = $this->getService('AtProfile')->find($profileId);
                if (count($profile)) $profile = $profile->current();
                $data['profile_id'] = $profileId;
                $data['is_manager'] = $profile->is_manager;
            }
            
            $newPosition = $this->insert($data, $ownerSoid);
        }

        if (!$newPosition && $profileId) {
            $profile = $this->getService('AtProfile')->find($profileId);
            if (count($profile)) {
                $profile = $profile->current();
                $this->update(array(
                    'soid' => $positionPrev->soid,
                    'is_manager' => $profile->is_manager,
                    'profile_id' => $profileId
                ));
            }
        }


        $this->assignUserToPosition($userId, $newPosition->soid, $assignContextEvents);
    }
    
    
    /**
     * DEPRECATED!
     *      */
    public function insertUser($userId, $positionId, $positionName = null, $profileId = null)
    {

        /**
         * Добавил вариант что $positionId может быть не задано.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        $position = false;
        $data = array(
            'name' => !$positionName ? _('Пользователь') : $positionName,
            'mid' => $userId,
            'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
            'owner_soid' => !$positionId ? 0 : $positionId
        );
        if ($profileId) {
            $profile = $this->getService('AtProfile')->find($profileId);
            if (count($profile)) {
                $profile = $profile->current();
                $data['is_manager'] = $profile->is_manager ?: 0;
            }
            $data['profile_id'] = $profileId;
        }
        if ($positionId == 0) {
            $this->updateWhere(array('mid' => 0), $this->quoteInto('mid = ?', $userId));
            $position = $this->insert($data);
            return $position;
        }
        $unit = $this->getOne($this->find($positionId));
        $this->updateWhere(array('mid' => 0), $this->quoteInto('mid = ?', $userId));
        if ($unit) {
            if ($unit->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                $position = $this->insert(
                    $data,
                    $unit->soid
                );
            } else {
                $position = $this->updateNode(array('name' => !$positionName ? _('Пользователь') : $positionName,'mid' => $userId), $positionId, $unit->owner_soid);
            }
        }

        return $position;
    }

    // DEPRECATED!!! 
    public function updateEmployees($soid)
    {
        $orgUnit = $this->getOne($this->find($soid));

        if($orgUnit && $orgUnit->is_manager == HM_Orgstructure_OrgstructureModel::SUPERVISOR){
            $employees = $this->fetchAll(array('owner_soid = ?' => $orgUnit->owner_soid, 'soid != ?' => $soid, 'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION));

            $list = $employees->getList('soid', 'mid');

            if(count($list)){
                $this->getService('User')->updateWhere(array('head_mid' => $orgUnit->mid), array('MID IN (?)' => array_values($list)));
                $this->updateWhere(array('is_manager' => 0), array('soid IN (?)' => array_keys($list)));
            }
            $this->getService('Supervisor')->assign($orgUnit->mid);
        }else{
            if($orgUnit->mid > 0){
                $this->getService('Employee')->assign($orgUnit->mid);
            }
        }
    }

    public function update($data, $unsetNull = true){
        //при апдейте проставляем ответственночть супевизора ДО сохранения данных,
        //чтобы иметь возможность проверить предыдущее назначение
        $this->updateSupervisor($data);

        $res = parent::update($data);
        //$this->updateEmployees($res->soid);
        return $res;
    }

    public function insert($data, $objectiveNodeId = 0, $position = HM_Db_Table_NestedSet::LAST_CHILD)
    {
        $res = parent::insert($data, $objectiveNodeId, $position);
        //$this->updateEmployees($res->soid);

        //при инсерте проставляем ответственночть супевизора ПОСЛЕ сохранения данных,
        //чтобы ШЕ нормально создалась
        $this->updateSupervisor($data);

        return $res;
    }
    
    //снимает/назначает отметку о супервизоре
    public function updateSupervisor($data)
    {
        if(!isset($data['is_manager']))  return; //Нет инфы о руководстве

        $oldData = $data['soid'] ? $this->getOne($this->fetchAll($this->quoteInto('soid = ?', $data['soid']))) : false;

        if ($oldData && ($oldData->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT)) {
            return;
        }

        /** @var HM_Role_SupervisorService $supervisorService */
        $supervisorService = $this->getService('Supervisor');

        //овая позиция
        if (!$oldData) {
            if ($data['mid'] && $data['is_manager']) {
                $supervisorService->assignDepartment($data['mid'], $data['owner_soid']);
            }

        //если в позиции убрали отметку менеджер
        } elseif (($oldData->is_manager == 1) && ($data['is_manager'] == 0)) {
            $supervisorService->unassignDepartment($oldData->mid, $oldData->owner_soid);
        //если в позиции добавили отметку менеджер
        } elseif (($oldData->is_manager == 0) && ($data['is_manager'] == 1)) {
            $supervisorService->assignDepartment($data['mid'], $oldData->owner_soid);
        //если отеметка менеджер была и осталась, а пользователь сменился
        } elseif (($oldData->is_manager == 1) && ($data['is_manager'] == 1) && ($oldData->mid != $data['mid'])) {
            $supervisorService->unassignDepartment($oldData->mid, $oldData->owner_soid);
            $supervisorService->assignDepartment($data['mid'], $oldData->owner_soid);
        }
    }

    public function onUserAssign()
    {
        // возможно, здесь 
    }
    public function getDefaultParent()
    {
        $role = $this->getService('User')->getCurrentUserRole();
        if (($this->getService('Acl')->inheritsRole($role, HM_Responsibility_ResponsibilityModel::getResponsibilityRoles()))) {
            if ($responsibilities = $this->getService('Responsibility')->get(
                $this->getService('User')->getCurrentUserId(),
                HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))
            {
                $department = $this->fetchAllDependence('Parent', array('soid in (?)' => $responsibilities));
                if (!empty($department) && (!empty($department->current()->parent) || $department->current()->owner_soid == 0)) {
                    return $department->current();
                } else {
                    return false;
                }
            } elseif (!HM_Responsibility_ResponsibilityModel::getResponsibilityDefaultAccess($role)) {
                throw new HM_Responsibility_ResponsibilityException(_('Не указана область ответственности'));
            }
        }
        return $this->getRoot();
    }
    
    public function getRoot()
    {
        $role = $this->getService('User')->getCurrentUserRole();
        if (count($responsibility = $this->getService('Responsibility')->get()) && ($role != HM_Role_Abstract_RoleModel::ROLE_ADMIN)) {
            $soid = array_shift($responsibility);  // сейчас нет возможности задать несколько responsibility
            if ($collection = $this->find($soid)) {
                return $collection->current();
        }
    }

        return $this->getDummyRoot();
    }

    public function getDummyRoot()
    {
        $root = new stdClass();
        $headUnitTitle = $this->getService('Option')->getOption('headStructureUnitName');
        if (!strlen($headUnitTitle)) {
            $headUnitTitle = _(HM_Orgstructure_OrgstructureModel::DEFAULT_HEAD_STRUCTURE_ITEM_TITLE);
        }

        $root->soid = 0;
        $root->name = $headUnitTitle;

        return $root;
    }

    static public function getIconClass($type, $isManager = false)
    {
        $return = 'type-' . $type;
        if ($isManager) {
            $return .= '-manager';
    }
        return $return;
    }

    static public function getIconTitle($type, $isManager = false)
    {
        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            return _('Подразделение');
        } elseif ($isManager) {
            return _('Руководитель подразделения');
        }
        return _('Пользователь');
    }

    // на случай, если человека нет в оргструктуре, сервис работает только с должностями
    // например, назначение оценочных мероприятий кандидатам 
    public function getDummyPosition($userId)
    {
        $data = array(
            'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,        
            'mid' => $userId,        
        );
        return call_user_func(array('HM_Orgstructure_OrgstructureModel', 'factory'), $data);
    }
    
    public function getManager($positionId)
    {
        $collection = $this->findDependence(array('Parent'), $positionId);
        if (count($collection)) {
            $position = $collection->current();
            if (!$position->is_manager) {
                $targetSoid = $position->owner_soid;
            } else {
                if (count($position->parent)) {
                    $targetSoid = $position->parent->current()->owner_soid;
                } else {
                    return false;
                }
            }
            $collection = $this->fetchAllDependence('User', array(
                'owner_soid = ?' => $targetSoid,
                'is_manager = ?' => HM_Orgstructure_OrgstructureModel::MANAGER,
            ));
            if (count($collection)) {
                return $this->getOne($collection);
            }
        }
        return false;
    }

    public function getAllManagersInDep($soid = 0) {
        $unit = $this->fetchAll(array('soid = ?' => $soid))->current();
        if($unit->soid !== null){
            $select = $this->getSelect();
            $select->from(array('so' => 'structure_of_organ'), array('p.MID'));
            $select->joinInner(array('p' => 'People'), 'p.MID = so.MID', array());
            $where = $this->quoteInto(
                array(
                    'so.owner_soid = ?',
                    ' AND is_manager = ?',
                ),
                array(
                    $unit->soid,
                    HM_Orgstructure_OrgstructureModel::MANAGER,
                )
            );
            $select->where($where);

            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
        } else {
            $rows = array();
        }
        return $rows;
    }

    public function getResponsibleDepartments()
    {
        /** @var HM_User_UserService $userService    */
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $userId = $userService->getCurrentUserId();
        $currentRole = $userService->getCurrentUserRole();
        $departmentIds = 0;
        switch ($currentRole) {
            case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:
                $responsibilityDepartmentIds = Zend_Registry::get('serviceContainer')->getService('Responsibility')->get($userId);
                $departmentIds = array_merge(
                    $responsibilityDepartmentIds,
                    Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDescendansForMultipleSoids(
                        $responsibilityDepartmentIds,
                        HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
                    )
                );
                break;
            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:
                $department = Zend_Registry::get('serviceContainer')->getService('Supervisor')
                    ->getResponsibleDepartment($userId);
                if ($department){
                    $departmentIds = $department->soid;
                }
                break;
        }
        return $departmentIds;
    }

    // возвращает подразделение (одно!), за которое отвчает текущий пользователь
    public function getResponsibleDepartment()
    {
        /** @var HM_User_UserService $userService    */
        $userService =$this->getService('User');
        $userId = $userService->getCurrentUserId();
        $currentRole = $userService->getCurrentUserRole();
        $department = false;
        switch ($currentRole) {
            case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:
                if ($responsibilityDepartmentIds = $this->getService('Responsibility')->get($userId)) {
                   $departmentId = array_shift($responsibilityDepartmentIds); // сейчас их не может быть несколько
                    $department = $this->getOne($this->find($departmentId));
                }
                break;
            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:
                $department = $this->getService('Supervisor')
                    ->getResponsibleDepartment($userId);
                break;
        }
        return $department;
    }


    /**
     * Рекурсивная функция,
     * возвращает массив с цепочкой адишников подразделений (soid),
     * начиная с переданного и заканчивая
     * подразделением в корне. Беря каждый раз айдишник родителя (owner_soid)
     * @param int $soid
     * @return array
     */

    public function getAllOwnersInTree($soid){
        $items = array();

        $items[] = $soid;
        $item = $this->fetchAll(array('soid = ?' => $soid))->current();
        if($item->owner_soid != 0 && $item->owner_soid !== null){
            $items = array_merge($items, $this->getAllOwnersInTree($item->owner_soid));
        }
        return $items;
    }

    /**
     * @method Получает всех родителей в текущей ветке
     * В отличие, от метода выше, использует ключи lft-rgt и не возвращает
     * подразделение для которого выполняется поиск.
     * @param int $leftKey - Левый ключ узла
     * @param int $rightKey - Правый ключ узла
     * @return array - Коллекция из массивов подразделений
     */
    public function getAllOwnersOnBranch($leftKey, $rightKey)
    {
        return $this->fetchAll(
            $this->quoteInto(
                array('lft < ?', ' AND rgt > ?'),
                array($leftKey, $rightKey)
            ),
            false, null, array('lft')
        )->asArrayOfArrays();
    }


    /**
     * for each $org_id descendant delete every links with $currentClassifierTypes classifiers
     * add links with $currentClassifiers
     * @param  $org_id
     * @param  $currentClassifierTypes
     * @param  $currentClassifiers
     * @return void
     */
    public function setClassifiers($org_id, $currentClassifierTypes, $currentClassifiers){

        $classifiers = $this->getService('Classifier')->fetchAll(array('type IN (?)' => $currentClassifierTypes))->getList('classifier_id', 'classifier_id');
        $descendants = $this->getDescendants($org_id);
        $descendants[] = $org_id;

        // structure links
        $res = $this->getService('ClassifierLink')->deleteBy(array(
            'classifier_id IN (?)' => $classifiers,
            'item_id IN (?)' => $descendants,
            'type = ?' => HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
        ));
        foreach($descendants as $descendant){
            foreach($currentClassifiers as $classifier)
                $res = $this->getService('ClassifierLink')->insert(array(
                    'classifier_id' => $classifier,
                    'item_id' => $descendant,
                    'type' => HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
                ));
        }

        // users links
        /*        $users = $this->fetchAll(array('soid IN (?)' => $descendants, 'mid IS NOT NULL', 'mid != 0'))->getList('soid', 'mid');
                $res = $this->getService('ClassifierLink')->deleteBy(array(
                                                                          'classifier_id IN (?)' => $classifiers,
                                                                          'item_id IN (?)' => $users,
                                                                          'type = ?' => HM_Classifier_Link_LinkModel::TYPE_PEOPLE
                                                                     ));
                foreach($users as $user){
                    foreach($currentClassifiers as $classifier)
                        $res = $this->getService('ClassifierLink')->insert(array(
                                                                                'classifier_id' => $classifier,
                                                                                'item_id' => $user,
                                                                                'type' => HM_Classifier_Link_LinkModel::TYPE_PEOPLE
                                                                           ));
                }*/
    }

    public function getInfo($unit)
    {
        $department = ($unit->owner_soid) ? $this->getOne($this->find($unit->owner_soid)) : false;
        $classifiers = $this->getService('ClassifierLink')->fetchAllDependence(
            'Classifier',
            $this->getService('ClassifierLink')->quoteInto(
                array(' item_id = ? ', ' AND type = ? '),
                array($unit->soid, HM_Classifier_Link_LinkModel::TYPE_STRUCTURE)
            )
        )->getList('classifier_id', 'classifiers');
        return array('department' => $department, 'post' => $unit, 'classifiers' => $classifiers);
    }
}
