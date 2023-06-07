<?php
class HM_Form_Dublicate extends HM_Form {

   /* public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        
        //$this->setName('OldName');
        //$rowDublUser = $rowUsers[0];
        //$rowUnicUser = $rowUsers[1];
        $widthInput = 'width:350px;';
   
        $this->addElement($this->getDefaultTextElementName(), 'LastNameUnical', array('Label' => _('Фамилия'),'disabled'=>true,'style'=>$widthInput));
        $this->addElement($this->getDefaultTextElementName(), 'FirstNameUnical', array('Label' => _('Имя'),'disabled'=>true,'style'=>$widthInput));
        $this->addElement($this->getDefaultTextElementName(), 'PatronymicUnical', array('Label' => _('Отчество'),'disabled'=>true,'style'=>$widthInput));
        
       
        $this->addElement($this->getDefaultTextElementName(), 'LastNameDublicate', array('Label' => _('Фамилия'),'disabled'=>true,'style'=>$widthInput));
        $this->addElement($this->getDefaultTextElementName(), 'FirstNameDublicate', array('Label' => _('Имя'),'disabled'=>true,'style'=>$widthInput));
        $this->addElement($this->getDefaultTextElementName(), 'PatronymicDublicate', array('Label' => _('Отчество'),'disabled'=>true,'style'=>$widthInput));       

        parent::init(); 
           
        $this->addDisplayGroup(array(
            'LastNameUnical',
            'FirstNameUnical',
            'PatronymicUnical',
        ),
            'Users1',
            array('legend' => _('Старая учетная запись'),'style'=>'float:left; width:400px;')
        );    
        $this->addDisplayGroup(array(
              'LastNameDublicate',
              'FirstNameDublicate',
              'PatronymicDublicate',
        ),
            'Users2',
            array('legend' => _('Новая учетная запись'),'style'=>'width:400px;margin-top:-12px;margin-left:470px;')
        );     
    }*/
	    public function init() {

        $userId = $this->getParam('MID', 0);
		 $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('userDublicate');

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


        $this->addElement($this->getDefaultTextElementName(), 'lastnameDublicate', array('Label' => $labelLastName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
			'disabled'=>true,
            'Filters' => array('StripTags')
			
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'firstnameDublicate', array('Label' => $labelFirstName,
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
			'disabled'=>true,
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'patronymicDublicate', array('Label' => $labelPatronymic,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
			'disabled'=>true,
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultRadioElementName(), 'genderDublicate', array(
            'Label' => _('Пол'),
            'Required' => false,
            'Validators' => array(

            ),
            'Value' => 1,
            'Filters' => array(
                'Int'
            ),
			'disabled'=>true,
            'MultiOptions' => HM_User_Metadata_MetadataModel::getGenderValues(),
            'separator' => ' '
        ));

        $this->addElement($this->getDefaultTextElementName(), 'year_of_birthDublicate', array(
            'Label' => _('Год рождения'),
            'Required' => false,
            'Validators' => array(
                array('Between', false, array(1910, date('Y')))
            ),
			'disabled'=>true,
            'Filters' => array(

            )
        ));
		
        $loginErrorMsg = _('В логине пользователя допустимы латинские символы, знак подчёркивания и точка');
        $loginValidator = new Zend_Validate_Regex('/^[\w-_\.]+$/');
        $loginValidator->setMessage($loginErrorMsg, 'regexNotMatch');

        $this->addElement($this->getDefaultTextElementName(), 'userloginDublicate', array(
            'Label' => _('Логин'),
            'Required' => true,
            //'Value' => Zend_Registry::get('serviceContainer')->getService('User')->generateLogin(),
            'Validators' => array(
                array('StringLength', 255, 1),
                array($loginValidator, true),
                array('Db_NoRecordExists', false, array('table' => 'People', 'field' => 'Login'))
            ),
            'Filters' => array('StripTags'),
			'disabled'=>true,
            'Description' => $loginErrorMsg
        ));

            $tags = $userId ? $this->getService('Tag')->getTags($userId, $this->getService('TagRef')->getUserType() ) : '';
 
        $this->addElement($this->getDefaultFileElementName(), 'photoDublicate', array(
            'Label' => _('Фотография'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
			'disabled'=>true,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'user_id' => 0
        )
        );

        $photo = $this->getElement('photoDublicate');
        $photo->addDecorator('UserImageDublicate')
                ->addValidator('FilesSize', true, array(
                        'max' => '10MB'
                    )
                )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);

        $this->addElement($this->getDefaultTextElementName(), 'emailDublicate', array('Label' => _('Контактный e-mail'),
            'Required' => true,
            'Validators' => array(
                array('EmailAddress')
            ),
			'disabled'=>true,
            'Filters' => array('StripTags')
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'tel', array(
            'Label' => _('Контактный телефон'),
            'Required' => false,
            'Validators' => array(
            ),
			'disabled'=>true,
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

        $this->addElement($this->getDefaultTreeSelectElementName(), 'position_id1', array(
			'Label' => _('Подразделение'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'params' => $positionIdJQueryParams
        ));

        $this->addElement($this->getDefaultTextElementName(), 'position_name1', array(
            'Label' => _('Должность'),
             'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        )
        );
        $this->addElement('hidden', 'mid_external');	
		parent::init(); 
		
        $this->addDisplayGroup(array(
            'userloginDublicate',
        ),
            'Users1Dublicate',
			
            array('legend' => _('Учётная запись'),	'style'=>'width:400px;')
			
        );

        $this->addDisplayGroup(array(
            'lastnameDublicate',
            'firstnameDublicate',
            'patronymicDublicate',
            'genderDublicate',
            'year_of_birthDublicate',
            'emailDublicate',
			'mid_external',
            'tel',
            'photoDublicate',
        ),
            'Users2Dublicate',
            array('legend' => _('Персональные данные'),'style'=>'width:400px;')
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
                'position_id1',
                'position_name1',
            ),
            'UserOrgstructureDublicate',
            array('legend' => _('Место работы'),'style'=>'width:400px;')
        );
	
    }
 public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'photo') {
            $decorators = parent::getElementDecorators($alias, 'UserImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }
}