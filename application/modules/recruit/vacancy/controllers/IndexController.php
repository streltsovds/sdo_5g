<?php
class Vacancy_IndexController extends HM_Controller_Action_Vacancy
{
    public function indexAction()
    {
        $vacancyId = $this->_getParam('vacancy_id', 0);
        $vacancy = $this->getService('RecruitVacancy')->findDependence('Session', $vacancyId)->current();
        $this->view->vacancy = $vacancy;
        if (count($vacancy->session)) {
            $this->view->session = $session->current();
        }
    }
    
    public function editAction()
    {
        $this->view->setHeader('Заявка на подбор');
        
        $form = new HM_Form_VacanciesRequest();
        $request = $this->getRequest();
        $redirectUrl = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'index', 'vacancy_id' => $this->_vacancy->vacancy_id, 'baseUrl' => 'recruit'));

        if ($request->isPost()) {

            $params = $request->getParams();

            if ($form->isValid($params)) {

                $this->update($form);

                $this->_flashMessenger->addMessage(_('Элемент успешно обновлён'));
                $this->_redirector->gotoUrl($redirectUrl, array('prependBase' => false));
            } else {
                
                if (!empty($params['parent_top_position_id']) && !empty($params['parent_top_position_id'][0])) {

                    $topManager = $this->getService('User')->find($params['parent_top_position_id'][0])->current();
                    $form->setDefault('parent_top_position_id', array(
                        $topManager->MID => $topManager->LastName.' '.$topManager->FirstName.' '.$topManager->Patronymic
                    ));
                }                
            }

        } else {

            if (count($this->_vacancy->session)) {
                $session = $this->_vacancy->session->current();
            }
            
            $positions = $this->getService('Orgstructure')->findDependence(array('Parent', 'Profile'), $this->_vacancy->position_id);
            // проверяем, что позиция в оргструктуре существует
            if (count($positions)) {
                
                $position = $positions->current(); // позиция в оргструктуре
                if (count($position->parent)) {
                    $parent = $position->parent->current(); // подразделение
                }
                
                if (count($position->profile)) {
                    $profile = $position->profile->current(); // профиль должности
                }

                if ($this->_vacancy->parent_position_id && count($collection = $this->getService('User')->find($this->_vacancy->parent_position_id))) {
                    $manager = $collection->current();
                }

                if ($this->_vacancy->parent_top_position_id && count($collection = $this->getService('User')->find($this->_vacancy->parent_top_position_id))) {
                    $topManager = $collection->current();
                }
            }

            $defaults = array(
                'description'        => $session->description,
                'position_name'      => $position->name,
                'department'         => $parent->name,
                'category_id'        => $profile->category_id,
                'manager'            => $manager ? $manager->getName() : '',
                'parent_top_position_id' => $topManager ? array($topManager->MID => $topManager->getName()) : '',
            );
                        
            $defaults += $this->_vacancy->getValues();
            
            $openDate = new HM_Date($defaults['open_date']);
            $defaults['open_date'] = $openDate->toString('dd.MM.Y');              
            $closeDate = new HM_Date($defaults['close_date']);
            $defaults['close_date'] = $closeDate->toString('dd.MM.Y');              
            
            $defaults['subordinates_categories'] = unserialize($defaults['subordinates_categories']);              
            $defaults['tasks'] = unserialize($defaults['tasks']);              
            $defaults['search_channels_papers_list'] = unserialize($defaults['search_channels_papers_list']);              
            $defaults['search_channels_universities_list'] = unserialize($defaults['search_channels_universities_list']);              
            $defaults['experience'] = unserialize($defaults['experience']);              
            $defaults['experience_other'] = unserialize($defaults['experience_other']);       
            $defaults['experience_companies'] = unserialize($defaults['experience_companies']);              
            
            $form->setDefaults($defaults);

        }
        $this->view->form = $form;
    }
    
    public function hireAction()
    {
        $vacancyId  = $this->_getParam('vacancy_id',0);
        
        $vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->findDependence('CandidateAssign', $vacancyId));
        if ($vacancy) {
            if (count($vacancy->candidates)) {
                foreach($vacancy->candidates as $candidate) {
                    if ($candidate->result == HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS) {
                        $this->getService('User')->updateWhere(array(
                            'blocked' => 0,         
                        ), array(
                            'MID = ?' => $candidate->user_id
                        ));
                        // $this->getService('Orgstructure')->assignUserToPosition($candidate->user_id, $vacancy->position_id);
                        break;
                    }
                }
            }
        }          
        
        $vacancy->status = HM_At_Session_SessionModel::STATE_CLOSED;
        $this->getService('RecruitVacancy')->update($vacancy->getValues()); 
        $this->getService('Process')->goToNextState($vacancy);
        
        $this->_redirector->gotoSimple('index', 'list');       
    }
      
    // DEPRECATED!
    public function changeStateAction()
    {
        $vacancyId  = $this->_getParam('vacancy_id',0);
        $state      = (int) $this->_getParam('state', 0);

        $vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->findDependence('RecruiterAssign', $vacancyId));
        $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find(intval($vacancy->session_id)));
        
        $currentState = $session->state;
        
        if ($vacancy && $session && $session->isStateAllowed($state)) {

            switch ($state) {
                case HM_At_Session_SessionModel::STATE_ACTUAL:
                    
                    if (!count($vacancy->recruiterAssign)) {
                        $errorMessage = _('Невозможно изменить статус сессии, не назначен ни один менеджер/специалист по подбору');
                    } else {
                        $this->getService('RecruitVacancy')->startSession($vacancy, $session);
                        $this->getService('Process')->goToNextState($vacancy);
                    }
                    break;
                    
                case HM_At_Session_SessionModel::STATE_CLOSED:
                    $this->getService('RecruitVacancy')->stopSession($vacancy, $session);
                    $this->getService('Process')->goToFail($vacancy);
                    break;
                    
                default:
                    // something wrong..
                    return false;
                    break;
            }
            
        } else {
            $errorMessage = _('При изменении статуса сессии произошла ошибка');
        }
        
        if ($errorMessage) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => $errorMessage,
            ));
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Статус сессии успешно изменен'),
            ));
        }

        $this->_redirector->gotoUrl($this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'index', 'state' => null, 'baseUrl' => 'recruit')), array('prependBase' => false));
    }

    public function programmAction()
    {
        if (count($collection = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $this->_vacancy->vacancy_id, HM_Programm_ProgrammModel::TYPE_RECRUIT))) {
            $programm = $collection->current();
            $url = $this->view->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'index', 'baseUrl' => '', 'programm_id' => $programm->programm_id, 'vacancy_id' => $this->_vacancy->vacancy_id));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Программа не найдена')
            ));
            $this->_redirector->gotoSimple('index', 'index', 'default', array('programm_id' => null));
        }
    }

    public function update($form)
    {
        $values = $form->getValues();

        // filter empty
        $values['subordinates_categories'] = array_filter($values['subordinates_categories']);
        $values['tasks'] = array_filter($values['tasks']);
        $values['search_channels_papers_list'] = array_filter($values['search_channels_papers_list']);
        $values['search_channels_universities_list'] = array_filter($values['search_channels_universities_list']);
        $values['experience'] = array_filter($values['experience']);
        $values['experience_other'] = array_filter($values['experience_other']);
        $values['experience_companies'] = array_filter($values['experience_companies']);
        
        $vacancyValues = array(
            'vacancy_id' => $this->_vacancy->vacancy_id,
            'parent_top_position_id' => is_array($values['top_manager']) ? $values['top_manager'][0] : null,
            'name'               => $values['name'],
            'reason'             => $values['reason'],
            'open_date'          => substr($values['open_date'], 6, 4) . '-' . substr($values['open_date'], 3, 2) . '-' . substr($values['open_date'], 0, 2),
            'close_date'         => substr($values['close_date'], 6, 4) . '-' . substr($values['close_date'], 3, 2) . '-' . substr($values['close_date'], 0, 2),
            'trip_mode'          => $values['trip_mode'],
            'work_place'         => $values['work_place'],
            'work_mode'          => $values['work_mode'],
            'salary'             => $values['salary'],
            'bonus'              => $values['bonus'],
            'age_min'            => $values['age_min'],
            'age_max'            => $values['age_max'],
            'gender'             => $values['gender'],
            'subordinates'       => $values['subordinates'],
            'subordinates_count' => $values['subordinates_count'],
            'subordinates_categories' => (is_array($values['subordinates_categories']) && count($values['subordinates_categories'])) ? serialize($values['subordinates_categories']) : '',
            'tasks'              => (is_array($values['tasks']) && count($values['tasks'])) ? serialize($values['tasks']) : '',
            'education'          => $values['education'],
            'requirements'       => $values['requirements'],
            'search_channels_corporate_site' => $values['search_channels_corporate_site'],
            'search_channels_recruit_sites' => $values['search_channels_recruit_sites'],
            'search_channels_papers' => $values['search_channels_papers'],
            'search_channels_papers_list' => (is_array($values['search_channels_papers_list']) && count($values['search_channels_papers_list'])) ? serialize($values['search_channels_papers_list']) : '',
            'search_channels_universities' => $values['search_channels_universities'],
            'search_channels_universities_list' => (is_array($values['search_channels_universities_list']) && count($values['search_channels_universities_list'])) ? serialize($values['search_channels_universities_list']) : '',
            'search_channels_workplace' => $values['search_channels_workplace'],
            'search_channels_email' => $values['search_channels_email'],
            'search_channels_inner' => $values['search_channels_inner'],
            'search_channels_outer' => $values['search_channels_outer'],
            'experience' => (is_array($values['experience']) && count($values['experience'])) ? serialize($values['experience']) : '',
            'experience_other' => (is_array($values['experience_other']) && count($values['experience_other'])) ? serialize($values['experience_other']) : '',
            'experience_companies' => (is_array($values['experience_companies']) && count($values['experience_companies'])) ? serialize($values['experience_companies']) : '',
        );
        
        if (count($this->_vacancy->session)) {
            $session = $this->_vacancy->session->current();
            $session->name = $values['name'];
            $session->description = $values['description'];
            $this->getService('AtSession')->update($session->getValues());
        }
        $vacancy = $this->getService('RecruitVacancy')->update($vacancyValues);
        return $vacancy;
    }
    
    public function experienceListAction()
    {
        $this->_ajaxify();
        
        $vacancyId = $this->_getParam('vacancy_id');
        if ($vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->find($vacancyId))) {
            if (!empty($vacancy->experience) && is_array($array = unserialize($vacancy->experience))) {
                 $selectedClassifiers = $array;
            }
        }

//         $classifiers = $this->getService('Classifier')->getTreeContent(null, 0, HM_Classifier_Type_TypeModel::BUILTIN_TYPE_HH_SPECIALIZATIONS); // очень медленно :(
        $classifiersLevel0 = $this->getService('Classifier')->fetchAll(array('level = ?' => 0, 'type = ?' => HM_Classifier_Type_TypeModel::BUILTIN_TYPE_HH_SPECIALIZATIONS));
        $classifiersLevel1 = $this->getService('Classifier')->fetchAll(array('level = ?' => 1, 'type = ?' => HM_Classifier_Type_TypeModel::BUILTIN_TYPE_HH_SPECIALIZATIONS));
        $count = 0;
        foreach ($classifiersLevel0 as $classifier) {
            if (empty($classifier->classifier_id_external)) continue;
            if ($count++ > 0) echo "\n";
            echo sprintf("%s=%s", $classifier->classifier_id_external, $classifier->name);
            foreach ($classifiersLevel1 as $subClassifier) {
                if (($subClassifier->lft > $classifier->lft) && ($subClassifier->rgt < $classifier->rgt)) {
                    echo "\n";
                    $classifier_id = $subClassifier->classifier_id_external;
                    if (in_array($classifier_id, $selectedClassifiers)) {
                        $classifier_id .= '+';
                    }
                    echo sprintf("%s=- %s", $classifier_id, $subClassifier->name);
                }
            }
        }
    }

    private function _ajaxify()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }      
    
}
