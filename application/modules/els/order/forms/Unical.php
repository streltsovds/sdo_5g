<?php
class HM_Form_Unical extends HM_Form {
	    public function init() {

        $userId = $this->getParam('MID', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('userUnical');

		$this->setAttrib('id', 'target');
        $this->setAction('');
		$this->addElement('hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            )
        );


        $labelLastName   = _('Фамилия');
        $labelFirstName  = _('Имя');
        $labelPatronymic = _('Отчество');

		

        $this->addElement($this->getDefaultTextElementName(), 'lastname', array('Label' => $labelLastName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
			
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'firstname', array('Label' => $labelFirstName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'patronymic', array('Label' => $labelPatronymic,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultRadioElementName(), 'gender', array(
            'Label' => _('Пол'),
            'Required' => false,
            'Validators' => array(

            ),
            'Value' => 1,
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => HM_User_Metadata_MetadataModel::getGenderValues(),
            'separator' => ' '
        ));

        $this->addElement($this->getDefaultTextElementName(), 'year_of_birth', array(
            'Label' => _('Год рождения'),
            'Required' => false,
            'Validators' => array(
                array('Between', false, array(1910, date('Y')))
            ),
            'Filters' => array(

            )
        ));

        $loginErrorMsg = _('В логине пользователя допустимы латинские символы, знак подчёркивания и точка');
        $loginValidator = new Zend_Validate_Regex('/^[\w-_\.]+$/');
        $loginValidator->setMessage($loginErrorMsg, 'regexNotMatch');
		
        $this->addElement($this->getDefaultTextElementName(), 'userlogin', array(
            'Label' => _('Логин'),
            'Required' => true,
            'Value' => Zend_Registry::get('serviceContainer')->getService('User')->generateLogin(),
            'Validators' => array(
                array('StringLength', 255, 1),
                array($loginValidator, true),
               // array('Db_NoRecordExists', false, array('table' => 'People', 'field' => 'Login', 'value' => 'user_886'))
            ),
            'Filters' => array('StripTags'),
            'Description' => $loginErrorMsg
        ));

            $tags = $userId ? $this->getService('Tag')->getTags($userId, $this->getService('TagRef')->getUserType() ) : '';
 
        $this->addElement($this->getDefaultFileElementName(), 'photo', array(
            'Label' => _('Фотография'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'user_id' => 0
        )
        );

        $photo = $this->getElement('photo');
        $photo->addDecorator('UserImage')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                    )
                )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);

//        $this->addElement($this->getDefaultWysiwygElementName(), 'additional_info',
//            array(
//            	'Label' => _('Дополнительная информация'),
//            	'Required' => false,
//            )
//        );

        $this->addElement($this->getDefaultTextElementName(), 'email', array('Label' => _('Контактный e-mail'),
            'Required' => true,
            'Validators' => array(
                array('EmailAddress')
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'tel', array(
            'Label' => _('Контактный телефон'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
            )
        ));
		
		 // start preparing position_id element
        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1))
        );

        if ($userId) {
            $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
            if (count($units)) {
                $department = $units->current();
                $positionIdJQueryParams['selected'][] = [
                    "id" => $department->soid,
                    "value" => htmlspecialchars($department->name),
                    "leaf" => !(isset($department->descendants) && count($department->descendants))
                ];
                $positionIdJQueryParams['ownerId'] = $department->owner_soid;
            }
        }

        $this->addElement($this->getDefaultTreeSelectElementName(), 'position_id', array(
            'Label' => _('Подразделение'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Params' => $positionIdJQueryParams
        ));

        $this->addElement($this->getDefaultTextElementName(), 'position_name', array(
            'Label' => _('Должность'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );
		
		$this->addElement('hidden', 'midunical');
		
		$this->addElement('hidden', 'middublicate');

		$this->addElement('hidden', 'mid_external');	
        /* НА ГАЗПРОМЕ ПРИ МЕРЖЕ ОСТАВИТЬ!!!
        $this->addElement($this->getDefaultSelectElementName(), 'team', array(
            'Label' => _('Принадлежность к группе пользователей'),
            'Required' => false,
            'MultiOptions' => HM_User_Metadata_MetadataModel::getTeamValues()
        ));
        */
		parent::init(); 
        
		$this->addDisplayGroup(array(
            'userlogin',
            'user_id',
            'cancelUrl',
            'tags',	
        ),
            'Users1',
			
            array('legend' => _('Учётная запись'),	'style'=>'width:400px;')
			
        );

        $this->addDisplayGroup(array(
            'mid_external',
            'lastname',
            'firstname',
            'patronymic',
            'gender',
            'year_of_birth',
            'email',
            'tel',
            'photo',
            'additional_info'
        ),
            'Users2',
            array('legend' => _('Персональные данные'),'style'=>' width:400px;')
        );
		 if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN))) {
            $classifierElements = $this->addClassifierElements(
                HM_Classifier_Link_LinkModel::TYPE_PEOPLE,
                $userId
            );
            $this->addClassifierDisplayGroup($classifierElements);
        }
		
        $this->addDisplayGroup(
            array(
                'position_id',
                'position_name',
            ),
            'UserOrgstructureDublicate',
            array('legend' => _('Место работы'),'style'=>'width:400px;')
        );
		$this->addElement($this->getDefaultSubmitElementName(), 'unionDublicate', array('Label' => _('Сохранить'),'id'=>'unionDublicate'));
    }

}