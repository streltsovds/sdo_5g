<?php

use HM_Recruit_Application_ApplicationModel as Model;

class HM_Form_RecruitApplication extends HM_ParentForm_VacancyDataFields
{
    public function init()
    {
        $this->setName('recruit_application');
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'application', 'controller' => 'list', 'action' => 'index'), null, true)
        ));

        $this->addElement('hidden', 'recruit_application_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), $groupArrayDescription[] = 'vacancy_name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('hidden',  $groupArrayDescription[] = 'number_of_vacancies', array(
            'value' => 1
        ));
        
//        $this->addElement($this->getDefaultTextAreaElementName(), $groupArray []= 'vacancy_description', array(
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

        $this->addElement('hidden', 'status', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
//        $positionIdJQueryParams = array(
//            'remoteUrl' => $this->getView()->url(array('baseUrl'=> false, 'module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1))
//        );
//
//        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))) {
//            $min = 0;
//        } else {
//            $min = -1;
//        }
//
//        $this->addElement('uiTreeSelect', 'soid', array(
//            'Label'      => _('Подразделение'),
//            'Required'   => true,
//            'validators' => array(
//                'int',
//                array('GreaterThan', false, array('min' => $min, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать подразделение"))))
//
//            ),
//            'filters' => array('int'),
//            'jQueryParams' => $positionIdJQueryParams
//        ));
        
        parent::init($groupArrayDescription); // required!
    }

}