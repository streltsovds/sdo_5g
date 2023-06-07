<?php
class Vacancy_ListController extends HM_Controller_Action
{
	use HM_Controller_Action_Trait_Grid;
	
	protected $_candidatesCache = null;

    public function init()
    {
        $form = new HM_Form_Vacancies();
        $this->_setForm($form);
        parent::init();
    }

    protected function _getMessages() {

        return array(
            self::ACTION_INSERT    => _('Сессия подбора успешно создана'),
            self::ACTION_UPDATE    => _('Сессия подбора успешно отредактирована'),
            self::ACTION_DELETE    => _('Сессия подбора успешно удалена'),
            self::ACTION_DELETE_BY => _('Сессии подбора успешно удалены')
        );
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index');
    }

    public function create($form)
    {
        $values = $form->getValues();
        
        $dataFieldsService = $this->getService('RecruitVacancyDataFields');
        
        $vacancyValues = array(
            'name'               => $values['vacancy_name'],
            'position_id'        => $values['position_id'],
            'parent_position_id' => is_array($values['manager']) ? $values['manager'][0] : null,
            'created_by'         => $this->getService('User')->getCurrentUserId(),
            'create_date'        => date('Y-m-d H:i'),
            'open_date'          => date('Y-m-d'),
            'close_date'         => strlen($values['close_date']) ? substr($values['close_date'], 6, 4) . '-' . substr($values['close_date'], 3, 2) . '-' . substr($values['close_date'], 0, 2) : null,
            'salary'             => $values['salary'],
            'bonus'              => $values['bonus'],
            'age_min'            => $values['age_min'],
            'age_max'            => $values['age_max'],
            'profile_id'         => $values['profile_id'],
//            'session_id'         => $session->session_id,
        );

        $vacancy = $this->getService('RecruitVacancy')->insert($vacancyValues);

        $values['tasks'] = array_diff($values['tasks'], array(''));
        $values['additional_education'] = array_diff($values['additional_education'], array(''));
        $values['knowledge_of_computer_programs'] = array_diff($values['knowledge_of_computer_programs'], array(''));
        $values['knowledge_of_foreign_languages'] = array_diff($values['knowledge_of_foreign_languages'], array(''));
        $values['experience_other'] = array_diff($values['experience_other'], array(''));

        $values['tasks'] = implode('||',$values['tasks']);
        $values['additional_education'] = implode('||',$values['additional_education']);
        $values['knowledge_of_computer_programs'] = implode('||',$values['knowledge_of_computer_programs']);
        $values['knowledge_of_foreign_languages'] = implode('||',$values['knowledge_of_foreign_languages']);
        $values['experience_other'] = implode('||',$values['experience_other']);

        $dataFieldsValues = array(
            //@todo сохраняем поля из формы в таблицу DataFields
            'item_type' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
            'item_id'   => $vacancy->vacancy_id,
//            'create_date'   => $values['create_date'],
//            'last_update_date'   => $values['last_update_date'],
            'create_date'   => date('d.m.Y H:i:s'),
            'last_update_date'   => date('d.m.Y H:i:s'),
            'vacancy_name'   => $values['vacancy_name'],
            'subordinates_count'   => $values['subordinates_count'],
            'work_mode'   => $values['work_mode'],
            'type_contract'   => $values['type_contract'],
            'work_place'   => $values['work_place'],
            'probationary_period'   => $values['probationary_period'],
            'salary'   => $values['salary'],
            'career_prospects'   => $values['career_prospects'],
            'reason'   => $values['reason'],
            'tasks'   => $values['tasks'],
            'education'   => $values['education'],
            'skills'   => $values['skills'],
            'additional_education'   => $values['additional_education'],
            'knowledge_of_computer_programs'   => $values['knowledge_of_computer_programs'],
            'knowledge_of_foreign_languages'   => $values['knowledge_of_foreign_languages'],
            'work_experience'   => $values['work_experience'],
            'experience_other'   => $values['experience_other'],
            'personal_qualities'   => $values['personal_qualities'],
            'other_requirements'   => $values['other_requirements'],
            'number_of_vacancies'   => $values['number_of_vacancies'],
        );
        
        $dataFieldsService->insert($dataFieldsValues);
        
        $vacancy_id = $vacancy->vacancy_id;

        /* НЕ создаем профиль должности; заявки на вакансии без профиля не принимаются
        $position = $this->getService('Orgstructure')->fetchAllDependence('Profile', array('soid = ?' => $values['position_id']))->current();
        if (!count($profilesCollection = $position->profile)) {
            // создаем профиль должности
            $profileValues = array(
                'name' => $values['position_name'],
                'shortname' => $values['position_name'],
                'vacancy_id' => $vacancy->vacancy_id,
                'age_min'       => $values['age_min'],
                'age_max'       => $values['age_max'],
                'gender'        => $values['gender'],
            );
            $newProfile = $this->getService('AtProfile')->insert($profileValues);
        }
                  
        $this->getService('RecruitVacancy')->updateWhere(array('profile_id' => $newProfile->profile_id), array(
           'vacancy_id = ?' => $vacancy->vacancy_id
        )); */
        
        return $vacancy_id;
    }

    public function update($form)
    {
        $dataFieldsService = $this->getService('RecruitVacancyDataFields');
        $vacancyId = $this->_getParam('vacancy_id', 0);
        $vacancy = $this->getService('RecruitVacancy')->findOne($vacancyId);

        $values = $form->getValues();
        if (is_array($values['user_id'])) {
            $values['user_id'] = $values['user_id'][0];
        }

        $vacancyValues = array(
            'vacancy_id'         => $vacancyId,
            'name'               => $values['vacancy_name'],
            'user_id'            => $values['user_id'],
            'parent_position_id' => is_array($values['manager']) ? $values['manager'][0] : null,
            'close_date'         => $values['close_date'] ? (substr($values['close_date'], 6, 4) . '-' . substr($values['close_date'], 3, 2) . '-' . substr($values['close_date'], 0, 2)) : null,
            'salary'             => $values['salary'],
            'bonus'              => $values['bonus'],
            'age_min'            => $values['age_min'],
            'age_max'            => $values['age_max'],
        );

        if ($vacancy && $vacancy->status == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL) {

            // если отредактирована внешняя вакансия -
            // подцепить программу и стартовать сессию

            if(!$values['soid']) {
                return $this->redirectToVacancy(_('Не выбрана должность'), $vacancyId, HM_Notification_NotificationModel::TYPE_ERROR);
            }
            $position = $this->getService('Orgstructure')->find($values['soid'])->current();
            if(!$position || !$position->profile_id) {
                return $this->redirectToVacancy(_('У должности не указан профиль'), $vacancyId, HM_Notification_NotificationModel::TYPE_ERROR);
            }

            $vacancyValues['position_id'] = $position->soid;
            $vacancyValues['department_path'] = $position->getOrgPath(false);
            $vacancyValues['profile_id'] = $position->profile_id;
            $vacancyValues['status'] = HM_Recruit_Vacancy_VacancyModel::STATE_PENDING;//STATE_ACTUAL;

            $dataFieldsValues = array(
                //@todo сохраняем поля из формы в таблицу DataFields
                'item_type' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
                'item_id'   => $vacancy->vacancy_id,
                'create_date'   => date('d.m.Y H:i:s'),
                'last_update_date'   => date('d.m.Y H:i:s'),
                'vacancy_name'   => $vacancy->name,
//                'salary'   => $vacancy->salary,
                'soid'  => $position->owner_soid,
                'user_id' => $values['user_id'][0]
            );
            $dataFieldsService->insert($dataFieldsValues);

            $this->getService('RecruitVacancy')->addEnvironment($vacancyValues);
        } else {

            // только апдейт
            $this->getService('RecruitVacancy')->update($vacancyValues, false);
        }

        $values['tasks'] = array_diff($values['tasks'], array(''));
        $values['additional_education'] = array_diff($values['additional_education'], array(''));
        $values['knowledge_of_computer_programs'] = array_diff($values['knowledge_of_computer_programs'], array(''));
        $values['knowledge_of_foreign_languages'] = array_diff($values['knowledge_of_foreign_languages'], array(''));
        $values['experience_other'] = array_diff($values['experience_other'], array(''));

        $values['tasks'] = implode('||',$values['tasks']);
        $values['additional_education'] = implode('||',$values['additional_education']);
        $values['knowledge_of_computer_programs'] = implode('||',$values['knowledge_of_computer_programs']);
        $values['knowledge_of_foreign_languages'] = implode('||',$values['knowledge_of_foreign_languages']);
        $values['experience_other'] = implode('||',$values['experience_other']);

        $dataFieldsValues = array(
//            'create_date'   => $values['create_date'],
//            'last_update_date'   => $values['last_update_date'],
            'last_update_date'   => date('d.m.Y H:i:s'),
            'vacancy_name'   => $values['vacancy_name'],
            'subordinates_count'   => $values['subordinates_count'],
            'work_mode'   => $values['work_mode'],
            'type_contract'   => $values['type_contract'],
            'work_place'   => $values['work_place'],
            'probationary_period'   => $values['probationary_period'],
            'salary'   => $values['salary'],
            'career_prospects'   => $values['career_prospects'],
            'reason'   => $values['reason'],
            'tasks'   => $values['tasks'],
            'education'   => $values['education'],
            'skills'   => $values['skills'],
            'additional_education'   => $values['additional_education'],
            'knowledge_of_computer_programs'   => $values['knowledge_of_computer_programs'],
            'knowledge_of_foreign_languages'   => $values['knowledge_of_foreign_languages'],
            'work_experience'   => $values['work_experience'],
            'experience_other'   => $values['experience_other'],
            'personal_qualities'   => $values['personal_qualities'],
            'other_requirements'   => $values['other_requirements'],
            'number_of_vacancies'   => $values['number_of_vacancies'],
        );
        
        if(isset($values['soid'])) {
            $dataFieldsValues['soid']  = $values['soid'];
        }

        $where = array(
            'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
            'item_id = ?'   => $vacancyId,
        );
        
        $dataFieldsService->updateWhere($dataFieldsValues, $where);
        
    }

    /**
     * Делает редирект на индексную страницу оргструктуры
     */
    protected function redirectToOrgstructure($msg, $type = HM_Notification_NotificationModel::TYPE_ERROR)
    {
        $this->_flashMessenger->addMessage(array(
            'type'    => $type,
            'message' => _($msg)
        ));
        $url = $this->view->url(array('module' => 'orgstructure', 'controller' => 'list', 'action' => 'index', 'org_id' => null, 'baseUrl' => ''));
        $this->_redirector->gotoUrl($url . '?page_id=m0801', array('prependBase' => false));
    }

    /**
     * Делает редирект на индексную страницу оргструктуры
     */
    protected function redirectToVacancy($msg, $vacancyId, $type=HM_Notification_NotificationModel::TYPE_SUCCESS)
    {
        $this->_flashMessenger->addMessage(array(
            'type'    => $type,
            'message' => _($msg)
        ));
        $url = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $vacancyId, 'baseUrl' => ''));
        $this->_redirector->gotoUrl($url);
    }

    public function createFromStructureAction()
    {
        $this->view->setHeader('Сессия подбора');
        
        $form = new HM_Form_Vacancies();
        $request = $this->getRequest();
        if ($request->isPost()) {

            $params = $request->getParams();

            if ($form->isValid($params)) {

                $vacancy_id = $this->create($form);

                if ($vacancy_id) {
                    $this->redirectToVacancy($this->_getMessage(self::ACTION_INSERT), $vacancy_id);
                }
                
            } else {
                
                if (!empty($params['manager']) && !empty($params['manager'][0])) {

                    $manager = $this->getService('User')->find($params['manager'][0])->current();
                    $form->setDefault('manager', array(
                        $manager->MID => $manager->LastName.' '.$manager->FirstName.' '.$manager->Patronymic
                    ));
                }
            }

        } else {


            $position_id = $this->_getParam('org_id');

            // проверяем, что была передана позиция в оргструктуре
            if (!$position_id) {
                $this->redirectToOrgstructure('Не указана позиция в оргструктуре');
            }

            $positions = $this->getService('Orgstructure')->findDependence(array('Parent', 'Profile'), $position_id);
            // проверяем, что позиция в оргструктуре существует
            if (!count($positions)) {
                $this->redirectToOrgstructure('Не найдена позиция в оргструктуре');
            }

            $position = $positions->current(); // позиция в оргструктуре
            if (count($position->parent)) {
                $parent = $position->parent->current(); // подразделение
            }
            if (count($position->profile)) {
                $profile = $position->profile->current(); // профиль должности
            }
            if ($managerPosition = $this->getService('Orgstructure')->getManager($position_id)) {
                if (count($managerPosition->user)) {
                    $manager = $managerPosition->user->current();
                }
            }

            $profileName = explode(' ', $profile->name);
            array_shift($profileName);
            $profileName = implode(' ', $profileName);
            
            // массив дефолтных параметров для формы
            $defaults = array(
                'position_id'        => $position_id,
                'soid'               => $position->owner_soid,
                'vacancy_name'       => $profileName,
                'name'               => $position->name,
                'department'         => $parent->name,
                'category_id'        => $profile->category_id,
                'profile_id'         => $position->profile_id,
                'manager'            => ($manager) ? array($manager->MID => $manager->LastName.' '.$manager->FirstName.' '.$manager->Patronymic) : '',
            );
            
            $form->setDefaults($defaults);
        }
        $this->view->form = $form;
    }



    public function createFromApplicationAction()
    {
        $this->view->setHeader('Сессия подбора');

        $form = new HM_Form_VacanciesFromApplication();
        $request = $this->getRequest();

        $recruitApplicationId = $this->_getParam('recruit_application_id');
        $recruitApplication = $this->getService('RecruitApplication')->findOne($recruitApplicationId);

        $dataFieldsService = $this->getService('RecruitVacancyDataFields');

        // уже определено в заявке
        $form->removeElement('soid');

        if ($request->isPost()) {

            $params = $request->getParams();

            if ($form->isValid($params)) {

                $parent = $this->getService('Orgstructure')->getManager($recruitApplication->soid);
                if ($parent) {
                    $parentPositionId = $parent->mid;
                } else {
                    $parentPositionId = $recruitApplication->user_id;
                }

                if ($position = $this->getService('Orgstructure')->findOne($recruitApplication->soid)) {

                    $values = $form->getValues();
                    if (is_array($values['user_id'])) {
                        $values['user_id'] = $values['user_id'][0];
                    }

                    $vacancyValues = array(
                        'name'               => $values['vacancy_name'],
                        'position_id'        => $position->soid,
                        'user_id'            => $values['user_id'],
                        'department_path'    => $position->getOrgPath(false),
                        //'open_date'          => substr($values['open_date'], 6, 4) . '-' . substr($values['open_date'], 3, 2) . '-' . substr($values['open_date'], 0, 2),
                        'close_date'         => substr($values['close_date'], 6, 4) . '-' . substr($values['close_date'], 3, 2) . '-' . substr($values['close_date'], 0, 2),
                        'salary'             => $values['salary'],
                        'bonus'              => $values['bonus'],
                        'age_min'            => $values['age_min'],
                        'age_max'            => $values['age_max'],
                        'parent_position_id' => $parentPositionId,
                        'created_by'         => $this->getService('User')->getCurrentUserId(),
                        'create_date'        => date('Y-m-d H:i'),
                        'profile_id'         => $position->profile_id,
                        'recruit_application_id'         => $recruitApplicationId,
                    );

                    $vacancy = $this->getService('RecruitVacancy')->insert($vacancyValues);
                    $vacancy_id = $vacancy->vacancy_id;

                    $values['tasks'] = array_diff($values['tasks'], array(''));
                    $values['additional_education'] = array_diff($values['additional_education'], array(''));
                    $values['knowledge_of_computer_programs'] = array_diff($values['knowledge_of_computer_programs'], array(''));
                    $values['knowledge_of_foreign_languages'] = array_diff($values['knowledge_of_foreign_languages'], array(''));
                    $values['experience_other'] = array_diff($values['experience_other'], array(''));

                    $values['tasks'] = implode('||',$values['tasks']);
                    $values['additional_education'] = implode('||',$values['additional_education']);
                    $values['knowledge_of_computer_programs'] = implode('||',$values['knowledge_of_computer_programs']);
                    $values['knowledge_of_foreign_languages'] = implode('||',$values['knowledge_of_foreign_languages']);
                    $values['experience_other'] = implode('||',$values['experience_other']);

                    if ($vacancy_id) {
                        $dataFieldsValues = array(
                            //@todo сохраняем поля из формы в таблицу DataFields
                            'item_type' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
                            'item_id'   => $vacancy_id,
                            'create_date'   => date('d.m.Y H:i:s'),
                            'last_update_date'   => date('d.m.Y H:i:s'),
    //                        'create_date'   => $values['create_date'],
    //                        'last_update_date'   => $values['last_update_date'],
                            'soid'   => $position->soid,
                            'vacancy_name'   => $values['vacancy_name'],
                            'subordinates_count'   => $values['subordinates_count'],
                            'work_mode'   => $values['work_mode'],
                            'type_contract'   => $values['type_contract'],
                            'work_place'   => $values['work_place'],
                            'probationary_period'   => $values['probationary_period'],
                            'salary'   => $values['salary'],
                            'career_prospects'   => $values['career_prospects'],
                            'reason'   => $values['reason'],
                            'tasks'   => $values['tasks'],
                            'education'   => $values['education'],
                            'skills'   => $values['skills'],
                            'additional_education'   => $values['additional_education'],
                            'knowledge_of_computer_programs'   => $values['knowledge_of_computer_programs'],
                            'knowledge_of_foreign_languages'   => $values['knowledge_of_foreign_languages'],
                            'work_experience'   => $values['work_experience'],
                            'experience_other'   => $values['experience_other'],
                            'personal_qualities'   => $values['personal_qualities'],
                            'other_requirements'   => $values['other_requirements'],
                            'number_of_vacancies'   => $values['number_of_vacancies'],
                        );
                        $dataFieldsService->insert($dataFieldsValues);

                        $this->getService('RecruitApplication')->update(
                            array(
                                'recruit_application_id' => $recruitApplicationId,
                                'status' => HM_Recruit_Application_ApplicationModel::STATUS_CREATED,
                                'vacancy_id' => $vacancy_id,
                            )
                        );
                        $this->redirectToVacancy($this->_getMessage(self::ACTION_INSERT), $vacancy_id);
                    }
                }

            } else {

                if (!empty($params['user_id']) && !empty($params['user_id'][0])) {

                    if ($user = $this->getService('User')->findOne($params['user_id'][0])) {
                        $form->setDefault('user_id', array(
                            $user->MID => $user->getName(),
                        ));
                    }
                }
            }

        } else {

            $where = array(
                'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_APPLICATION,
                'item_id = ?'   => $recruitApplicationId,
            );
            
            if ($applicationDataFields = $dataFieldsService->getOne($dataFieldsService->fetchAll($where))) {

                $applicationDataFieldsData = $applicationDataFields->getData();
                if (!empty($applicationDataFieldsData)) {
                    $applicationDataFieldsData['tasks'] = explode('||', $applicationDataFieldsData['tasks']);
                    $applicationDataFieldsData['additional_education'] = explode('||', $applicationDataFieldsData['additional_education']);
                    $applicationDataFieldsData['knowledge_of_computer_programs'] = explode('||', $applicationDataFieldsData['knowledge_of_computer_programs']);
                    $applicationDataFieldsData['knowledge_of_foreign_languages'] = explode('||', $applicationDataFieldsData['knowledge_of_foreign_languages']);
                    $applicationDataFieldsData['experience_other'] = explode('||', $applicationDataFieldsData['experience_other']);
                }

                $defaults = array(
                    'create_date'   => $applicationDataFieldsData['create_date'],
                    'last_update_date'   => $applicationDataFieldsData['last_update_date'],
                    'vacancy_name'   => $applicationDataFieldsData['vacancy_name'],
                    'subordinates_count'   => $applicationDataFieldsData['subordinates_count'],
                    'work_mode'   => $applicationDataFieldsData['work_mode'],
                    'type_contract'   => $applicationDataFieldsData['type_contract'],
                    'work_place'   => $applicationDataFieldsData['work_place'],
                    'probationary_period'   => $applicationDataFieldsData['probationary_period'],
                    'salary'   => $applicationDataFieldsData['salary'],
                    'career_prospects'   => $applicationDataFieldsData['career_prospects'],
                    'reason'   => $applicationDataFieldsData['reason'],
                    'tasks'   => $applicationDataFieldsData['tasks'],
                    'education'   => $applicationDataFieldsData['education'],
                    'skills'   => $applicationDataFieldsData['skills'],
                    'additional_education'   => $applicationDataFieldsData['additional_education'],
                    'knowledge_of_computer_programs'   => $applicationDataFieldsData['knowledge_of_computer_programs'],
                    'knowledge_of_foreign_languages'   => $applicationDataFieldsData['knowledge_of_foreign_languages'],
                    'work_experience'   => $applicationDataFieldsData['work_experience'],
                    'experience_other'   => $applicationDataFieldsData['experience_other'],
                    'personal_qualities'   => $applicationDataFieldsData['personal_qualities'],
                    'other_requirements'   => $applicationDataFieldsData['other_requirements'],
                    'number_of_vacancies'   => $applicationDataFieldsData['number_of_vacancies'],
                );

                if ($recruitApplication && $recruitApplication->user_id) {
                    $user = $this->getService('User')->findOne($recruitApplication->user_id);
                    if ($user) {
                        $defaults['user_id'] = array($user->MID => $user->getName());
                    }
                }

                $form->setDefaults($defaults);
            }
        }
        $this->view->form = $form;
    }

    /*
     * ВНИМАНИЕ!
     *
     * Используется только для createFromStructure (т.е. пока не используется)
     * возможно, имеет неправильную логику
     *
     */
    public function setDefaults(Zend_Form $form)
    {
        $vacancyId = $this->_getParam('vacancy_id', 0);
        $vacancy = $this->getService('RecruitVacancy')->find($vacancyId)->current();
        $vacancyDataFields = $this->getService('RecruitVacancyDataFields')->fetchAll(array(
            'item_id = ?'   => $vacancyId,
            'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY,
        ))->current();
        
        $values = $vacancyDataFields->getData();
        
        $values['tasks']                          = explode('||', $values['tasks']);
        $values['additional_education']           = explode('||', $values['additional_education']);
        $values['knowledge_of_computer_programs'] = explode('||', $values['knowledge_of_computer_programs']);
        $values['knowledge_of_foreign_languages'] = explode('||', $values['knowledge_of_foreign_languages']);
        $values['experience_other']               = explode('||', $values['experience_other']);
        
        $data = array_merge($vacancy->getData(), $values);

        if ($vacancy && $vacancy->user_id) {
            $user = $this->getService('User')->findOne($vacancy->user_id);
            if ($user) {
                $data['user_id'] = array($user->MID => $user->getName());
            }
        }

        $data['open_date'] = date('d.m.Y', strtotime($data['open_date']));
        $data['close_date'] = $data['close_date'] ? date('d.m.Y', strtotime($data['close_date'])) : '';

        $form->populate($data);
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'create_date_DESC');
        }
        
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
            $default = Zend_Registry::get('session_namespace_default');
            $page = sprintf('%s-%s-%s', 'vacancy', 'list', 'index');
            $filter = $this->_request->getParam("filter");
            if (empty($filter) && empty($default->grid[$page]['grid']['filters'])){
                $default->grid[$page]['grid']['filters']['recruiters'] = $this->getService('User')->getCurrentUser()->LastName;
            }        
        }        
        
        $select = $this->getService('RecruitVacancy')->getSelect();
        $select->from(
            array(
                'rv' => 'recruit_vacancies'
            ),
            array(
                'rv.vacancy_id',
                'rv.position_id',
                'workflow_id' => 'rv.vacancy_id',
                'name' => 'rv.name',
                'status' => new Zend_Db_Expr("CASE WHEN rv.status=0 THEN 1 ELSE rv.status END"),
                'statusSrc' => 'rv.status',
                'rv.department_path',
                'recruiters' => new Zend_Db_Expr("GROUP_CONCAT(r.recruiter_id)"),
                'candidate_amount' => new Zend_Db_Expr("COUNT(DISTINCT p.MID)"),
                'rv.create_date',
                'rv.complete_date',
            )
        );

        $select
            ->joinLeft(array('sop' => 'state_of_process'), 'rv.vacancy_id = sop.item_id AND sop.process_type = ' . HM_Process_ProcessModel::PROCESS_VACANCY, array())
            ->joinLeft(array('sp' => 'structure_of_organ'), 'sp.soid = rv.position_id', array())
            ->joinLeft(array('rvr' => 'recruit_vacancy_recruiters'), 'rvr.vacancy_id = rv.vacancy_id', array())
            ->joinLeft(array('r' => 'recruiters'), 'r.recruiter_id = rvr.recruiter_id', array())
            ->joinLeft(array('rvc' => 'recruit_vacancy_candidates'), 'rvc.vacancy_id = rv.vacancy_id', array())
            ->joinLeft(array('rc' => 'recruit_candidates'), 'rvc.candidate_id = rc.candidate_id', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = rc.user_id', array())
            ->where('rv.deleted IS NULL OR rv.deleted != ?', 1)
            ->group(
                array(
                    'rv.vacancy_id',
                    'rv.position_id',
                    'rv.status',
                    'rv.vacancy_id',
                    'rv.name',
                    'rv.department_path',
                    'rv.create_date',
                    'rv.complete_date',
                    'rv.deleted'
                )
            );

            $selfVacancySessions = array(0);
            if (count($selfVacancies = $this->getService('RecruitVacancy')->getSelfVacanciesToHide())) {
                $selfVacancyIds = $selfVacancies->getList('vacancy_id');
                $select->where('rv.vacancy_id NOT IN (?)', $selfVacancyIds); 
            }
        

        $currentUser = $this->getService('User')->getCurrentUser();
        switch ($currentUser->role) {

            case HM_Role_Abstract_RoleModel::ROLE_HR:
                break;
                
            case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:

                // все по области ответственности, даже не назначенные
                $soid = $this->getService('Responsibility')->get();
                $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
                if ($responsibilityPosition) {
                    $subSelect = $this->getService('Orgstructure')->getSelect()
                        ->from('structure_of_organ', array('soid'))
                        ->where('lft > ?', $responsibilityPosition->lft)
                        ->where('rgt < ?', $responsibilityPosition->rgt);
                    $select->where("rv.position_id IN (?)", $subSelect);
                } else {
                    $select->where('1 = 0');
                }
                
                break;

            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:

                // @todo: не учитывается внутреннее совместительство
                $userPosition = $this->getOne($this->getService('Orgstructure')->fetchAll($this->quoteInto('mid = ?', $currentUser->MID)));
                $parentPosition = $this->getOne($this->getService('Orgstructure')->find($userPosition->owner_soid));

                if ($userPosition) {
                    $subSelect = $this->getService('Orgstructure')->getSelect()
                        ->from('structure_of_organ', array('soid'))
                        ->where('lft > ?', $parentPosition->lft)
                        ->where('rgt < ?', $parentPosition->rgt);
                    $select->where("rv.position_id IN (?)", $subSelect);
                } else {
                    $select->where('1 = 0');
                }
            break;
            default:
                $select->where('1 = 0');
        }
// exit($select->__toString());
        $grid = $this->getGrid($select, array(
            'vacancy_id' => array('hidden' => true),
            'position_id' => array('hidden' => true),
            'params' => array('hidden' => true),
            'statusSrc' => array('hidden' => true),
            'workflow_id' => array(
                 'title' => _('Бизнес-процесс'), // бизнес проуцесс
                 'callback' => array(
                     'function' => array($this, 'printWorkflow'),
                     'params' => array('{{workflow_id}}', _('Бизнес-процесс сессии подбора')),
                 ),
                 'sortable'=>false
             ),
            'name' => array(
                'title' => _('Название'),
            ),
            'department_path' => array(
                'title' => _('Подразделение'),
                'callback' => array(
                    'function'=> array($this, 'updateDepartmentPath'),
                    'params'=> array('{{department_path}}')
                ),
            ),
            'recruiters' => array(
                'title' => _('Специалисты по подбору'),
                 'callback' => array(
                     'function' => array($this, 'recruitersCache'),
                     'params' => array('{{recruiters}}'),
                 ),
            ),
            'candidate_amount' => array(
                'title' => _('Количество кандидатов'),
            ),
            'create_date' => array(
                'title' => _('Дата создания'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
            ),
            'complete_date' => array(
                'title' => _('Дата завершения'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
            ),
            'status' => array('title' => _('Статус'),
                'callback' => array(
                    'function'=> array($this, 'updateState'),
                    'params'=> array('{{status}}')
                )
            ),
        ),
            array(
                'department_path' => null, //array('render' => 'department'),
                'name' => null,
                'recruiters' => array(
                    'callback' => array(
                        'function' => array($this, 'recruitersFilter')
                    )
                ),
                'status' => array('values' => HM_Recruit_Vacancy_VacancyModel::getStatesCustom()),
                'create_date' => array('render' => 'DateSmart'),
                'candidate_amount' => null,
                'complete_date' => array('render' => 'DateSmart'),
                'workflow_id' => array(
                    'render' => 'process',
                    'values' => Bvb_Grid_Filters_Render_Process::getStates(HM_Recruit_Vacancy_VacancyModel, 'vacancy_id'),
                   'field4state' => 'sop.current_state',
                ),

            )

        );

        $grid->updateColumn('name',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{vacancy_id}}', '{{name}}', '{{position_id}}', '{{statusSrc}}')
                )
            )
        );
        
        $grid->updateColumn('candidate_amount',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateCount'),
                    'params'=> array('{{vacancy_id}}', '{{candidate_amount}}', '{{statusSrc}}')
                )
            )
        );

        $grid->addAction(array(
            'module' => 'vacancy',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('vacancy_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );        

        $grid->addAction(array(
            'module' => 'vacancy',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('vacancy_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'list',
            'action' => 'load-new-resumes-by',
        ),
            array('vacancy_id'),
            _('Загрузить отклики')
        );

        $grid->addMassAction(
            array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'load-new-resumes-by',
            ),
            _('Загрузить отклики')
        );

        $grid->addMassAction(
            array(
                'module' => 'vacancy',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить сессии подбора'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );    

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array('{{statusSrc}}')
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }


    public function recruitersFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];


        // Только больше 2 символов чтобы много не лезло в in
        if(strlen($value) > 2){
            $value = '%' . $value . '%';
            $select->joinLeft(
                array('rvr1' => 'recruit_vacancy_recruiters'), 'rvr1.vacancy_id = rv.vacancy_id', array()
            )->joinLeft(
                array('r1' => 'recruiters'), 'r1.recruiter_id = rvr1.recruiter_id', array()
            )->joinLeft(
                array('u' => 'People'),
                "r1.user_id = u.MID",
                array()
            )->where(
                "(u.LastName LIKE (?)", $value
            )->orWhere(
                "u.FirstName LIKE (?)", $value
            )->orWhere(
                "u.Patronymic LIKE (?))", $value
            );
        }

    }

    public function delete($id) 
    {

        $application = $this->getService('RecruitApplication')->fetchOne(array(
            'vacancy_id = ?' => $id
        ));
        if ($application) {
            $this->getService('RecruitApplication')->update(
                array(
                    'recruit_application_id' => $application->recruit_application_id,
                    'status' => HM_Recruit_Application_ApplicationModel::STATUS_INWORK,
                    'vacancy_id' => null
                )
            );
        }
        $this->getService('RecruitVacancy')->delete($id);
    }    

    public function updateContactUserId($recruiterIds)
    {
        static $recruiters = false;
        
        if (!$recruiters) {
            $select = $this->getService('Recruiter')->getSelect();
            
            $select->from(array('r' => 'recruiters'), array(
                'r.recruiter_id',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            ));
            $select->joinInner(array('p' => 'People'), 'p.MID = r.user_id', array());
            $users = $select->query()->fetchAll();
            $recruiters = array();
            
            foreach ($users as $user) {
                $recruiters[$user['recruiter_id']] = $user['fio'];
            }
        }
        
        $recruiterIds = explode(',', $recruiterIds);
        $result = array();
        
        foreach ($recruiterIds as $recruiterId) {
            if (isset($recruiters[$recruiterId])) {
                $result[] = $recruiters[$recruiterId];
            }
        }
        
        return implode(', ', $result);
    }

    public function printWorkflow($vacancyId)
    {
        if ($this->_vacanciesCache === null) {
            $this->_vacanciesCache = array();
            $collection = $this->getService('RecruitVacancy')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_vacanciesCache[$item->vacancy_id] = $item;
                }
            }
        }
        if(intval($vacancyId) > 0 && count($this->_vacanciesCache) && array_key_exists($vacancyId, $this->_vacanciesCache)){
            $model = $this->_vacanciesCache[$vacancyId];
            $this->getService('Process')->initProcess($model);
            return $this->view->workflowBulbs($model);
        }
        return '';
    }

    public function updateName($vacancyId, $name, $positionId, $status)
    {
       return $status == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL
           ? $name
           : $this->view->cardLink(
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
            ) . '<a href="' . $this->view->url(array('controller' => 'report', 'action' => 'card', 'vacancy_id' => $vacancyId, 'candidate_id' => null)) . '">' . $this->view->escape($name) . '</a>';
    }

    public function updateCount($vacancyId, $count, $status)
    {
        return $status == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL
           ? $count
           : '<a href="' . $this->view->url(array('module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'vacancy_id' => $vacancyId)) . '">' . $this->view->escape($count) . '</a>';
    }

    public function updateState($status)
    {
        $arr = HM_Recruit_Vacancy_VacancyModel::getStatesCustom();
        foreach ($arr as $key => $state) {
            if ($key == $status) return $state;
        }
    }

    public function workflowAction()
    {
        $vacancyId = $this->_getParam('index', 0);

        if(intval($vacancyId) > 0){

            $model =  $this->getService('RecruitVacancy')->find($vacancyId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }
    
    public function updateActions($status, $actions)
    {
         if ($status == HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL) {
             $this->unsetAction($actions, array('module' => 'candidate', 'controller' => 'list', 'action' => 'load-new-resumes-by'));
         }
        return $actions;
    }

    protected function initHH()
    {
        if ($this->hh) {
            return;
        }

        $factory = $this->getService('RecruitServiceFactory');
        $config = Zend_Registry::get('config')->vacancy;

        if ($config->hh->enabled) {

            $this->hh = $factory->getRecruitingService($config->externalSource, $config->api);

            $actionName = $this->getRequest()->getActionName();

            if (!$this->hh->getAuthToken()) {
                if($this->hh->getAuth()){
                    $this->_redirector->gotoSimple($actionName);
                }
            }
        }
    }

    public function loadNewVacanciesAction(){
        
        $this->initHH();
//        $this->initSuperjob();
        $hhService        = $this->hh;
//        $superjobService  = $this->superjob;

        $vacanciesHH = $hhService ? $hhService->getVacancyList() : false;

/*
$vacanciesHH = (object)array
(
    'per_page' => 20,
    'items' => Array
        (
            0 => (object) array
                (
                    'premium' => '',
                    'alternate_url' => 'https://hh.ru/vacancy/25368616',
                    'apply_alternate_url' => 'https://hh.ru/applicant/vacancy_response?vacancyId=25368616',
                    'expires_at' => '2018-05-12T17:06:24+0300',
                    'department' => '',
                    'has_new_messages' => 1,
                    'address' => (object) array
                        (
                            'building' => '25к3',
                            'city' => 'Санкт-Петербург',
                            'description' => '',
                            'metro' => (object) array
                                (
                                    'line_name' => 'Невско-Василеостровская',
                                    'station_id' => '16.235',
                                    'line_id' => 16,
                                    'lat' => 59.848709,
                                    'station_name' => 'Обухово',
                                    'lng' => 30.457743
                                ),

                            'metro_stations' => Array
                                (
                                    0 => (object) array
                                        (
                                            'line_name' => 'Невско-Василеостровская',
                                            'station_id' => 16.235,
                                            'line_id' => 16,
                                            'lat' => 59.848709,
                                            'station_name' => 'Обухово',
                                            'lng' => 30.457743
                                        )

                                ),

                            'raw' => '',
                            'street' => 'улица Грибакиных',
                            'lat' => 59.849664,
                            'lng' => 30.45844,
                            'id' => 321319
                        ),

                    'sort_point_distance' => '',
                    'id' => 25368616,
                    'salary' => (object) array
                        (
                            'to' => '',
                            'gross' => 1,
                            'from' => 30000,
                            'currency' => 'RUR'
                        ),

                    'archived' => '',
                    'name' => 'Менеджер среднего звена',
                    'can_upgrade_billing_type' => '',
                    'area' => (object) array
                        (
                            'url' => 'https://api.hh.ru/areas/96',
                            'id' => 96,
                            'name' => 'Ижевск'
                        ),

                    'url' => 'https://api.hh.ru/vacancies/25368616?host=hh.ru',
                    'created_at' => '2018-04-12T17:06:24+0300',
                    'has_updates' => 1,
                    'published_at' => '2018-04-12T17:06:24+0300',
                    'relations' => Array(),
                    'employer' => (object) array
                        (
                            'logo_urls' => (object) array
                                (
                                    '90' => 'https://hhcdn.ru/employer-logo/964442.png',
                                    '240' => 'https://hhcdn.ru/employer-logo/964443.png',
                                    'original' => 'https://hhcdn.ru/employer-logo-original/217776.gif'
                                ),

                            'vacancies_url' => 'https://api.hh.ru/vacancies?employer_id=63290',
                            'name' => 'ГиперМетод',
                            'url' => 'https://api.hh.ru/employers/63290',
                            'alternate_url' => 'https://hh.ru/employer/63290',
                            'id' => 63290,
                            'trusted' => 1
                        ),

                    'response_letter_required' => '',
                    'billing_type' => (object) array
                        (
                            'id' => 'free',
                            'name' => 'Бесплатная'
                        ),

                    'type' => (object) array
                        (
                            'id' => 'closed',
                            'name' => 'Закрытая'
                        ),

                    'counters' => (object) array
                        (
                            'views' => 3,
                            'invitations' => 0,
                            'unread_responses' => 1,
                            'responses' => 1,
                            'resumes_in_progress' => 0
                        ),

                ),

            1 => (object) array
                (
                    'premium' =>        '',
                    'alternate_url' => 'https://hh.ru/vacancy/25013164',
                    'apply_alternate_url' => 'https://hh.ru/applicant/vacancy_response?vacancyId=25013164',
                    'expires_at' => '2018-04-19T17:16:23+0300',
                    'department' => '',
                    'has_new_messages' => 1,
                    'address' => (object) array
                        (
                            'building' => '25к3',
                            'city' => 'Санкт-Петербург',
                            'description' => '',
                            'metro' => (object) array
                                (
                                    'line_name' => 'Невско-Василеостровская',
                                    'station_id' => 16.235,
                                    'line_id' => 16,
                                    'lat' => 59.848709,
                                    'station_name' => 'Обухово',
                                    'lng' => 30.457743
                                ),

                            'metro_stations' => Array
                                (
                                    0 => (object) array
                                        (
                                            'line_name' => 'Невско-Василеостровская',
                                            'station_id' => 16.235,
                                            'line_id' => 16,
                                            'lat' => 59.848709,
                                            'station_name' => 'Обухово',
                                            'lng' => 30.457743
                                        )

                                ),

                            'raw' => '',
                            'street' => 'улица Грибакиных',
                            'lat' => 59.849664,
                            'lng' => 30.45844,
                            'id' => 321319
                        ),

                    'sort_point_distance' => '',
                    'id' => 25013164,
                    'salary' => (object) array
                        (
                            'to' => 30000,
                            'gross' => 1,
                            'from' => '',
                            'currency' => 'RUR'
                        ),

                    'archived' => '',
                    'name' => 'Узкотехнический специалист',
                    'can_upgrade_billing_type' => '',
                    'area' => (object) array
                        (
                            'url' => 'https://api.hh.ru/areas/96',
                            'id' => 96,
                            'name' => 'Ижевск'
                        ),
                    'url' => 'https://api.hh.ru/vacancies/25013164?host=hh.ru',
                    'created_at' => '2018-03-20T17:16:23+0300',
                    'has_updates' => 1,
                    'published_at' => '2018-03-20T17:16:23+0300',
                    'relations' => Array(),
                    'employer' => (object) array
                        (
                            'logo_urls' => (object) array
                                (
                                    '90' => 'https://hhcdn.ru/employer-logo/964442.png',
                                    '240' => 'https://hhcdn.ru/employer-logo/964443.png',
                                    'original' => 'https://hhcdn.ru/employer-logo-original/217776.gif',
                                ),

                            'vacancies_url' => 'https://api.hh.ru/vacancies?employer_id=63290',
                            'name' => 'ГиперМетод',
                            'url' => 'https://api.hh.ru/employers/63290',
                            'alternate_url' => 'https://hh.ru/employer/63290',
                            'id' => 63290,
                            'trusted' => 1
                        ),

                    'response_letter_required' => '',
                    'billing_type' => (object) array
                        (
                            'id' => 'free',
                            'name' => 'Бесплатная'
                        ),

                    'type' => (object) array
                        (
                            'id' => 'closed',
                            'name' => 'Закрытая'
                        ),

                    'counters' => (object) array
                        (
                            'views' => 180,
                            'invitations' => 0,
                            'unread_responses' => 3,
                            'responses' => 4,
                            'resumes_in_progress' => 0
                        )

                )

        ),

    'page' => 0,
    'pages' => 1,
    'found' => 2
);*/

        $vacancyService   = $this->getService('RecruitVacancy');
        
        $vacancies = $vacancyService->fetchAll('hh_vacancy_id > 0');// OR superjob_vacancy_id > 0
        $vacancyExist = $vacancies->getList(hh_vacancy_id);
        $counter = 0;

        if ($vacanciesHH) {
            foreach($vacanciesHH->items as $item) {
                if(isset($vacancyExist[$item->id])) continue;

                $newVacancy = array(
                    'hh_vacancy_id' => $item->id,
                    'name' => $item->name,
                    'create_date' => $item->create_at,
                    'open_date' => $item->create_at,
                    'salary' => $item->salary->to ? $item->salary->to : $item->salary->from,
                    'status' => HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL,
                    'create_date' => date('d.m.Y', strtotime($item->created_at)),
                    'close_date' => date('d.m.Y', strtotime($item->created_at)),
                    'created_by'         => $this->getService('User')->getCurrentUserId(),
                    'justAdd' =>true
                );
                $newVacancy = $this->getService('RecruitVacancy')->insert($newVacancy);

                $counter++;
            }
        }

        $this->_flashMessenger->addMessage(array(
            'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Загружено вакансий:') . $counter
        ));
        
        $this->_redirector->gotoSimple('index', 'list', 'vacancy', array(), null, true);
    }

}
