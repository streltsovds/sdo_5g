<?php

class Application_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $_recruitApplicationId = 0;
    protected $_recruitApplication = null;

    const ACTION_TAKETOWORK    = 'take-to-work';
    const ACTION_TAKETOWORK_BY = 'take-to-work-by';
    const ACTION_STOP          = 'stop';
    const ACTION_MASS_STOP     = 'mass-stop';
    const ACTION_RETURNTOWORK  = 'return-to-work';

    public function init()
    {

        $userService           = $this->getService('User');          
        $currentUserId = $userService->getCurrentUserId();

        $form = new HM_Form_RecruitApplication();
        $this->_setForm($form);

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {

            $form->addModifier(new HM_Form_Modifier_Hideuser());
            $form->getElement('user_id')->setRequired(false);
            $form->removeDisplayGroup('projectGroupCommon');

        } elseif (

            $this->getService('User')->isRoleExists($currentUserId, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR) &&
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))
        ) {
            // если потенциально имеет роль - переключаем автоматом
            $this->view->switchRole = HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR;
        }

        $this->_recruitApplicationId = $this->_getParam('recruit_application_id', 0);

        parent::init();
    }

    public function indexAction() 
    {
        $showSelect = false;
        $my = 'all';
//        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
//            $showSelect = true;
//            $session = new Zend_Session_Namespace('select_my');
//            if ($my = $this->_getParam('select_my', false)) {
//                $session->my = $my;
//            } else {
//                if (isset($session->my)) {
//                    $my = $session->my;
//                } else {
//                    $my = 'all';
//                }
//
//            }
//        }

//        if (!$this->isGridAjaxRequest() && $this->_request->getParam('statusgrid') == "") {
//            $this->_request->setParam('statusgrid', 1);
//        }

        $selectVacancy = $this->getService('RecruitVacancy')->getSelect();
        $selectVacancy->from('recruit_vacancies', array('vacancy_id', 'name', 'recruit_application_id', 'deleted'));
        $allVacancies = $selectVacancy->query()->fetchAll();

        $vacancyArray = array();
        foreach ($allVacancies as $vacancy){
            $item = $vacancyArray[$vacancy['recruit_application_id']];
            if ($vacancy['deleted'] != 1) {
                $vacancyArray[$vacancy['recruit_application_id']][] = array(
                    'vacancy_id' => $vacancy['vacancy_id'],
                    'name'       => $vacancy['name']
                );
            }
        }

        $select = $this->getService('RecruitApplication')->getGridSelect(($my=='my')?true:false);
        $grid = HM_Application_Grid_RecruitApplicationGrid::create();

        $selectMy = new Zend_Form_Element_Select('select_my',
            array(
                'Label' => _('Выводить в таблице заявки:'),
                'multiOptions' => array('all' => _('Все заявки'), 'my' => _('Только мои')),
                'Filters' => array('StripTags'),
                'value' => $my
            )
        );

        $submit = new Zend_Form_Element_Submit('submit', array('Label' => _('Выбрать')));

        $this->view->isAjaxRequest = $this->isAjaxRequest();
        $this->view->selectMy = $selectMy;
        $this->view->showSelect = $showSelect;
        $this->view->submit = $submit;
        $this->view->grid = $grid->init($select);
        
    }

    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getParams();
            if ($params['soid'] == 0) {
                $params['soid'] = -1;
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
                    $currentUserId = $this->getService('User')->getCurrentUserId();
                    if (count($responsibility = $this->getService('Responsibility')->get($currentUserId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {
                        $params['soid'] = array_shift($responsibility); // сейчас нет возможности задать несколько responsibility
                    } else {
                        $div = $this->getOne(
                            $this->getService('Orgstructure')->fetchAll(
                                $this->quoteInto(
                                    "mid = ?",
                                    $currentUserId
                                )
                            )
                        );

                        if ($div !== false && $div->owner_soid ) {
                            $params['soid'] = $div->owner_soid;
                        }
                    }
                }
            }


            if ($form->isValid($params)) {

                $values = $form->getValues();

                if ($department = $this->getService('Orgstructure')->findOne($values['soid'])) {
                    $values['department_path'] = $department->getOrgPath(false);
                }

                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
                    $values['user_id'] = $this->getService('User')->getCurrentUserId();
                } elseif (is_array($values['user_id'])) {
                    $values['user_id'] = $values['user_id'][0];
                }

                $dataFieldsService = $this->getService('RecruitVacancyDataFields');

                $recruit_application = $this->getService('RecruitApplication')->insert(array(
                    'vacancy_description'    => $values['vacancy_description'],
                    'vacancy_name'           => $values['vacancy_name'],
                    'user_id'                => $values['user_id'], // инициатор заявки
                    'soid'                   => $values['soid'],
                    'status'                 => $values['status'],
                    'department_path'        => $values['department_path'],
                ));

                $recruit_application_id = $recruit_application->recruit_application_id;
                if ($recruit_application) {
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));

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
                        'item_type' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_APPLICATION,
                        'item_id'   => $recruit_application_id,
                        'create_date'   => date('d.m.Y H:i:s'),
                        'last_update_date'   => date('d.m.Y H:i:s'),
//                        'create_date'   => $values['create_date'],
//                        'last_update_date'   => $values['last_update_date'],
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

                } else {
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ERROR_COULD_NOT_CREATE));
                }

                $this->_redirectToIndex();
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

		    $data = array('status' => HM_Recruit_Application_ApplicationModel::STATUS_NEW);

            $position_id = $this->_getParam('soid');
            $positions = $this->getService('Orgstructure')->findDependence('Profile', $position_id);

            if (count($positions)) {

                $position = $positions->current();
                if (count($position->profile)) {
                    $profile = $position->profile->current();
                    $data['vacancy_name'] = $profile->name;
                }

                // дефолтное значение поля "инициатор"
                // начальник подразделения (если создает рекрутер)
                // или сам пользователь
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_HR))) {
                    $manager = $this->getService('Orgstructure')->getManager($position->soid);
                    if ($manager && count($manager->user)) {
                        $user = $manager->user->current();
                        $data['user_id'] = array($manager->mid => sprintf('%s (%s)', $user->getName(), $manager->name));
                    }
                } else {
                    $user = $this->getService('User')->getCurrentUser();
                    $data['user_id'] = array($user->MID => $user->getName());
                }
            }

		    $form->populate($data);
    	}
        
        $this->view->form = $form;
    }


    public function editAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getParams();
            if (!$params['soid']) {
                $params['soid'] = -1;
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
                    $currentUserId = $this->getService('User')->getCurrentUserId();
                    if (count($responsibility = $this->getService('Responsibility')->get($currentUserId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {
                        $params['soid'] = array_shift($responsibility); // сейчас нет возможности задать несколько responsibility
                    }
                }
            }
            if ($form->isValid($params)) {

                $values = $form->getValues();

                if ($department = $this->getService('Orgstructure')->findOne($values['soid'])) {
                    $values['department_path'] = $department->getOrgPath(false);
                }

                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
                    $values['user_id'] = $this->getService('User')->getCurrentUserId();
                } elseif (is_array($values['user_id'])) {
                    $values['user_id'] = $values['user_id'][0];
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

                $dataFieldsService = $this->getService('RecruitVacancyDataFields');
                $dataFieldsValues = array(
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

                $where = array(
                    'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_APPLICATION,
                    'item_id = ?'   => $values['recruit_application_id'],
                );

                $dataFieldsService->updateWhere($dataFieldsValues, $where);

                $this->getService('RecruitApplication')->update(array(
                    'recruit_application_id'    => $values['recruit_application_id'],
                    'vacancy_description'    => $values['vacancy_description'],
                    'vacancy_name'           => $values['vacancy_name'],
                    'user_id'                => $values['user_id'],
                    'soid'                   => $values['soid'],
                    'status'                 => $values['status'],
                    'department_path'        => $values['department_path'],
                ));

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();

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

            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function setDefaults(\Zend_Form $form)
    {
        $recruitApplication = $this->getService('RecruitApplication')
            ->fetchOneDependence('User' , array(
                'recruit_application_id = ?' => $this->_recruitApplicationId
            ));
        $where = array(
            'item_type = ?' => HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_APPLICATION,
            'item_id = ?'   => $this->_recruitApplicationId,
        );

        $recruitVacancyDataFields = $this->getService('RecruitVacancyDataFields')->fetchRow($where);
        $recruitVacancyDataFieldsData = $recruitVacancyDataFields->getData();

        $recruitApplicationData = $recruitApplication->getData();

        if ($recruitApplicationData['user_id']) {
            $user = $this->getService('User')->findOne($recruitApplicationData['user_id']);
            if ($user) {
                $recruitApplicationData['user_id'] = array($user->MID => $user->getName());
            }
        }

        $recruitApplicationData['cursoid'] = $recruitApplicationData['soid'];

        if (!empty($recruitVacancyDataFieldsData)) {
            $recruitVacancyDataFieldsData['user_id'] = $recruitApplicationData['user_id'];
            $recruitVacancyDataFieldsData['tasks'] = explode('||',$recruitVacancyDataFieldsData['tasks']);
            $recruitVacancyDataFieldsData['additional_education'] = explode('||',$recruitVacancyDataFieldsData['additional_education']);
            $recruitVacancyDataFieldsData['knowledge_of_computer_programs'] = explode('||',$recruitVacancyDataFieldsData['knowledge_of_computer_programs']);
            $recruitVacancyDataFieldsData['knowledge_of_foreign_languages'] = explode('||',$recruitVacancyDataFieldsData['knowledge_of_foreign_languages']);
            $recruitVacancyDataFieldsData['experience_other'] = explode('||',$recruitVacancyDataFieldsData['experience_other']);
        }

        $resultArray = array_merge($recruitApplicationData, $recruitVacancyDataFieldsData);
        $form->populate($resultArray);
    }

    public function delete($id)
    {
        $recruitApplication = $this->getService('RecruitApplication')->findOne($id);
        if ($recruitApplication->status != HM_Recruit_Application_ApplicationModel::STATUS_COMPLETED) {
            $this->getService('RecruitApplication')->delete($id);
        }
    }

    public function stopAction()
    {
        $this->_stop($this->_getParam('recruit_application_id'));
        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_STOP));
        $this->_redirectToIndex();
    }

    public function massStopAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->_stop($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_MASS_STOP));
            }
        }
        $this->_redirectToIndex();
    }

    protected function _stop($id)
    {
        $recruitApplication = $this->getService('RecruitApplication')->getOne(
            $this->getService('RecruitApplication')->fetchAll(array(
                'recruit_application_id = ?' => $id
            ))
        );
        if ($recruitApplication->status != HM_Recruit_Application_ApplicationModel::STATUS_STOPPED &&
            $recruitApplication->status != HM_Recruit_Application_ApplicationModel::STATUS_CLOSED  &&
            $recruitApplication->status != HM_Recruit_Application_ApplicationModel::STATUS_COMPLETED) {
            $statusToSave = $recruitApplication->status;
            $this->getService('RecruitApplication')->updateWhere(
                array(
                    'status'       => HM_Recruit_Application_ApplicationModel::STATUS_STOPPED,
                    'saved_status' => $statusToSave
                ),
                array('recruit_application_id = ?' => $id)
            );
        }
    }

    public function returnToWorkAction()
    {
        $recruitApplicationId = $this->_getParam('recruit_application_id');
        $recruitApplication = $this->getService('RecruitApplication')->getOne(
            $this->getService('RecruitApplication')->fetchAll(array(
                'recruit_application_id = ?' => $recruitApplicationId
            ))
        );
        $statusToRestore = $recruitApplication->saved_status;
        $this->getService('RecruitApplication')->updateWhere(
            array(
                'status'       => $statusToRestore,
                'saved_status' => NULL
            ),
            array('recruit_application_id = ?' => $recruitApplicationId)
        );
        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_RETURNTOWORK));
        $this->_redirectToIndex();
    }

    public function takeToWorkAction()
    {
        $recruitApplicationId = $this->_getParam('recruit_application_id', 0);
        $this->takeToWork($recruitApplicationId);
        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_TAKETOWORK));
            
        $redirectToSessionUrl = $this->view->url(array(
            'module'                 => 'vacancy',
            'controller'             => 'list',
            'action'                 => 'create-from-application',
            'recruit_application_id' => $recruitApplicationId,
            
        ), null, true);
        $redirectToIndexUrl = $this->view->url(array(
            'module'     => 'application',
            'controller' => 'list',
            'action'     => 'index',
        ), null, true);
        
        $this->view->redirectToSessionUrl = $redirectToSessionUrl;
        $this->view->redirectToIndexUrl   = $redirectToIndexUrl;
//        $this->_redirectToIndex();
    }

    public function massTakeToWorkAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->takeToWork($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_TAKETOWORK_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function takeToWork($recruitApplicationId)
    {
        $this->getService('RecruitApplication')->takeToWork($recruitApplicationId);
    }


    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT        => _('Заявка на подбор создана'),
            self::ACTION_UPDATE        => _('Заявка на подбор изменена'),
            self::ACTION_DELETE        => _('Заявка на подбор удалена'),
            self::ACTION_STOP          => _('Заявка на подбор приостановлена'),
            self::ACTION_MASS_STOP     => _('Заявки на подбор приостановлены'),
            self::ACTION_RETURNTOWORK  => _('Заявка на подбор возвращена в работу'),
            self::ACTION_DELETE_BY     => _('Заявки на подбор удалены'),
            self::ACTION_TAKETOWORK    => _('Заявка на подбор принята в работу'),
            self::ACTION_TAKETOWORK_BY => _('Заявки на подбор приняты в работу')
        );
    }

}
