<?php
class HM_View_Helper_ProjectPreview extends HM_View_Helper_Abstract
{

    public function projectPreview($project, $marks,  $graduatedList, $participantCourseData)
    {

/*        $this->view->allowEdit = $this->view->allowDelete = in_array(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_TEACHER)
        );*/

/*        $this->view->showScore = (in_array(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT)
        ));*/
        $userId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();

        $this->view->showScore = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        if(
            Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT))
            ){
            $this->view->action = 'my';
        }else{
            $this->view->action = 'index';
        }
       $this->view->disperseName = '';
       $this->view->disperse = $this->view->graduated = false;

       if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

            $graduatedItem = ($graduatedList)? $graduatedList->exists('CID', $project->projid) : FALSE;

            if($graduatedItem === false){
                if (($project->reg_type == HM_Project_ProjectModel::REGTYPE_FREE || $project->reg_type == HM_Project_ProjectModel::REGTYPE_SELF_ASSIGN) && $project->isAccessible()){
                    $this->view->disperse = true;
                }
            }else{
                $this->view->graduated = true;
            }

            // пока не заработал автоматический перевод в прош.обучение по cron'у - имитируем его на лету
            if ((Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
                !$graduatedItem &&
                strtotime($participantCourseData['end']) &&
                (strtotime($participantCourseData['end']) < time()) &&
                (($project->period == HM_Project_ProjectModel::PERIOD_FIXED) || ($project->period == HM_Project_ProjectModel::PERIOD_FIXED && $project->period_restriction_type == HM_Project_ProjectModel::PERIOD_RESTRICTION_STRICT))
            ) {

                $isGraduated = Zend_Registry::get('serviceContainer')->getService('Graduated')->fetchAll(
                    array(
                        'CID = ?' => $project->projid,
                        'MID = ?' => $userId
                    )
                );

                if (!count($isGraduated)) {
                    Zend_Registry::get('serviceContainer')->getService('Graduated')->insert(
                        array(
                             'MID'            => $userId,
                             'CID'            => $project->projid,
                             'status'         => HM_Role_GraduatedModel::STATUS_FAIL,
                             'is_lookable'    => HM_Role_GraduatedModel::LOOKABLE,
                        )
                    );
                }

                $this->view->disperse = false; // не выводить "Завершить обучение"
                $this->view->graduated = true; // выводить красным "Курс завершен"
            }

     /*      if($project->reg_type == HM_Project_ProjectModel::REGTYPE_FREE || $project->reg_type == HM_Project_ProjectModel::REGTYPE_SELF_ASSIGN){
               $this->view->disperse = true;
           }elseif($project->reg_type == HM_Project_ProjectModel::REGTYPE_ASSIGN_ONLY && isset($graduatedList[$project->projid])){
               $this->view->disperse = true;
           }*/
       }

        $this->view->currentUserId = $userId;
        $this->view->project = $project;
        $this->view->participantCourseData = $participantCourseData;
		//$this->view->teacher = array('user_id' => $lesson->teacher[0]->MID, 'fio' => trim($lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic));

        return $this->view->render('project-preview.tpl');
    }
}