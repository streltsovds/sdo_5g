<?php
class HM_Hr_Rotation_RotationService extends HM_Service_Abstract
{
    public function createByPosition($values)
    {
        $user = $this->getService('User')->getOne(
            $this->getService('User')->find($values['user_id'][0])
        );

        $position = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->find($values['position_id'])
        );

        $values['user_id']    = $values['user_id'][0];
        $values['begin_date'] = date('Y-m-d', strtotime($values['begin_date']));
        $values['end_date']   = date('Y-m-d', strtotime($values['end_date']));
        $values['state_change_date'] = date('Y-m-d');
        $values['state_id']   = HM_Hr_Rotation_RotationModel::PROCESS_STATE_OPEN;
        $values['name']   = sprintf('%s - %s', $position->name, $user->LastName);

        $rotation = $this->getService('HrRotation')->insert($values);

        $param = array(
            'rotation_id' => $rotation->rotation_id,
        );

        $steps = $this->getStepsFromProcessesXml('rotation');

        $params = array();
        foreach ($steps as $step) {
            $params[$step] = $param;
        }

        $this->getService('Process')->startProcess($rotation, $params);

        if ($hr = $this->getService('Recruiter')->getOne(
            $this->getService('Recruiter')->fetchAll(array('user_id = ?' => $this->getService('User')->getCurrentUserId())))
        ) {
            $reserveRecruiterAssign = $this->getService('HrRotationAssignRecruiter')->insert(array(
                'rotation_id' => $rotation->rotation_id,
                'recruiter_id' => $hr->recruiter_id,
            ));
        }

//        // сгенерить event'ы
//        if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence('Parent', $rotation->position_id))) {
//
//            $sessionValues = array(
//                'name'          => $rotation->name,
//                'shortname'     => $rotation->name,
//                'description'   => '',
//                'checked_soids' => (string)$position->soid,
//                'programm_type' => HM_Programm_ProgrammModel::TYPE_ROTATION,
//            );
//
//            // добавляем сессию, создаем  формы и запускаем сессию
//            if ($session = $this->getService('AtSession')->insert($sessionValues, true)) {
//                $rotation->session_id = $session->session_id;
//                $this->update($rotation->getData());
//            }
//
//            $programmData = array(
//                'name' => HM_Programm_ProgrammModel::getProgrammTitle(HM_Programm_ProgrammModel::TYPE_ROTATION, HM_Programm_ProgrammModel::ITEM_TYPE_ROTATION, $rotation->name),
//                'item_id' => $rotation->rotation_id,
//                'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_ROTATION,
//            );
//            if ($programm = $this->getService('Programm')->getOne(
//                $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $rotation->profile_id, HM_Programm_ProgrammModel::TYPE_ROTATION)
//            )) {
//                $programm = $this->getService('Programm')->copy($programm, $programmData);
//            } else {
//                // даже если на уровне профиля не задана программа, создаём пустую
//                $programm = $this->getService('Programm')->insert($programmData);
//            }
//
//            if ($programm = $this->getService('Programm')->getOne($this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_ROTATION, $rotation->rotation_id, HM_Programm_ProgrammModel::TYPE_ROTATION))) {
//                $this->getService('Programm')->assignToUser($rotation->user_id, $programm->programm_id);
//            }
//
//            $this->getService('AtSession')->addUserFrom(HM_Programm_ProgrammModel::TYPE_ROTATION, $rotation, $rotation->getProcess());
//            $this->getService('AtSession')->startSession($rotation->session_id);
//        }

        return $rotation;
    }

    public function planSession($rotationIds)
    {
        if (!is_array($rotationIds)) $rotationIds = array($rotationIds);
        if (count($collection = $this->fetchAll(array('rotation_id IN (?)' => $rotationIds)))) {
            foreach ($collection as $rotation) {

                parent::update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'state_id' => HM_Hr_Rotation_RotationModel::PROCESS_STATE_PLAN,
                        'state_change_date' => date('Y-m-d')
                    )
                );
                $this->getService('Process')->goToNextState($rotation);
            }
        }
        return true;
    }

    public function publishSession($rotationIds)
    {
        if (!is_array($rotationIds)) $rotationIds = array($rotationIds);
        if (count($collection = $this->fetchAll(array('rotation_id IN (?)' => $rotationIds)))) {
            foreach ($collection as $rotation) {
                parent::update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'status' => HM_Hr_Rotation_RotationModel::STATE_ACTUAL,
                        'state_id' => HM_Hr_Rotation_RotationModel::PROCESS_STATE_PUBLISH,
                        'state_change_date' => date('Y-m-d')
                    )
                );
                $this->getService('Process')->goToNextState($rotation);
            }
        }
        return true;
    }




    public function resultSession($rotationIds)
    {
        if (!is_array($rotationIds)) $rotationIds = array($rotationIds);
        if (count($collection = $this->fetchAll(array('rotation_id IN (?)' => $rotationIds)))) {
            foreach ($collection as $rotation) {
                parent::update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'state_id' => HM_Hr_Rotation_RotationModel::PROCESS_STATE_RESULT,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToNextState($rotation);
            }
        }
        return true;
    }



    public function completeSession($rotationIds)
    {
        if (!is_array($rotationIds)) $rotationIds = array($rotationIds);
        if (count($collection = $this->fetchAll(array('rotation_id IN (?)' => $rotationIds)))) {
            foreach ($collection as $rotation) {
                parent::update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'state_id' => HM_Hr_Rotation_RotationModel::PROCESS_STATE_COMPLETE,
                        'status' => HM_Hr_Rotation_RotationModel::STATE_CLOSED,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToNextState($rotation);
            }
        }
        return true;
    }


    public function abortSession($rotationIds)
    {
        if (!is_array($rotationIds)) $rotationIds = array($rotationIds);
        if (count($collection = $this->fetchAll(array('rotation_id IN (?)' => $rotationIds)))) {
            foreach ($collection as $rotation) {

                parent::update(
                    array(
                        'rotation_id' => $rotation->rotation_id,
                        'status' => HM_Hr_Rotation_RotationModel::STATE_CLOSED,
                        'state_change_date' => date('Y-m-d')
                    )
                );

                $this->getService('Process')->goToFail($rotation);
            }
        }
        return true;
    }

    /*
    public function updateProcess($rotationId)
    {
        $process = false;
        $collection = $this->getService('Programm')->fetchAllDependence(array('Process', 'Event'), array(
            'item_id = ?' => $rotationId,
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER,
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ADAPTING,
        ));

        if (count($collection)) {

            $programm = $collection->current();
            if (count($programm->process)) {
                $process = $programm->process->current();
            } else {
                $process = $this->getService('Process')->insert(array(
                    'type' => HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING,
                    'programm_id' => $programm->programm_id,
                ));
            }
            //$process->update($programm);
        }
        return array($process, $programm);
    }
    */

//    public function getSubjectsGridSelect($rotationId)
//    {
//        $select = parent::getSelect();
//
//        $select->from('subjects', array(
//            'subid' => 'subjects.subid',
//            'name' => 'subjects.name',
//            'mark' => 'cm.mark',
//        ));
//
//        $select->joinInner(
//            array('s' => 'Students'),
//            's.CID = subjects.subid',
//            array()
//        );
//
//        $select->joinLeft(
//            array('cm' => 'courses_marks'),
//            'cm.cid = subjects.subid AND cm.mid = s.MID',
//            array()
//        );
//
//        $select->where('s.rotation_id = ?', $rotationId);
//
//        return $select;
//    }
//
//
//    public function delete($rotationId)
//    {
//        if ($rotation = $this->getOne($this->find($rotationId))) {
//
//            $this->getService('RecruitRotationRecruiterAssign')->deleteBy(array('rotation_id = ?' => $rotationId));
//            $this->getService('Cycle')->deleteBy(array('rotation_id = ?' => $rotationId));
//
//            $this->getService('AtSession')->delete($rotation->session_id);
//            /*
//            if (count($collection = $this->getService('Programm')->fetchAll(array(
//                'item_id = ?' => $rotationId,
//                'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER,
//                'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ADAPTING,
//            )))) {
//                $programm = $collection->current();
//                $collection = $this->getService('Programm')->delete($programm->programm_id);
//            }
//            */
//            parent::delete($rotationId);
//        }
//    }

    public function getName($rotationId)
    {
        $rotation = $this->fetchAllDependence('User', array('rotation_id = ?' => $rotationId))->current();
        $user = $rotation->user->current();
        return $user->getName();
    }

}