<?php

class Candidate_IndexController extends HM_Controller_Action {

    protected $_candidateId = 0;
    protected $_candidate = null;
    protected $_vacancyId = 0;
    protected $_vacancy = null;


    const NOVALUE = 'Не указано';

    public function init()
    {
        $this->view->lists = array();
        $this->view->tables = array();
        parent::init();
    }

    public function preDispatch()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
    }

    public function indexAction()
    {
        $candidateId = $this->_getParam('candidate_id', 0);
        $candidate = $this->getService('RecruitCandidate')->find($candidateId)->current();
        $this->view->candidate = $candidate;
    }


    public function resumeAction()
    {
        $this->view->setHeader(_('Резюме'));
        $print = $this->_getParam('print', 0);

        if ($this->getService('User')->getCurrentUserRole() == 'guest') {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Недостаточно прав для просмотра страницы')));
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        $candidateId = $this->_getParam('candidate_id');
        $candidate = $this->_candidate = $this->getService('RecruitCandidate')->getOne(
            $this->getService('RecruitCandidate')->findDependence(array('User', 'VacancyAssign'), $candidateId)
        );

        $this->view->isInitiator = false;

        $currentUserId = $this->getService('User')->getCurrentUserId();

        if ($candidate) {

            $vacancyCandidate = count($candidate->vacancies) ? $candidate->vacancies->current() : false;

            if ($vacancy = $this->getService('RecruitVacancy')->getOne(
                $this->getService('RecruitVacancy')->findManyToMany('Recruiter', 'RecruiterAssign', $vacancyCandidate->vacancy_id)
            )) {
                $this->view->setSubHeader($vacancy->name);
            }

            if ($vacancy->status != HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL) {
                $processId = $vacancyCandidate ? $candidate->vacancies->current()->process_id : null;

                if ($processId) {

                    $state = $this->getService('State')->fetchAllDependence('StateData', array(
                        'item_id = ?' => $vacancyCandidate->vacancy_candidate_id,
                        'process_id = ?' => $processId
                    ))->current();

                    $hideButtons = false;
                    $stateData = null;

                    if ($state && $state->stateData) {
                        $stateData = $state->stateData->current();
                    }

                    $this->getService('Process')->initProcess($vacancyCandidate);
                    $process = $vacancyCandidate->getProcess();
                    foreach ($process->getStates() as $processState) {
                        if (mb_strpos($processState->getClass(), 'failed')) {
                            $hideButtons = true;
                            break;
                        }
                    }
                }

                $this->view->hideButtons = $hideButtons;

                $currentUserCommentStates = null;
                if ($state->state_of_process_id) {
                    $currentUserCommentStates = $this->getService('StateData')->fetchAll(array(
                        'state_of_process_id =?' => $state->state_of_process_id,
                        'comment_user_id =?' => $currentUserId
                    ));
                }
                $currentUserHasComments = count($currentUserCommentStates) > 0;

                $this->view->vacancyCandidate = $vacancyCandidate;
                $this->view->processId = $processId;
                $this->view->stateData = $stateData;
                $this->view->currentUserHasComments = $currentUserHasComments;
            }

            $isOwner = false;
            if (count($vacancy->recruiters)) {
                $recruiterIds = $vacancy->recruiters->getList('user_id');
                $isOwner = in_array($this->getService('User')->getCurrentUserId(), $recruiterIds);
            }

//            $this->view->isInitiator = $this->getInitiators($candidate);
            $this->view->isInitiator = !$this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL));

            $this->view->candidate = $candidate;
            $this->view->userId = $candidate->user_id;
            $this->view->user = count($candidate->user) ? $candidate->user->current() : false;
            $this->view->declineable = ($isOwner && $vacancyCandidate) ? ($vacancyCandidate->status != HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED) : false;
            $this->_helper->viewRenderer->setNoRender();
            $this->view->print = $print;

            if ($candidate->isJsonResume()) {
                switch ($candidate->source) {
                    case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                        $resumeData = json_decode($candidate->resume_json);
                        $this->_resumeHH($resumeData);
                        break;
                    case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                        $resumeData = json_decode($candidate->resume_json);
                        $this->_resumeSuperJob($resumeData);
                        break;
                    case HM_Recruit_Provider_ProviderModel::ID_ESTAFF:
                        $this->_resumeEstaff();
                        break;
                    default:
                        $this->_resumeSmall();
                        break;
                }
            } elseif ($candidate->isFileResume()) {
                $this->_resumeFile();
            } elseif ($candidate->isHtmlResume()) {
                $this->_resumeHtml();
            } elseif (!($candidate->isJsonResume() && $candidate->isFileResume() && $candidate->isHtmlResume()) && $isBlankView) {
                $this->_resume404();
            } else {
                $this->downloadResumeFile($candidateId);
            }
        }
    }

    public function setChiefCommentAction()
    {
        $vacancyCandidateId = $this->getRequest()->getParam("vacancy_candidate_id", 0);
        $processId = $this->getRequest()->getParam("process_id", 0);
        $comment   = $this->getRequest()->getParam("comment");

        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'item_id = ?' => $vacancyCandidateId,
            'process_id = ?' => $processId
        )));

        if ($state && count($state->stateData) && ($stateData = $state->stateData->current())) {
            $this->getService('StateData')->update(array(
                'comment' => $comment,
                'comment_date' => date('Y-m-d H:i:s'),
                'comment_user_id' => $this->getService('User')->getCurrentUserId(),
                'state_of_process_data_id' => $stateData->state_of_process_data_id
            ));
        }

        echo json_encode($comment);
        die();
    }

    public function downloadAction()
    {
        $candidateId = $this->getRequest()->getParam('candidate_id');
        $this->downloadResumeFile($candidateId);
    }

    protected function downloadResumeFile($candidateId)
    {
        $candidate = $this->getService('RecruitCandidate')->find($candidateId)->current();
        $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $candidate->candidate_id);
        $filePath = $path. $candidate->candidate_id . '.docx';
        if (file_exists($filePath) && is_file($filePath)) {
            $options = array('filename' => $candidate->candidate_id . '.docx');
            $this->_helper->SendFile(
                $filePath,
                'application/unknown',
                $options
            );
            die();
        } else {
            $this->_resume404();
//            $this->_flashMessenger->addMessage(_('Файл не найден'));
//            if ($this->_getParam('user-report', 0)) {
//                $url = $this->view->url(array('module' => 'user', 'controller' => 'report', 'action' => 'index', 'user_id' => $candidate->user_id, 'baseUrl' => ''), null, true);
//                $this->_redirector->gotoUrl($url, array('prependBase' => false));
//
//            } else {
//                if ($this->_getParam('vacancy_id', 0)) {
//                    $this->_redirector->gotoSimple('index', 'assign', 'candidate', array('vacancy_id' => $this->_getParam('vacancy_id', 0)));
//                } else {
//                    $this->_redirector->gotoSimple('index', 'list', 'candidate');
//                }
//            }
        }
    }

    protected function getInitiators($candidate)
    {
        $initiator = 0;

        $vacancy = $candidate->vacancies->current();
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

        if ($vacancyDataFields) {
            $initiator = $vacancyDataFields->user_id;
        }

        return $this->getService('User')->getCurrentUserId() == $initiator;
    }

    protected function _resumeSmall()
    {
        $user = $this->getService('User')->findOne($this->view->userId);

        $this->view->photo = '/'.$user->getPhoto();
        $this->view->name = $user->getName();
        $this->view->downloadUrl = $this->view->url(array(
            'action' => 'resume',
            'controller' => 'index',
            'module' => 'candidate',
            'baseUrl' => 'recruit',
            'vacancy_id' => $this->_vacancy->vacancy_id,
            'candidate_id' =>  $this->_candidate->candidate_id,
            'fromquest' =>  null,
            'ajax' =>  null,
        ));

        $request = $this->getRequest();
        $this->view->referer = $request->getHeader('referer');
        $tpl = 'index/resume/small.tpl';

        if ($this->isAjaxRequest()) {
            $this->view->isAjax = true;
            return $this->view->content =  $this->view->render($tpl);
        }
        echo  $this->view->render($tpl);
    }

    protected function _resume404()
    {
        $user = $this->getService('User')->findOne($this->view->userId);
        $this->view->name = $userName = $user->getName();
        $request = $this->getRequest();
        $this->view->referer = $request->getHeader('referer');

        $tpl = 'index/resume/404.tpl';
        if ($this->isAjaxRequest()) {
            $this->view->title = $userName;
            $this->view->isAjax = true;
            return $this->view->content = $this->view->render($tpl);
        }

        echo $this->view->render($tpl);
    }

    protected function _resumeFile()
    {
        $user = $this->getService('User')->findOne($this->view->userId);
        $this->view->name = $user->getName();

        $request = $this->getRequest();
        $this->view->referer = $request->getHeader('referer');

        $tpl = 'index/resume/file.tpl';
        if ($this->isAjaxRequest()) {
            $this->view->isAjax = true;
            return $this->view->content = $this->view->render($tpl);
        }
        echo $this->view->render($tpl);
    }

    protected function _resumeHtml()
    {
        $user = $this->getService('User')->findOne($this->view->userId);
        $this->view->name = $name = $user->getName();

        $tpl = 'index/resume/html.tpl';
        if ($this->isAjaxRequest()) {
            $this->view->isAjax = true;
            $this->view->fullscreen = true;
            $links[] = [
                'name' => _('Печать'),
                'url' => $this->view->url(array("module" => "candidate", "controller" => "index", "action" => "resume", "print" => 1, "blank" => 1))
            ];
            $this->view->links = $links;
            return $this->view->content = $this->view->render($tpl);
        }

        $request = $this->getRequest();
        $this->view->referer = $request->getHeader('referer');
        echo $this->view->render($tpl);
    }

    protected function _resumeEstaff()
    {
        $user = $this->getService('User')->findOne($this->view->userId);

        $this->view->photo = '/'.$user->getPhoto();
        $this->view->name = $user->getName();

        $this->view->estaffUrl = $this->view->url(array(
            'action' => 'resume',
            'controller' => 'report',
            'module' => 'user',
            'baseUrl' => 'recruit',
            'user_id' => $user->MID,
            'spot_id' =>  $this->_candidate->spot_id,
            'fromquest' =>  null,
            'ajax' =>  null,
        ));
        
        $request = $this->getRequest();
        $this->view->referer = $request->getHeader('referer');
        echo $this->view->render('index/resume/small.tpl');
    }

    protected function resumeView($data, $type, $gender = null)
    {
        $months = array(
            1 => _('Января'),
            2 => _('Февраля'),
            3 => _('Марта'),
            4 => _('Апреля'),
            5 => _('Мая'),
            6 => _('Июня'),
            7 => _('Июля'),
            8 => _('Августа'),
            9 => _('Сентября'),
            10 => _('Октября'),
            11 => _('Ноября'),
            12 => _('Декабря'),
        );
        switch ($type) {
            case 'age':
                if (in_array($data % 10, array(2, 3, 4))) return $data . ' года';
                elseif ($data % 10 == 1) return $data . ' год';
                else return $data . ' лет';
                break;
            case 'birth_date':
                $part = explode('-', $data);
                return _('дата рождения: ') . $part[2].' '.strtolower($months[(int)$part[1]]).' '.$part[0].' года';
                break;
            case 'period':
                $start = $data[0];
                $end   = $data[1];
                list($startYear, $startMonth, $startDay) = explode('-', $start);
                if ($end == '') {
                    $endDate = ' - <br>по настоящее время';
                } else {
                    list($endYear, $endMonth, $endDay) = explode('-', $end);
                    $endDate = ' - <br>'.(int)$endDay.' '.strtolower($months[(int)$endMonth]).' '.$endYear;
                }

                return (int)$startDay.' '.strtolower($months[(int)$startMonth]).' '.$startYear.$endDate;
                break;
        }
    }

    protected function _resumeHH($resumeData)
    {
//        $resumeData1 = json_decode('{"id":"12345678901234567890123456789012abcdef","last_name":"Фамилия","first_name":"Имя","middle_name": "Отчество","age": 36,"birth_date": "1980-05-08","gender":{"id": "male","name": "Мужской"},"area": {"url": "https://api.hh.ru/areas/1","id": "1","name": "Москва"},"metro": {"lat": 55.658147,"lng": 37.540957,"order": 19,"id": "6.41","name": "Калужская"},"relocation": {"type": {"id": "relocation_possible","name": "готов к переезду"},"area": [{"url": "https://api.hh.ru/areas/2","id": "2","name": "Санкт-Петербург"},{"url": "https://api.hh.ru/areas/76","id": "76","name": "Ростов-на-Дону"}]},"business_trip_readiness": {"id": "ready","name": "Готов к командировкам"},"contact": [{"comment": null,"type": {"id": "cell","name": "Мобильный телефон"},"preferred": true,"value": {"country": "7","city": "123","number": "4567890","formatted": "+71234567890"}},{"type": {"id": "email","name": "Эл. почта"},"preferred": false,"value": "applicant@example.com"}],"site": [{"url": "echo123","type": {"id": "skype","name": "Skype"}},{"url": "123456","type": {"id": "icq","name": "ICQ"}}],"title": "Программист Python","photo": {"small": "https://hh.ru/...","medium": "https://hh.ru/...","id": "1337"},"portfolio": [{"small": "https://hh.ru/...","medium": "https://hh.ru/...","id": "1337","description": "..."}],"specialization": [{"id": "1.221","name": "Программирование, Разработка","profarea_id": "1","profarea_name": "Информационные технологии, интернет, телеком","laboring": false},{"id": "1.89","name": "Интернет","profarea_id": "1","profarea_name": "Информационные технологии, интернет, телеком","laboring": false},{"id": "1.9","name": "Web инженер","profarea_id": "1","profarea_name": "Информационные технологии, интернет, телеком","laboring": false}],"salary": {"amount": 100500,"currency": "RUR"},"employments": [{"id": "full","name": "Полная занятость"},{"id": "part","name": "Частичная занятость"}],"schedules": [{"id": "fullDay","name": "Полный день"},{"id": "flexible","name": "Гибкий график"}],"education": {"elementary": [{"name": "Школа №1923","year": 2003}],"additional": [{"name": "Курс повышения квалификации","organization": "Проводившая организация","result": "Специализация","year": 2006}],"attestation": [{"name": "Тест на IQ","organization": "IQ центр","result": "Интеллект квалификейшн","year": 2005}],"primary": [{"name": "Московская государственная академия ветеринарной медицины и биотехнологии имени К.И. Скрябина, Москва","name_id": "39464","organization": "Факультет зоотехнологий и агробизнеса","organization_id": "25181","result": "Социальная психология","result_id": null,"year": 2000}],"level": {"id": "higher","name": "Высшее"}},"language": [{"id": "rus","name": "Русский","level": {"id": "native","name": "родной"}},{"id": "eng","name": "Английский","level": {"id": "can_read","name": "читаю профессиональную литературу"}}],"experience": [{"company": "Название работодателя","company_id": null,"area": {"url": "https://api.hh.ru/areas/113","id": "113","name": "Россия"},"company_url": "http://www.rbc.ru","industries": [{"id": "7.540","name": "Разработка программного обеспечения"},{"id": "9.399","name": "Мобильная связь"}],"position": "Должность","start": "2005-04-01","end": "2013-01-01","description": "Описание деятельности в компании"}],"total_experience": {"months": 94},"skills": "Дополнительная информация: ключевые навыки","skill_set": ["HTML","CSS"],"citizenship": [{"url": "https://api.hh.ru/areas/113","id": "113","name": "Россия"} ],"work_ticket": [{"url": "https://api.hh.ru/areas/113","id": "113","name": "Россия"}],"travel_time": {"id": "any","name": "Не имеет значения"},"recommendation": [{"name": "Петров Петр","position": "старший научный пользователь","organization": "Роскосмос"}], "resume_locale": {"id": "RU", "name": "Русский"}, "certificate": [{"title": "Oracle Certified Java Professional Programmer","achieved_at": "2013-01-01","type": "custom","owner": null,"url": "https://example.com/certificate/123456"},{"title": "MCSE: Windows NT 4.0","achieved_at": "1998-01-26","owner": "JOHN DOE","type": "microsoft","url": null}],"alternate_url": "https://hh.ru/resume/12345678901234567890123456789012abcdef","created_at": "2013-05-31T14:27:04+0400","updated_at": "2013-10-17T15:22:55+0400"}');

        $generalInfo = array();
        $fio = trim($resumeData->last_name.' '.$resumeData->first_name.' '.$resumeData->middle_name);
        if($fio){
            $generalInfo[_('ФИО:')] = $fio;
        }
        $age = trim($resumeData->age);
        if($age){
            $generalInfo[_('Возраст:')] = $this->resumeView($age, 'age');
        }
        $birth_date = trim($resumeData->birth_date);
        if($birth_date){
            $generalInfo[_('День рождения:')] = $this->resumeView($birth_date, 'birth_date', $resumeData->gender->name);
        }
        $genderName = trim($resumeData->gender->name);
        if($genderName){
            $generalInfo[_('Пол:')] = ($genderName == 'Женский') ? 'Женщина' : 'Мужчина';
        }
        $areaName = trim($resumeData->area->name);
        if($areaName){
            $generalInfo[_('Расположение:')] = $areaName;
        }
        $metro = trim($resumeData->metro->name);
        if($metro){
            $generalInfo[_('Метро:')] = $metro;
        }
        $relocationTypeName = trim($resumeData->relocation->type->name);
        $relocationAreaName = '';
        if(isset($resumeData->relocation->area)){
            foreach ($resumeData->relocation->area as $item){
                $relocationAreaName .= $item->name.", ";
            }
            $relocationAreaName = substr($relocationAreaName,0,-2);
        }
        if($relocationTypeName){
            $generalInfo[$this->upFirstCharacter($relocationTypeName)] = $relocationAreaName;
        }
        $business_trip_readinessName = trim($resumeData->business_trip_readiness->name);
        if($business_trip_readinessName){
            $generalInfo[_('Командировки:')] = $business_trip_readinessName;
        }
        if(isset($resumeData->contact)){
            foreach ($resumeData->contact as $item){
                $contact = trim($item->type->name);
                if($contact){
                    $preferred = '';
                    if($item->preferred){
                        $preferred = '<span id="preffered-link"> - (предпочитаемый способ связи)</span>';
                    }
                    if (is_a($item->type, 'stdClass')){
                        $generalInfo[$contact] = $item->value->formatted.' '.$preferred;
                    } else {
                        $generalInfo[$contact] = $item->value.' '.$preferred;
                    }
                }
            }
        }
        if(isset($resumeData->site)){
            foreach ($resumeData->site as $item){
                $site = trim($item->type->name);
                if($site){
                    $generalInfo[$site] = $item->url;
                }
            }
        }
        $this->view->lists['resumeGeneral'] = $generalInfo;
        $this->view->lists['resumeTitle'] = $resumeData->title;
        $this->view->lists['resumeAmount'] = $resumeData->salary->amount.' '.$resumeData->salary->currency;
        if(isset($resumeData->specialization)){
            $specialization = array();
            foreach ($resumeData->specialization as $item){
                $specialization[$item->profarea_name] .= "- ".$item->name."<br>";
            }
            $this->view->lists['resumeSpecialization'] = $specialization;
        }
        if(isset($resumeData->employments)){
            $employments = '';
            foreach ($resumeData->employments as $item){
                $employments .= $item->name.", ";
            }
            $employments = substr($employments,0,-2);
            $this->view->lists['resumeEmployments'] = $employments;
        }
        if(isset($resumeData->schedules)){
            $schedules = '';
            foreach ($resumeData->schedules as $item){
                $schedules .= $item->name.", ";
            }
            $schedules = substr($schedules,0,-2);
            $this->view->lists['resumeSchedules'] = $schedules;
        }

        $this->view->total_experience = $this->experienceToString($resumeData->total_experience->months);

        $experience = array();
        if(isset($resumeData->experience)){
            foreach ($resumeData->experience as $item){
                $startExperience = is_null($item->start) ? self::NOVALUE : $item->start;
                $endExperience = is_null($item->end) ? '' : $item->end;
                $workPeriod = $this->resumeView(array($startExperience, $endExperience), 'period');
                $industries = '';
                foreach ($item->industries as $industry){
                    $industries .= $industry->name.', ';
                }
                $industries = substr($industries,0, -1);
                $aboutExperience = '';
                if($item->company){
                    $aboutExperience .= $item->company.'<br>';
                }
                if($item->company_url){
                    $aboutExperience .= $item->company_url.'<br>';
                }
                if($item->area->name){
                    $aboutExperience .= $item->area->name.'<br>';
                }
                if($industries){
                    $aboutExperience .= $industries.'<br>';
                }
                if($item->position){
                    $aboutExperience .= $item->position.'<br>';
                }
                if($item->description){
                    $aboutExperience .= nl2br(str_replace('?', '&bull;', $item->description)) . '<br>';
                }
                $aboutExperience = trim($aboutExperience);
                $experience []= array(
                    $workPeriod => $aboutExperience ? $aboutExperience : self::NOVALUE,
                );
            }
        }
        $this->view->lists['resumeExperience'] = $experience;
        if(isset($resumeData->skill_set)){
            foreach ($resumeData->skill_set as $item){
                $this->view->lists['resumeSkill_set'] .= $item.', ';
            }
        }

        $this->view->lists['resumeSkill_set'] = substr($this->view->lists['resumeSkill_set'],0,-2);
        $this->view->lists['resumeSkills'] = $resumeData->skills;
        $this->view->lists['resumeEducationLevel'] = trim($resumeData->education->level->name);
        $educationPrimary = array();
        $educationElementary = array();
        $educationAdditional = array();
        $educationAttestation = array();
        if(isset($resumeData->education)){
            foreach ($resumeData->education as $key=>$value){
                foreach ($value as $item) {
                    $year = is_null($item->year) ? self::NOVALUE : $item->year;
                    $aboutEducation = '';
                    $aboutEducation .= $item->name . '<br>';
                    if('primary' == $key) {
                        $aboutEducation .= $item->organization . '<br>';
                        $aboutEducation .= $item->result . '<br>';
                        $educationPrimary []= array($year => $aboutEducation);
                    }elseif('elementary' == $key){
                        $educationElementary []= array($year => $aboutEducation);
                    }elseif('additional' == $key){
                        $aboutEducation .= $item->organization . '<br>';
                        $aboutEducation .= $item->result . '<br>';
                        $educationAdditional []= array($year => $aboutEducation);
                    }elseif('attestation' == $key){
                        $aboutEducation .= $item->organization . '<br>';
                        $aboutEducation .= $item->result . '<br>';
                        $educationAttestation []= array($year => $aboutEducation);
                    }
                }
            }
        }
        $this->view->lists['resumeEducationPrimary'] = $educationPrimary;
        $this->view->lists['resumeEducationElementary'] = $educationElementary;
        $this->view->lists['resumeEducationAdditional'] = $educationAdditional;
        $this->view->lists['resumeEducationAttestation'] = $educationAttestation;
        if(isset($resumeData->language)){
            foreach ($resumeData->language as $item){
                $this->view->lists['resumeLanguage'] .= $item->name.' - '.$item->level->name.'<br>';
            }
        }
        if(isset($resumeData->certificate)){
            foreach ($resumeData->certificate as $item){
                $resumeCertificates = array();
                $resumeCertificate = '';
                $resumeCertificate .= $item->title.'<br>';
                $resumeCertificate .= $item->type.'<br>';
                $resumeCertificate .= $item->url.'<br>';
                $resumeCertificates[$item->achieved_at] = $resumeCertificate;
                $this->view->lists['resumeCertificate'] []= $resumeCertificates;            }
        }
        $portfolioInfo = array();
        if(isset($resumeData->portfolio)){
            foreach ($resumeData->portfolio as $item){
                $portfolio = trim($item->small);
                $description = trim($item->description);
                $description = $description ? $description : self::NOVALUE;
                if($portfolio){
                    $portfolioInfo[$portfolio] = $description;
                }
            }
        }
        $this->view->lists['resumePortfolio'] = $portfolioInfo;
        if(isset($resumeData->citizenship)){
            foreach ($resumeData->citizenship as $item){
                $this->view->lists['resumeCitizenship'] .= $item->name.', ';
            }
        }
        $this->view->lists['resumeCitizenship'] = substr($this->view->lists['resumeCitizenship'],0,-2);
        if(isset($resumeData->work_ticket)){
            foreach ($resumeData->work_ticket as $item){
                $this->view->lists['resumeWork_ticket'] .= $item->name.', ';
            }
        }
        $this->view->lists['resumeWork_ticket'] = substr($this->view->lists['resumeWork_ticket'],0,-2);
        $this->view->lists['resumeTravel_time'] = $resumeData->travel_time->name;
        $resumeRecommendation = '';
        $resumeRecommendations = array();
        if(isset($resumeData->recommendation)){
            foreach ($resumeData->recommendation as $item){
                $resumeRecommendation .= $item->name.'<br>';
                $resumeRecommendation .= $item->position.'<br>';
                $resumeRecommendations[$item->organization] = $resumeRecommendation;
            }
        }
        $this->view->lists['resumeRecommendation'] = $resumeRecommendations;
        // Разобрать $resumeData
//        $this->view->resumeData = $resumeData; // TODO: убрать
        
        $request = $this->getRequest();
        $this->view->referer = $request->getHeader('referer');
        $tpl = 'index/resume.tpl';

        if ($this->isAjaxRequest()) {
            $this->view->isAjax = true;
            $this->view->fullscreen = true;
            $links[] = [
                'name' => _('Печать'),
                'url' => $this->view->url(array("module" => "candidate", "controller" => "index", "action" => "resume", "print" => 1, "blank" => 1))
            ];
            $this->view->links = $links;
            return $this->view->content = $this->view->render($tpl);
        }
        echo $this->view->render($tpl);
    }

    protected function _resumeSuperJob($resumeData)
    {
        $generalInfo = array();
        $fio = trim($resumeData->lastname.' '.$resumeData->firstname.' '.$resumeData->middlename);
        if($fio){
            $generalInfo[_('ФИО:')] = $fio;
        }
        $age = trim($resumeData->age);
        if($age){
            $generalInfo[_('Возраст:')] = $this->resumeView($age, 'age');
        }
        $birthYear = $resumeData->birthyear ? $resumeData->birthyear : ' ';
        $birthMonth = $resumeData->birthmonth ? $resumeData->birthmonth : ' ';
        $birthDay = $resumeData->birthday ? $resumeData->birthday : ' ';
        $birth_date = trim($birthYear.'-'.$birthMonth.'-'.$birthDay);
        if($birth_date){
            $generalInfo[_('День рождения:')] = $this->resumeView($birth_date, 'birth_date', $resumeData->gender->name);
        }
        $genderName = trim($resumeData->gender->title);
        if($genderName){
            $generalInfo[_('Пол:')] = ($genderName == 'Женский') ? 'Женщина' : 'Мужчина';
        }
        $areaName = '';
        $areaName .= $resumeData->region->title.', ';
        $areaName .= $resumeData->town->title.', ';
        $areaName .= $resumeData->address;
        $areaName = trim($areaName);
        if($areaName){
            $generalInfo[_('Расположение:')] = $areaName;
        }
        $metro = '';
        foreach ($resumeData->metro as $item){
            $metro .= $item->title.', ';
        }
        $metro = trim(substr($metro,0,-2));
        if($metro){
            $generalInfo[_('Метро:')] = $metro;
        }
        $relocationAreaName = '';
        if(isset($resumeData->moveable_towns) && $resumeData->moveable){
            foreach ($resumeData->moveable_towns as $item){
                $relocationAreaName .= $item->title.", ";
            }
            $relocationAreaName = trim(substr($relocationAreaName,0,-2));
        }
        if($relocationAreaName){
            $generalInfo[_('Готов к переезду:')] = $relocationAreaName;
        }
        $business_trip_readinessName = trim($resumeData->business_trip->title);
        if($business_trip_readinessName){
            $generalInfo[_('Командировки:')] = $business_trip_readinessName;
        }
        $phone = '';
        if($resumeData->phone1){
            $phone .= $resumeData->phone1.' ( '.$resumeData->timebeg1.' - '.$resumeData->timeend1.' ), ';
        }
        if($resumeData->phone2){
            $phone .= $resumeData->phone2.' ( '.$resumeData->timebeg2.' - '.$resumeData->timeend2.' ), ';
        }
        $phone = trim(substr($phone,0, -2));
        if($phone){
            $generalInfo[_('Мобильный телефон:')] = $phone;
        }
        if(trim($resumeData->email)){
            $generalInfo[_('Эл. почта:')] = trim($resumeData->email);
        }
        if(trim($resumeData->other_contacts)){
            $generalInfo[_('Другие контакты:')] = trim($resumeData->other_contacts);
        }
        if(isset($resumeData->social_links)){
            foreach ($resumeData->social_links as $item){
                $generalInfo[_($item->title)] = trim($item->link);
            }
        }
        $this->view->lists['resumeGeneral'] = $generalInfo;
        $this->view->lists['resumeTitle'] = trim($resumeData->profession);
        $this->view->lists['resumeAmount'] = trim($resumeData->payment.' '.$resumeData->currency);
        if(isset($resumeData->catalogues)){
            $specialization = array();
            foreach ($resumeData->catalogues as $item){
                foreach ($item->positions as $value){
                    $specialization[$item->title] .= "- ".$value->title."<br>";
                }
            }
            $this->view->lists['resumeSpecialization'] = $specialization;
        }
        $resumeEmployments = '';
        $resumeEmployments .= $resumeData->type_of_work->title;
        if(isset($resumeData->place_of_work->title)){
            $resumeEmployments .= ' ( '.$resumeData->place_of_work->title.' )';
        }
        $this->view->lists['resumeEmployments'] = trim($resumeEmployments);

        $total_month_experience = 0;
        $experience = array();

        if(isset($resumeData->work_history)){
            foreach ($resumeData->work_history as $item){
                if(isset($item->yearend) && isset($item->yearbeg) && ($item->yearend > $item->yearbeg)){
                    $count_fullYear_month = $item->yearend - $item->yearbeg - 1;
                    if($count_fullYear_month > 0){
                        $count_fullYear_month *= 12;
                    }
                    $count_monthend = $item->monthend;
                    $count_monthbeg = 12 - $item->monthbeg;
                    $total_month_experience += $count_fullYear_month + $count_monthend + $count_monthbeg;
                }
                $startExperience = $this->getMonth($item->monthbeg).' '.$item->yearbeg;
                $endExperience = $this->getMonth($item->monthend).' '.$item->yearend;
                $workPeriod = $startExperience.' - '.$endExperience;
                $aboutExperience = '';
                if($item->town->title){
                    $aboutExperience .= $item->town->title.'<br>';
                }
                if($item->name){
                    $aboutExperience .= $item->name.'<br>';
                }
                if($item->profession){
                    $aboutExperience .= $item->profession.'<br>';
                }
                if($item->type->title){
                    $aboutExperience .= $item->type->title.'<br>';
                }
                $aboutExperience = trim($aboutExperience);
                $experience []= array(
                    $workPeriod => $aboutExperience ? $aboutExperience : self::NOVALUE);
            }
        }

        $this->view->total_experience = $this->experienceToString($total_month_experience);
        $this->view->lists['resumeExperience'] = $experience;

        $this->view->lists['resumeSkills'] = '';
        if($resumeData->achievements){
            $this->view->lists['resumeSkills'] .= ' - '.$resumeData->achievements.'<br>';
        }
        if($resumeData->additional_info){
            $this->view->lists['resumeSkills'] .= ' - '.$resumeData->additional_info.'<br>';
        }
        if($resumeData->maritalstatus->title){
            $this->view->lists['resumeSkills'] .= ' - '.$resumeData->maritalstatus->title.'<br>';
        }
        if($resumeData->children->title){
            $this->view->lists['resumeSkills'] .= ' - дети: '.$resumeData->children->title.'<br>';
        }
        if($resumeData->driving_licence){
            $drivingRights = '';
            foreach ($resumeData->driving_licence as $item){
                $drivingRights .= $item.', ';
            }
            $drivingRights = substr($drivingRights,0,-2);
            $this->view->lists['resumeSkills'] .= ' - водительские права: '.$drivingRights.'<br>';
        }

        $this->view->lists['resumeEducationLevel'] = trim($resumeData->education->title);
        $educationPrimary = array();
        if(isset($resumeData->base_education_history)){
            foreach ($resumeData->base_education_history as $item){
                $year = trim($item->yearend) ? $item->yearend : self::NOVALUE;
                $aboutEducation = '';
                if($item->institute->title){
                    $aboutEducation .= $item->institute->title . '<br>';
                }
                if($item->town->title){
                    $aboutEducation .= $item->town->title . '<br>';
                }
                if($item->faculty){
                    $aboutEducation .= $item->faculty . '<br>';
                }
                if($item->profession){
                    $aboutEducation .= $item->profession . '<br>';
                }
                if($item->education_type->title){
                    $aboutEducation .= $item->education_type->title . '<br>';
                }
                if($item->education_form->title){
                    $aboutEducation .= $item->education_form->title . '<br>';
                }
                $educationPrimary [] = array($year => $aboutEducation);
            }
        }
        $this->view->lists['resumeEducationPrimary'] = $educationPrimary;
        if(isset($resumeData->languages)){
            foreach ($resumeData->languages as $item){
                $this->view->lists['resumeLanguage'] .= $item[0]->title.' - '.$item[1]->title.'<br>';
            }
        }
        if(isset($resumeData->education_history)){
            foreach ($resumeData->education_history as $item){
                $resumeCertificates = array();
                $resumeCertificate = '';
                $year = trim($item->yearend) ? $item->yearend : self::NOVALUE;
                if($item->institute){
                    $resumeCertificate .= $item->institute.'<br>';
                }
                if($item->town->title){
                    $resumeCertificate .= $item->town->title.'<br>';
                }
                if($item->name){
                    $resumeCertificate .= $item->name.'<br>';
                }
                $resumeCertificates[$year] = $resumeCertificate;
                $this->view->lists['resumeEducationAdditional'] []= $resumeCertificates;            }
        }
        if(trim(isset($resumeData->citizenship->title))){
            $this->view->lists['resumeCitizenship'] = $resumeData->citizenship->title;
        }
        if(isset($resumeData->work_ticket)){
            foreach ($resumeData->work_ticket as $item){
                $this->view->lists['resumeWork_ticket'] .= $item->name.', ';
            }
        }
        $this->view->lists['resumeRecommendation'] = $resumeData->recommendations;
        // Разобрать $resumeData
//        $this->view->resumeData = $resumeData; // TODO: убрать
//        echo $this->view->render('index/resume/superjob.tpl');
        echo $this->view->render('index/resume.tpl');
    }

    /**
     * Переводит общее количество месяцев работы в читаемый вид
     * @param int $total_month_experience Общее количество месяцев работы
     * @return string
     */
    public function experienceToString($total_month_experience){
        $years_experience = floor($total_month_experience/12);
        if($years_experience == 1) {
            $strYear = 'год';
        }
        elseif($years_experience == 2 || $years_experience == 3 || $years_experience == 4){
            $strYear = 'года';
        }
        else{
            $strYear = 'лет';
        }
        $months_experience = $total_month_experience - $years_experience * 12;
        if($months_experience == 1) {
            $strMonth = 'месяц';
        }
        elseif($months_experience == 2 || $months_experience == 3 || $months_experience == 4){
            $strMonth = 'месяца';
        }
        else{
            $strMonth = 'месяцев';
        }
        $total_experience = 'Опыт работы';
        if($years_experience != 0 || $months_experience != 0){
            if($years_experience != 0){
                $total_experience .= " $years_experience $strYear";
            }
            if($months_experience != 0){
                $total_experience .= " $months_experience $strMonth";
            }
        }else{
            $total_experience .= " отсутствует";
        }
        return $total_experience;
    }

    /**
     * Получает название месяца по его порядковому номеру
     * @param int $search Порядковый номер месяца
     * @return mixed
     */
    public function getMonth($search){
        $months = array(
            'январь' => 1,
            'февраль' => 2,
            'март' => 3,
            'апрель' => 4,
            'май' => 5,
            'июнь' => 6,
            'июль' => 7,
            'август' => 8,
            'сентябрь' => 9,
            'октябрь' => 10,
            'ноябрь' => 11,
            'декабрь' => 12,
        );
        return array_search ($search, $months);
    }

    /**
     * Делает первый символ строки заглавным
     * @param string $str Строка для преобразования
     * @return string
     */
    public function upFirstCharacter($str){
        $first = mb_substr($str,0,1, 'UTF-8');
        $last = mb_substr($str,1);
        $first = mb_strtoupper($first, 'UTF-8');
        $last = mb_strtolower($last, 'UTF-8');
        return $first.$last;
    }




    public function ignoreResumesAction()
    {
        $params = $this->getRequest()->getParams();

        $vacancyId = $params['vacancy_id'];
        $userId = $this->getService('User')->getCurrentUserId();
        $response = array();
        $ids = array();
        foreach ($params['resumes'] as $resumeId => $resumeData) {
            try {
                $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
                    'vacancy_id' => 0, // ������ ignore
                    'hh_resume_id' => (int) $resumeId,
                    'date' => new Zend_Db_Expr('NOW()'),
                    'create_user_id' => $userId
                ));
                $ids[] = $resumeId;
            } catch (Exception $e) {

            }
        }
        $response['state'] = 'ok';
        $response['ids'] = $ids;
        $k = sizeof($params['resumes']);
        if ($k > 0 && sizeof($ids) != $k) {
            $response['state'] = 'error';
        }
        $this->_helper->json($response);
    }



    // DEPRECATED!
    public function applyAction()
    {
return 0;
        // на самом деле сюда приходит массив user_id
        $candidates = $this->_getParam('resumes', $this->_getParam('postMassIds_grid'));

        $params = $this->getRequest()->getParams();

        $vacancy_id = $params['vacancy_id'];
        $userId = $this->getService('User')->getCurrentUserId();
        $vacancy = $this->getService('RecruitVacancy')->find($vacancy_id)->current();
        $ids = array();
        $response = array();
        foreach ($candidates as $candidateId => $candidate) {
            $user = $this->getService('User')->fetchRow("MID = " . intval($candidateId));
            // добавляем кандидата
            try {
                $candidate = $this->getService('RecruitCandidate')->insert(array(
                    'user_id' => intval($candidateId),
                    'source' => HM_Recruit_Candidate_CandidateModel::SOURCE_INTERNAL,
                ));
                // присваиваем кандидата вакансии
                $this->getService('RecruitVacancyAssign')->assign($vacancy_id, $candidate->candidate_id);
                // отправляем письмо
                $messenger = $this->getService('Messenger');

                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_INVITE_TO_INTERVIEW, array('vacancy' => $vacancy->name), '', 0
                );

                try {
                    $messenger->send($this->getService('User')->getCurrentUserId(), $user->MID);
                } catch (Exception $e) {

                }

                // делаем невидимым
                $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
                    'vacancy_id' => (int) $vacancy_id,
                    'hh_resume_id' => (int) $candidateId,
                    'date' => new Zend_Db_Expr('NOW()'),
                    'create_user_id' => $userId
                ));
                $ids[] = $candidateId;
            } catch (Exception $e) {

            }
        }
        $k = sizeof($candidates);
        $response['state'] = 'ok';
        $response['ids'] = $ids;
        if ($k > 0 && sizeof($ids) != $k) {
            $response['state'] = 'error';
        }
        $this->_helper->json($response);
    }


    // DEPRECATED!
    public function applyResumesAction()
    {
return 0;

        $candidates = $this->_getParam('resumes');
        $k = sizeof($candidates);
        $vacancyId = $this->_getParam('vacancy_id');
        $status = $this->_getParam('status', HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE);

        $userId = $this->getService('User')->getCurrentUserId();
        $vacancy = $this->getService('RecruitVacancy')->find($vacancyId)->current();
        $candidateSearchServiceName = Zend_Registry::get('config')->vacancy->externalSource;
        /* @var $huntingService HM_Recruit_RecruitingServices_PlacementBehavior */
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService($candidateSearchServiceName);
        $ids = array();
        $response = array();
        foreach ($candidates as $resumeId => $resumeData) {
            try {
                $resumeHash = $resumeData['resumeHash'];
                /* @var $resume HM_Recruit_RecruitingServices_Entity_AbstractCandidate */
                $resume = $huntingService->getCandidateResume(array('resumeHash' => $resumeData['resumeHash']));

                // создаем пользователя
                $user = $this->getService('User')->insert(array(
                    'LastName' => $resume->getLastName(),
                    'FirstName' => $resume->getFirstName(),
                    'Patronymic' => $resume->getPatronymic(),
                    'BirthDate' => $resume->getBirthDate(),
                    'EMail' => $resume->getEmail(),
                    'Phone' => $resume->getPhone(),
                    'Login' => 'hh_' . $resumeId,
                    'Password' => new Zend_Db_Expr("PASSWORD('hh_$resumeId')"),
                    'blocked' => 1
                ));

                // добавляем кандидата
                $candidate = $this->getService('RecruitCandidate')->insert(array(
                    'user_id' => $user->MID,
                    'source' => HM_Recruit_Candidate_CandidateModel::SOURCE_EXTERNAL,
                    'resume_external_url' => $resume->getUrl(),
                ));

                // присваиваем кандидата вакансии
                $this->getService('RecruitVacancyAssign')->assign($vacancyId, $candidate->candidate_id, $status);

                // делаем невидимым
                $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
                    'vacancy_id' => (int) $vacancy_id,
                    'hh_resume_id' => (int) $resumeId,
                    'date' => new Zend_Db_Expr('NOW()'),
                    'create_user_id' => $userId
                ));
                $ids[] = $resumeId;
            } catch (Exception $e) {

            }
        }
        $response['state'] = 'ok';
        $response['ids'] = $ids;
        if ($k > 0 && sizeof($ids) != $k) {
            $response['state'] = 'error';
        }

        $this->_helper->json($response);
    }
}
