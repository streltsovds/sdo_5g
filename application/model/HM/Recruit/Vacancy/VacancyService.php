<?php
class HM_Recruit_Vacancy_VacancyService extends HM_Service_Abstract
{
    protected $vacancyInfoCache = array();
    
    public function addEnvironment($data)
    {
        /********* init Session ********/
        $description = $this->getService('Option')->getOption('sessionComment', HM_Option_OptionModel::MODIFIER_RECRUIT);
        list($position,) = explode($data['name']);
        $sessionValues = array(
            'name'          => $data['name'],
            'shortname'     => $position,
            'begin_date'    => $data['open_date'], // дату окончания невозможно вычислить, т.к. кандидаты проходят программу несинхронно
            'description'   => $description?$description:'',
            'initiator_id'  => $this->getService('User')->getCurrentUserId(),
            'checked_items' => $data['position_id'],
            'item_type'     => 'recruit',
            'programm_type' => HM_Programm_ProgrammModel::TYPE_RECRUIT,
        );
        $session = $this->getService('AtSession')->insert($sessionValues, true);
        $data['session_id'] = $session->session_id;

        $vacancy = parent::update($data);      

        /********* init Programm ********/
        
        $programmData = array(
            'name' => HM_Programm_ProgrammModel::getProgrammTitle(HM_Programm_ProgrammModel::TYPE_RECRUIT, HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $vacancy->name),
            'item_id' => $vacancy->vacancy_id,
            'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY,
        );
        if ($programm = $this->getService('Programm')->getOne(
                $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $data['profile_id'], HM_Programm_ProgrammModel::TYPE_RECRUIT)
        )) {
            $programm = $this->getService('Programm')->copy($programm, $programmData);
        } else {
            // даже если на уровне профиля не задана программа, создаём пустую
            $programm = $this->getService('Programm')->insert($programmData);
        }
        
        /********* init Process ********/
        
        // это очень важное место
        // здесь задаются все параметры, которые можно будет использовать внутри Action 
        // почему-то надо задавать отдельно для каждого шага..
        $this->getService('Process')->startProcess($vacancy, array(
            'HM_Recruit_Vacancy_State_Open' => array(
                'vacancy_id' => $vacancy->vacancy_id,
            ),
            /*'HM_Recruit_Vacancy_State_Search' => array(
                'vacancy_id' => $vacancy->vacancy_id,
            ),*/
            'HM_Recruit_Vacancy_State_Assessment' => array(
                'vacancy_id' => $vacancy->vacancy_id,
            ),
            'HM_Recruit_Vacancy_State_Hire' => array(
                'vacancy_id' => $vacancy->vacancy_id,
            ),
        ));

        return $vacancy;
    }


    public function insert($data, $unsetNull = true)
    {
        $justAdd = isset($data['justAdd']) ? $data['justAdd'] : false;//Управление способом вставки
        unset($data['justAdd']);        

        if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence('Parent', $data['position_id']))) {
            if (count($position->parent)) {
                $department = $position->parent->current();
            }
            $data['department_path'] = $position->getOrgPath(false);
        }

        /********* init Vacancy itself ********/
        $vacancy = parent::insert($data);

        if(!$justAdd) {

            if ($recruiter = $this->getService('Recruiter')->getOne($this->getService('Recruiter')->fetchAll(array('user_id = ?' => $this->getService('User')->getCurrentUserId())))) {
                $recruiterId = $recruiter->recruiter_id;
            } else {
                $recruiterId = $this->_getDefaultRecruiter($position);
            }

            if ($recruiterId) $this->getService('RecruitVacancyRecruiterAssign')->assign($vacancy->vacancy_id, $recruiterId);

            $vacancy = $this->addEnvironment($vacancy->getValues());
        }

        return $vacancy;
    }
    
    public function delete($vacancyId)
    {
        if ($vacancy = $this->getOne($this->findDependence('CandidateAssign', $vacancyId))) {

            // работает медленнее, зато гарантировано всё что нужно удаляет
            if (count($vacancy->candidates)) {
                foreach ($vacancy->candidates as $vacancyCandidate) {
                    $this->getService('RecruitVacancyAssign')->unassign($vacancyCandidate->vacancy_candidate_id);
                }
            }
            $this->getService('RecruitVacancyResumeHhIgnore')->deleteBy(array('vacancy_id = ?' => $vacancyId)); 
            $this->getService('RecruitVacancyRecruiterAssign')->deleteBy(array('vacancy_id = ?' => $vacancyId));
            
            $this->getService('AtSession')->delete($vacancy->session_id);
            
            if (count($collection = $this->getService('Programm')->fetchAll(array(
                'item_id = ?' => $vacancyId,       
                'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY,       
                'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_RECRUIT,       
            )))) {
                $programm = $collection->current();
                $collection = $this->getService('Programm')->delete($programm->programm_id);
            }

//            parent::delete($vacancyId);
            parent::update(array(
                'vacancy_id' => $vacancyId,
                'deleted' => 1,
                'status'  => HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED
            ));
        }
    }

    public function getUsedAssessmentIds($vacancyId)
    {
        $result = array();
        $vacancy = $this->getOne($this->getService('RecruitVacancy')->fetchAllDependence('AssessmentRef',$vacancyId));
        if ( $vacancy ) {
            $curRoles = $vacancy->competence_ref;
            foreach($curRoles as $roleItem) {
                $result[] = $roleItem->competence_role_id;
            }
        }

        return $result;
    }

    public function getAllAsArray()
    {
        $return = array();
        if ($vacancies = $this->fetchAll()) {
            foreach ($vacancies as $vacancy) {
                $return[$vacancy->vacancy_id] = $vacancy->name;
            }
        }
        return $return;
    }

    /**
     * Запуск сессии подбора
     * @param int | HM_Recruit_Vacancy_VacancyModel $vacancy
     * @param null | HM_At_Session_SessionModel $session
     * @return bool
     */
    public function startSession($vacancy, $session = null)
    {
        $state = HM_At_Session_SessionModel::STATE_ACTUAL;
        $this->_updateVacancySession($vacancy, $session, $state);

        // нет необходимости добавлять кандидатов, они уже добавлены
        // изменение статуса "отклик=>активный" теперь происходит не по смене БП вакансиии, а персонально
/*
        $vacancyCandidates = $this->getService('RecruitVacancyAssign')->fetchAllDependence(array('Candidate', 'Vacancy'), array(
            'vacancy_id = ?' => $vacancy->vacancy_id,
            'status != ?' => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON
        ));

        if (count($vacancyCandidates)) {
            
            $process = $this->getService('RecruitVacancyAssign')->updateUserProcess($vacancy->vacancy_id);
            
            foreach ($vacancyCandidates as $vacancyCandidate) {
                if (count($vacancyCandidate->candidate)) {
                    // в перерывах между сессиями подбора внешние кандидаты заблокированы (в HM_Recruit_Vacancy_Assign_AssignProcess::onProcessComplete())
                    $vacancyCandidate->candidate->current()->setAutoBlocked(HM_User_UserModel::BLOCKED_OFF);
                }
                $this->getService('AtSession')->addUserFromVacancy($vacancyCandidate, $process);
            }
        }
*/
        return true;
    }

    /**
     * @param $vacancyId
     */
    public function stopSession($vacancy, $session = null)
    {
        $state = HM_At_Session_SessionModel::STATE_CLOSED;
        $this->_updateVacancySession($vacancy, $session, $state);

        return true;
    }
    
    // просто меняет status'ы в базе
    // вся логика в onProcessComplete() 
    protected function _updateVacancySession($vacancy, $session, $state)
    {
        if ($vacancy instanceof HM_Recruit_Vacancy_VacancyModel) {
            $vacancyId = $vacancy->vacancy_id;
        } else {
            $vacancyId = (int) $vacancy;
            $vacancy   = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->find($vacancyId));
        }

        if (!$vacancy) return false;

        if (!$session && $vacancy->session_id) {
            $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find(intval($vacancy->session_id)));
        }

        $vacancy->status = $state;
        $this->getService('RecruitVacancy')->update($vacancy->getValues());

        if ($session && $session instanceof HM_At_Session_SessionModel) {
            $session->state = $state;
            $this->getService('AtSession')->update($session->getValues());
        }
        return true;
    }
    
    /**
     * Добавление пользователя в оценочную сессию, связанную с сессией подбора
     * 
     * @param (int) $vacancyId - идентификатор вакансии
     * @param (int) $mid - идентификатор пользователя
     * 
     * @return bool
     */
    public function addUserToSession($vacancyId, $mid)
    {
        // кэшируем информацию по вакансии на случай многократного использования сессии
        if (!isset($this->vacancyInfoCache[$vacancyId])) {
            $select = $this->getSelect();
            
            $select->from(array('v' => 'recruit_vacancies'), array(
                'v.session_id',
                'v.position_id',
                'p.profile_id'
            ));
            $select->joinLeft(array('p' => 'at_profiles'), 'p.vacancy_id = v.vacancy_id', array());
            $select->where('v.vacancy_id = ?', $vacancyId);
            $result = $select->query()->fetchAll();

            if (!count($result)) {
                $this->vacancyInfoCache[$vacancyId] = false;
            }
        }

        $vacancyInfo = $this->vacancyInfoCache[$vacancyId];

        if (!$vacancyInfo) {
            return false;
        }
        
        if (!$vacancyInfo['position_id'] || !$vacancyInfo['profile_id'] || !$vacancyInfo['session_id']) {
            return false;
        }

        $messages = array();
        $this->getService('AtSession')->addUser($vacancyInfo['session_id'], $mid, $vacancyInfo['position_id'], $vacancyInfo['profile_id'], $messages);
        
        return true;
    }

    // сложная логика определения компетенций вакансии
    // нужно в первую очередь для автопоиска кандидатов - чтоб понять по каким критериям искать
    public function getVacancyCriteria($vacancy)
    {
        $profileCriterionValues = $evaluationCriterionValues = array();
        if ($vacancy->profile_id) {
            if ($profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence(array('CriterionValue'), $vacancy->profile_id))) {
                
                // компетенции, назначенные через контекстное меню родительского профиля (есть всегда)
                if (count($profile->criteriaValues)) {
                    foreach ($profile->criteriaValues as $criterionValue) {
                        if ($criterionValue->criterion_type == HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
                            $profileCriterionValues[$criterionValue->criterion_id] = $criterionValue;
                        }
                    }
                }
            }

            if ($evaluation = $this->getService('AtEvaluation')->getOne($this->getService('AtEvaluation')->fetchAllDependence('EvaluationCriterion', array(
                'vacancy_id = ?' => $vacancy->vacancy_id,
                'method = ?' => HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE,
            )))) {
                // компетенции, настроенные через карандашик программы подбора вакансии (может и не быть) 
                if (count($evaluation->evaluation_criterion)){
                    foreach($evaluation->evaluation_criterion as $evaluationCriteria) {
                        // у них нет плановых значений
                        $evaluationCriterionValues[$evaluationCriteria->criterion_id] = true;
                    }
                }
            }
        }
        // у компетенций, настроенных не уровне вакансии выше приоритет
        // на всякий случай сохраним плановые значения из профиля
        if ((count($profileCriterionValues) != count($evaluationCriterionValues)) || count(array_diff_key($profileCriterionValues, $evaluationCriterionValues))) {
            foreach ($profileCriterionValues as $criterionId => $profileCriterionValue) {
                if (isset($evaluationCriterionValues[$criterionId])) {
                    $evaluationCriterionValues[$criterionId] = $profileCriterionValue;
                } 
            }
            return $evaluationCriterionValues;
        }
        return $profileCriterionValues;
    }
    
    // если вакансию создаёт руководитель, надо назначить ответственного рекрутёра
    // пытаемся найти специалиста по области ответственности; если не получается - менеджера
    // если несколько рекрутёров подходят по области ответственности, то выбираем случайным образом 
    // из числа младших по области видимости  
    public function _getDefaultRecruiter($position)
    {
        $recruiterIds = $responsibleIds = $responsibleLevels = array();
        if (count($collection = $this->getService('Recruiter')->fetchAll())) {
            $recruiterIds = $collection->getList('user_id', 'recruiter_id');
            if (count($responsibles = $this->getService('Responsibility')->getResponsiblesForPosition($position->soid, array_keys($recruiterIds), HM_Responsibility_ResponsibilityService::RESPONSIBILITY_LEVEL_LOW))) {
                $randomUserId = array_rand($responsibles);
                return $recruiterIds[$randomUserId]; 
            }
        }
        if (count($managerRecruiterIds = $this->getService('Recruiter')->getUnresponsible())) {
            return array_rand($managerRecruiterIds); // первый попавшийся;
        }
        return false;
    }
    
    // вакансии, которые надо скрыть от человека
    // здесь не только непосредственно self
    // еще например вакансию на руководителя надо скрывать от его зама  
    public function getSelfVacanciesToHide()
    {
        $positionIdsToHide = array();
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            $positions = $this->getService('Orgstructure')->fetchAllDependence('Sibling', array('mid = ?' => $this->getService('User')->getCurrentUserId()));
            if (count($positions)) {
                foreach ($positions as $position) {
                    $positionIdsToHide[] = $position->soid;
                    if (count($position->siblings)) {
                        $siblings = $position->siblings;
                        foreach ($siblings as $sibling) {
                            if ($sibling->is_manager && !in_array($sibling->soid, $positionIdsToHide)) {
                                $positionIdsToHide[] = $sibling->soid;
                            }
                        }
                    }
                }
            }
        }
        if (count($positionIdsToHide)) {
            if (count($collection = $this->fetchAll(array('position_id IN (?)' => $positionIdsToHide)))) {
                return $collection;
            }
        }
        return array();
    }

}