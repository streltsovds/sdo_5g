<?php
class HM_Hr_Reserve_ReserveService extends HM_Service_Abstract
{
    public function createByPosition($reservePosition, $user, $cycleId = false)
    {
        $targetPosition = $this->getService('Orgstructure')->find($reservePosition->position_id)->current();
        $manager = $this->getService('Orgstructure')->getManager($targetPosition->soid);

        $reserve = parent::insert(array(
            'name' => sprintf('%s - %s', $reservePosition->name, $user->LastName),
            'user_id' => $user->MID,
            'state_id' => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_OPEN,
            'state_change_date' => date('Y-m-d'),
            'profile_id' => $targetPosition->profile_id,
            'position_id' => $targetPosition->soid,
            'reserve_position_id' => $reservePosition->reserve_position_id,
            'manager_id' => $manager ? $manager->mid : null,
            'cycle_id' => $cycleId,
            'created' => date('Y-m-d'),
//            'evaluation_user_id' => $this->getService('User')->getCurrentUserId()
        ));

        // далее понадобится
        $reserve = $this->getService('HrReserve')->fetchOneDependence(array('Profile', 'Position', 'ReservePosition', 'Cycle'), array(
            'reserve_id = ?' => $reserve->reserve_id
        ));

        $param = array(
            'reserve_id' => $reserve->reserve_id,
        );

        $steps = $this->getStepsFromProcessesXml('reserve');

        $params = array();
        foreach ($steps as $step) {
            $params[$step] = $param;
        }

        $this->getService('Process')->startProcess($reserve, $params);

        // назначить рекрутеров, указанных в должности КР
        if (count($reserve->reservePosition)) {
            $reservePosition = $reserve->reservePosition->current();
            $userIds = unserialize($reservePosition->recruiters);
            if (is_array($userIds) && count($userIds)) {

                $collection = $this->getService('User')->fetchAllDependence('Recruiter', array('MID IN (?)' => $userIds));
                foreach ($collection as $user) {
                    if (count($user->recruiter) && ($user->blocked != 1)) {
                        $recruiter = $user->recruiter->current();
                        $this->getService('HrReserveAssignRecruiter')->insert(array(
                            'reserve_id' => $reserve->reserve_id,
                            'recruiter_id' => $recruiter->recruiter_id,
                        ));
                    }
                }
            }
        }


        // сгенерить event'ы
        if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence('Parent', $reserve->position_id))) {

            $sessionValues = array(
                'name'          => $reserve->name,
                'shortname'     => $reserve->name,
                'description'   => '',
                'checked_items' => (string) $position->soid,
                'programm_type' => HM_Programm_ProgrammModel::TYPE_RESERVE,
                'cycle_id' => $reserve->cycle ? $reserve->cycle->current()->cycle_id : 0,
            );

            // добавляем сессию, создаем  формы и запускаем сессию
            if ($session = $this->getService('AtSession')->insert($sessionValues, true)) {
                $this->update(array(
                    'reserve_id' => $reserve->reserve_id,
                    'session_id' => (int) $session->session_id,
                ));
                // Надо обновить даные в переменной $reserve после внесённых изменений
                $reserve = $this->getService('HrReserve')->fetchOneDependence(array('Profile', 'Position', 'Cycle', 'ReservePosition'), array(
                    'reserve_id = ?' => $reserve->reserve_id
                ));
        }

            $programmData = array(
                'name' => HM_Programm_ProgrammModel::getProgrammTitle(HM_Programm_ProgrammModel::TYPE_RESERVE, HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE, $reserve->name),
                'item_id' => $reserve->reserve_id,
                'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE,
            );
            if ($programm = $this->getService('Programm')->getOne(
                $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $reserve->profile_id, HM_Programm_ProgrammModel::TYPE_RESERVE)
            )) {
                $programm = $this->getService('Programm')->copy($programm, $programmData);
            } else {
                // даже если на уровне профиля не задана программа, создаём пустую
                $programm = $this->getService('Programm')->insert($programmData);
            }

            if ($programm = $this->getService('Programm')->getOne($this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE, $reserve->reserve_id, HM_Programm_ProgrammModel::TYPE_RESERVE))) {
                $this->getService('Programm')->assignToUser($reserve->user_id, $programm->programm_id);
            }

            $this->getService('AtSession')->addUserFromReserve($reserve);
            $this->getService('AtSession')->startSession($reserve->session_id, false);
        }

        return $reserve;
    }        

    public function changeState($reserveId, $state)
    {
        $reserve = $this->getOne($this->find($reserveId));
        /** @var HM_Process_ProcessService $processService */
        $processService = $this->getService('Process');
        switch ($state) {
            case HM_State_Abstract::STATE_STATUS_CONTINUING:
                $result = $processService->goToNextState($reserve);
                break;
            case HM_State_Abstract::STATE_STATUS_FAILED:
                $result = $processService->goToFail($reserve);
                break;
        }
        return $result;
    }


    public function getSubjectsGridSelect($reserveId)
    {
        $select = parent::getSelect();

        $laborSafetyIds = implode(',', HM_Subject_SubjectModel::getBuiltInCourses());
        $select->from('subjects', array(
            'subid' => 'subjects.subid',
            'name' => 'subjects.name',
            'mark' => 'cm.mark',
            'isLaborSafety' => new Zend_Db_Expr("CASE WHEN (subjects.subid IN ({$laborSafetyIds}) OR subjects.base_id IN ({$laborSafetyIds})) THEN 1 ELSE 0 END"),
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

        $select->where('s.reserve_id = ?', $reserveId);

//        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY)) {
//            $select->where("subjects.subid IN (?) OR subjects.base_id IN (?)", HM_Subject_SubjectModel::getBuiltInCourses());
//        } else {
//            $select->where("subjects.subid NOT IN (?) AND subjects.base_id NOT IN (?)", HM_Subject_SubjectModel::getBuiltInCourses());
//        }

        return $select;
    }


    public function delete($reserveId)
    {
        if ($reserve = $this->getOne($this->findDependence('Position', $reserveId))) {

            $this->getService('HrReserveAssignRecruiter')->deleteBy(array('reserve_id = ?' => $reserveId));
            $this->getService('Cycle')->deleteBy(array('reserve_id = ?' => $reserveId));
            
            $this->getService('AtSession')->delete($reserve->session_id);
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
            $position = $reserve->position ? $reserve->position->current() : false;
            if ($position && $position->profile_id) {
                $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $position->profile_id, HM_Programm_ProgrammModel::TYPE_ELEARNING);
                foreach ($programms as $programm) {
                    $this->getService('ProgrammUser')->unassign($reserve->user_id, $programm->programm_id);
                }
            }

            parent::delete($reserveId);
        }
    }    
    
    public function getName($reserveId)
    {
        $reserve = $this->fetchAllDependence('User', array('reserve_id = ?' => $reserveId))->current();
        $user = $reserve->user->current();
        return $user->getName();
    }

    public function planSession($reserveIds)
    {
        if (!is_array($reserveIds)) $reserveIds = array($reserveIds);
        if (count($collection = $this->fetchAll(array('reserve_id IN (?)' => $reserveIds)))) {
            foreach ($collection as $reserve) {

                parent::update(
                    array(
                        'reserve_id' => $reserve->reserve_id,
                        'state_id' => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_PLAN,
                        'state_change_date' => date('Y-m-d')
                    )
                );
                $this->getService('Process')->goToNextState($reserve);
            }
        }
        return true;
    }

    public function publishSession($reserveIds)
    {
        if (!is_array($reserveIds)) $reserveIds = array($reserveIds);
        if (count($collection = $this->fetchAll(array('reserve_id IN (?)' => $reserveIds)))) {
            foreach ($collection as $reserve) {
                parent::update(
                    array(
                        'reserve_id' => $reserve->reserve_id,
                        'status' => HM_Hr_Reserve_ReserveModel::STATE_ACTUAL,
                        'state_id' => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_PUBLISH,
                        'state_change_date' => date('Y-m-d')
                    )
                );
                $this->getService('Process')->goToNextState($reserve);
            }
        }
        return true;
    }




    public function resultSession($reserveIds)
    {
        if (!is_array($reserveIds)) $reserveIds = array($reserveIds);
        if (count($collection = $this->fetchAll(array('reserve_id IN (?)' => $reserveIds)))) {
            foreach ($collection as $reserve) {
                parent::update(
                    array(
                        'reserve_id' => $reserve->reserve_id,
                        'state_id' => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_RESULT,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToNextState($reserve);
            }
        }
        return true;
    }

    public function completeSession($reserveIds)
    {
        if (!is_array($reserveIds)) $reserveIds = array($reserveIds);
        if (count($collection = $this->fetchAll(array('reserve_id IN (?)' => $reserveIds)))) {
            foreach ($collection as $reserve) {
                parent::update(
                    array(
                        'reserve_id' => $reserve->reserve_id,
                        'state_id' => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_COMPLETE,
                        'status' => HM_Hr_Reserve_ReserveModel::STATE_CLOSED,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToNextState($reserve);
            }
        }
        return true;
    }


    public function abortSession($reserveIds)
    {
        if (!is_array($reserveIds)) $reserveIds = array($reserveIds);
        if (count($collection = $this->fetchAll(array('reserve_id IN (?)' => $reserveIds)))) {
            foreach ($collection as $reserve) {
                parent::update(
                    array(
                        'reserve_id' => $reserve->reserve_id,
                        'status' => HM_Hr_Reserve_ReserveModel::STATE_CLOSED,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToFail($reserve);
            }
        }
        return true;
    }
        
}
