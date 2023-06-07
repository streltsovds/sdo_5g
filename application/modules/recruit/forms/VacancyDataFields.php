<?php

use HM_Recruit_Application_ApplicationModel as Model;

class HM_ParentForm_VacancyDataFields extends HM_Form
{
    public function init($groupArrayDescription = array(), $groupArrayCommon = array())
    {
        $this->setMethod(Zend_Form::METHOD_POST);

        $vacancyId = $this->getParam('vacancy_id', 0);
        $vacancy = $this->getService('RecruitVacancy')->findOne($vacancyId);


        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array(
                'baseUrl'=> false,
                'module' => 'orgstructure',
                'controller' => 'ajax',
                'action' => 'tree',
                'only-departments' => 0,
            )),
            'onlyLeaves' => true,
        );

        if ($this->getParam('create-from-structure')) {

            $this->addElement('hidden', $groupArrayDescription[] = 'soid', array('Required' => false));

        }  elseif ($vacancy && in_array($vacancy->status, array(
            HM_Recruit_Vacancy_VacancyModel::STATE_PENDING,
            HM_Recruit_Vacancy_VacancyModel::STATE_ACTUAL,
        ))) {

            // отключено редактирование, т.к. может измениться программа => всё поплывёт в процессе подбора
            $this->addElement('hidden', 'soid', array('Required' => false));

        }  else {

            if ($recruitApplicationId = $this->getRequest()->getParam('recruit_application_id', false)) {

                $recruitApplication = $this->getService('RecruitApplication')->find($recruitApplicationId)->current();
                if ($position = $this->getService('Orgstructure')->find($recruitApplication->soid)->current()) {
                    $positionIdJQueryParams['selected'] = $position->soid;
                    $positionIdJQueryParams['itemId'] = $position->owner_soid;
                    $positionIdJQueryParams['ignoreDefaultSelectedValue'] = true;
                }
            }

            $this->addElement('uiTreeSelect', $groupArrayDescription[] = 'soid', array(
                'Label'      => _('Должность'),
                'Required'   => true,
                'validators' => array(
                    'int',
                    array('GreaterThan', false, array('min' => -1, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать должность"))))

                ),
                'filters' => array('int'),
                'jQueryParams' => $positionIdJQueryParams
            ));
        }

        $this->addElement(
            new HM_Form_Element_FcbkComplete($groupArrayCommon[] = 'user_id', array(
                    'required' => false,
                    'Label' => _('ФИО инициатора'),
                    'Description' => _('По умолчанию будет назначен руководитель выбранного подраздения, а при отсутсвии руководителя, создавший заявку.'),
                    'json_url' => '/user/ajax/users-list',
                    'newel' => false,
                    'maxitems' => 1
                )
            ));

        $this->addElement('text', $groupArrayDescription[] = 'work_place', array(
            'Label' => _('Место работы (город)'),
            'Required' => false,
            'Filters' => array('StripTags'),
//            'value' => $workPlace,
//            'class' => 'wide',
        ));

        $this->addElement('text', $groupArrayDescription[] = 'salary', array(
            'Label' => _('Предполагаемый уровень месячного дохода'),
            'Required' => false,
            'Filters' => array('StripTags'),
        ));

//        $this->addElement(
//            new HM_Form_Element_FcbkComplete($groupArrayDescription[] = 'who_obeys', array(
//                    'required' => true,
//                    'Label' => _('Кому подчиняется'),
//                    'Description' => _(''),
//                    'json_url' => '/user/ajax/users-list-is-manager',
//                    'newel' => false,
//                    'maxitems' => 1
//                )
//            ));

//        $this->addElement('select', $groupArrayDescription[] = 'subordinates_count', array(
//            'Label' => _('Пользователей в подчинении'),
//            'required' => false,
//            'filters' => array('int'),
//            'multiOptions' => array(0 => _('Не указано')) + HM_Recruit_Vacancy_VacancyModel::getStaffCountVariants()
//        ));

        $this->addElement('select', $groupArrayDescriptionAdditional[] = 'work_mode', array(
            'Label' => _('График работы'),
            'required' => false,
            'filters' => array('int'),
            'multiOptions' => array(0 => _('Не указано')) + HM_Recruit_Vacancy_VacancyModel::getWorkModeVariants()
        ));

//        $this->addElement('select', $groupArrayDescription[] = 'type_contract', array(
//            'Label' => _('Тип и срок договора'),
//            'required' => false,
//            'filters' => array('int'),
//            'multiOptions' => array(0 => _('Не указано')) + HM_Recruit_Vacancy_VacancyModel::getContractVariants()
//        ));
//
//        $this->addElement('text', $groupArrayDescription[] = 'probationary_period', array(
//            'Label' => _('Испытательный срок, мес.'),
//            'Required' => false,
//            'Filters' => array('StripTags'),
////            'value' => $workPlace,
//            'class' => 'brief2'
//        ));

//        $this->addElement('textarea', $groupArrayDescription[] = 'career_prospects', array(
//            'Label' => _('Карьерные перспективы'),
//            'rows' => 5,
//            'Required' => false,
//            'Validators' => array(
//                array('StringLength', 4000, 0),
//            ),
//            'Filters' => array(
//                'StripTags'
//            )
//        ));
//
//        $this->addElement('select', $groupArrayDescription[] = 'reason', array(
//            'Label' => _('Причина появления вакансии'),
//            'required' => false,
//            'filters' => array('int'),
//            'multiOptions' => array(0 => _('Не указано')) + HM_Recruit_Vacancy_VacancyModel::getReasonVariants()
//        ));

        $this->addElement('multiText', $groupArrayResponsibility[] = 'tasks', array(
            'Label' => _('Должностные обязанности'),
            'SubLabel' => _('%s.'),
            'Filters' => array('StripTags'),
//            'class' => 'wide',
            'Required' => false,
        ));

        $this->addElement('textarea', $groupArrayPrimaryRequirements[] = 'education', array(
            'Label' => _('Образование'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('textarea', $groupArrayPrimaryRequirements[] = 'skills', array(
            'Label' => _('Навыки'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('multiText', $groupArrayPrimaryRequirements[] = 'additional_education', array(
            'Label' => _('Дополнительное образование (курсы, тренинги)'),
//            'SubLabel' => _('%s.'),
            'Filters' => array('StripTags'),
//            'class' => 'wide',
            'Required' => false,
        ));

        $this->addElement('multiText', $groupArrayPrimaryRequirements[] = 'knowledge_of_computer_programs', array(
            'Label' => _('Знание компьютерных программ'),
//            'SubLabel' => _('%s.'),
            'Filters' => array('StripTags'),
//            'class' => 'wide',
            'Required' => false,
        ));

        $this->addElement('multiText', $groupArrayPrimaryRequirements[] = 'knowledge_of_foreign_languages', array(
            'Label' => _('Знание иностранных языков (язык, степень владения)'),
//            'SubLabel' => _('%s.'),
            'Filters' => array('StripTags'),
//            'class' => 'wide',
            'Required' => false,
        ));

        $this->addElement('text', $groupArrayDescriptionAdditional[] = 'work_experience', array(
            'Label' => _('Опыт работы (период, лет)'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'brief2'
        ));

//        $this->addElement('textarea', $groupArrayPrimaryRequirements[] = 'personal_qualities', array(
//            'Label' => _('Личные качества'),
//            'rows' => 5,
//            'Required' => false,
//            'Validators' => array(
//                array('StringLength', 4000, 0),
//            ),
//            'Filters' => array(
//                'StripTags'
//            )
//        ));

//        $this->addElement('multiText', $groupArrayAdditionalRequirements[] = 'experience_other', array(
//            'Label' => _('Специальные точки поиска кандидатов (конкретные вузы, организации, профессиональные сообщества)'),
//            'Required' => false,
//            'Filters' => array('StripTags'),
////            'class' => 'wide'
//        ));
//
//        $this->addElement('textarea', $groupArrayAdditionalRequirements[] = 'other_requirements', array(
//            'Label' => _('Прочие требования'),
//            'rows' => 5,
//            'Required' => false,
//            'Validators' => array(
//                array('StringLength', 4000, 0),
//            ),
//            'Filters' => array(
//                'StripTags'
//            )
//        ));


//        $this->addElement('text', 'vacancy_name', array(
//            'Label' => Model::getLabel('vacancy_name'),
//            'Required' => true,
//            'Validators' => array(
//                array('StringLength', 255, 1)
//            ),
//            'Filters' => array(
//                'StripTags'
//            )
//        ));

//        $this->addElement('textarea', 'vacancy_description', array(
//            'Label' => Model::getLabel('vacancy_description'),
//            'rows' => 5,
//            'Required' => false,
//            'Validators' => array(
//                array('StringLength', 4000, 0),
//            ),
//            'Filters' => array(
//                'StripTags'
//            )
//
//        ));

//        $this->addElement('hidden','status', array(
//            'Required' => true,
//            'Validators' => array('Int'),
//            'Filters' => array('Int')
//        ));

        $this->addDisplayGroup($groupArrayDescription,'projectGroupDescription',
            array('legend' => _('Общая информация'))
        );
        $this->addDisplayGroup($groupArrayResponsibility
            ,'projectGroupResponsibility',
            array('legend' => _('Должностные обязанности'))
        );
        $this->addDisplayGroup($groupArrayPrimaryRequirements,'projectGroupPrimaryRequirements',
            array('legend' => _('Требования к кандидату'))
        );
//        $this->addDisplayGroup($groupArrayAdditionalRequirements,'projectGroupAdditionalRequirements',
//            array('legend' => _('Дополнительные требования к кандидату'))
//        );

        $this->addDisplayGroup($groupArrayDescriptionAdditional,'projectGroupDescriptionAdd',
            array('legend' => _('Дополнительная информация'))
        );

        $this->addDisplayGroup($groupArrayCommon,'projectGroupCommon',
            array('legend' => _('Заявка на подбор'))
        );

        $this->addElement('Submit','submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}