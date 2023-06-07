<?php

class Vacancy_SuperjobController extends HM_Controller_Action_Vacancy
{
    protected $_candidateId = 0;
    protected $_candidate   = null;

    protected $_vacancyId   = 0;
    protected $_vacancy     = null;

    protected $superjob;

    public function init()
    {
        return parent::init();
    }
    
    protected function initSuperjob()
    {
        if ($this->superjob) {
            return;
        }
        
        $factory = $this->getService('RecruitServiceFactory');
        $this->superjob = $factory->getRecruitingService('Superjob', HM_Recruit_RecruitingServices_PlacementBehavior::API_REST);
        
        if (!$this->superjob->getAuthToken()) {
            $this->superjob->getAuth();
        }
        
        $this->view->setHeader('Публикация на SuperJob');
    }

    public function testAction(){
        
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('superjob');
        
        $path = '/references/';
        
        $result = $huntingService->sendGetRequest($path, $query = null);
        
        var_dump(json_decode($result->getBody()));
        die;
    }
    
    
    public function create($form)
    {
        $values = $form->getValues();

        $result = $this->superjob->createVacancy($values);
        if(is_array($result)){
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => implode(". ", array_merge(array(_('Публикация на SuperJob не удалась')),$result['error_field']))
            ));
            $this->_redirector->gotoUrl($_SERVER['REQUEST_URI'], array('prependBase' => false));
//            foreach ($result['error_field'] as $errorField) {
//                if ($elem = $form->getElement($errorField)) {
//                    $elem->addError(_('Значение поля не принято на HeadHunter'));
//                }
//            }
//            var_dump($result);
            return false;
        } else {
            $this->_flashMessenger->addMessage(sprintf('Вакансия #%s успешно создана', $result));
            $vacancy = $this->getService('RecruitVacancy')->find($this->_getParam('vacancy_id'))->current();
            $vacancy->superjob_vacancy_id = $result;
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
        
        if ($vacancy->superjob_vacancy_id) {
            $this->view->superjob_vacancy_id = $vacancy->superjob_vacancy_id;
            return;
        }
        
        // инициализируем объект для работы с SJ
        $this->initSuperjob();
        $superjob = $this->superjob;
        $view     = $this->view;

        
        $form = new HM_Form_SuperjobVacancy(array('superjob' => $superjob));
        $request = $this->getRequest();

        if ($request->isPost()) {

            $params = $request->getParams();

            if ($form->isValid($params) && $this->create($form)) {
                $redirectUrl = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $this->_getParam('vacancy_id'), 'baseUrl' => 'recruit'));
                $this->_redirector->gotoUrl($redirectUrl, array('prependBase' => false));
                exit();
            }

        } else {
            $default = array(
                'profession' => $vacancy->name,
            );

            $dataFieldsWhere = array(
                'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
                'item_id = ?'   => $vacancy_id,
            );
            $dataFields = $this->getService('RecruitVacancyDataFields')->fetchOne($dataFieldsWhere);

            $defaultTown = 14;
            if ($dataFields && $dataFields->work_place) {
                $towns = $superjob->getTowns();
                foreach ($towns->objects as $town) {
                    if ($town->title == $dataFields->work_place) {
                        $defaultTown = $town->id;
                        break;
                    }
                }
            }
            $default['town'] = $defaultTown;
            $default['published'] = 1;
            $default['firm_name'] = $this->getService('Option')->getOption('publicationCompanyName', HM_Option_OptionModel::MODIFIER_RECRUIT);
            $default['firm_activity'] = $this->getService('Option')->getOption('publicationCompanyDescription', HM_Option_OptionModel::MODIFIER_RECRUIT);

            if (is_array($tasks = explode('||', $dataFields->tasks))) {
                $li = array();
                foreach ($tasks as $item) {
                    if ($item) {
                        $li[] = '- ' . $item;
                    }
                }
                $default['work'] = implode(PHP_EOL, $li);
            }


            $default['compensation'] = $this->getService('Option')->getOption('publicationCompanyConditions', HM_Option_OptionModel::MODIFIER_RECRUIT);

            $description = array();
            if ($dataFields->education) {
                //$description[] = '<p>'.'Образование:'.'</p>';
                $description[] = $dataFields->education;
            }
            if ($dataFields->skills) {
                //$description[] = '<p>'.'Навыки:'.'</p>';
                $description[] = $dataFields->skills;
            }
            if (is_array($additional_education = explode('||',$dataFields->additional_education))) {
                $description[] = 'Дополнительное образование (курсы, тренинги):';
                $li = array();
                foreach ($additional_education as $item) {
                    if ($item) {
                        $li[] = '- ' . $item;
                    }
                }
                $description[] = implode(PHP_EOL,$li);
            }
            if (is_array($knowledge_of_computer_programs = explode('||', $dataFields->knowledge_of_computer_programs))) {
                $description[] = 'Знание компьютерных программ:';
                $li = array();
                foreach ($knowledge_of_computer_programs as $item) {
                    if ($item) {
                        $li[] = '- ' . $item;
                    }
                }
                $description[] = implode(PHP_EOL,$li);
            }
            if (is_array($knowledge_of_foreign_languages = explode('||', $dataFields->knowledge_of_foreign_languages))) {
                $description[] = 'Знание иностранных языков (язык, степень владения):';
                $li = array();
                foreach ($knowledge_of_foreign_languages as $item) {
                    if ($item) {
                        $li[] = '- ' . $item;
                    }
                }
                $description[] = implode(PHP_EOL,$li);
            }
            if ($dataFields->work_experience) {
                //$description[] = '<p>'.'Опыт работы (лет):'.'</p>';
                $description[] = 'Опыт работы: '.$dataFields->work_experience.' лет';
            }
            if ($dataFields->personal_qualities) {
                //$description[] = '<p>'.'Личные качества:'.'</p>';
                $description[] = $dataFields->personal_qualities;
            }
            if ($dataFields->other_requirements) {
                //$description[] = '<p>'.'Прочие требования:'.'</p>';
                $description[] = $dataFields->other_requirements;
            }

            $default['candidat'] = implode(PHP_EOL, $description);

            $form->setDefaults($default);
        }

        $view->form           = $form;
    }
    
    public function cataloguesAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $this->initSuperjob();
        $spec = $this->superjob->getCatalogues();
        
//        var_dump($spec);
        
        foreach ($spec as $specId => $specData) {
            echo sprintf("%s=%s", $specData->key, $specData->title);
            echo "\n";
            foreach ($specData->positions as $subSpecId => $subSpecData) {
                echo sprintf("%s=- %s", $subSpecData->key, $subSpecData->title);
                echo "\n";
            }
        }
    }
    
    public function responsesAction() {
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('superjob');
        
        $responses = $huntingService->getVacancyResponse($this->_vacancy);
        
        $assignSuperjobUrl = $this->view->url(array(
            'module'     => 'candidate',
            'controller' => 'list',
            'action'     => 'assign-superjob',
            'switcher'   => null,
            'status'     => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE,
        ));
        
        $assignSuperjobHoldOnUrl = $this->view->url(array(
            'module'     => 'candidate',
            'controller' => 'list',
            'action'     => 'assign-superjob',
            'switcher'   => null,
            'status'     => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON,
        ));
        
        $actions = array(
            $assignSuperjobUrl => array(
                'label' => _('Включить в сессию подбора'),
                'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в данную сессию подбора? Если сессия подбора уже идёт, им сразу будут назначены оценочные мероприятия. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они будут автоматически исключены.'),         
            ),
//            $assignSuperjobHoldOnUrl => array(
//                'label' => _('Включить в сессию подбора в качестве потенциального кандидата'),
//                'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в данную сессию подбора в качестве потенциальных кандидатов? При этом оценочные мероприятия назначены не будут. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они будут автоматически исключены.'),
//            )
        );
        
        
        $this->view->responses = $responses->objects;
        $this->view->actions = $actions;
    }
    
    
}