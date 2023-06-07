<?php
class HM_Form_Skills extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('skills');
        
        if ($profileId = $this->getParam('profile_id', 0)) {
            $profile = Zend_Registry::get('serviceContainer')->getService('AtProfile')->find($profileId)->current();
        } 

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'report', 'action' => 'index'))
            )
        );
        
        $this->addElement('hidden',
            'profile_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );   


//        $functions = array(1=>'adas asd f asf asf',2=>'11111111 11111111 1111112');
        $this->addElement($this->getDefaultMultiSelectElementName(), 'functions',
            array(
        	    'Style' => 'width:900px;height:600px',
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'jQueryParams' => array(
                    'remoteUrl' => $this->getView()->url(array('module' => 'standard', 'controller' => 'ajax', 'action' => 'tree')),
                ),
//                'multiOptions' => $functions,
                'class' => 'multiselect'
            )
        );

        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        }
        parent::init(); // required!
    }
}