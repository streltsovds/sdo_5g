<?php
class HM_Role_SupervisorService extends HM_Service_Abstract
{

    public function assign($mid)
    {
        if($mid > 0){
            $res = $this->fetchAll(array('user_id = ?' => $mid));

            if(count($res) > 0 ){
                return true;
            }else{
                $this->insert(array('user_id' => $mid));
                return true;
            }
        }
        return false;
    }

    // сейчас подчиненными супервайзера являются enduser'ы из его подразделения, без вложенности
    public function isResponsibleFor($userId, $supervisorId = null)
    {
        if (!$supervisorId) {
            $supervisorId = $this->getService('User')->getCurrentUserId();
        }
        $department = $this->getService('Orgstructure')->fetchAllDependence('Sibling', array('mid = ?' => $userId, 'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION))->current();
        if (count($department->siblings)) {
            foreach ($department->siblings as $sibling) {
                if ($sibling->mid == $userId) return true;
            }
        }
        return false;
    }
    
    // метод взят из beeline, но логика совсем другая
    public function getSlaves($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getService('User')->getCurrentUserId();
        }
        $user = $this->getOne($this->getService('User')->findDependence('Position', $userId));
        $slaves = array();
        if ($user && count($user->positions)) {
            $position = $user->positions->current();
            $department = $this->getOne($this->getService('Orgstructure')->find($position->owner_soid));
            if ($department) {
                if (count($collection = $this->getService('Orgstructure')->fetchAll(array(
                        'lft > ?' => $department->lft,
                        'rgt < ?' => $department->rgt,
                        'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                )))) {
                    $slaves = $collection->getList('mid');
                }
            }
        }
        return $slaves;
    }

    // выбирает прямых подчиненных супервизора: подчиненные того же уровня и менеджеры первой вложенности
    public function getDirectSlaves($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getService('User')->getCurrentUserId();
        }
        $user = $this->getOne($this->getService('User')->findDependence('Position', $userId));
        $slaves = array();
        if ($user && count($user->positions)) {
//            $position = $user->positions->current();
            foreach($user->positions as $position)
                if(!$position->blocked) break;

            $department = $this->getOne($this->getService('Orgstructure')->find($position->owner_soid));


            if ($department) {
                $select1 = $this->getSelect();
                $select1->from(
                    array('so' => 'structure_of_organ'),
                    array(
                        'so.mid'
                    ))
                    ->where('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                    ->where('so.mid > 0 AND (so.owner_soid=' . $position->owner_soid . ' AND so.is_manager=0 AND so.blocked=0)');


                $select2 = $this->getSelect();
                $select2->from(
                    array('so' => 'structure_of_organ'),
                    array(
                        'so.mid'
                    ))
                    ->joinLeft(
                        array('so2' => 'structure_of_organ'),
                        'so2.soid = so.owner_soid',
                        array()
                    )
                    ->where('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                    ->where('so.mid > 0 AND (so2.owner_soid=' . $position->owner_soid . ' AND so.is_manager=1 AND so.blocked=0)');

                $select = $this->getSelect();
                $select->union(array($select1, $select2));

                $slavesResult = $select->query()->fetchAll();
                foreach ($slavesResult as $slave) {
                    $slaves[] = $slave['mid'];
                }



            }
        }
        return $slaves;
    }

    // метод взят из beeline, но логика совсем другая    
    public function filterSelectForSlavesOnly(Zend_Db_Select $select, $where, $supervisorId = null)
    {
        if (null === $supervisorId) $supervisorId = $this->getService('User')->getCurrentUserId();
    
        $slavesIds = $this->getSlaves($supervisorId);
        if (count($slavesIds)) {
            $select->where($where, $slavesIds);
        }
        return $select;
    }

    public function checkSimpleUser($userId, $simpleRoles)
    {
        if (!$userId) {
            return false;
        }

        $roles = $this->getService('User')->getUserRoles($userId);
        if (!$roles) {
            return false;
        }

        foreach ($roles as $role) {
            if (!in_array($role, $simpleRoles)) {
                return false;
            }
        }

        return true;
    }

    public function assignDepartment($userId, $departmentId) 
    {
        //руководитель может быть в корне, т.е. department_id = 0
        if (!$departmentId && $departmentId!== 0 && $departmentId !== '0') return false;
        
        $this->assign($userId);
        
        // ситуация: я HR, отвечаю за целый департамент в оргструктуре
        // и тут меня назначают начальником HR-отдела из 3-х человек;
        // правильное поведение: область ответственности "департамент" НЕ снимается;
        // область ответственности "отдел из 3-х человек" не появляется;
        // по-хорошему надо иметь массив областей, но это пока невозможно

        $responsibilities = $this->getService('Responsibility')->get($userId,
            HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE);

        if (empty($responsibilities)) {
            $responsibilities = array($departmentId);
            $this->getService('Responsibility')->set($userId,
                HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, array_values($responsibilities));
        }
    }

    public function unassignDepartment($userId, $departmentId) 
    {
        //руководитель может быть в корне, т.е. department_id = 0
        if (!$departmentId && $departmentId!== 0 && $departmentId !== '0') return false;

        $responsibilities = $this->getService('Responsibility')->get($userId,
            HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE);

        if (in_array($departmentId, $responsibilities)) {
            
            // если он обычный руководитель отвечает только за это одно подразделение
            unset($responsibilities[$departmentId]);
            $this->getService('Responsibility')->set($userId,
                HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, array_values($responsibilities));
        } else {
            // если отвечает за что-то еще - оно не должно поменяться
        }
        
        // на всякий случай проверяем, не подрабатывает ли он руководителем еще где-нибудь 
        $collection = $this->getService('Orgstructure')->fetchAll(array(
            'mid = ?' => $userId,
            'owner_soid != ?' => $departmentId,
            'is_manager = ?' => 1,
            'blocked = ?' => 0,
        ));
        if (!count($collection)) {
            $this->deleteBy('user_id=' . $userId);
        }
    }

    public function getResponsibleDepartment($userId)
    {
        $position = $this->getService('Orgstructure')->fetchAllDependence('Parent',
            array('mid = ?' => $userId, 'blocked=0  AND type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION))
            ->current();
        return $position->parent ? $position->parent->current() : 0;
    }
}