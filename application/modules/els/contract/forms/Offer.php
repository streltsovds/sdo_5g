<?php

class HM_Form_Offer extends HM_Form
{

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('Offer');

        $this->addElement('hidden', 'cancelUrl', [
            'Required' => false,
            'value' => $this->getView()->url(['module' => 'default', 'controller' => 'index', 'action' => 'index'])
        ]);

//        $this->addElement('RadioGroup', 'regAllow', array(
//            'Label' => _('Свободная регистрация'),
//            'Description' => _('Если установлена данная опция, система позволяет новым пользователям самостоятельно регистрироваться в системе; возможность доступа непосредственно к учебным материалам определяется настройками этих материалов.'),
//            'MultiOptions' => array(
//                0 => 'Запретить регистрацию без подачи заявки на курс',
//                1 => 'Разрешить свободную регистрацию',
//            ),
//            'form' => $this,
//            'dependences' => array(
//                0 => array(),
//                1 => array('regRequireAgreement', 'regUseCaptcha', 'regValidateEmail', 'regAutoBlock'),
//            )
//        ));    

        $this->addElement($this->getDefaultCheckboxElementName(), 'loginStart', [
            'Label' => _('Сделать страницу авторизации стартовой'),
            'Description' => _('Если установлена данная опция, при входе будет открываться страница авторизации (без виджетов, только авторизация).'),
        ]);

        $this->addElement($this->getDefaultCheckboxElementName(), 'regDeny', [
            'Label' => _('Запретить регистрацию без подачи заявки на курс'),
            'Description' => _('Если установлена данная опция, система не позволяет новым пользователям самостоятельно регистрироваться в системе; возможность доступа непосредственно к учебным материалам определяется настройками этих материалов.'),
        ]);

        $this->addElement($this->getDefaultCheckboxElementName(), 'regRequireAgreement', [
            'Label' => _('Требовать согласие на обработку персональных данных'),
            'Description' => _('Если установлена данная опция, пользователю в процессе регистрации будет предложено ознакомиться с условиями и подтвердить согласие на хранение и обработку его персональных данных.'),
        ]);

        $this->addElement($this->getDefaultCheckboxElementName(), 'regUseCaptcha', [
            'Label' => _('Подтверждать ручной ввод данных (CAPTCHA)'),
            'Description' => _('Если установлена данная опция, система потребует подтвердить ручной ввод данных с помощью механизма CAPTCHA.'),
        ]);

        $this->addElement($this->getDefaultCheckboxElementName(), 'regValidateEmail', [
            'Label' => _("Блокировать нового пользователя до подтверждения e-mail"),
            'Description' => _("Если установлена данная опция, система автоматически заблокирует вновь созданную учетную запись, до подтверждения email'а, введенного при регистрации."),
        ]);

        $this->addElement($this->getDefaultCheckboxElementName(), 'regAutoBlock', [
            'Label' => _('Блокировать нового пользователя до проверки администрацией'),
            'Description' => _('Если установлена данная опция, система автоматически заблокирует вновь созданную учетную запись, с возможностью последующего ручного разблокирования администратором.'),
        ]);

        $this->addElement($this->getDefaultTextElementName(), 'codeword', [
            'Label' => _('Кодовое слово'),
            'Description' => _('Для самостоятельной регистрации в системе пользователю будет необходимо ввести это слово'),
        ]);


        $hidden = new Zend_Form_Element_Hidden('field_id');
        $text = new HM_Form_Element_Vue_Text('field_name', [
            'Label' => _('Название поля')
        ]);
        $checkbox = new HM_Form_Element_Vue_Checkbox('field_required', [
            'Label' => _('Обязательное')
        ]);

        $this->addElement($this->getDefaultMultiSetElementName(), 'userFields', [
            'Required' => true,
            'dependences' => [$hidden, $text, $checkbox]
        ]);


        $this->addElement($this->getDefaultWysiwygElementName(), 'contractOfferText', [
            'Label' => _('Публичная оферта на оказание образовательных услуг'),
            'Required' => false,
            'Validators' => [
                [
                    'validator' => 'StringLength',
                    'options' => ['min' => 3]
                ]],
            'Filters' => ['HtmlSanitizeRich'],
            'connectorUrl' => $this->getView()->url([
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder',
                'subject' => $this->getView()->subjectName,
                'subject_id' => $this->getView()->subjectId
            ]),
            //'toolbar' => 'hmToolbarMidi',
            'fmAllow' => true,
        ]);

        $this->addElement($this->getDefaultWysiwygElementName(), 'contractPersonalDataText', [
            'Label' => _('Согласие на обработку персональных данных'),
            'Required' => false,
            'Validators' => [
                [
                    'validator' => 'StringLength',
                    'options' => ['min' => 3]
                ]],
            'Filters' => ['HtmlSanitizeRich'],
            'connectorUrl' => $this->getView()->url([
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder',
                'subject' => $this->getView()->subjectName,
                'subject_id' => $this->getView()->subjectId
            ]),
            //'toolbar' => 'hmToolbarMidi',
            'fmAllow' => true,
        ]);

        $this->addDisplayGroup(
            [
                'regDeny',
//                    'regAllow', 
                'loginStart',
                'regRequireAgreement',
                'regUseCaptcha',
                'regValidateEmail',
                'regAutoBlock',
                'codeword'
            ],
            'Allow',
            [
                'legend' => _('Регистрационные требования')
            ]
        );

        $this->addDisplayGroup([
            'userFields',
            'userFields2',
        ],
            'key',
            ['legend' => _('Дополнительные поля в форме регистрации')]
        );

        $this->addDisplayGroup(
            [
                'contractOfferText',
                'contractPersonalDataText',
            ],
            'Requirements',
            [
                'legend' => _('Информационные страницы')
            ]
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', [
                'Label' => _('Сохранить')]
        );

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_CONTRACT);
        $this->getService('EventDispatcher')->filter($event, $this);


        parent::init(); // required!
    }

    /* public function getElementDecorators($alias, $first = 'ViewHelper'){
         if(in_array($alias, array('checkPasswordDifficult'))){
             return array ( // default decorator
                 array($first),
                 array('RedErrors'),
                 array('Description', array('tag' => 'p', 'class' => 'description')),
                 array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                 array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
             );
         }else{
             return parent::getElementDecorators($alias, $first);
         }


     }*/
}