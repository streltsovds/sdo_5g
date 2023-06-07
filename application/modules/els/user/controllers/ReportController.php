<?php
class User_ReportController extends HM_Controller_Action_User
{
    use HM_Controller_Action_Trait_Report;
    use HM_Controller_Action_Trait_Context;

    public function init()
    {
        parent::init();
        $this->initReport();
    }

    public function indexAction()
    {
        $this->view->user = $this->_user;

        $recruitCandidates = $this->getService('RecruitCandidate')->fetchAllDependence('VacancyAssign', array('user_id = ?' => $this->_userId));
        foreach ($recruitCandidates as $recruitCandidate) $candidate = $recruitCandidate;

        $position = $category = $profile = $parent = null;
        if ($this->_user && $this->_user->MID && count($collection = $this->getService('Orgstructure')->fetchAllDependence(array('Parent'), array(
            'mid = ?' => $this->_user->MID,
            'blocked = ?' => 0,
        )))) {

            $position = $collection->current(); // позиция в оргструктуре
            if (count($position->parent)) {
                $parent = $position->parent->current(); // подразделение
            }

            $scaleId = $this->getService('Option')->getOption('competenceScaleId');
            if (count($collection = $this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue', 'Category'), $position->profile_id))) {
                $profile = $collection->current();
                if (count($profile->category)) {
                    $category = $profile->category->current(); // категория должности
                }
            }

            if (count($collection = $this->getService('AtCriterion')->fetchAll())) {
                $criteriaCache = $collection->getList('criterion_id', 'name');
            }


            $sessionUsers = $this->getService('AtSessionUser')->fetchAllDependence(array('Session', 'CriterionValue'), array('user_id = ?' => $this->_user->MID), 'session_user_id ASC');
        }

        /************************************/

        // $education = $this->_user->Information
        // ? (strpos($this->_user->Information, 'tel~') !== false
        //         ? $this->view->reportNoValue()
        //         : $this->_user->Information)
        // : $this->view->reportNoValue();

        $this->view->lists['general'] = array(
            _('Должность') => $position ? $position->name : $this->view->reportNoValue(),
            _('Стаж работы на должности') => $position->position_date ? HM_Date::getPeriodSinceDate($position->position_date) : $this->view->reportNoValue(),
            _('Категория должности') => $category ? $category->name : $this->view->reportNoValue(),
            _('Профиль должности') => $profile ? $profile->name : $this->view->reportNoValue(),
            _('Структурное подразделение') => $parent ? $parent->name : $this->view->reportNoValue(),
//             _('Стаж работы в Компании') => $this->_user->PositionDate ? HM_Date::getPeriodSinceDate($this->_user->PositionDate) : $this->view->reportNoValue(),
            _('Email') => $this->_user->EMail ? $this->_user->EMail : $this->view->reportNoValue(),
            _('Телефон') => $this->_user->Phone ? $this->_user->Phone : $this->view->reportNoValue(),
            // _('Образование') => [
            //     'value' => $education,
            //     'block' => true
            // ],
            _('Стаж') => $this->_user->Position ? $this->_user->Position : $this->view->reportNoValue(),
        );

        //if ($url = $this->_user->getResume()) {
        //    $this->view->resume = $url;
        //}

        /************************************/

        $clusters = array();
        try {
            $clusters = $this->getService('AtKpiUser')->getUserKpis($this->_user->MID);
        } catch (HM_Exception $e) {
            $this->view->message = $e->getMessage();
        }

        // пока без кластеров; добавить при необходимости
        $kpis = array(array(
            _('Показатель эффективности'),
            _('Плановое значение'),
            _('Фактическое значение'),
            _('Вес'),
        ));
        foreach ($clusters as $cluster) {
            foreach ($cluster as $kpiId => $kpi) {
                $kpis[] = array(
                    $kpi['name'],
                    sprintf('%s (%s)', $kpi['value_plan'], $kpi['unit']),
                    $kpi['value_fact'],
                    $kpi['weight'],
                );
            }
        }
        $this->view->tables['kpis'] = count($clusters) ? $kpis : array();

        /************************************/

        $resumes = array();

        foreach ($recruitCandidates as $candidate) {
            if ($candidate->vacancies) {

                $vacancyAssign = $candidate->vacancies->current();
                $vacancy = $this->getService('RecruitVacancy')->findOne($vacancyAssign->vacancy_id);

                $vacancyDataFields = $this->getService('RecruitVacancyDataFields')->fetchOne(
                    $this->getService('RecruitVacancyDataFields')->quoteInto(
                        array(
                            ' item_id = ? ',
                            ' AND item_type = ? '
                        ),
                        array(
                            $vacancy->vacancy_id,
                            HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY
                        )
                    )
                );

                $status = '';
                if ($vacancyAssign->status !== HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED) {
                    $status = HM_Recruit_Vacancy_Assign_AssignModel::getStatus($vacancyAssign->status);
                } elseif ($vacancyAssign->result) {
                    $status = HM_Recruit_Vacancy_Assign_AssignModel::getResultStatus($vacancyAssign->result);
                }

                if ($vacancy->status == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL) {
                    $vacancyLink = $vacancy->name;
                } else {
                    $vacancyLink = (($this->getService('User')->getCurrentUserId() != $vacancyDataFields->user_id)) ?
                        '<a href="' . $this->view->url(
                            array(
                                'baseUrl' => 'recruit',
                                'module' => 'vacancy',
                                'controller' => 'report',
                                'action' => 'card',
                                'vacancy_id' => $vacancy->vacancy_id
                            ),
                            null,
                            true
                        ) . '">' . $vacancy->name . '</a>' : $vacancy->name;
                }

                if (
                    in_array($this->getService('User')->getCurrentUserId(), array($vacancyDataFields->user_id, $candidate->user_id)) ||
                    $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))
                ) {
                    if ($candidate->hasResumeJson() || $candidate->hasResumeHtml()) {
                        $resumeLink = '<a href="'. $this->view->url(
                            array(
                                'baseUrl' => 'recruit',
                                'module' => 'candidate',
                                'controller' => 'index',
                                'action' => 'resume',
                                'candidate_id' => $candidate->candidate_id,
                                'user-report' => 1
                            ),
                            null,
                            true
                        ) .'">'. _('Резюме') .'</a>';
                    } elseif ($candidate->hasResumeFile()) {
                        $resumeLink = '<a href="'. $this->view->url(
                                array(
                                    'baseUrl' => 'recruit',
                                    'module' => 'candidate',
                                    'controller' => 'index',
                                    'action' => 'download',
                                    'candidate_id' => $candidate->candidate_id
                                ),
                                null,
                                true
                            ) .'">'. _('Резюме') .'</a>';
                    }

                } else {
                    $resumeLink = '';
                }

                if ($vacancy->status == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL) {
                    $reportLink = '';
                } else {
                    $reportLink = (($this->getService('User')->getCurrentUserId() != $vacancyDataFields->user_id)) ?
                        '<a href="' . $this->view->url(
                            array(
                                'baseUrl' => 'recruit',
                                'module' => 'vacancy',
                                'controller' => 'report',
                                'action' => 'user',
                                'vacancy_id' => $vacancy->vacancy_id,
                                'vacancy_candidate_id' => $vacancyAssign->vacancy_candidate_id,
                            ),
                            null,
                            true
                        ) . '">' . _('Отчёт') . '</a>' : _('Отчёт');
                }

                $date = new DateTime($vacancy->create_date);
                $date = $date->format('d.m.Y');

                $resumes[] = array(
                    $date,
                    $vacancyLink,
                    $status,
                    $resumeLink,
                    $reportLink,
                );
            }
        }

        if (count($resumes)) {
            $resumes = array_merge(
                array(array(
                    _('Дата'),
                    _('Название вакансии'),
                    _('Статус'),
                     _('Резюме'),
                    _('Отчет')
                )), $resumes
            );
        }

        $this->view->tables['resumes'] = $resumes;

        /************************************/

        $competences = array(array(
            _('Компетенция'),
            _('Уровень успешности'),
        ));
        if (!empty($profile->criteriaValues) and count($profile->criteriaValues)) {
            foreach ($profile->criteriaValues as $criterionValue) {
                if ($criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) continue;
                $value = HM_Scale_Converter::getInstance()->id2value($criterionValue->value_id, $scaleId);
                $competences[] = array(
                    $criteriaCache[$criterionValue->criterion_id],
                    $value != HM_Scale_Value_ValueModel::VALUE_NA ? $value : '',
                );
            }
            $this->view->tables['competences'] = $competences;
        }

        /************************************/

        $absences = array(
            array(
                _('Дата начала'),
                _('Дата окончания'),
                _('Причина')
            ),
            //! Для теста
            // array(
            //     _('Дата начала'),
            //     _('Дата окончания'),
            //     _('Причина')
            // ),
        );
        $absenceCollection = $this->getService('Absence')->fetchAll(array('user_id=?' => $this->_user->MID));
        if (count($absenceCollection)) {
            foreach ($absenceCollection as $absence) {
                $begin = new HM_Date($absence->absence_begin);
                $end = new HM_Date($absence->absence_end);
                if (abs(time() - $begin->get(Zend_Date::TIMESTAMP)) > 63072000) continue; // 2 года
                $absences[] = array(
                    $begin->toString('dd.MM.YYYY'),
                    // хак для решения проблемы 31-го декабря; неверно работает метод toString(), прибавляет год
                    strpos($absence->absence_end, '-12-31') === false ? $end->toString('dd.MM.YYYY') : '',
                    HM_Absence_AbsenceModel::getType($absence->type),
                );
            }
        }
        
        $this->view->tables['absences'] = count($absences) ? $absences : array();

        /************************************/
        $sessions = $criteria = $results = array();
        $lastSessionId = 0;
        if (!empty($sessionUsers) and count($sessionUsers)) {
            foreach ($sessionUsers as $sessionUser) {
                if (count($sessionUser->session) && ($sessionUser->status == HM_At_Session_User_UserModel::STATUS_COMPLETED)) {
                    $session = $sessionUser->session->current();
                    if (in_array($session->programm_type, array(HM_Programm_ProgrammModel::TYPE_RECRUIT, HM_Programm_ProgrammModel::TYPE_ASSESSMENT, HM_Programm_ProgrammModel::TYPE_RESERVE))) {
                        $sessions[$session->session_id] = $session->name;

                        if ($lastSessionId) {
                            $lastSession = $this->getService('AtSession')->find($lastSessionId)->current();
                            if ((strtotime($lastSession->end_date) < strtotime($session->end_date)) || ($lastSession->session_id < $session->session_id)) {
                                $lastSessionId = $session->session_id;
                            }
                        } else {
                            $lastSessionId = $session->session_id;
                        }

                        if (count($sessionUser->criterionValues)) {
                            foreach ($sessionUser->criterionValues as $criterionValue) {
                                if ($criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) continue;
                                $criteria[$criterionValue->criterion_id] = $criteriaCache[$criterionValue->criterion_id];
                                $results[$criterionValue->criterion_id][$session->session_id] = $criterionValue->value;
                            }
                        }
                    }
                }
            }
        }
        $sessionResults = array(array(_('Компетенция')) + $sessions);
        foreach ($criteria as $criterionId => $criterionName) {
            $row = $lastSessionRow = array($criterionName);
            foreach ($sessions as $sessionId => $name) {
                $row[] = isset($results[$criterionId][$sessionId]) ? $results[$criterionId][$sessionId] : '-';
                if ($sessionId == $lastSessionId )
                    $lastSessionRow[] = isset($results[$criterionId][$sessionId]) ? $results[$criterionId][$sessionId] : '-';
            }
            $sessionResults[] = $row;
        }
        $this->view->tables['sessionResults'] = count($sessions) ? $sessionResults : array();

        $this->view->print = $this->_getParam('print', 0);
        $this->view->withoutPrintButton = $this->_getParam('withoutPrintButton', 0);
        $this->view->lastSessionId = $lastSessionId;
        $this->view->position = $position;
    }

    public function resumeAction()
    {
        $spot_id = (int) $this->_getParam('spot_id', 0);
        $results = $this->getService('EstaffSpot')->fetchAll(array('spot_id = ?' => $spot_id));
        foreach ($results as $result) {
            $this->view->html = $result->resume_text;
        }
    }
}
