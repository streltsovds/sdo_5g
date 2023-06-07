<?php
class HM_Form_Profiles extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('profiles');
        
        if ($profileId = $this->getParam('profile_id', 0)) {
            $profile = Zend_Registry::get('serviceContainer')->getService('AtProfile')->find($profileId)->current();
        } 

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index'))
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

        $categories = $this->getService('AtCategory')->fetchAll(array(), 'name')->getList('category_id', 'name', _('Выберите категорию должности'));
        $this->addElement($this->getDefaultSelectElementName(), 'category_id', array(
            'Label' => _('Категория должности'),
            'Required' => false,
            'filters' => array('int'),
            'multiOptions' => $categories,
//            'disabled' => !empty($profileId) ? true : null,
        ));

//        $profiles = $this->getService('AtProfile')->fetchAll(array(
//            'base_id IS NULL' => null
//        ), 'name')->getList('profile_id', 'name');
////        asort($profiles);
//        $profiles = array(0 => _('Выберите базовый профиль должности')) + $profiles;
//
//        $this->addElement('select', 'base_id', array(
//            'Label' => _('Базовый профиль должности'),
//            'Required' => true,
//            'filters' => array('int'),
//            'multiOptions' => $profiles,
//            'disabled' => !empty($profileId) ? true : null,
//            'Validators' => array(
//                array('GreaterThan',
//                    false,
//                    array('min' => 0)
//                )
//            ),
//        ));

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );
        
        $this->addElement($this->getDefaultTextElementName(), 'shortname', array(
            'Label' => _('Краткое название'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 64)
                )
            ),
            'Filters' => array('StripTags'),
        ));

        $icon = '';

        $profile = $this->getService('AtProfile')->find($profileId);
        if (count($profile)) {
            $icon = $profile->current()->getUserIcon();
        }

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
                'Label' => _('Загрузить иконку из файла'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
//                'Description' => _('Для использования в виджете "Витрина учебных курсов"'),
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
                'file_upload_limit' => 1,
                'subject' => null,
                'crop' => [
                    'ratio' => HM_Subject_SubjectModel::THUMB_WIDTH / HM_Subject_SubjectModel::THUMB_HEIGHT
                ],
                'preview_url' => $icon,
//            'delete_button' => true
            )
        );
        
        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'is_manager', array(
            'Label' => _('Руководящая должность'),
        ));
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'category_id',
//            'base_id',
            'name',
            'shortname',
            'icon',
            'description',
            'is_manager'
        ),
            'profiles',
            array('legend' => _('Профиль должности'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}