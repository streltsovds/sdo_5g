<?php
class HM_Form_TcProvider extends HM_Form
{
    protected $_cancelUrl = '';

    public function setCancelUrl($url)
    {
        $this->_cancelUrl = $url;
    }

    public function getCancelUrl()
    {
        if ($this->_cancelUrl) {
            return $this->_cancelUrl;
        }

        return $this->getView()->url(array(
            'module'     => 'provider',
            'controller' => 'list',
            'action'     => 'index'
        ));
    }

	public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('tcprovider');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getCancelUrl()
        ));
        
        $this->addElement('hidden', 'provider_id', array(            
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'value' => $this->getParam('provider_id', 0)
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    255,
                    1
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое описание'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

        $this->addElement($this->getDefaultTagsElementName(), 'city', array(
            'Label' => _('Город'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => '/tc/provider/ajax/city',
            //'value' => $city,
            'Required' => false,
            'newel' => false,
            'Filters' => array()
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('city', array(
//                'Label' => _('Город'),
//                'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
//                'json_url' => '/tc/provider/ajax/city',
//                //'value' => $city,
//                'Required' => false,
//                'newel' => false,
//                'Filters' => array()
//            )
//        ));

        $this->addElement($this->getDefaultTagsElementName(), 'department_id', array(
            'Label'      => _('Область ответственности (подразделение)'),
            'json_url' => '/orgstructure/ajax/search',
            'Required' => true,
            'newel' => false,
            'Filters' => array(),
            'class'      => 'multiset-trigger wide',
            'maxitems' => 1,
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('department_id', array(
//                'Label'      => _('Область ответственности (подразделение)'),
//                'json_url' => '/orgstructure/ajax/search',
//                'Required' => true,
//                'newel' => false,
//                'Filters' => array(),
//                'class'      => 'multiset-trigger wide',
//                'maxitems' => 1,
//            )
//        ));


        $this->addElement($this->getDefaultTextElementName(), 'inn', array(
                'Label' => _('ИНН'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        32,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'kpp', array(
                'Label' => _('КПП'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        32,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
            )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'address_legal', array(
            'Label' => _('Юридический адрес'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'address_postal', array(
            'Label' => _('Почтовый адрес'),
            'rows' => 5,
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

        $this->addElement($this->getDefaultTextElementName(), 'account', array(
                'Label' => _('Номер счета'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );
        $this->addElement($this->getDefaultTextElementName(), 'account_corr', array(
                'Label' => _('Номер кор. счета'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'bik', array(
                'Label' => _('БИК'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        32,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
            )
        );

        $this->addElement($this->getDefaultMultiSetElementName(), 'contacts',
            array(
                'Label' => '',
                'Required' => false,
                'dependences' => array(
                    new HM_Form_Element_Vue_Text(
                        'fio',
                        array(
                            'Label' => _('ФИО'),
//                            'class' => ' normal multiset-trigger'
                        )
                    ),
                    new HM_Form_Element_Vue_Text(
                        'position',
                        array(
                            'Label' => _('Должность'),
//                            'class' => 'normal'
                        )
                    ),
                    new HM_Form_Element_Vue_Text(
                        'phone',
                        array(
                            'Label' => _('Телефон'),
//                            'class' => 'normal'
                        )
                    ),
                    new HM_Form_Element_Vue_Text(
                        'email',
                        array(
                            'Label' => _('E-mail'),
//                            'class' => 'normal'
                        )
                    ),
                )
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'subscriber_fio', array(
                'Label' => _('ФИО подписанта'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );
        $this->addElement($this->getDefaultTextElementName(), 'subscriber_position', array(
                'Label' => _('Должность подписанта'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );
        $this->addElement($this->getDefaultTextElementName(), 'subscriber_reason', array(
                'Label' => _('Основание для подписанта'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'licence', array(
                'Label' => _('Лицензия'),
                'Required' => false,
                'Validators' => array(array('StringLength', 255, 1)),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );
        $this->addElement($this->getDefaultTextElementName(), 'registration', array(
                'Label' => _('Регистрационный №'),
                'Required' => false,
                'Validators' => array(array('StringLength', 255, 1)),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );
        $this->addElement($this->getDefaultTextElementName(), 'pass_by', array(
                'Label' => _('Пропускная способность в месяц'),
                'Required' => true,
                'Validators' => array('Int', array('StringLength', 255, 1)),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );



        $this->addElement($this->getDefaultWysiwygElementName(), 'information', array(
            'Label' => _('Информация для пользователей'),
            'Required' => false,
            'Validators' => array(
//                array('StringLength',255,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addDisplayGroup(
        	array(
	            'cancelUrl',
	            'provider_id',
        		'name',
        		'description',
                'city',
                'information',
                'licence',
                'registration',
                'department_id',
                'pass_by',
        	),
            'mainProperties',
            array(
            'legend' => _('Общие свойства')
            ));

        $this->addDisplayGroup(
            array(
                'inn',
                'kpp',
                'address_legal',
                'address_postal',
                'account',
                'account_corr',
                'bik',
                'subscriber_fio',
                'subscriber_position',
                'subscriber_reason',
            ),
            'details',
            array(
                'legend' => _('Реквизиты')
            ));
        $this->addDisplayGroup(
            array(
                'contacts'
            ),
            'contact',
            array(
                'legend' => _('Контактные лица')
            ));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init(); // required!
        
    }

    public function __toString()
    {
        $css = Zend_Controller_Front::getInstance()->getModuleDirectory().'/views/css/forms/provider.css';

        $css = '<style type="text/css">'.file_get_contents($css).'</style>';

        return $css.parent::__toString();
    }

}