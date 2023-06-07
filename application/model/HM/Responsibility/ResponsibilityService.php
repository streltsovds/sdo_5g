<?php
class HM_Responsibility_ResponsibilityService extends HM_Service_Abstract
{
    const RESPONSIBILITY_LEVEL_HIGH = 'high'; 
    const RESPONSIBILITY_LEVEL_LOW = 'low';
    
    protected $_items = array();
    protected $_subjectIds = false;
    
    public function set($userId, $itemType, $itemIds)
    {
        $this->deleteBy(array(
            'user_id = ?' => $userId,
            'item_type = ?' => $itemType,
        ));

        if (!$itemIds) {
            return;
        }

        if (!is_array($itemIds)) {
            $itemIds = array($itemIds);
        }

        foreach ($itemIds as $itemId) {
            $this->insert(array(
                'user_id'   => $userId,
                'item_type' => $itemType,
                'item_id'   => $itemId,
            ));
        }
    }
    
    public function get($userId = 0, $itemType = HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE)
    {
        if (!$userId) $userId = $this->getService('User')->getCurrentUserId();
        
        $collection = $this->fetchAll(array(
            'user_id = ?' => $userId,
            'item_type = ?' => $itemType,
        ));

        if (count($collection)) {
            return $collection->getList('item_id');
        }
        return array();
    }
    
    public function isResponsibleFor($userId, $itemType, $itemId)
    {
        if (empty($userId)) $userId = $this->getService('User')->getCurrentUserId();
        
        switch ($itemType) {
            case HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT:
            case HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM:
            case HM_Responsibility_ResponsibilityModel::TYPE_GROUP:
                $collection = $this->fetchAll(array(
                    'user_id = ?' => $userId,
                    'item_type = ?' => $itemType,
                ));
                return count($collection);
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE:

                $collection = $this->fetchAll(array(
                    'user_id = ?' => $userId,
                    'item_type = ?' => $itemType,
                ));

                if(count($collection)==0)  return true; // Область ответственности не ограничена - раньше считалось, что отвественность ограничена полностью
                $soids = $collection->getList('item_id');
                $soid = array_shift($soids); // сейчас может быть только одно подответственное подраздлеление

                return $this->getService('Orgstructure')->isGrandOwner($itemId, $soid);

                break;
        }
        return false;
    }

    public function isResponsibleForUser($supervisorId, $userId)
    {
        $soids = $this->get($supervisorId);
        if (!$soids) {
            return true;
        }

        $position = $this->getOne($this->getService('Orgstructure')->fetchAll('mid='.$userId));

        if (!$position || !$position->soid) {
            return false;
        }

        return $this->isResponsibleFor($supervisorId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $position->soid);
    }

    public function getResponsibleForPosition($positionId)
    {
        $return = array();
        $collection = $this->fetchAllDependence('Department', array(
            'item_type = ?' => HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE,
        ));


        foreach ($collection as $responsible) {
            if ($this->isResponsibleFor($responsible->user_id, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $positionId)) {
                $return[$responsible->user_id] = $responsible;
            }
        }
        return $return;
    }

    // возвращает id юзеров (специалистов по подбору или оценке), ответственных за данную позицию
    public function getResponsiblesForPosition($positionId, $onlyUserIds = array(), $responsibilityLevel = self::RESPONSIBILITY_LEVEL_HIGH)
    {
        $return = $responsibles = $responsibleLevels = array();
        
        $condition = array(
            'item_type = ?' => HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE,
        );
        if (count($onlyUserIds)) {
            $condition['user_id IN (?)'] = $onlyUserIds;
        }
        
        $collection = $this->fetchAllDependence('Department', $condition);
        foreach ($collection as $responsible) {
            if ($this->isResponsibleFor($responsible->user_id, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $positionId)) {
                $responsibles[$responsible->user_id] = $responsible;
                $responsibleLevels[$responsible->user_id] = count($responsible->department) ? $responsible->department->current()->level : 0;
            }
        }
        $function = ($responsibilityLevel == self::RESPONSIBILITY_LEVEL_HIGH) ? 'min' : 'max'; // чем меньше level в оргструктуре, тем higher responsibility
        $requiredLevel = $function($responsibleLevels); 
        
        foreach ($responsibleLevels as $userId => $level) {
            if ($level == $requiredLevel) {
                $return[$userId] = $responsibles[$userId];
            }
        }
        
        return $return;
    }
    
    // возвращает id всех подотчётных юзеров для специалиста по подбору или оценке  
    public function getAccountableUsers($userId = false)
    {
        $userIds = array();
        if (!$userId) $userId = $this->getService('User')->getCurrentUserId();

        if (count($collection = $this->fetchAll(array(
            'user_id = ?' => $userId,
            'item_type = ?' => HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE,
        )))) {
            $soids = $collection->getList('item_id');
            $soid = array_shift($soids); // сейчас может быть только одно подответственное подраздлеление
            if (count($positionIds = $this->getService('Orgstructure')->getDescendants($soid))) {
                // в оракле может не работать конструкция вида "IN (много-премного)"
                if (count($collection = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $positionIds)))) {
                    $userIds = $collection->getList('mid');
                }
                if (count($collection = $this->getService('RecruitVacancy')->fetchAllManyToMany('Candidate', 'CandidateAssign', array('position_id IN (?)' => $positionIds)))) {
                    foreach ($collection as $vacancy) {
                        if (count($vacancy->candidates)) {
                            $userIds = $userIds + $vacancy->candidates->getList('user_id'); 
                        }
                    }
                }
            }                        
        }
        return array_unique($userIds);
    }    
    
    public function filterLowerResponsible($collection)
    {
        
    }
    
    public function getResponsibleUsersByItemIds($items_ids, $item_type = HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE) {
        if(is_array($items_ids) && count($items_ids)){
            $select = $this->getSelect();
            $select->from(array('r' => 'responsibilities'), array('user_id'));
            $where = $this->quoteInto(
                array(
                    'r.item_id IN (?)',
                    ' AND r.item_type = ?',
                ),
                array(
                    $items_ids,
                    $item_type,
                )
            );
            $select->where($where);
            var_dump($select->__toString());
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
        } else {
            $rows = array();
        }
        return $rows;
    }

    //Проверка на доступ к оргструктуре
    public function checkUsers($select, $orgTableAlias = 'so', $userIdColumn = false)
    {
        $soids       = $this->get(
            $this->getService('User')->getCurrentUserId(),
            HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE);

        if ($soids) {
            if (!empty($userIdColumn)) {
                $select->joinLeft(
                    array('so_resp' => 'structure_of_organ'),
                    'so_resp.mid='.$userIdColumn,
                    array());

                $orgTableAlias = 'so_resp';
            }
            $departments = $this->getService('Orgstructure')->fetchAll($this->getService('Orgstructure')->quoteInto('soid in (?)', $soids));
            $departmentsWhere = array('0=1');

            foreach ($departments as $department) {
                $departmentsWhere[] = '('.$orgTableAlias.'.lft>'.$department->lft.' AND '.$orgTableAlias.'.rgt<'.$department->rgt.')';
            }

            $select->where(implode(' OR ', $departmentsWhere));
        }

        return $select;
    }

    public function isUnlimitedSubjects($userId)
    {
        $resp = $this->countAll($this->quoteInto(
            array('user_id = ?', ' AND item_type <> ?'),
            array($userId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE)));

        return ($resp == 0) ? true : false;
    }

    public function isUnlimitedUsers($userId)
    {
        $resp = $this->countAll($this->quoteInto(
            array('user_id=?', ' AND item_type = ?'),
            array($userId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE)));

        return ($resp == 0) ? true : false;
    }

    //Проверка доступа к курсу
    public function checkSubjects($select, $subIdColumn, $userId = false, $groupIdColumn = null)
    {
        if (!$userId ) {
            $userId = $this->getService('User')->getCurrentUserId();
        }

        $sign = '<>';
        $type = HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE;

        if  (in_array($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY
        ))) {
            $sign = '=';
            $type = (in_array($this->getService('User')->getCurrentUserRole(), [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]))
                ? HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT
                : HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT;
        }

        $resp = $this->fetchAll($this->quoteInto(
            array('user_id = ?', ' AND item_type ' . $sign . ' ?'),
            array($userId, $type)));

        $respGroup =
        $respProgram = [];
        if (!count($resp)) {
            $respGroup = $this->fetchAll($this->quoteInto(
                array('user_id = ?', ' AND item_type = ?'),
                array($userId, HM_Responsibility_ResponsibilityModel::TYPE_GROUP)));

            $respProgram = $this->fetchAll($this->quoteInto(
                array('user_id = ?', ' AND item_type = ?'),
                array($userId, HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM)));

            if (!count($respGroup) && !count($respProgram)) {
                return $select;
            } else {
                $resp = count($respGroup) ? $respGroup : $respProgram;
            }
        }

        $type = is_object($resp->current()) ? $resp->current()->item_type : null;
        $ids  = $resp->getList('item_id');

        switch ($type) {
            case HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT:
                $select->where($this->quoteInto(
                    $subIdColumn." IN (?)", $ids));
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT:
                $select->where($this->quoteInto(
                    $subIdColumn." IN (?)", $ids));
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_GROUP:
                $select->joinInner(
                    array('resp_stgr' => 'study_groups_courses'),
                    'resp_stgr.course_id='.$subIdColumn,
                    array());
                $select->where($this->quoteInto(
                    "resp_stgr.group_id IN (?)", $ids));
                if ($groupIdColumn) {
                    $select->where($this->quoteInto(
                        $groupIdColumn . " IN (?)", $ids));
                } else {
                    $select->joinInner(
                        array('resp_u' => 'study_groups_users'),
                        'resp_u.group_id=resp_stgr.group_id',
                        array());
                }
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM:
                $select->joinInner(
                    array('resp_stgr' => 'study_groups_courses'),
                    'resp_stgr.course_id='.$subIdColumn,
                    array());
                $select->joinInner(
                    array('resp_prg' => 'study_groups_programms'),
                    'resp_stgr.group_id=resp_prg.group_id',
                    array());
                $select->where($this->quoteInto(
                    "resp_prg.programm_id IN (?)", $ids));
                break;
        }

        return $select;
    }

    public function getSubjectIds($userId)
    {
        if ($this->_subjectIds === false) {
            $select = $this->getSelect();
            $select->from(array('s' => 'subjects'), array('s.subid'));
            $this->checkSubjects($select, 's.subid', $userId);
            $result = $select->query()->fetchAll();
            $this->_subjectIds = array();
            foreach ($result as $row) {
                $this->_subjectIds[] = $row['subid'];
            }
        }

        return $this->_subjectIds;
    }

    public function checkGroups($select, $groupColumn)
    {
        $resp = $this->fetchAll($this->quoteInto(
            array('user_id=?', ' AND item_type in (?)'),
            array($this->getService('User')->getCurrentUserId(), array(HM_Responsibility_ResponsibilityModel::TYPE_GROUP, HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM))));

        if (!$resp || !count($resp)) {
            return $select;
        }

        $type = $resp->current()->item_type;
        $ids  = $resp->getList('item_id');

        switch ($type) {
            case HM_Responsibility_ResponsibilityModel::TYPE_GROUP:
                $select->where($this->quoteInto(
                    $groupColumn." IN (?)", $ids));
                break;
            case HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM:
                $select->joinInner(
                    array('resp_prg' => 'study_groups_programms'),
                    'resp_prg.group_id='.$groupColumn,
                    array());
                $select->where($this->quoteInto(
                    "resp_prg.programm_id IN (?)", $ids));
                break;
        }

        return $select;
   }

    public function resetResponsibility($userId)
    {
        // сбрасываем область ответственности при назначении первой менеджерской роли
        // иначе получается не менеджер, а специалист
        $roles = $this->getService('User')->getUserRoles($userId);
        if (!count(array_intersect($roles, array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                HM_Role_Abstract_RoleModel::ROLE_HR,
                HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            )))) {
            $this->deleteBy($this->quoteInto('user_id = ?', $userId));
        }
    }
}