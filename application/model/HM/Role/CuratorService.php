<?php
class HM_Role_CuratorService extends HM_Service_Abstract
{

    public function userIsCurator($userId)
    {
        $res = false;
        if($this->countAll('MID = '. (int) $userId) > 0) $res = true;
        return $res;
    }

    public function getResponsibilityOptions($userId)
    {
        $options = $this->getOne($this->getService('CuratorOptions')->fetchAll('user_id = ' . (int) $userId));
        if(!$options){
            $options = array('user_id' => (int) $userId, 'unlimited_projects' => 1, 'unlimited_classifiers' => 1, 'assign_new_projects' => 0);
        }
        else{
            $options = $options->getValues();
        }
        return $options;
    }

    /**
     * Устанавливает параметры областей ответственности
     * @param array $options('user_id', 'unlimited_courses', 'unlimited_projects', 'assign_new_courses')
     */
    public function setResponsibilityOptions($options)
    {
        if($this->getOne($this->getService('CuratorOptions')->find($options['user_id']))){
            $this->getService('CuratorOptions')->update($options);
        }else{
            $this->getService('CuratorOptions')->insert($options);
        }
    }

    /**
     * Проверяет наличие области ответственности
     *
     * @param unknown_type $userId
     * @param unknown_type $projectId
     * @return string|string
     */
    public function isProjectResponsibility($userId, $projectId)
    {
        $options = $this->getResponsibilityOptions($userId);
        if($options['unlimited_projects'] == 1)
        {
            return true;
        }else{
            $res = $this->countAll(
                $this->quoteInto(
                    array('MID = ?', ' AND project_id = ?'),
                    array($userId, $projectId)
                )
            );
            if($res > 0) return true;
        }
        return false;
    }

    public function isClassifierResponsibility($userId, $classifierId)
    {
        $options = $this->getResponsibilityOptions($userId);
        $res = $options['unlimited_classifiers'];
        if(!$res) $res = $this->getService('CuratorResponsibilities')->isResponsibilitySet($userId, $classifierId);
        return $res;
    }
    
    /**
     * Добавляет область ответственности
     * 
     * @param unknown_type $userId
     * @param unknown_type $projectId
     * @return string|string
     */
    public function addProjectResponsibility($userId, $projectId)
    {
        $res = $this->fetchAll(array('MID = ?' => $userId, 'project_id = ?' => $projectId));

        if(count($res) == 0){
            $this->insert(
                array(
                	'MID' => $userId,
                    'project_id' => $projectId
                )
            );
            return true;
        }
        return false;
        
    }

    public function deleteProjectsResponsibilities($userId)
    {
        if($this->countAll($this->quoteInto(array('MID = ? AND project_id = 0'), array($userId))) == 0)
            $this->insert(array('MID' => $userId,'project_id' => '0'));
        return $this->deleteBy($this->quoteInto(array('MID = ? AND project_id > 0'), array($userId)));
    }

    /**
     * deprecated! use $this->getService('CuratorResponsibility')->deleteResponsibilities($userId);
     *
     * @param unknown_type $userId
     * @param unknown_type $projectId
     * @return string|string
     */
    public function deleteClassifiersResponsibility($userId)
    {
        return $this->getService('CuratorResponsibility')->deleteResponsibilities($userId);
    }

    /**
     * По ид пользователя возвращаем коллекцию моделей областей ответственности
     * (т.е. учебных курсов)
     *
     * @param unknown_type $userId
     * @return HM_Collection
     */
    public function getProjectsResponsibilities($userId)
    {
        
        $options = $this->getResponsibilityOptions($userId);
        
        if($options['unlimited_projects'] == 1)
            return $this->getService('Project')->fetchAll(null, 'projects.name');
        else
            return $this->getAssignedProjectsResponsibilities($userId);
        
    }
    
     /**
     * По ид пользователя возвращаем коллекцию АКТИВНЫХ моделей областей ответственности
     * (т.е. учебных курсов дата окончания которых > сегодня или их время неограничено)
     *
     * @param unknown_type $userId
     * @return HM_Collection
     */
    public function getActiveProjectsResponsibilities($userId)
    {
        
        $options = $this->getResponsibilityOptions($userId);
        
        if($options['unlimited_projects'] == 1)
            return $this->getService('Project')->fetchAll("period = 1 OR end > NOW() OR end IS NULL", 'projects.name');
            //return $this->getService('Project')->fetchAll("end > NOW() OR end = '0000-00-00' OR end IS NULL", 'projects.name');
        else
            return $this->getAssignedProjectsResponsibilities($userId);
        
    }


    public function getAssignedProjectsResponsibilities($userId)
    {
    	$curator = $this->getService('User')->fetchAllManyToMany('Project','Curator', array('MID = ?' => $userId)/*, 'projects.name'*/); // wtf???
    	return ($curator[0]->projects) ? $curator[0]->projects : new HM_Collection();
        
    }

    public function getAssignedClassifiersResponsibilities($userId)
    {
    	$curator = $this->getService('User')->fetchAllManyToMany('Classifier','CuratorResponsibility', array('MID = ?' => $userId));
    	return ($curator[0]->classifiers) ? $curator[0]->classifiers : new HM_Collection();
        
    }

    public function getProjects($userId)
    {
        return $this->getProjectsResponsibilities($userId);
    }

}