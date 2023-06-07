<?php
class Vacancy_ReportController extends HM_Controller_Action_Vacancy
{
    use HM_Controller_Action_Trait_Report;

    public function init()
    {
        $this->initReport();
        return parent::init();
    }

    public function cardAction()
    {
        $vacancyDataFieldsService = $this->getService('RecruitVacancyDataFields');
        $userService = $this->getService('User');

        if($this->_vacancy->profile_id){
            $this->_profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue', 'Category', 'Quest'), $this->_vacancy->profile_id));
        }
        
        $positionId = 0;
        if($this->_vacancy){
            $vacancyDataFields = $vacancyDataFieldsService->fetchAll(array(
                'item_id = ?'   => $this->_vacancy->vacancy_id,
                'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
            ));
            $positionId = $this->_vacancy->position_id;
            $name = $this->_vacancy->name;
            $editUrl = $this->view->url(array(
                'module'     => 'vacancy',
                'controller' => 'list',
                'action'     => 'edit',
                'vacancy_id' => $this->_vacancy->vacancy_id,
            ), null, true);
        } else {
            $vacancyDataFields = $vacancyDataFieldsService->fetchAll(array(
                'item_id = ?'   => $this->_application->recruit_application_id,
                'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_APPLICATION,
            ));
            $name = $this->_application->vacancy_name;
            $editUrl = $this->view->url(array(
                'module'                 => 'application',
                'controller'             => 'list',
                'action'                 => 'edit',
                'recruit_application_id' => $this->_application->recruit_application_id,
            ), null, true);
        }
        
        if(count($vacancyDataFields)){
            $vacancyDataFields = $vacancyDataFields->current();
        }
        
        if($positionId){
            $collection = $this->getService('Orgstructure')->fetchAllHybrid(
                array('Parent'),
                'Category',
                'Profile',
                array('soid = ?' => $positionId)
            );
        }
        
        if (count($collection)){
            $this->_position = $collection->current();
        }
        
        $workModeVariants = HM_Recruit_Vacancy_VacancyModel::getWorkModeVariants();

        $this->view->editUrl = $editUrl;

        $userLink = '';

        if ($this->_vacancy->user_id){
            if ($user = $userService->findOne($this->_vacancy->user_id)) {
                $userLink = $this->view->cardLink(
                    $this->view->url(
                        array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $user->MID, 'baseUrl' => '')
                    )) . $user->getName();
            }
        }

        $this->view->lists['generalLeft'] = array(
            _('Наименование должности')         => $name,
            _('Инициатор подбора')              => $userLink ? : $this->view->reportNoValue(),
//            _('Кому подчиняется')               => $whoObeysLink,
//            _('Пользователей в подчинении')       => $staffCountVariants[$vacancyDataFields->subordinates_count],
        );
        
        if($this->_vacancy){
//            $this->view->lists['generalLeft'][_('Должность')] = $this->_position->name;
            $this->view->lists['generalLeft'][_('Профиль должности')] = $this->_profile->name;
        }
        
        if($this->_application){
            $this->view->lists['generalLeft'][_('Количество вакантных мест')] = $vacancyDataFields->number_of_vacancies;
        }

        $closeDate = new HM_Date($this->_vacancy->close_date);
        $this->view->lists['generalLeft'][_('Желательная дата начала работы')] = $this->_vacancy->close_date ? $closeDate->get(HM_Date::DATE_MEDIUM) : $this->view->reportNoValue();

        $this->view->lists['generalRight'] = array(
            _('Заработная плата')           => $vacancyDataFields->salary ? : $this->view->reportNoValue(),
            _('График работы')              => $workModeVariants[$vacancyDataFields->work_mode] ? : $this->view->reportNoValue(),
//            _('Тип и срок договора')        => $contractVariants[$vacancyDataFields->type_contract],
            _('Место работы (город)')       => $vacancyDataFields->work_place ? : $this->view->reportNoValue(),
//            _('Испытательный срок, мес.')   => $vacancyDataFields->probationary_period,
//            _('Карьерные перспективы')      => $vacancyDataFields->career_prospects,
//            _('Причина появления вакансии') => $reasonVariants[$vacancyDataFields->reason],
        );
        
        
        $tasks = explode('||', $vacancyDataFields->tasks);

        $table = array($head = array(
            _('Задача/обязанность'),
        ));
        foreach($tasks as $task){
            $table[] = array($task);
        }
        $this->view->tables['responsibility'] = $table;
        
        
        $this->view->lists['primaryRequirements'] = array(
            _('Образование')                                        => $vacancyDataFields->education ? : $this->view->reportNoValue(),
            _('Навыки')                                             => nl2br($vacancyDataFields->skills) ? : $this->view->reportNoValue(),
            _('Дополнительное образование (курсы, тренинги)')       => str_replace('||', ', ', $vacancyDataFields->additional_education) ? : $this->view->reportNoValue(),
            _('Знание компьютерных программ')                       => str_replace('||', ', ', $vacancyDataFields->knowledge_of_computer_programs) ? : $this->view->reportNoValue(),
            _('Знание иностранных языков (язык, степень владения)') => str_replace('||', ', ', $vacancyDataFields->knowledge_of_foreign_languages) ? : $this->view->reportNoValue(),
            _('Опыт работы (период, лет)')                          => $vacancyDataFields->work_experience ? : $this->view->reportNoValue(),
//            _('Личные качества')                                    => $vacancyDataFields->personal_qualities,
        );
        
//        $this->view->lists['additionalRequirements'] = array(
//            _('Специальные точки поиска кандидатов (конкретные вузы, организации, профессиональные сообщества)')
//                => str_replace('||', ', ', $vacancyDataFields->experience_other),
//            _('Прочие требования')
//                => $vacancyDataFields->other_requirements,
//        );

        $this->view->lists['additionalRequirements'] = array(
            _('Необходимость командировок') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->trips, 'getTripsVariants')) ? $val : $this->view->reportNoValue(),
            _('Длительность  командировок, дн.') => strlen(trim($this->_profile->trips_duration)) ? $this->_profile->trips_duration : $this->view->reportNoValue(),
            _('Статус мобильности') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->mobility, 'getMobilityVariants')) ? $val : $this->view->reportNoValue(),
//             _('Подчинение') => '',
        );

        if ($this->_profile) {
            $requirements = $this->getService('AtProfile')->getRequirements4Report($this->_profile->profile_id);

            $table = array(array_merge(array(_('Профстандарт'),_('Обобщенная трудовая функция')), HM_At_Standard_Function_FunctionModel::getTypes()));
            foreach($requirements as $requirement) {
                $table[] = $requirement;
            }
            $this->view->tables['skills'] = $table;
        }


        if($this->_application){
            $this->view->backUrl = $this->view->url(array(
                'module'     => 'application',
                'controller' => 'list',
                'action'     => 'index',
            ), null, true);
        }

    }
    
    public function indexAction()
    {
        $this->view->vacancy = $this->_vacancy;
        
        // здесь берётся шкала из настроек ассессмента, т.к. уровни задаются в профиле
        // считаем, что в подборе используется та же шкала
        // или как минимум имеет те же значения уровней
        $scaleId = $this->getService('Option')->getOption('competenceScaleId');  
        
        if (count($collection = $this->getService('Orgstructure')->fetchAllHybrid(array('Parent'), 'Category', 'Profile', array('soid = ?' => $this->_vacancy->position_id)))) {

            $this->_position = $collection->current(); // позиция в оргструктуре
            if (count($this->_position->parent)) {
                $parent = $this->_position->parent->current(); // подразделение
            }
            
            $this->_profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue', 'Category', 'Quest'), $this->_vacancy->profile_id));

            if (count($this->_position->category)) {
                $category = $this->_position->category->current(); // категория должности
            }

            if ($this->_vacancy->parent_position_id && count($collection = $this->getService('User')->find($this->_vacancy->parent_position_id))) {
                $manager = $collection->current();
            }

            if ($this->_vacancy->parent_top_position_id && count($collection = $this->getService('User')->find($this->_vacancy->parent_top_position_id))) {
                $topManager = $collection->current();
            }
        }
        
        $openDate = new HM_Date($this->_vacancy->open_date);
        $openDate = $openDate->toString('dd.MM.Y');              
        $closeDate = new HM_Date($this->_vacancy->close_date);
        $closeDate = $closeDate->toString('dd.MM.Y');  

        $subordinatesCategories = array();
        $subordinatesCategoryIds = unserialize($this->_vacancy->subordinates_categories);
        if (count($subordinatesCategoryIds)) {
            $collection = $this->getService('AtCategory')->fetchAll(array('category_id IN (?)' => $subordinatesCategoryIds));
            $subordinatesCategories = $collection->getList('category_id', 'name');
        }
        
        $tasks = unserialize($this->_vacancy->tasks);
        if (!count($tasks) || !is_array($tasks)) {
            $tasks = array();
        }

        $requirements = $this->getService('AtProfile')->getRequirements4Report($this->_profile->profile_id);
        
        $searchChannels = HM_Recruit_Vacancy_VacancyModel::getSearchChannelVariants();
        
        $criteria = $criteriaTypes = $criteriaSuccessValues = $criteriaClusters = array();

        $this->view->criteriaTypes = $criteriaTypes = $this->getService('AtEvaluation')->getVacancyCriteria($this->_vacancy->vacancy_id);
        if ($this->_profile && count($this->_profile->criteriaValues)) $criteriaSuccessValues = $this->_profile->criteriaValues->getList('criterion_id', 'value_id');
        
        if (count($criteriaTypes)) {
            foreach ($criteriaTypes as $method => $criterionIds) {
                $criteria[$method] = $this->getService('AtCriterion')->getCriteriaByMethod($method, $criterionIds);
            }
        }
        
        /************************************/
        
        $this->view->lists['general'] = array(
            _('Название вакансии') => $this->_vacancy->name,
            _('Название должности') => $this->_position->name,
            _('Профиль должности') => $this->_profile->name,
            _('Категория должности') => $category ? $category->name : $this->view->reportNoValue(),
            _('Структурное подразделение') => $parent ? $parent->name : $this->view->reportNoValue(),
            _('Непосредственный руководитель') => $manager ? $manager->getName() : $this->view->reportNoValue(),
            _('Функциональный руководитель') => $topManager ? $topManager->getName() : $this->view->reportNoValue(),
        );
        
        $this->view->lists['compensation'] = array(
            _('Причины открытия вакансии') => ($val = HM_Recruit_Vacancy_VacancyModel::getVariant($this->_vacancy->reason, 'getReasonVariants')) ? $val : $this->view->reportNoValue(),
            _('Дата открытия вакансии') => $openDate,
            _('Желательная дата начала работы') => $closeDate,
            _('Постоянная часть заработной платы') => $this->_vacancy->salary,
            _('Переменная часть заработной платы') => $this->_vacancy->bonus,
        );
        
        /************************************/
        
        $table = array($head = array(
            _('Задача/обязанность'),
        ));
        foreach ($tasks as $task) {
            if (!empty($task)) {
                $table[] = array($task);
            }
        }
        $this->view->tables['tasks'] = $table;        

        /************************************/
        
        $this->view->lists['options-1'] = array(
            _('Место работы (территориальное расположение)') => !empty($this->_vacancy->work_place) ? nl2br($this->_vacancy->work_place) : $this->view->reportNoValue(),
            _('Режим работы') => ($val = HM_Recruit_Vacancy_VacancyModel::getVariant($this->_vacancy->work_mode, 'getWorkModeVariants')) ? $val : $this->view->reportNoValue(),
            _('Командировки (наличие, периодичность, продолжительность)') => ($val = HM_Recruit_Vacancy_VacancyModel::getVariant($this->_vacancy->trip_mode, 'getBusinessTripVariants')) ? $val : $this->view->reportNoValue(),
            _('Предполагаемая заработная плата (тарифная ставка/оклад)') => !empty($this->_vacancy->salary) ? $this->_vacancy->salary : $this->view->reportNoValue(),
            _('Предполагаемая заработная плата (средний процент премии)') => !empty($this->_vacancy->bonus) ? $this->_vacancy->bonus : $this->view->reportNoValue(),
        );

        $this->view->lists['options-2'] = array(
            _('Наличие подчиненных') => $this->_vacancy->subordinates ? _('Да') : _('Нет'),
            _('Количество подчиненных') => ($val = HM_Recruit_Vacancy_VacancyModel::getVariant($this->_vacancy->subordinates_count, 'getStaffCountVariants')) ? $val : $this->view->reportNoValue(),
            _('Категории должности подчиненных') => count($subordinatesCategories) ? implode(',<br>', $subordinatesCategories) : $this->view->reportNoValue(),
        );
                
        $table = array(array_merge(array(_('Профстандарт'),_('Обобщенная трудовая функция')), HM_At_Standard_Function_FunctionModel::getTypes()));
        foreach($requirements as $requirement) {
            $table[] = $requirement;
        }
        $this->view->tables['skills'] = $table;

        $this->view->lists['options-misc'] = array(
            _('Пол') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_vacancy->gender, 'getGenderVariants')) ? $val : $this->view->reportNoValue(),
            _('Возраст') => implode(' - ', array($this->_vacancy->age_min, $this->_vacancy->age_max)),
            _('Дополнительные требования к образованию') => nl2br($this->_vacancy->education),
            _('Дополнительные требования') => nl2br($this->_vacancy->requirements),
        );
        
        /************************************/

        $searchChannelOptions = array();
        $this->view->lists['search-channels'] = array();
        foreach ($searchChannels as $key => $element) {
            
            $this->view->lists['search-channels'][is_array($element) ? $element[0] : $element] = $this->_vacancy->$key ? _('Да') : _('Нет');
            if (is_array($element)) {
                $list = array();
                $element = array_pop($element);
                foreach ($element as $key => $title) {
                    $values = unserialize($this->_vacancy->$key);
                    if (is_array($values) && count($values)) {
                        foreach ($values as $value) {
                            if (!empty($value)) {
                                $list[] = $value;
                            }
                        }
                    }
                    $searchChannelOptions[$title] = $key;
                    $this->view->lists[$key] = $list;
                }
            }
        }
        
        $this->view->searchChannelOptions = $searchChannelOptions;
        
        /************************************/
        
        $classifiers = $this->getService('Classifier')->fetchAll(array('type = ?' => HM_Classifier_Type_TypeModel::BUILTIN_TYPE_HH_SPECIALIZATIONS));
        if (count($classifiers)) {
            $classifiers = $classifiers->getList('classifier_id_external', 'name');
        }
        $experience = unserialize($this->_vacancy->experience);
        if (is_array($experience) && count($experience)) {
            foreach ($experience as $item) {
                if ($item) $this->view->lists['experience'][] = $classifiers[$item];
            }
        }
        
        $experienceOther = unserialize($this->_vacancy->experience_other);
        if (is_array($experienceOther) && count($experienceOther)) {
            foreach ($experienceOther as $item) {
                if ($item) $this->view->lists['experience'][] = $item;
            }
        }
        
        $experienceCompanies = unserialize($this->_vacancy->experience_companies);
        if (is_array($experienceCompanies) && count($experienceCompanies)) {
            foreach ($experienceCompanies as $item) {
                if ($item) $this->view->lists['experience_companies'][] = $item;
            }
        }
        
        /************************************/
        
        if (count($criteria[HM_At_Evaluation_EvaluationModel::TYPE_TEST])) {
            foreach ($criteria[HM_At_Evaluation_EvaluationModel::TYPE_TEST] as $criterion) {
                $this->view->lists['criteria-test'][] = $criterion->name;
            }
        }

        /************************************/
        
        $table = array(array(
            _('Кластер'),        
            _('Компетенция'),        
            _('Уровень успешности'),        
        ));
        if (count($criteria[HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE])) {
            foreach ($criteria[HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE] as $criterion) {
                $table[] = array(
                    count($criterion->cluster) ? $criterion->cluster->current()->name : '',
                    $criterion->name,
                    isset($criteriaSuccessValues[$criterion->criterion_id]) ? HM_Scale_Converter::getInstance()->id2value($criteriaSuccessValues[$criterion->criterion_id], $scaleId) : '',
                );
            }
        }
        $this->view->tables['criteria'] = $table;
        
        /************************************/
                
        $table = array(array(
            _('Характеристика'),        
            _('Описание'),        
        ));
        if (count($criteria[HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO])) {
            foreach ($criteria[HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO] as $criterion) {
                $table[] = array(
                    $criterion->name,
                    $criterion->description,
                );
            }
        }
        $this->view->tables['criteria-personal'] = $table;
        $this->view->evaluations = $evaluations;
        $this->view->editable = !$print && $this->getService('Acl')->isCurrentAllowed('mca:vacancy:index:edit');
        
        if (count($collection = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $this->_vacancy->vacancy_id, HM_Programm_ProgrammModel::TYPE_RECRUIT))) {
            $this->view->programm = $programm = $collection->current();
        }        
        $processes = $this->getService('Programm')->getActiveProcesses($programm);
        $this->view->programmEditable = !$print && !count($processes) && $this->getService('Acl')->isCurrentAllowed('mca:vacancy:index:programm');
    }
    
    public function userAction()
    {
        $vacancyCandidateId = $this->_getParam('vacancy_candidate_id');

        $methods = array();
//        $this->view->setHeader(_('Индивидуальный отчет'));

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            if (($this->getService('User')->getCurrentUserRole() == 'guest' && ($this->_getParam('hash') === $this->getService('RecruitVacancyAssign')->getHash($vacancyCandidateId))) ||
                ($this->_getParam('hash') !== $this->getService('RecruitVacancyAssign')->getHash($vacancyCandidateId))) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Недостаточно прав для просмотра страницы')));
                $this->_redirector->gotoSimple('index', 'index', 'default');
            }
        }
        $vacancyCandidate = $this->getService('RecruitVacancyAssign')->fetchAllHybrid(array('SessionUser', 'Candidate'), 'User', 'Candidate', array('vacancy_candidate_id = ?' => $vacancyCandidateId))->current();
        
        $user = $vacancyCandidate->user->current();
        if (count($vacancyCandidate->sessionUser)) {
            $sessionUserId = $vacancyCandidate->sessionUser->current()->session_user_id;
            $collection = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
            if (!count($collection)) {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник сессии подбора не найден')));
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                    $this->_redirector->gotoSimple('my', 'list', 'session');
                } else {
                    $this->_redirector->gotoSimple('index', 'list', 'vacancy');
                }
            }

            $this->_sessionUser = $collection->current();
            $this->_position = $this->getService('Orgstructure')->findDependence(array('Parent'), $this->_vacancy->position_id)->current();
            $this->_profile = $this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_vacancy->profile_id)->current();

            if (count($this->_position->parent) && !is_null($this->_position->parent) ) {
                $positionName = $this->_position->parent->current()->name;
            }

            $cardUrl = $this->view->url(array(
                'module'     => 'user',
                'controller' => 'report',
                'action'     => 'index',
                'user_id' => $user->MID,
                'baseUrl' => '',
            ), null, true);

            $recruiters = array();
            if (count($this->_vacancy->recruiters)) {
                $recruiterUserIds = $this->_vacancy->recruiters->getList('user_id');
                $collection = $this->getService('User')->fetchAll(array('MID IN (?)' => $recruiterUserIds));
                if (count($collection)) {
                    foreach ($collection as $recruiterUser) {
                        $recruiters[$recruiterUser->MID] = $recruiterUser->getName();
                    }
                }
            }

            $this->view->lists['general'] = array(
                _('ФИО') => sprintf('<a href="%s">%s</a>', $cardUrl, $user->getName()),
                _('Подразделение') => $positionName,
//                _('Должность') => $this->_position->name . ($this->_position->is_manager ? ' (' . _('руководитель') . ')' : ''),
                _('Профиль должности') => $this->_profile->name,
                _('Сессия подбора') => $this->_vacancy->name,
                _('Специалист(ы) по подбору') => implode(',<br>', $recruiters),
            );

            $this->view->scaleMaxValue = Zend_Registry::get('serviceContainer')->getService('Scale')->getMaxValue(
                Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId', HM_Option_OptionModel::MODIFIER_RECRUIT)
            );

            $process = $vacancyCandidate->getProcess();
            $processId = $process->getProcessId();
            $chain = $process->getProcessAbstract()->getChain();
            $state = $this->getService('State')->fetchAll(
                $this->getService('State')->quoteInto(
                    array(
                        ' item_id = ? ',
                        ' AND process_id = ? ',
                        ' AND process_type = ? ',
                    ),
                    array(
                        $vacancyCandidate->getPrimaryKey(),
                        $processId,
                        $process->getType()
                    )
                )
            );

            $methods = array();
            $params = array('sessionUser' => $this->_sessionUser, 'profile' => $this->_profile);
            if ($programm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAllDependence('Event', array(
                'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_RECRUIT,
                'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY,
                'item_id = ?' => $this->_vacancy->vacancy_id,
            )))) {

                if (count($programm->events)) {

                    $evaluationIds = $programm->events->getList('ordr', 'item_id'); // главное, чтобы не сломался ordr
                    $evaluations = $this->getService('AtEvaluation')->fetchAll(array('evaluation_type_id IN (?)' => $evaluationIds))->asArrayOfObjects();

                    $stateOfProcess = $state->current();

                    if ($stateOfProcess && $stateOfProcess->state_of_process_id) {
                        // здесь могут быть комментарии и прикрепленные файлы
                        $stateOfProcessDatas = $this->getService('StateData')->fetchAll(
                            array(
                                'state_of_process_id = ?' => $stateOfProcess->state_of_process_id
                            ));
                        $statesData = array();
                        foreach ($stateOfProcessDatas as $stateOfProcessData) {
                            $statesData[$stateOfProcessData->state] = $stateOfProcessData;
                        }
                    }

                    $programmEventIds = $programm->events->getList('programm_event_id');
                    $programmEvents = $this->getService('ProgrammEventUser')->fetchAll(array(
                        'programm_event_id IN (?)' => $programmEventIds,
                        'user_id = ?' => $user->MID,
                    ));
                    if (count($programmEvents)) {
                        $programmEventStatuses = $programmEvents->getList('programm_event_id', 'status');
                    }

                    $programmEvents = $programm->events->asArrayOfObjects();
                    usort($programmEvents, function ($item1, $item2) {
                        return $item1->ordr < $item2->ordr ? -1 : 1;
                    });

                    foreach ($programmEvents as $event) {

                        $stateId = 'HM_Recruit_Vacancy_Assign_State_' . $event->programm_event_id;
                        if (!$event->hidden && is_array($statesData) && isset($statesData[$stateId])) {

                            $params['name'] = $event->name;
                            $params['comment'] = $statesData[$stateId]->comment;
                            $params['comment_date'] = $statesData[$stateId]->comment_date ? date('d.m.Y', strtotime($statesData[$stateId]->comment_date)) : null;
                            $params['comment_user'] = ($statesData[$stateId]->comment_user_id && isset($recruiters[$statesData[$stateId]->comment_user_id])) ? $recruiters[$statesData[$stateId]->comment_user_id] : '';
                            $params['date'] = $statesData[$stateId]->end_date ? date('d.m.Y', strtotime($statesData[$stateId]->end_date)) : null;

                            // красный квадрат фиксируется только в state_of_process/current_state
                            // зеленый квадрат - в programm_event_user/status (для процессов где есть юзер)
                            //
                            // ВНИМАНИЕ! поле state_of_process/status - очень сомнительное, стоит хорошо подумать прежде чем использовать

//                            if ($stateOfProcess->status == HM_Process_Abstract::PROCESS_STATUS_COMPLETE) {
//                                // есть косяк в programm_event_user - последний event не отмечается как пройденный
//                                $params['status'] = HM_Programm_Event_User_UserModel::STATUS_PASSED;
//                            } else

                            if (
                                ($stateOfProcess->status == HM_Process_Abstract::PROCESS_STATUS_FAILED) &&
                                ($stateOfProcess->current_state == $stateId)
                            ){
                                $params['status'] = HM_Programm_Event_User_UserModel::STATUS_FAILED;
                            } else {
                                $params['status'] = $programmEventStatuses[$event->programm_event_id];
                            }

                            $evaluationId = $event->item_id;
                            $evaluation = $evaluations[$evaluationId];

                            if (!isset($methods[$evaluation->method])) {

                                if ($evaluation->method == HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE) {
                                    // какие срезы выводить на круговых диаграммах
                                    $params['relationTypes'] = array(
                                        HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_RECRUITER,
                                    );
                                } elseif ($evaluation->method == HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE) {
                                    // @todo: надо сделать специальную форму для итоговых мероприятий
                                    $evaluation->method = HM_At_Evaluation_EvaluationModel::TYPE_FORM;
                                }

                                $params['evaluation'] = $evaluation;
                                $methods[] = $params;
                            }
                        }
                    }
                }
            }
        }

        $this->view->texts['general'] = $this->_session->report_comment;
        $this->view->texts['competence_general'] = $this->getService('Option')->getOption('competenceReportComment', HM_Option_OptionModel::MODIFIER_RECRUIT);
        
        $this->view->sessionUser = $this->_sessionUser;
        $this->view->methods = $methods;


        $candidate = $vacancyCandidate->candidates->current();
        if ($candidate->source == HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER
            || $candidate->source == HM_Recruit_Provider_ProviderModel::ID_SUPERJOB
        ) {
            $this->view->getUrl = false;

        } else {
            $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $candidate->candidate_id);
            $filePath = $path. $candidate->candidate_id . '.docx';
            if (file_exists($filePath) && is_file($filePath)) {
                $this->view->getUrl = true;
            }
        };

        $this->view->vacancy_id   = $vacancyCandidate->vacancy_id;
        $this->view->candidate_id = $vacancyCandidate->candidate_id;
        $this->view->reportOnly   = $this->_getParam('report_only');
    }
    
    public function userSelectedAction()
    {
        $vacancyId  = $this->_getParam('vacancy_id',0);

        $candidate = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancyAssign')->fetchAllDependence('Vacancy', array(
            'vacancy_id = ?' => $vacancyId,
            'result = ?' => HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS,
        )));

        if ($candidate) {
            $this->_redirector->gotoSimple('user', null, null, array('vacancy_candidate_id' => $candidate->vacancy_candidate_id, 'vacancy_id' => $vacancyId));
        }
        
        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Ни один кандидат не прошел успешно сессию подбора')));
        $this->_redirector->gotoSimple('list', 'list', 'vacancy');
    
    }
    
    public function _getAge($min, $max)
    {
        if ($min && $max) {
            return sprintf('не моложе %s, не старше %s', $min, $max);
        } elseif ($min) {
            return sprintf('не моложе %s', $min);
        } elseif ($max) {
            return sprintf('не старше %s', $max);
        }
        return $this->view->reportNoValue();
    }

    public function _getGender($gender)
    {
        if ($gender) {
            return ($gender == 1) ? _('Мужской') : _('Женский');
        }
        return $this->view->reportNoValue();
    }
}
