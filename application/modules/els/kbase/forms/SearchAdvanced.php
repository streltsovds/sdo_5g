<?php
class HM_Form_SearchAdvanced extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('search-advanced');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement($this->getDefaultTextElementName(), 'content', array(
            'Label' => _('Содержимое файла'),
            'Description' => _('Применимо только к ресурсам с типами "Файл" (поддерживаются типы файлов .docx и .txt)'),
            'class' => 'wide',
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название ресурса'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'filename', array(
            'Label' => _('Название файла'),
            'Description' => _('Применимо только к ресурсам с типом "Файл"'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

//        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_MANAGER, HM_Role_Abstract_RoleModel::ROLE_DEVELOPER))) {
//            $statuses = array('-1' => '') + HM_Resource_ResourceModel::getStatuses();
//            $this->addElement($this->getDefaultSelectElementName(), 'status', array(
//                    'label' => _('Статус ресурса БЗ'),
//                    'required' => false,
//                    'filters' => array(array('int')),
//                    'value' => -1,
//                    'multiOptions' => $statuses
//                )
//            );
//        } else {
            $this->addElement('hidden', 'status',
                array(
                    'required' => false,
                    'value' => -1,
                    'filters' => array(array('int'))
                )
            );
//        }

        /*
        //Поле tags исключено из поисковой машины
        //значение просто сбрасывается, выглядит это очень глючно.
        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
                'Label' => _('Метки'),
                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'tags')),
                'value' => '',
                'Filters' => array()
            )
        ));
        */

        $this->addElement($this->getDefaultDatePickerElementName(), 'created_from', array(
            'Label' => _('Дата публикации, не ранее'),
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
            ),
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'created_to', array(
            'Label' => _('Дата публикации, не позднее'),
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
            ),
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'content',
            ),
            'resourceGroup1',
            array('legend' => _('Поиск по содержимому'))
        );

        $this->addDisplayGroup(
            array(
                'title',
                'description',
                'status',
                'filename',
                'created_from',
                'created_to',
//                'tags',
                'submit'
            ),
            'resourceGroup2',
            array('legend' => _('Поиск по атрибутам'))
        );


        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Найти')));

        $action = array('module' => 'resource', 'controller' => 'search', 'action' => 'advanced-search', 'page' => null);
        foreach ($this->getElements() as $element) {
        	$action[$element->getName()] = null;
        }
        $this->setAction($this->getView()->url($action));

        parent::init(); // required!
	}
}