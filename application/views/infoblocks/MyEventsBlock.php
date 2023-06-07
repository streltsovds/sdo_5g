<?php

class HM_View_Infoblock_MyEventsBlock extends HM_View_Infoblock_Abstract
{

    protected $id = 'myeventsblock';

    public function myEventsBlock($param = null)
    {
        $ajax = isset($options['ajax']);
        $date = strtotime(date('Y-m-d'));

        $currentUserId = $this->getService('User')->getCurrentUserId();
        $recruiter = $this->getService('Recruiter')->getOne(
            $this->getService('Recruiter')->fetchAll(
                array('user_id = ?' => $currentUserId)
            )
        );

        // Выбрали все сессии подбора для текущего юзера в роли рекрутера
        $recruiterVacancyIds = $this->getService('RecruitVacancyRecruiterAssign')->fetchAll(
            array('recruiter_id = ?' => $recruiter->recruiter_id)
        )->getList('vacancy_id');

        $allSessionEvents = array();
        foreach ($recruiterVacancyIds as $vacancyId) {
            $vacancy = $this->getService('RecruitVacancy')->findDependence('Session', $vacancyId)->current();
            if ($vacancy->session) {
                $session = $vacancy->session->asArray();
                $sessionId = $session[0]['session_id'];
                $sessionState = $session[0]['state'];

                // Выясняем на каком этапе находится сессия подбора.
                // Если на третьем (последнем), либо сессия подбора уже закрыта, пропускаем её и все её мероприятия.
                $recruitVacancy = $this->getService('RecruitVacancy')->find($vacancyId)->current();
                $this->getService('Process')->initProcess($recruitVacancy);
                $process = $recruitVacancy->getProcess();
                $states  = $process->getStates();
                $currentStateIsHire = false;
                foreach ($states as $state) {
                    if ($state instanceof HM_Recruit_Vacancy_State_Hire && $state->getClass() != 'status-waiting') {
                        $currentStateIsHire = true;
                    }
                }
                if ($currentStateIsHire || $sessionState == HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED) continue;

                // Проходим по мероприятиям в каждой сессии подбора
                // и выбираем все мероприятия, у которых текущий юзер является респондентом
                // и дата начала меньше или равна искомой.
                $sessionEvents = $this->getService('AtSessionEvent')->fetchAll(
                    array(
                        'session_id =  ?' => $sessionId,
                        'respondent_id = ?' => $currentUserId
                    )
                )->asArrayOfArrays();
                foreach ($sessionEvents as $sessionEvent) {
                    $sessionEvent = $this->setDatesFromStateOfProcessData($sessionEvent);
                    if (!$sessionEvent) continue;

                    if (
                        (strtotime($sessionEvent['date_begin']) <= $date)
//                        && (strtotime($sessionEvent['date_end'  ]) >= $date)
                    ) {

                        // Для каждого мероприятия формируем ссылку на его прохождение,
                        // если это прохождение возможно.
                        if (!$sessionEvent['is_empty_quest']) {
                            if ($sessionEvent['session_event_id']) {
                                $sessionEvent['name'] = ' <a href="' . $this->view->url(array(
                                    'module' => 'event',
                                    'controller' => 'index',
                                    'action' => 'index',
                                    'session_event_id' => $sessionEvent['session_event_id'],
                                    'baseUrl' => 'at'
                                ), null, true, false) . '">' . $sessionEvent['name'] . '</a>';
                            }
                        }

                        // Определяем личность кандидата
                        $sessionUser = $this->getService('AtSessionUser')->find($sessionEvent['session_user_id'])->current();
                        $sessionEvent['candidate'] = $this->getService('User')->find($sessionUser->user_id)->current();

                        $recruitVacancyCandidate = $this->getService('RecruitVacancyAssign')->getOne(
                            $this->getService('RecruitVacancyAssign')->fetchAll(
                                $this->getService('RecruitVacancyAssign')->quoteInto(
                                    array('user_id = ? AND ', 'vacancy_id = ?'),
                                    array($sessionEvent['user_id'], $vacancyId)
                                )
                            )
                        );

                        if (isset($recruitVacancyCandidate->status) && $recruitVacancyCandidate->status != HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE)
                            continue;

                        $programmEventUser = $this->getService('ProgrammEventUser')->fetchAllDependence('ProgrammEvent', array(
                            'user_id = ?' => $sessionEvent['user_id'],
                            'programm_event_user_id = ?' => $sessionEvent['programm_event_user_id']
                        ))->current();

                        if (!empty($programmEventUser->programmEvent) and count($programmEventUser->programmEvent)) {
                            $programmEvent = $programmEventUser->programmEvent->current();

                            // Если мероприятие скрытое, то оно исключается из выборки мероприятий.
                            if ($programmEvent->hidden) continue;

                            $sessionEvent['vacancy_id'  ] = $vacancyId;
                            $sessionEvent['vacancy_name'] = $this->sessionName($vacancy);
                            $sessionEvent['candidate_id'] = $candidate_id = isset($recruitVacancyCandidate->candidate_id) ? $recruitVacancyCandidate->candidate_id : null;
                            $sessionEvent['date_begin'] = date('d.m.Y', strtotime($sessionEvent['date_begin']));
                            $sessionEvent['date_end'] = date('d.m.Y', strtotime($sessionEvent['date_end']));

                            $candidate = $sessionEvent['candidate'];
                            $sessionEvent['candidate_name'] = $this->candidateName($candidate_id, $candidate);
                            $sessionEvent['candidate_phone'] = $candidate->Phone;
                            $sessionEvent['candidate_email'] = $candidate->EMail;

                            $allSessionEvents[$programmEvent->programm_id][$sessionEvent['user_id']][$sessionEvent['session_event_id']] = $sessionEvent;
                        }
                    }
                }
            }
        }

        $resultEvents = array();

        // Фильтруем полученный набор мероприятий следующей логикой:
        // Если прохождение этапов программы произвольное, то забираем в итоговую выборку текущее мероприятие,
        // если же прохождение строго последовательное, то
        // выбираем все не скрытые мероприятия в программе,
        // из выбранных мероприятий программы берём текущее мероприятие в виде массива данных,
        // в данные нового мероприятия помещаем кандидата, название мероприятия, название сессии подбора,
        // для отображения в виджете.
        foreach ($allSessionEvents as $programId => $sessionUserEvents) {
            $program = $this->getService('Programm')->find($programId)->current();
            foreach ($sessionUserEvents as $sessionUserId => $sessionEvents) {
                foreach ($sessionEvents as $sessionId => $sessionEvent) {
                    if ($program->mode_strict == HM_Programm_ProgrammModel::MODE_STRICT_ON) {
                        $newSessionEvent = $this->getCurrentEventStrict($programId, $sessionUserId, $sessionEvent, $currentUserId);
                        if (!$newSessionEvent) continue;
                        $newSessionEvent['is_strict'] = 1;
                        $resultEvents[$programId][$sessionUserId][$sessionEvent['session_event_id']] = $newSessionEvent;
                    } else {
                        $newSessionEvent['is_strict'] = 0;
                        $resultEvents[$programId][$sessionUserId][$sessionEvent['session_event_id']] = $sessionEvent;
                    }
                }
            }
        }

        $finalSessionEvents = array();
        foreach ($resultEvents as $userEvents) {
            foreach ($userEvents as $events) {
                foreach ($events as $event) {
                    // Если дата окончания мероприятия меньше указанной в виджете,
                    // а также, если мероприятие из сессии со строгим прохождением или с нестрогим, но непройденное,
                    // значит его выполнение просрочено и надо подсветить его красным.
                    if (isset($event['is_strict']))
                        $event['tr_class'] = (($event['is_strict'] || (!$event['is_strict'] && $event['status'] != HM_At_Session_Event_EventModel::STATUS_COMPLETED)) && strtotime($event['date_end']) < $date) ? 'red-tr' : '';

                    // Финальная фильтрация перед выводом в виджет:
                    // Выводим мероприятие в виджет только если дата его начала не больше выбранной в виджете
                    if (strtotime($event['date_begin']) <= $date)
                        $finalSessionEvents[$event['session_id'].'_'.$event['session_event_id'].'_'.$event['vacancy_id'].'_'.$event['candidate_id'].'_'] = $event;
                }
            }
        }

        $this->view->ajax = $ajax;
        $this->view->date = date('d.m.Y', $date);
        $this->view->sessionEvents = $finalSessionEvents;

        $content = $this->view->render('myEventsBlock.tpl');

        return $this->render($content);
    }

    public function sessionName($vacancy)
    {
        $vacancyId = $vacancy->vacancy_id;
        $name = $vacancy->name;
        $positionId = $vacancy->position_id;

        return $this->view->cardLink(
            $this->view->url(array(
                'module' => 'orgstructure',
                'controller' => 'list',
                'action' => 'card',
                'baseUrl' => '',
                'org_id' => '')
            ) . $positionId,
            HM_Orgstructure_OrgstructureService::getIconTitle(HM_Orgstructure_OrgstructureModel::TYPE_POSITION),
            'icon-custom',
            'pcard',
            'pcard',
            'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass(HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
        ) . '<a href="' . $this->view->url(array('baseUrl' => 'recruit', 'module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $vacancyId, 'candidate_id' => null)) . '">' . $this->view->escape($name) . '</a>';
    }

    public function candidateName($candidate_id, $candidate)
    {
        $cardLink = $this->view->cardLink(
            $this->view->url(
                array(
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'resume',
                    'baseUrl' => 'recruit',
                    'blank' => 1,
                    'candidate_id' => $candidate_id), null, true),
            null,
            'candidate',
            'candidate',
            'candidate',
            true,
            'candidate');
        $candidateUrl = $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => $candidate->MID), null, true);
        $candidateLink = "<a href='$candidateUrl'>{$candidate->getName()}</a>";

        return "$cardLink $candidateLink";
    }

    protected function setDatesFromStateOfProcessData($sessionEvent)
    {
        if (!$sessionEvent['session_event_id']) return false;
        $select = $this->getService('AtSessionEvent')->getSelect();
        $select
            ->from(
                array('se' => 'at_session_events'),
                array(
                    'sopd.begin_date',
                    'sopd.end_date',
                    'sopd.begin_date_planned',
                    'sopd.end_date_planned'
                ))
            ->joinLeft(
                array('sopd' => 'state_of_process_data'),
                'sopd.programm_event_user_id = se.programm_event_user_id',
                array())
            ->where('se.session_event_id = ?', $sessionEvent['session_event_id']);

        $result = $select->query()->fetchAll();
        $fetch = array_shift($result);

        if ($fetch['begin_date'])
            $sessionEvent['date_begin'] = $fetch['begin_date_planned'] ? $fetch['begin_date_planned'] : $fetch['begin_date'];
        if ($fetch['begin_date'])
            $sessionEvent['date_end'] = $fetch['end_date_planned'] ? $fetch['end_date_planned'] : $fetch['end_date'];

        return $sessionEvent;
    }

    /*
     *  Метод определяет текущее мероприятие в программе со строго последовательным прохождением.
     *
     * @param int $programId - Id программы
     * @param int $sessionUserId - Id пользователя
     * @param array $oldSessionEvent - массив данных исходного мероприятия
     *
     * @return array - Массив данных вычесленного мероприятия
     */
    protected function getCurrentEventStrict($programId, $sessionUserId, $oldSessionEvent, $currentUserId)
    {
        // Выбираем все не скрытые мероприятия в программе
        $programmEventIds = $this->getService('ProgrammEvent')->fetchAll(array(
            'programm_id = ?' => $programId,
            'hidden = ?' => 0
        ))->getList('programm_event_id');

        // Из выбранных мероприятий программы берём текущее мероприятие в виде массива данных
        $programmEventUser = $this->getService('ProgrammEventUser')->fetchAll(array(
            'programm_event_id IN (?)' => empty($programmEventIds) ? array(0) : $programmEventIds,
            'programm_id = ?' => $programId,
            'user_id = ?' => $sessionUserId,
            'status  = ?' => HM_Programm_Event_User_UserModel::STATUS_CONTINUING,
        ))->current();

        $newSessionEvent = $this->getService('AtSessionEvent')->fetchAll(
            array('programm_event_user_id = ?' => $programmEventUser->programm_event_user_id)
        )->asArray();

        $newSessionEvent = empty($newSessionEvent) ? array() : $newSessionEvent[0];

        // В данные нового мероприятия помещаем кандидата, название мероприятия, название сессии подбора
        $newSessionEvent['candidate'] = $oldSessionEvent['candidate'];

        // Для каждого мероприятия формируем ссылку на его прохождение,
        // если это прохождение возможно.
        if (!$newSessionEvent['is_empty_quest']) {
            if (
                $newSessionEvent['session_event_id']
            ) {
                $newSessionEvent['name'] = ' <a href="' . $this->view->url(array(
                        'module' => 'event',
                        'controller' => 'index',
                        'action' => 'index',
                        'session_event_id' => $newSessionEvent['session_event_id'],
                        'baseUrl' => 'at'
                    ), null, true, false) . '">' . $newSessionEvent['name'] . '</a>';
            }
        }

        $newSessionEvent['vacancy_id'  ] = $oldSessionEvent['vacancy_id'  ];
        $newSessionEvent['vacancy_name'] = $oldSessionEvent['vacancy_name'];
        $newSessionEvent['candidate_id'] = $oldSessionEvent['candidate_id'];
        $newSessionEvent = $this->setDatesFromStateOfProcessData($newSessionEvent);

        // Возвращаем массив данных вычесленного мероприятия
        return $newSessionEvent;
    }
}