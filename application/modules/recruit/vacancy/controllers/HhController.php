<?php
/**
 * Контроллер для работы с сайтом hh.ru
 * 
 * Для корректной работы необходимо в config.ini прописать:
 * 
 * ;настройки для работы с HH.ru
 * ;идентификатор клиента
 * vacancy.hh.employerId = 63290
 * ;тип вакансии: FREE (бесплатная), STANDART (платная)
 * vacancy.hh.vacancyType = "FREE"
 * 
 * @author Sevastyanov Cyril
 * @since 2013-03-06
 */
class Vacancy_HhController extends HM_Controller_Action_Vacancy
{
    protected $_candidateId = 0;
    protected $_candidate   = null;

    protected $_vacancyId   = 0;
    protected $_vacancy     = null;

    /**
     * @var HM_Recruit_RecruitingServices_Rest_Hh
     */

    protected $hh;
    /**
     * Инициализация
     */
    public function init()
    {
        return parent::init();
    }
    
    protected function initHH()
    {
        if ($this->hh) {
            return;
        }
        
        $factory = $this->getService('RecruitServiceFactory');
        $config = Zend_Registry::get('config')->vacancy;
        $this->hh = $factory->getRecruitingService($config->externalSource, $config->api);
        $this->view->setHeader('Публикация на HeadHunter');
    }

    public function create($form)
    {
        $values = $form->getValues();

        $result = $this->hh->createVacancy($values);
        if(is_array($result)){

            if ($result['message']) {

                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Публикация на HeadHunter не удалась') . ($result['message'] ? " ({$result['message']})" : ''),
                ));

                $redirectUrl = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $this->_getParam('vacancy_id'), 'baseUrl' => 'recruit'));
                $this->_redirector->gotoUrl($redirectUrl, array('prependBase' => false));
                exit();

            }
            foreach ($result['error_field'] as $errorField) {
                if ($elem = $form->getElement($errorField)) {
                    $elem->addError(_('Значение поля не принято на HeadHunter'));
                }
            }
            return false;
        } else {
            $this->_flashMessenger->addMessage(sprintf('Вакансия #%s успешно создана', $result));
            $vacancy = $this->getService('RecruitVacancy')->find($this->_getParam('vacancy_id'))->current();
            $vacancy->hh_vacancy_id = $result;
            $this->getService('RecruitVacancy')->update($vacancy->getValues());
            return true;
        }
    }
    
    /**
     * Публикация вакансии
     */
    public function indexAction()
    {
        $vacancy_id = $this->_getParam('vacancy_id');
        
        $this->view->vacancy_id = $vacancy_id;
        
        $vacancy = $this->getService('RecruitVacancy')->findDependence(array('Profile', 'Position'), $vacancy_id)->current();
        
        if ($vacancy->hh_vacancy_id) {
            $this->view->hh_vacancy_id = $vacancy->hh_vacancy_id;
            return;
        }
        
        // инициализируем объект для работы с hh
        $this->initHH();
        $hh   = $this->hh;
        $view = $this->view;

        $form = new HM_Form_HHVacancy(array('hh' => $hh));
        $request = $this->getRequest();

        if ($request->isPost()) {

            $params = $request->getParams();

            if ($form->isValid($params) && $this->create($form)) {
                $redirectUrl = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $this->_getParam('vacancy_id'), 'baseUrl' => 'recruit'));
                $this->_redirector->gotoUrl($redirectUrl, array('prependBase' => false));
                exit();
            }

            // восстанавливаем значения городов по их id
            if (!empty($params['area'])) {

                $select = $this->getService('User')->getSelect();
                $select->from('at_hh_regions', array('id', 'name'));
                $select->where('id IN (?)', $params['area']);
                $cities = $select->query()->fetchAll();

                $citiesIndex = array();

                foreach  ($cities as $city) {
                    $citiesIndex[$city['id']] = $city['name'];
                }
                $form->setDefault('area', $citiesIndex);
            }

        } else {

            $default = array(
                'name' => $vacancy->name,
                'compensationTo' => $vacancy->salary,
                'type' => 'open',
                'billing_type' => 'standard',
                'custom_employer_name' => $this->getService('Option')->getOption('publicationCompanyName', HM_Option_OptionModel::MODIFIER_RECRUIT),
                'managerId' => $hh->getUserId(),
                //'custom_employer_name' => $this->getService('Option')->getOption('custom_employer_name'),
            );


            $dataFieldsWhere = array(
                'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
                'item_id = ?'   => $vacancy_id,
            );
            $dataFields = $this->getService('RecruitVacancyDataFields')->fetchOne($dataFieldsWhere);

            if ($dataFields && $dataFields->work_place) {
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();

                $where = $this->getService('User')->quoteInto('LOWER(name) LIKE LOWER(?)', $dataFields->work_place);
                $placeItems =  $adapter->query('SELECT id, name FROM at_hh_regions WHERE '.$where)->fetchAll();

                if (count($placeItems)) {
                    $item = $placeItems[0];
                    $default['area'] = array(
                        $item['id'] =>  $item['name']
                    );
                } else {

                    $where = $this->getService('User')->quoteInto('LOWER(name) LIKE LOWER(?)', 'Санкт-Петербург');
                    $placeItems =  $adapter->query('SELECT id, name FROM at_hh_regions WHERE '.$where)->fetchAll();

                    $item = $placeItems[0];
                    $default['area'] = array(
                        $item['id'] =>  $item['name']
                    );
                }
            }


            $description = array();
            $description[] = '<p>'.nl2br($this->getService('Option')->getOption('publicationCompanyDescription', HM_Option_OptionModel::MODIFIER_RECRUIT)).'</p>';

            if (is_array($tasks = explode('||', $dataFields->tasks))) {
                $description[] = '<p><b>'.'Обязанности'.'</b></p>';
                $li = array();
                foreach ($tasks as $item) {
                    if ($item) {
                        $li[] = '<li>' . $item. '</li>';
                    }
                }
                $description[] = '<ul>'.implode('', $li).'</ul>';
            }


            $description[] = '<p><b>'.'Требования'.'</b></p>';
            if ($dataFields->education) {
                //$description[] = '<p>'.'Образование:'.'</p>';
                $description[] = '<p>'.$dataFields->education.'</p>';
            }
            if ($dataFields->skills) {
                //$description[] = '<p>'.'Навыки:'.'</p>';
                $description[] = '<p>'.$dataFields->skills.'</p>';
            }
            if (is_array($additional_education = explode('||',$dataFields->additional_education))) {
                $description[] = '<p>'.'Дополнительное образование (курсы, тренинги):'.'</p>';
                $li = array();
                foreach ($additional_education as $item) {
                    if ($item) {
                        $li[] = '<li>' . $item. '</li>';
                    }
                }
                $description[] = '<ul>'.implode('',$li).'</ul>';
            }
            if (is_array($knowledge_of_computer_programs = explode('||', $dataFields->knowledge_of_computer_programs))) {
                $description[] = '<p>'.'Знание компьютерных программ:'.'</p>';
                $li = array();
                foreach ($knowledge_of_computer_programs as $item) {
                    if ($item) {
                        $li[] = '<li>' . $item. '</li>';
                    }
                }
                $description[] = '<ul>'.implode('',$li).'</ul>';
            }
            if (is_array($knowledge_of_foreign_languages = explode('||', $dataFields->knowledge_of_foreign_languages))) {
                $description[] = '<p>'.'Знание иностранных языков (язык, степень владения):'.'</p>';
                $li = array();
                foreach ($knowledge_of_foreign_languages as $item) {
                    if ($item) {
                        $li[] = '<li>' . $item. '</li>';
                    }
                }
                $description[] = '<ul>'.implode('',$li).'</ul>';
            }
            if ($dataFields->work_experience) {
                //$description[] = '<p>'.'Опыт работы (лет):'.'</p>';
                $description[] = '<p>'.'Опыт работы: '.$dataFields->work_experience.' лет'.'</p>';
            }
            if ($dataFields->personal_qualities) {
                //$description[] = '<p>'.'Личные качества:'.'</p>';
                $description[] = '<p>'.$dataFields->personal_qualities.'</p>';
            }
            if ($dataFields->other_requirements) {
                //$description[] = '<p>'.'Прочие требования:'.'</p>';
                $description[] = '<p>'.$dataFields->other_requirements.'</p>';
            }

            $description[] = '<p><b>'.'Условия'.'</b></p>';
            $description[] = '<p>'.nl2br($this->getService('Option')->getOption('publicationCompanyConditions', HM_Option_OptionModel::MODIFIER_RECRUIT)).'</p>';


            $default['description'] = implode('', $description);

            $form->setDefaults($default);
        }
        
        $view->connectionInfo = $hh->connectionInfo;
        $view->log            = $hh->log;
        $view->form           = $form;
        $view->time           = $hh->time;
        $view->times          = $hh->times;
        
    }

    public function specializationsAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $this->initHH();
        $spec = $this->hh->getSpecializations();
        foreach ($spec as $specId => $specData) {
            echo sprintf("%s=%s", $specId, $specData['name']);
            echo "\n";
            foreach ($specData['specializations'] as $subSpecId => $subSpecName) {
                echo sprintf("%s=- %s", $subSpecId, $subSpecName);
                echo "\n";
            }
        }
    }

    public function candidatesAction()
    {
        $vacancy = $this->getService('RecruitVacancy')->findDependence('Profile', $this->_getParam('vacancy_id'))->current();

        if ($vacancy->hh_vacancy_id) {
            $this->view->hh_vacancy_id = $vacancy->hh_vacancy_id;
        } else {
            return;
        }

        // инициализируем объект для работы с hh
        $this->initHH();
        
        $hh = $this->hh;

        $candidates = $hh->getVacancyCandidates($vacancy->hh_vacancy_id);
        $resumeIds = array();
        
        // собираем список идентификаторов резюме
        foreach ($candidates as $candidate) {
            $resumeIds[] = (int) $candidate['resumeId'];
        }

        // проверяем, нет ли какого в списке игнорированных кандидатов
        $select = $this->getService('RecruitVacancyResumeHhIgnore')->getSelect();
        
        if (count($resumeIds)) {
            $select->from('recruit_vacancy_hh_resume_ignore', array('hh_resume_id'));
            $select->where('vacancy_id = ?', $vacancy->vacancy_id);
            $select->where('hh_resume_id IN (?)', $resumeIds);

            $ignoreList = $select->query()->fetchAll();

            // удаляем все заигнорированные резюме
            foreach ($ignoreList as $ignoreItem) {
                if (isset($candidates[$ignoreItem['hh_resume_id']])) {
                    unset($candidates[$ignoreItem['hh_resume_id']]);
                }
            }
        }
        
        $this->view->candidates = $candidates;
        $this->view->vacancy_id = $vacancy->vacancy_id;
        
    }
    
    public function ignoreResumesAction()
    {
        $params = $this->getRequest()->getParams();
        
        $vacancy_id = $params['vacancy_id'];
        $userId = $this->getService('User')->getCurrentUserId();
        
        foreach ($params['resumes'] as $resumeId => $resumeData) {
            $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
                'vacancy_id' => (int) $vacancy_id,
                'hh_resume_id' => (int) $resumeId,
                'date' => new Zend_Db_Expr('NOW()'),
                'create_user_id' => $userId
            ));
        }
        
        die();
        
    }

    /**
     * Удаление вакансии в архив.
     * Только вот на самом HH не архивирует, а зачем тогда оно..???
     */
    public function archiveVacancyAction()
    {
        $vacancy_id = $this->_getParam('vacancy_id', 0);
        
        $vacancy = $this->getOne($this->getService('RecruitVacancy')->find($vacancy_id));
        
        if (!$vacancy || !$vacancy->hh_vacancy_id) {
            die('0');
        }
        
        $hh_vacancy_id = $vacancy->hh_vacancy_id;
                
        $this->initHH();
        
        try {
            $result = $this->hh->archiveVacancy($hh_vacancy_id);
            if ($result) {

                $vacancy->hh_vacancy_id = NULL;

                $this->getService('RecruitVacancy')->updateWhere(array('hh_vacancy_id' => new Zend_Db_Expr('NULL')), $this->quoteInto('vacancy_id = ?', $vacancy_id));
                echo '1';
            } else {
                echo '0';
            }
            exit;

        } catch (Exception $e) {
            echo '0';
            exit;
        }
    }

    /**
     * Добавление резюме в кандидаты на собеседование
     */
    public function inviteResumesAction()
    {
        $params = $this->getRequest()->getParams();

        $vacancy_id = $params['vacancy_id'];
        $userId = $this->getService('User')->getCurrentUserId();
        $vacancy = $this->getService('RecruitVacancy')->find($vacancy_id)->current();
        
        $this->initHH();

        foreach ($params['resumes'] as $resumeId => $resumeData) {
            
            $resumeHash = $resumeData['resumeHash'];

            $resume = $this->hh->getResume($resumeData['resumeHash']);

            // создаем пользователя
            $user = $this->getService('User')->insert(array(
                'LastName'   => $resume['LastName'],
                'FirstName'  => $resume['FirstName'],
                'Patronymic' => $resume['Patronymic'],
                'BirthDate'  => $resume['BirthDate'],
                'EMail'      => $resume['EMail'],
                'Phone'      => $resume['Phone'],
                'Login'      => 'hh_'.$resumeId,
                'Password'   => new Zend_Db_Expr("PASSWORD('hh_$resumeId')"),
                'blocked'    => 1
            ));
            
            // добавляем кандидата
            $candidate = $this->getService('RecruitCandidate')->insert(array(
                'fio' => $resume['LastName'].' '.$resume['FirstName'].' '.$resume['Patronymic'],
                'user_id' => $user->MID
            ));
            
            // присваиваем кандидата вакансии
            $this->getService('RecruitVacancyAssign')->assign($vacancy_id, $candidate->candidate_id);

            // отправляем письмо
            $messenger = $this->getService('Messenger');

            $messenger->setOptions(
                HM_Messenger::TEMPLATE_INVITE_TO_INTERVIEW,
                array('vacancy' => $vacancy->name),
                '',
                0
            );

            try {
                $messenger->send($this->getService('User')->getCurrentUserId(), $user->MID);
            } catch (Exception $e) {

            }

            // делаем невидимым
            $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
                'vacancy_id' => (int) $vacancy_id,
                'hh_resume_id' => (int) $resumeId,
                'date' => new Zend_Db_Expr('NOW()'),
                'create_user_id' => $userId
            ));
        }

        die();

    }
    
    public function regionSearchAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');

        $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $countriesOnly = (int) $this->_request->getParam('countries_only');
        
        $q = strtolower(trim($this->_request->getParam('tag')));
        $res = array();
        
        if(!empty($q)) {
            $q = '%'.$q.'%';
            $where = $this->getService('User')->quoteInto('LOWER(name) LIKE LOWER(?)', $q);

            if($countriesOnly){
                $items = $adapter->query('SELECT id, name FROM at_hh_regions WHERE (parent IS NULL OR parent = 0) AND '.$where)->fetchAll();
            } else {
                $items = $adapter->query('SELECT id, name FROM at_hh_regions WHERE '.$where)->fetchAll();
            }
            
            foreach($items as $item) {
                $o = new stdClass();
                $o->key = $item['name'];
                $o->value = $item['id'];
                $res[] = $o;
            }
        }

        echo HM_Json::encodeErrorSkip($res);
    }
    
    public function updateRegionsAction()
    {
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');
        $data = $huntingService->getRegions();
        
        $db = $this->getService('User')->getMapper()->getAdapter()->getAdapter();

        $db->query('TRUNCATE TABLE at_hh_regions');
        
        $this->insertRegionsWithChildren($data);

        die();
    }
    
    private function insertRegionsWithChildren($items){
        $db = $this->getService('User')->getMapper()->getAdapter()->getAdapter();
        foreach($items as $item){
            if($item->areas){
                $this->insertRegionsWithChildren($item->areas);
            }
            $insert = array(
                'id'     => $item->id,
                'parent' => $item->parent_id,
                'name'   => $item->name,
            );
            $db->insert('at_hh_regions', $insert);
        }
    }
    
    public function responsesAction() {
        /* @var $huntingService HM_Recruit_RecruitingServices_PlacementBehavior */
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');
        
        $responses = $huntingService->getVacancyResponse($this->_vacancy);
        
        $assignHhUrl = $this->view->url(array(
            'module'     => 'candidate',
            'controller' => 'list',
            'action'     => 'assign-hh',
            'switcher'   => null,
            'status'     => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE,
        ));
        
        $assignHhHoldOnUrl = $this->view->url(array(
            'module'     => 'candidate',
            'controller' => 'list',
            'action'     => 'assign-hh',
            'switcher'   => null,
            'status'     => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON,
        ));
        
        
        $actions = array(
            $assignHhUrl => array(
                'label' => _('Включить в сессию подбора'),
                'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в данную сессию подбора? Если сессия подбора уже идёт, им сразу будут назначены оценочные мероприятия. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они будут автоматически исключены.'),         
            ),
//            $assignHhHoldOnUrl => array(
//                'label' => _('Включить в сессию подбора в качестве потенциального кандидата'),
//                'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в данную сессию подбора в качестве потенциальных кандидатов? При этом оценочные мероприятия назначены не будут. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они будут автоматически исключены.'),
//            )
        );
        
        
        $this->view->responses = $responses->items;
        $this->view->actions = $actions;
    }

    
}