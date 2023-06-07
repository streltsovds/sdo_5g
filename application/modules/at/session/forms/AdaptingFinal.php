<?php
class HM_Form_AdaptingFinal extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('adapting_final_form');
        
        $newcomerId = $this->getParam('newcomer_id', 0);
        
        $this->addElement('hidden', 'newcomer_id', array(
            'Required' => true,
            'value'    => $newcomerId,
        ));        
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array(
                'module'      => 'session',
                'controller'  => 'event',
                'action'      => 'list',
                'newcomer_id' => $newcomerId
            ), null, true)
        ));        
        
        $this->addElement('radioGroup', 'result', array(
            'Value' => $sessionUser->session_user_id,
            'form' => $this,
            'value' => HM_Recruit_Newcomer_NewcomerModel::RESULT_SUCCESS,
            'multiOptions' => array(
                HM_Recruit_Newcomer_NewcomerModel::RESULT_SUCCESS      => _('Адаптация пройдена успешно'),
                HM_Recruit_Newcomer_NewcomerModel::RESULT_FAIL_DEFAULT => _('Адаптация не пройдена'),
                HM_Recruit_Newcomer_NewcomerModel::RESULT_EXTENDED     => _('Адаптация будет продлена'),
            ),
            'dependences' => array(
                HM_Recruit_Newcomer_NewcomerModel::RESULT_SUCCESS      => array(),
                HM_Recruit_Newcomer_NewcomerModel::RESULT_FAIL_DEFAULT => array(),
                HM_Recruit_Newcomer_NewcomerModel::RESULT_EXTENDED     => array('extended_to'),
            ),
        ));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR) ||
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $this->addElement($this->getDefaultTextElementName(), 'final_comment', array(
                'Label' => _('Комментарий'),
                'Required' => false,
                'class' => 'wide',
                'Filters' => array('StripTags'),
                //'toolbar' => 'hmToolbarMini',
            ));
        }
        
        
        $this->addElement($this->getDefaultDatePickerElementName(), 'extended_to', array(
            'Label' => _('Продлить до'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                    false,
                    array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        ));

        $fieldsArray = array(
            'result',
            'extended_to',
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR) ||
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $fieldsArray[] = 'final_comment';
        }
        
        $this->addDisplayGroup(
            $fieldsArray,
            'main',
            array('legend' => _(''))
        );
        
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        
        parent::init(); // required!
    }
}