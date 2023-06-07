<?php
class HM_Recruit_Newcomer_NewcomerService extends HM_Service_Abstract
{
    /* DEPRECATED */
    public function createByVacancy($vacancy)
    {
        if (count($vacancy->candidates)) {
            foreach ($vacancy->candidates as $candidate) {
                if ($candidate->result == HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS) {
                    $newcomer = parent::insert(array(
                        'vacancy_candidate_id' =>  $candidate->vacancy_candidate_id,
                        'name' => $vacancy->name,
                        'user_id' => $candidate->user_id, 
                        'profile_id' => $vacancy->profile_id,
                        'position_id' => $vacancy->position_id,
                        'created' => date('Y-m-d'),
                    ));
                    break;                            
                }
            }
        } 
        
        // по дефолту ответственные те же менеджеры, что и при подборе
        if (count($vacancy->recruiterAssign)) {
            foreach ($vacancy->recruiterAssign as $recruiterAssign) {
                $newcomerRecruiterAssign = $this->getService('RecruitNewcomerRecruiterAssign')->insert(array(
                    'newcomer_id' => $newcomer->newcomer_id,
                    'recruiter_id' => $recruiterAssign->recruiter_id,
                ));
            }
        }        
        
        $this->_create($newcomer);
        return $newcomer;
    }        
    
    public function createByPosition($position)
    {
        if (!count($position->user)) {
            return false;
        }

        $user = $position->user->current();
        $manager = $this->getService('Orgstructure')->getManager($position->soid);
        $departmentPath = $position->getOrgPath(false);

        $newcomer = parent::insert(array(
            'name' => $position->name,
            'user_id' => $user->MID,
            'profile_id' => $position->profile_id,
            'position_id' => $position->soid,
            'department_path' => $departmentPath,
            'manager_id' => $manager ? $manager->mid : null,
            'created' => date('Y-m-d'),
            'state' => HM_Recruit_Newcomer_NewcomerModel::PROCESS_STATE_OPEN,
            'status' => HM_Recruit_Newcomer_NewcomerModel::STATE_ACTUAL,
            'state_change_date' => date('Y-m-d H:i:s')
        ));


	$currentUserId = $this->getService('User')->getCurrentUserId();
	if (
		$currentUserId &&
		$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))
	) {
		$recruiter = $this->getService('Recruiter')->getOne($this->getService('Recruiter')->fetchAll(array('user_id = ?' => $currentUserId)));
        } else {
                // @todo: назначать дефолтного рекрутера
            $recruiter = $this->getService('Recruiter')->getOne($this->getService('Recruiter')->fetchAll());
        }
	
	    if ($recruiter) {
            $this->getService('RecruitNewcomerRecruiterAssign')->insert(array(
               	'newcomer_id' => $newcomer->newcomer_id,
	        'recruiter_id' => $recruiter->recruiter_id,
            ));
        }

        // далее понадобится
        $newcomer = $this->getService('RecruitNewcomer')->fetchOneDependence(array('Profile', 'Position'), array(
            'newcomer_id = ?' => $newcomer->newcomer_id
        ));

	    $profile = $this->getService('AtProfile')->find($position->profile_id)->current();
	    $newcomerDuration = ($profile->double_time) ?
            HM_Cycle_CycleModel::NEWCOMER_DURATION * 2 :
            HM_Cycle_CycleModel::NEWCOMER_DURATION;

        //персональный период оценки для KPI
        $cycleBegin = new HM_Date();
        $cycleEnd = clone $cycleBegin;
        $cycleEnd->add($newcomerDuration, HM_Date::MONTH);

        $cycle = $this->getService('Cycle')->insert(array(
            'name' => $newcomer->name,
            'begin_date' => $cycleBegin->get('YYYY-MM-dd'),
            'end_date' => $cycleEnd->get('YYYY-MM-dd'),
            'newcomer_id' => $newcomer->newcomer_id,
        ));

        $param = array(
            'newcomer_id' => $newcomer->newcomer_id,
        );

        $steps = $this->getStepsFromProcessesXml('adaptation');

        $params = array();
        foreach ($steps as $step) {
            $params[$step] = $param;
        }

        // @todo: похоже это продулбировано в addUserFromAdapting
        $this->getService('Process')->startProcess($newcomer, $params, false);


        // сгенерить event'ы
        if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence('Parent', $newcomer->position_id))) {

            $sessionValues = array(
                'name'          => $newcomer->name,
                'shortname'     => $newcomer->name,
                'description'   => '',
                'checked_items' => (string)$position->soid,
                'programm_type' => HM_Programm_ProgrammModel::TYPE_ADAPTING,
                'cycle_id' => $cycle->cycle_id,
            );

            // добавляем сессию, создаем  формы и запускаем сессию
            if ($session = $this->getService('AtSession')->insert($sessionValues, true)) {
                $newcomer->session_id = $session->session_id;
                $this->update(array(
                    'newcomer_id' => $newcomer->newcomer_id,
                    'session_id' => $newcomer->session_id,
                ));

                // назначаем адаптанту дефолтную программу адаптации (см db_dump2)
                // не путать со статичным процессом сессии адаптации
                if ($defaultProgramm = $this->getService('Programm')->getOne($this->getService('Programm')->getProgramms(0, 0, HM_Programm_ProgrammModel::TYPE_ADAPTING))) {

                    $programmData = array(
                        'name' => HM_Programm_ProgrammModel::getProgrammTitle(HM_Programm_ProgrammModel::TYPE_ADAPTING, HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER, $newcomer->name),
                        'item_id' => $newcomer->newcomer_id,
                        'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER,
                    );
                    $programm = $this->getService('Programm')->copy($defaultProgramm, $programmData);

                    $this->getService('Programm')->assignToUser($newcomer->user_id, $programm->programm_id);
                }

                $this->getService('AtSession')->addUserFromAdapting($newcomer);//, $newcomer->getProcess());
                $this->getService('AtSession')->startSession($newcomer->session_id);
            }

            // только здесь назначаются программы нач.обучения
            // при назначения профиля они сознательно не назначаются
            if ($position->profile_id) {
                $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $position->profile_id, HM_Programm_ProgrammModel::TYPE_ELEARNING);
                foreach ($programms as $programm) {
                    $this->getService('Programm')->assignToUser($newcomer->user_id, $programm->programm_id, true, $newcomer->newcomer_id);
                }
            }
        }

        $this->_sendNotification($newcomer);

        return $newcomer;
    }

    protected function _sendNotification($newcomer)
    {
        $newcomerUser = $this->getService('User')->find($newcomer->user_id)->current();
        if ($newcomer->manager_id) {
            if (count($collection = $this->getService('User')->find($newcomer->manager_id))) {

                $managerUser = $collection->current();
                $href = Zend_Registry::get('view')->serverUrl() .
                    Zend_Registry::get('view')->url(array(
                        'baseUrl' => 'recruit',
                        'module' => 'newcomer',
                        'controller' => 'kpi',
                        'action' => 'index',
                        'newcomer_id' => $newcomer->newcomer_id,
                    ), null, true);

                $url = '<a href="'.$href.'">'.$href.'</a>';

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ADAPTING_PLAN,
                    array(
                        'name_patronymic' => $managerUser->FirstName . ' ' . $managerUser->Patronymic,
                        'fio_newcomer' => $newcomerUser->getName(),
                        'url' => $url,
                        'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                    ),
                    'newcomer',
                    $newcomer->newcomer_id
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $managerUser->MID);
            }
        }
    }


    public function changeState($newcomerId, $state)
    {
        $newcomer = $this->getOne($this->find($newcomerId));
        /** @var HM_Process_ProcessService $processService */
        $processService = $this->getService('Process');
        switch ($state) {
            case HM_State_Abstract::STATE_STATUS_CONTINUING:
                $result = $processService->goToNextState($newcomer);
                break;
            case HM_State_Abstract::STATE_STATUS_FAILED:
                $result = $processService->goToFail($newcomer);
                break;
        }
        return $result;
    }


    public function getSubjectsGridSelect($newcomerId)
    {
        $select = parent::getSelect();

        $select->from('subjects', array(
            'subid' => 'subjects.subid',
            'name' => 'subjects.name',
            'mark' => 'cm.mark',
            'isLaborSafety' => 'subjects.is_labor_safety',
        ));

        $select->joinInner(
            array('s' => 'Students'),
            's.CID = subjects.subid',
            array()
        );

        $select->joinLeft(
            array('cm' => 'courses_marks'),
            'cm.cid = subjects.subid AND cm.mid = s.MID',
            array()
        );

        $select->where('s.newcomer_id = ?', $newcomerId);

//        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY)) {
//            $select->where("subjects.subid IN (?) OR subjects.base_id IN (?)", HM_Subject_SubjectModel::getBuiltInCourses());
//        } else {
//            $select->where("subjects.subid NOT IN (?) AND subjects.base_id NOT IN (?)", HM_Subject_SubjectModel::getBuiltInCourses());
//        }

        return $select;
    }


    public function delete($newcomerId)
    {
        if ($newcomer = $this->getOne($this->findDependence('Position', $newcomerId))) {

            $this->getService('RecruitNewcomerRecruiterAssign')->deleteBy(array('newcomer_id = ?' => $newcomerId));
            $this->getService('Cycle')->deleteBy(array('newcomer_id = ?' => $newcomerId));
            
            $this->getService('AtSession')->delete($newcomer->session_id);
            /*
            if (count($collection = $this->getService('Programm')->fetchAll(array(
                'item_id = ?' => $newcomerId,       
                'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER,       
                'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ADAPTING,       
            )))) {
                $programm = $collection->current();
                $collection = $this->getService('Programm')->delete($programm->programm_id);
            }
            */

            // отписать с программы нач.обучения
            $position = $newcomer->position ? $newcomer->position->current() : false;
            if ($position && $position->profile_id) {
                $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $position->profile_id, HM_Programm_ProgrammModel::TYPE_ELEARNING);
                foreach ($programms as $programm) {
                    $this->getService('ProgrammUser')->unassign($newcomer->user_id, $programm->programm_id);
                }
            }

            parent::delete($newcomerId);
        }
    }    
    
    public function getName($newcomerId)
    {
        $newcomer = $this->fetchAllDependence('User', array('newcomer_id = ?' => $newcomerId))->current();
        $user = $newcomer->user->current();
        return $user->getName();
    }

    public function completeSession($newcomerIds)
    {
        if (!is_array($newcomerIds)) $newcomerIds = array($newcomerIds);
        if (count($collection = $this->fetchAll(array('newcomer_id IN (?)' => $newcomerIds)))) {
            foreach ($collection as $newcomer) {
                parent::update(
                    array(
                        'newcomer_id' => $newcomer->newcomer_id,
                        'state' => HM_Recruit_Newcomer_NewcomerModel::PROCESS_STATE_COMPLETE,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToNextState($newcomer);
            }
        }
        return true;
    }


    public function abortSession($newcomerIds)
    {
        if (!is_array($newcomerIds)) $newcomerIds = array($newcomerIds);
        if (count($collection = $this->fetchAll(array('newcomer_id IN (?)' => $newcomerIds)))) {
            foreach ($collection as $newcomer) {
                $this->getService('Process')->goToFail($newcomer);

                parent::update(
                    array(
                        'newcomer_id' => $newcomer->newcomer_id,
                        'status' => HM_Recruit_Newcomer_NewcomerModel::STATE_CLOSED,
                        'state_change_date' => date('Y-m-d'),
                        'result' => HM_Recruit_Newcomer_NewcomerModel::RESULT_FAIL_MANAGER
                    )
                );
            }
        }
        return true;
    }
        
}
?>