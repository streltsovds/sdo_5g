<?php

/**
 * Форма для создания и редактирования пользователей
 *
 */
class HM_Form_User extends HM_Form
{

    public function init()
    {
        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');

        $isAdmin = $aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ADMIN);
        $isGuest = $aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_GUEST);

        $codeword = $this->getService('Option')->getOption('codeword');
        $userId = $this->getParam('MID', 0);

        $this
            ->setMethod(Zend_Form::METHOD_POST)
            ->setName('user');

        $request = $this->getRequest();

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $request->getParam('cancelUrl', $request->getServer('HTTP_REFERER')),
            )
        );

        $this->addElement('hidden',
            'user_id',
            array(
                'Required' => false,
                'value' => $this->getParam('user_id', 0)
            )
        );

        $steps = array(
            _('Учётная запись') => ['Users1'],
            _('Персональные данные') => ['Users2'],
            // _('Место работы') => ['UserOrgstructure'],
            _('Дополнительная информация') => ['UserAdditionalFields'],
            _('Назначения') => ['Users3'],
            _('Проверка') => ['CaptchaBlock'],
        );

        if (!empty($codeword) && $isGuest) {
            $steps[_('Кодовое слово')] = ['Codeword'];
        }

        $this->addElement($this->getDefaultStepperElementName(), 'stepper', [
            'steps' => $steps,
            "form" => $this
        ]);


        $this->addElement($this->getDefaultTextElementName(), 'mid_external', array('Label' => _('Табельный номер'),
                'Required' => false,
                'Description' => _('Произвольный номер, отображается в карточке пользователя'),
                'Validators' => array(
                    array('StringLength', 255, 1)
                ),
                'Filters' => array('StripTags'),
            )
        );

        $labelLastName = _('Фамилия');
        $labelFirstName = _('Имя');
        $labelPatronymic = _('Отчество');

        $this->addElement($this->getDefaultTextElementName(), 'lastname', array('Label' => $labelLastName,
                'Required' => true,
                'Validators' => array(
                    array('StringLength', 255, 1),
                    array('AlphaForNames'),
                ),
                'Filters' => array('StripTags')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'firstname', array('Label' => $labelFirstName,
                'Required' => true,
                'Validators' => array(
                    array('StringLength', 255, 1),
                    array('AlphaForNames'),
                ),
                'Filters' => array('StripTags')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'patronymic', array('Label' => $labelPatronymic,
                'Required' => false,
                'Validators' => array(
                    array('StringLength', 255, 1),
                    array('AlphaForNames'),
                ),
                'Filters' => array('StripTags')
            )
        );

        $this->addElement($this->getDefaultRadioElementName(), 'gender', array(
            'Label' => _('Пол'),
            'Required' => false,
            'Validators' => array(),
            'Value' => HM_User_Metadata_MetadataModel::GENDER_UNKNOWN,
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => HM_User_Metadata_MetadataModel::getGenderValues(),
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'birthdate', array(
                'Label' => _('Дата рождения:'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    )
                ),
                'Filters' => array('StripTags')
            )
        );

        $loginErrorMsg = _('В логине пользователя допустимы латинские символы, знак подчёркивания и точка');
        $loginValidator = new Zend_Validate_Regex('/^[\w\-_\.]+$/');
        $loginValidator->setMessage($loginErrorMsg, 'regexNotMatch');

        $this->addElement($this->getDefaultTextElementName(), 'userlogin', array(
            'Label' => _('Логин'),
            'Required' => true,
            'Value' => Zend_Registry::get('serviceContainer')->getService('User')->generateLogin(),
            'Validators' => array(
                array('StringLength', 255, 1),
                array($loginValidator, true),
                array('Db_NoRecordExists', false, array('table' => 'People', 'field' => 'Login'))
            ),
            'Filters' => array('StripTags'),
            'Description' => $loginErrorMsg
        ));

        $passwordOptions = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);

        $this->addElement($this->getDefaultPasswordCheckboxElementName(), 'userpassword',
            array(
                'Label' => _('Пароль'),
                'Description' => sprintf(_("Количество символов в пароле должно быть не менее %d"), $passwordOptions['passwordMinLength']),
                'Validators' => array(
                    array('identical',
                        false,
                        array('token' => 'userpasswordrepeat')
                    )
                ),
                'rules' => [
                    'min' => $passwordOptions['passwordMinLength']
                ],
                'generatepassword' => [
                    'label' => _('Сгенерировать пароль автоматически'),
                    'required' => false,
                ],
                'userpasswordrepeat' => [
                    'label' => _('Повторите пароль'),
                    'required' => true,
                ]
            )
        );

        /** @var HM_Form_Element_Vue_PasswordCheckbox $password */
        $password = $this->getElement('userpassword');

        if ($passwordOptions['passwordCheckDifficult'] == 1) {
            $password->addValidator('HardPassword');
        } else {
            $password->addValidator('Regex', false, array('/^[a-zа-яёЁ0-9%\\$#!]+$/ui', 'messages' => array(Zend_Validate_Regex::NOT_MATCH => _("Пароль может содержать только латинские или кириллические буквы, а также символы '$', '#' и '!'"))));
        }

        if ($passwordOptions['passwordMinLength'] > 0) {
            $password->addValidator('StringLength', false, array('min' => $passwordOptions['passwordMinLength']));
        }

        if ($passwordOptions['passwordMinPeriod'] > 0 && !$isAdmin) {
            $password->addValidator('MinimalDatePassword');
        }

        if ($passwordOptions['passwordMinNoneRepeated'] > 0 && !$isAdmin) {
            $password->addValidator('AmountPassword');
        }

        if ($isAdmin || $aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN)
        ) {
            if (!$userId) {
                $userId = $this->getElement('user_id')->getValue();
            }
            $_tags = $userId ? $this->getService('Tag')->getTags($userId, $this->getService('TagRef')->getUserType()) : array();
            $tags = $this->getService('Tag')->convertAllToStrings($_tags);

            $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
                'Label' => _('Метки'),
                'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'index', 'action' => 'tags')),
                'value' => $tags,
                'Filters' => array()
            ));

//            $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
//                    'Label' => _('Метки'),
//                    'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
//                    'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'index', 'action' => 'tags')),
//                    'value' => $tags,
//                    'Filters' => array()
//                )
//            ));
        }

        /** @var HM_User_UserModel $user */
        $user = $this->getService('User')->find($userId)->current();
        $photo = '';

        if ($user) {
            $photo = $user->getPhoto();
        }

        $hasStartSlash = mb_substr($photo, 0, 1) === '/';
        if (strlen($photo) and !$hasStartSlash) {
            $photo = '/' . $photo;
        }

        $tmpUploadPath = Zend_Registry::get('config')->path->upload->tmp;
        $this->addElement($this->getDefaultFileElementName(), 'photo', array(
            'Label' => _('Фотография'),
            'Destination' => $tmpUploadPath,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'user_id' => 0,
            'crop' => [
                'ratio' => 1
            ],
        ));

        $photo = $this->getElement('photo');
        $photo->addDecorator('UserImage')
            ->addValidator('FilesSize', true, array(
                    'max' => '10MB'
                )
            )
            ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
            ->setMaxFileSize(10485760); // 10MB

        $this->addElement($this->getDefaultTextElementName(), 'email', array('Label' => _('E-mail'),
                'Required' => true,
                'Validators' => array(
                    array('EmailAddress')
                ),
                'Filters' => array('StripTags')
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'tel', array(
            'Label' => _('Телефон'),
            'Required' => false,
            'Validators' => array(),
            'Filters' => array()
        ));

        $this->addElement($this->getDefaultTextElementName(), 'tel2', array(
            'Label' => _('Дополнительный телефон'),
            'Required' => false,
            'Validators' => array(),
            'Filters' => array()
        ));

        if ($isAdmin) {

            $this->addElement($this->getDefaultSelectElementName(), 'status', array('Label' => _('Статус'),
                    'Required' => true,
                    'Validators' => array(),
                    'Filters' => array('StripTags'),
                    'multiOptions' => array(
                        '0' => 'Активный',
                        '1' => 'Заблокирован'
                    )
                )
            );

            $roles = HM_Role_Abstract_RoleModel::getBasicRoles(false, true);
            if ($request->getActionName() == 'new') {
                $this->addElement($this->getDefaultSelectElementName(), 'role', array('Label' => _('Назначить роль'),
                        'Required' => false,
                        'Validators' => array(),
                        'Filters' => array('StripTags'),
                        'multiOptions' => $roles
                    )
                );

                # Обязательные поля пароля и его подтверждения
                $password = $this->getElement('userpassword');
                $password->setRequired(true);
                //            $rpassword = $this->getElement('userpasswordrepeat');
                //            $rpassword -> setRequired(true);
            }
        }

        // start preparing position_id element
        // $positionIdJQueryParams = array(
        //     'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1), false, true)
        // );

        /**
         * Тут выбирались айдишники должности, поэтому в селекте вместо того
         * чтобы показывать родительское подразделение с выбранным своим подразделением
         * добавлен еще один шаг по дереву.
         * т.к. uiTreeSelect значение использовал из формы, т.е. опять же значение должности
         * пришлось добавить еще один параметр передаваемый в JqueryParams
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        // if ($userId) {
        //     $units = $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $userId));
        //     if (count($units)) {
        //         $units = $this->getService('Orgstructure')->fetchAll(array('soid = ?' => $units->current()->owner_soid));
        //     }
        //     if (count($units)) {
        //         $department = $units->current();
        //         $positionIdJQueryParams['selected'][] = [
        //             "id" => $department->soid,
        //             "value" => htmlspecialchars($department->name),
        //             "leaf" => !(isset($department->descendants) && count($department->descendants))
        //         ];
        //         $positionIdJQueryParams['ownerId'] = $department->owner_soid;
        //     }
        // }

        $isHr = $aclService->checkRoles(array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL));
        // if (!$isHr) //#17380
        //     $this->addElement($this->getDefaultTreeSelectElementName(), 'position_id', array(
        //         'Label' => _('Подразделение'),
        //         'Required' => false,
        //         'validators' => array(
        //             'int',
        //             array('GreaterThan', false, array(-1))
        //         ),
        //         'filters' => array('int'),
        //         'params' => $positionIdJQueryParams
        //     ));

        // if (!$isHr) { //#17380
        //     $profiles = $this->getService('AtProfile')->fetchAll()->getList('profile_id', 'name');
        //     $this->addElement($this->getDefaultSelectElementName(), 'position_name', array(
        //             'Label' => _('Должность'),
        //             'Required' => false,
        //             'validators' => array(
        //                 'int',
        //                 array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
        //             ),
        //             'Filters' => array('StripTags'),
        //             'multiOptions' => $profiles
        //         )
        //     );
        // }

        if ($isHr) {
            $providers = $this->getService('RecruitProvider')->getList('userform');
            $this->addElement($this->getDefaultSelectElementName(), 'provider_id', array(
                    'Label' => _('Провайдер'),
                    'Required' => true,
                    'validators' => array(
                        'int',
                        array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
                    ),
                    'Filters' => array('StripTags'),
                    'multiOptions' => $providers
                )
            );
        }

        $this->addDisplayGroup(array(
            'userlogin',
            'user_id',
//            'userpassword2',
//            'generatepassword',
            'userpassword',
//            'userpasswordrepeat',
            'cancelUrl',
            'tags',
        ),
            'Users1',
            array('legend' => _('Учётная запись'))
        );

        $this->addDisplayGroup(array(
            'lastname',
            'firstname',
            'patronymic',
//            'lastnameLat',
//            'firstnameLat',
            'email',
            'photo',
            'mid_external',
            'gender',
            'birthdate',
            'tel',
            'tel2',
            //'team',
            'additional_info'
        ),
            'Users2',
            array('legend' => _('Персональные данные'))
        );

        if ($isHr) {

            $this->addElement($this->getDefaultFileElementName(), 'resume_file', array(
                    'Label' => _('Резюме (файл)'),
                    'Destination' => $tmpUploadPath,
                    'Required' => false,
                    'Description' => _('Для загрузки рекомендуется использовать файлы формата docx (только они индексируются поисковой машиной). Максимальный размер файла &ndash; 10 Mb'),
                    'Filters' => array('StripTags'),
                    'file_size_limit' => 10485760,
                    'file_types' => '*.docx',
                    'file_upload_limit' => 1,
                    'user_id' => 0
                )
            );

            $resume = $this->getElement('resume_file');
            $resume->addValidator('FilesSize', true, array('max' => '10MB'))
//                     ->addValidator('Extension', true, 'docx')
                ->setMaxFileSize(10485760);


            $this->addElement($this->getDefaultWysiwygElementName(), 'resume_html', array(
                'Label' => _('Резюме (текст)'),
                'Required' => false,
                'class' => 'wide',
            ));

            $this->addDisplayGroup(
                array(
                    'resume_file',
                    'resume_html',
                ),
                'resumeGroup',
                array('legend' => _('Резюме'))
            );
        }

        if ($isHr) {
            $this->addDisplayGroup(array(
                'provider_id',
            ),
                'source',
                array('legend' => _('Источник кандидатов'))
            );
        }

        if ((null != $this->getElement('status')) || (null != $this->getElement('role'))) {
            $this->addDisplayGroup(array(
                'status',
                'role'
            ),
                'Users3',
                array('legend' => _('Назначения'))
            );
        }

        // if (!$isHr) {
        //     //#17380
        //     $this->addDisplayGroup(
        //         array(
        //             'position_id',
        //             'position_name'
        //         ),
        //         'UserOrgstructure',
        //         array('legend' => _('Место работы'))
        //     );
        // }

        if (!empty($codeword) && $isGuest) {
            $this->addElement($this->getDefaultTextElementName(), 'codeword', array(
                    'Label' => _('Кодовое слово'),
                    'Required' => true,
                    'Validators' => array(
                        array('StringLength', 255, 1)
                    ),
                    'Filters' => array('StripTags')
                )
            );

            $this->addDisplayGroup(
                array(
                    'codeword'
                ),
                'Codeword',
                array('legend' => _('Кодовое слово'))
            );
        }

        $additional = $this->getService('Option')->getOption('userFields');
        $additional = json_decode($additional);
        if (is_array($additional) && count($additional)) {
            $addFieldsList = [];
            foreach ($additional as $userField) {
                $addFieldsList[] = 'userField_' . $userField->field_id;

                $this->addElement($this->getDefaultTextElementName(), 'userField_' . $userField->field_id, [
                        'Label' => $userField->field_name,
                        'Required' => $userField->field_required,
                        'Filters' => ['StripTags']
                    ]
                );
            }

            $this->addDisplayGroup(
                $addFieldsList,
                'UserAdditionalFields',
                ['legend' => _('Дополнительная информация')]
            );
        }

        if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN))) {
            $classifierElements = $this->addClassifierElements(
                HM_Classifier_Link_LinkModel::TYPE_PEOPLE,
                $userId
            );
            $this->addClassifierDisplayGroup($classifierElements);
        }

        if ($this->getService('Option')->getOption('regUseCaptcha') && $isGuest) {
            $this->addElement('captcha', 'captcha', array(
                'Required' => true,
                'Label' => _('Код подтверждения:'),
                'captcha' => 'Image',
                'separator' => '',
                'captchaOptions' => array(
                    'captcha' => 'Image',
                    'width' => 145,
                    'height' => 45,
                    'wordLen' => 6,
                    'timeout' => 300,
                    'expiration' => 300,
                    'font'      => APPLICATION_PATH . '/../public/fonts/ptsans.ttf', // Путь к шрифту
                    'imgDir'    => APPLICATION_PATH . '/../public/upload/captcha/', // Путь к изобр.
                    'imgUrl'    => Zend_Registry::get('config')->url->base.'upload/captcha/', // Адрес папки с изображениями
                    'gcFreq'    => 5,
                    'DotNoiseLevel' => HM_Form_Element_Captcha::NOISE_LEVEL,
                    'LineNoiseLevel' => HM_Form_Element_Captcha::NOISE_LEVEL,
                )
            ));
            $captcha = $this->getElement('captcha');
            $captcha->setOrder(5);
            $captcha->setDecorators(array(
                array('RedErrors'),
                array(array('wrapper1' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt')),
                array(array('wrapper2' => 'HtmlTag'), array('tag' => 'div', 'class'  => 'captcha')),
            ));

            $this->addDisplayGroup(
                [
                    'captcha'
                ],
                'CaptchaBlock',
                array('legend' => _('Проверка'))
            );
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        if ($isGuest) {
            $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
                'Label' => _('Отмена'),
                'url' => '/'
            ));
        }

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_USER);
        $this->getService('EventDispatcher')->filter($event, $this);

        parent::init(); // required!
    }


    public function getElementDecorators($alias, $first = 'ViewHelper')
    {
        if ($alias == 'photo') {
            $decorators = parent::getElementDecorators($alias, 'UserImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }


}
