<?php
class HM_Role_DeanService extends HM_Service_Abstract
{
    protected $_options;

    public function userIsDean($userId)
    {
        $res = false;
        if($this->countAll('MID = '. (int) $userId) > 0) $res = true;
        return $res;
    }

    public function getResponsibilityOptions($userId)
    {
        if (empty($this->_options)) {
            $this->_options = array(
                'unlimited_subjects' => $this->getService('Responsibility')->isUnlimitedSubjects($userId) ? 1 : 0,
                'unlimited_users'    => $this->getService('Responsibility')->isUnlimitedUsers($userId) ? 1 : 0
            );
        }
        return $this->_options;
    }

    /**
     * Устанавливает параметры областей ответственности
     * @param array $options('user_id', 'unlimited_courses', 'unlimited_subjects', 'assign_new_courses')
     */
    public function setResponsibilityOptions($options)
    {
        if($this->getOne($this->getService('DeanOptions')->find($options['user_id']))){
            $this->getService('DeanOptions')->update($options, false);
        }else{
            $this->getService('DeanOptions')->insert($options, false);
        }
    }

    /**
     * Проверяет наличие области ответственности
     *
     * @param unknown_type $userId
     * @param unknown_type $subjectId
     * @return string|string
     */
    public function isSubjectResponsibility($userId, $subjectId)
    {
        $subjectIds = $this->getService('Responsibility')->getSubjectIds($userId);

        if (empty($subjectIds) || in_array($subjectId, $subjectIds)) {
            return true;
        }
        return false;
    }

    /**
     * Добавляет область ответственности
     * 
     * @param unknown_type $userId
     * @param unknown_type $subjectId
     * @return string|string
     */
    public function addSubjectResponsibility($userId, $subjectId)
    {
        $res = $this->fetchAll(array('MID = ?' => $userId, 'subject_id = ?' => $subjectId));

        if(count($res) == 0){
            $this->insert(
                array(
                	'MID' => $userId,
                    'subject_id' => $subjectId
                )
            );
            return true;
        }
        return false;
        
    }

    /**
     * По ид пользователя возвращаем коллекцию моделей областей ответственности
     * (т.е. учебных курсов)
     *
     * @param $userId
     * @param array $userId
     * @return HM_Collection
     */
    public function getSubjectsResponsibilities($userId, $where = [])
    {
        $options = $this->getResponsibilityOptions($userId);
        if($options['unlimited_subjects'] == 1) {

            $isLaborSafety = ($this->getService('Acl')->checkRoles([
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL]
            ));

            $where['is_labor_safety = ?'] = $isLaborSafety;

            $isDean = 0; //($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN))) ? 1 : 0;
            if (!$isDean) {
                $where['type != ?'] = HM_Subject_SubjectModel::TYPE_FULLTIME;
            }

            return $this->getService('Subject')->fetchAll($where, 'name ASC');
        }  else {
            return $this->getAssignedSubjectsResponsibilities($userId);
        }
    }
    
     /**
     * По ид пользователя возвращаем коллекцию АКТИВНЫХ моделей областей ответственности
     * (т.е. учебных курсов дата окончания которых > сегодня или их время неограничено)
     *
     * @param integer $userId
     * @return HM_Collection
     */
    public function getActiveSubjectsResponsibilities($userId)
    {
        
        $options = $this->getResponsibilityOptions($userId);
        if($options['unlimited_subjects'] == 1) {

            $condition = "(period IN (1,2) OR end > NOW() OR end IS NULL)";

            $isLaborSafety = ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL))) ? 1 : 0;
            $condition .= " AND is_labor_safety = {$isLaborSafety}";

            $isDean = 0;// ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN))) ? 1 : 0;
            if (!$isDean) {
                $condition .= " AND type != " . HM_Subject_SubjectModel::TYPE_FULLTIME;
            }

            return $this->getService('Subject')->fetchAll($condition, 'name ASC');
        } else {
            return $this->getAssignedSubjectsResponsibilities($userId, ['(period IN (1,2) OR end > NOW() OR end IS NULL)']);
        }
    }


    public function getAssignedSubjectsResponsibilities($userId, $where = false)
    {
        $where['is_labor_safety != ?'] = 1;

        $subjectIds = $this->getService('Responsibility')->getSubjectIds($userId);
        if (count($subjectIds)) {
            $where['subid IN (?)'] = $subjectIds;
        }

        $subjects = $this->getService('Subject')->fetchAll($this->quoteInto($where, 'name'));
        return $subjects;
    }

    public function getSubjects($userId)
    {
        return $this->getSubjectsResponsibilities($userId);
    }

    public function getResponsibilitiesByType($userId)
    {
        $responsibilities = array();
        /** @var HM_Responsibility_ResponsibilityService $responsibilityService */
        $responsibilityService = $this->getService('Responsibility');

        $responsibilities[HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT] = $responsibilityService->get(
            $userId, HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT
        );
        $responsibilities[HM_Responsibility_ResponsibilityModel::TYPE_GROUP] = $responsibilityService->get(
            $userId, HM_Responsibility_ResponsibilityModel::TYPE_GROUP
        );
        $responsibilities[HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM] = $responsibilityService->get(
            $userId, HM_Responsibility_ResponsibilityModel::TYPE_PROGRAMM
        );
        return $responsibilities;
    }

    public function isNeedResponsibilityNotification($userId, $itemId, $responsibilityType)
    {
        $responsibilities = $this->getResponsibilitiesByType($userId);
        $isNeedNotification = false;
        // если есть ограничение с таким типом то смело добавляем итем в область ответственности и молчим
        if (count($responsibilities[$responsibilityType])) {
            // надо так, ибо в set() deleteBy делается
            $itemIds = $responsibilities[$responsibilityType];
            $itemIds[] = $itemId;
            $this->getService('Responsibility')->set($userId, $responsibilityType, $itemIds);
            return $isNeedNotification;
        }

        // чет ничего умнее в голову не идет =\
        foreach ($responsibilities as $key => $responsibilitiesByType)
        {
            if (count($responsibilitiesByType)) {
                $isNeedNotification = true;
                break;
            }
        }
        return $isNeedNotification;
    }

}