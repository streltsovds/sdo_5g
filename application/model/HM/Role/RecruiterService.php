<?php
class HM_Role_RecruiterService extends HM_Service_Abstract
{
    public function getRecruiterVacancies($onlyActive = true)
    {
        $condition = $this->_getCondition($onlyActive);
        $vacancies = $this->getService('RecruitVacancy')->fetchAll($condition, 'name');
        return $vacancies;
    }

    public function getRecruiterVacanciesWithRecruiters($onlyActive = true)
    {
        $condition = $this->_getCondition($onlyActive);

        $select = $this->getSelect();

        $select->from(
            array('rv' => 'recruit_vacancies')
        );

        $select->joinLeft(array('rvr' => 'recruit_vacancy_recruiters'), 'rv.vacancy_id = rvr.vacancy_id', array());
        $select->joinLeft(array('r' => 'recruiters'), 'r.recruiter_id = rvr.recruiter_id', array());
        $select->joinLeft(array('p' => 'People'), 'p.MID = r.user_id');

        $condition = $this->_getCondition($onlyActive, 'rv');

        foreach($condition as $key => $value) {
            $select->where($key, $value);
        }

        $select->where('status != ?', HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL);
        $select->order(array('r.user_id', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'rv.name'));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("rv.position_id IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $result = $select->query()->fetchAll();

        return $result;
    }

    public function getVacanciesForDropdownSelect()
    {
        $vacancies = $this->getRecruiterVacanciesWithRecruiters();
        $currentUserId = $this->getService('User')->getCurrentUserId();

        $vacanciesFirstPart  = array();
        $vacanciesMiddlePart = array();
        $vacanciesLastPart   = array();
        foreach($vacancies as $vacancy){
            if($vacancy['MID'] == $currentUserId){
                $vacanciesFirstPart[] = $vacancy;
            } elseif(!$vacancy['MID']) {
                $vacanciesLastPart[] = $vacancy;
            } else {
                $vacanciesMiddlePart[] = $vacancy;
            }
        }

        $vacancies = array_merge($vacanciesFirstPart, $vacanciesMiddlePart, $vacanciesLastPart);

        $vacanciesByManager = array();
        foreach($vacancies as $vacancy) {
            if($vacancy['MID']){
                $key = $vacancy['LastName'].' '.$vacancy['FirstName'].' '.$vacancy['Patronymic'];
            } else {
                $key = _('Не назначенные');
            }
            if(!is_array($vacanciesByManager[$key])){
                $vacanciesByManager[$key] = array();
            }
            $vacanciesByManager[$key][$vacancy['vacancy_id']] = $vacancy['name'];
        }

        return $vacanciesByManager;
    }


    private function _getCondition($onlyActive = true, $tableName = ''){
        if($tableName){
            $tableName .= '.';
        }
        $recruiterVacancyIds = $condition = array();
        // специалист видит только назначенные вакансии; менеджер - все
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
            $recruiterVacancyIds = array();
            $collection = $this->fetchAllDependence('VacancyAssign', array('user_id = ?' => $this->getService('User')->getCurrentUserId()));
            if (count($collection)) {
                $recruiter = $collection->current();
                if (count($recruiter->vacancyAssign)) {
                    $recruiterVacancyIds = $recruiter->vacancyAssign->getList('vacancy_id');
                    $condition[$tableName.'vacancy_id IN (?)'] = $recruiterVacancyIds;
                } else {
                    $condition['1 = 0'] = true;
                }
            }
        }

        if ($onlyActive) {
            $condition['('.$tableName.'status IS NULL OR '.$tableName.'status != ?)'] = HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED;
        }

        return $condition;
    }

    // рекрутеры с неограниченной ответственностью (менеджеры)
    public function getUnresponsible()
    {
        if ($collection = $this->getService('Responsibility')->fetchAll()) {
            $userIds = $collection->getList('user_id');
            if (count($collection = $this->fetchAll(array('user_id NOT IN (?)' => $userIds)))) {
                return $collection->getList('recruiter_id');
            }
        }
        return array();
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('специалист по подбору plural', '%s специалист по подбору', $count), $count);
    }

    public function getCurrentRecruiterInfo()
    {
        $recruiterId = $this->getService('User')->getCurrentUserId();
        return $this->getRecruiterInfo($recruiterId);
    }

    public function getRecruiterInfo($userId)
    {
        $recruiter = $this->getService('User')->findOne($userId);
        $result[] = $recruiter->getName();

        if ($position = $this->getOne($this->getService('Orgstructure')->fetchAll(array('mid = ?' => $recruiter->MID)))) {
            $subdivision = $position->getParent();
            $subdivisionParent = $subdivision->getParent();
            $result[] = $position->name;
            $result[] = $subdivision->name;
            $result[] = $subdivisionParent->name;
        }

        $result[] = $recruiter->Phone;
        $result[] = $recruiter->Fax;

        return implode('<br>', array_filter($result));
    }

}