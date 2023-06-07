<?php
class HM_Form_Generate extends HM_Form {

    public function init() {

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('generate');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array(
                    'module' => 'user',
                    'controller' => 'list',
                    'action' => 'index'
                )
                )
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'number', array('Label' => _('Количество'),
            'Required' => true,
            'Validators' => array(
                'Int',
                array('GreaterThan', false, array('min' => 0))
            ),
            'Filters' => array(
                'Int'
            )
        )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'role', array('Label' => _('Роль'),
            'Required' => true,
            'Validators' => array(),
            'Filters' => array('StripTags'),
            'multiOptions' => HM_Role_Abstract_RoleModel::getBasicRoles(false, true) //array_merge(array(_('Пользователь (без роли)')), HM_Role_Abstract_RoleModel::getBasicRoles(false))
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'prefix', array('Label' => _('Логин (префикс)'),
		'Description' => _('Несколько латинских символов, например, stud_, в итоге логин получит вид stud_001, stud_002 и т. д.'),
            'Required' => true,
            'Validators' => array(
                array('Regex', true, '/^[\w\-_]+$/'),
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        )
        );

        $this->addElement($this->getDefaultTextElementName(),
            'password',
            array('Label' => _('Пароль'),
                'Required' => true,
                'type' => 'password',
                'Validators' => array(
                 ),
                'Filters' => array('StripTags')
            )
        );
        
        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
        $password = $this->getElement('password');
        if($passwordOptions['passwordCheckDifficult'] == 1){
            $password->addValidator('HardPassword');
        }else{
            $password->addValidator('Regex', false, array('/^[a-zа-яёЁ0-9%\\$#!]+$/i'));
        }
        
        if($passwordOptions['passwordMinLength'] > 0){
            $password->addValidator('StringLength', false, array('min' => $passwordOptions['passwordMinLength']));
        }

        $this->addDisplayGroup(array(
            'cancelUrl',
            'number',
            'role',
            'prefix',
            'password'
        ),
            'Generate',
            array('legend' => _('Учетные записи'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

}