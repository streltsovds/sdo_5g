<?php
class HM_Form_Certificate extends HM_Form
{
    protected $user_id;
    
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }
 
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        
        if (!$this->user_id) {
            $this->addElement($this->getDefaultTagsElementName(), 'mid', array(
                'required' => true,
                'Label' => _('ФИО'),
                'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
                'json_url' => '/user/ajax/users-list-for-certificate',
                'newel' => false,
                'maxitems' => 1
            ));

//            $this->addElement(
//              new HM_Form_Element_FcbkComplete('mid', array(
//                'required' => true,
//                'Label' => _('ФИО'),
//                'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
//                'json_url' => '/user/ajax/users-list-for-certificate',
//                'newel' => false,
//                'maxitems' => 1
//              )
//            ));
        } else {
            $this->addElement('hidden', 'mid', array(
               'value' => $this->user_id
            ));
        }

        $this->addElement($this->getDefaultTagsElementName(), 'name', array(
            'required' => true,
            'Label' => _('Название'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из названия сертификата'),
            'json_url' => '/orgstructure/certificate/ajaxnamelist',
            'newel' => true,
            'maxitems' => 1
        ));

//        $this->addElement(
//          new HM_Form_Element_FcbkComplete('name', array(
//            'required' => true,
//            'Label' => _('Название'),
//            'Description' => _('Для поиска можно вводить любое сочетание букв из названия сертификата'),
//            'json_url' => '/orgstructure/certificate/ajaxnamelist',
//            'newel' => true,
//            'maxitems' => 1
//          )
//        ));
        $this->addElement($this->getDefaultTagsElementName(), 'organization', array(
            'required' => true,
            'Label' => _('Организация'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из названия организации'),
            'json_url' => '/orgstructure/certificate/ajaxorganizationlist',
            'newel' => true,
            'maxitems' => 1
        ));

//        $this->addElement(
//          new HM_Form_Element_FcbkComplete('organization', array(
//            'required' => true,
//            'Label' => _('Организация'),
//            'Description' => _('Для поиска можно вводить любое сочетание букв из названия организации'),
//            'json_url' => '/orgstructure/certificate/ajaxorganizationlist',
//            'newel' => true,
//            'maxitems' => 1
//          )
//        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'startdate', array(
            'Label' => _('Дата выдачи сертификата'),
            'Required' => true
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'enddate', array(
            'Label' => _('Дата окончания срока действия сертификата'),
            'Required' => true
        ));

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
            'Label' => _('Скан'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Validators' => array(
                array('Count', false, 1)
            ),
            'file_size_limit' => 0,
            'file_upload_limit' => 1,
            'Required' => false
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое описание')
        ));

        $this->addDisplayGroup(
            array(
              'mid',
              'name',
              'organization',
              'startdate',
              'enddate',
              'file',
              'description'
            ),
            'Users1',
            array('legend' => _('Заполните информацию о сертификате'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        
        return parent::init();

    }


}