<?php
class HM_Form_Requirements extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('reqs');

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
        
        /*$this->addElement($this->getDefaultWysiwygElementName(), 'requirements', array(
            'Label' => _('Формальные требования'),
            'Required' => true,
            //'toolbar' => 'hmToolbarMidi',
            'fmAllow' => true
        )
        );*/

        $this->addElement(
            $this->getDefaultTextElementName(),
            'age_min',
            array(
                'Label'      => _('Возраст, не моложе'),
                'Required'   => false,
                'Validators' => array('Int'),
                'Filters'    => array('Int')
            )
        );

        $this->addElement(
            $this->getDefaultTextElementName(),
            'age_max',
            array(
                'Label'      => _('Возраст, не старше'),
                'Required'   => false,
                'Validators' => array('Int'),
                'Filters'    => array('Int')
            )
        );

        $this->addElement(
            $this->getDefaultRadioElementName(),
            'gender',
            array(
                'Label'        => _('Пол'),
                'Required'     => false,
                'separator'     => '',
                'Validators'   => array(array('InArray', null, array('haystack' => array(0,1,2)))),
                'MultiOptions' => array(_('Не имеет значения')) + HM_At_Profile_ProfileModel::getGenderVariants()
            )
        );

        $this->addElement(
            $this->getDefaultSelectElementName(),
            'education',
            array(
                'Label'    => _('Основное образование'),
                'Required' => false,
                'MultiOptions' => HM_At_Profile_ProfileModel::getMainEducationVariants(),
            )
        );

        $this->addElement('hidden',
            'specialities',
            array(
                'Required' => false,
                'value' => array(),
            )
        );

        $this->addElement('hidden',
            'universities',
            array(
                'Required' => false,
                'value' => array(),
            )
        );

//        $this->addElement(
//            'UiMultiSelect',
//            'specialities',
//            array(
//                'Label' => 'Специальности',
//                'Required' => false,
//                'Filters' => array(
//                    'Int'
//                ),
//                'multiOptions' => array(),
//                'value' => array(),
//                'class' => 'multiselect'
//            ));
//
//        $this->addElement(
//            'UiMultiSelect',
//            'universities',
//            array(
//                'Label' => 'ВУЗы',
//                'Required' => false,
//                'Filters' => array(
//                    'Int'
//                ),
//                'multiOptions' => array(),
//                'value' => array(),
//                'class' => 'multiselect'
//            ));

        $this->addElement(
            $this->getDefaultTextAreaElementName(),
            'additional_education',
            array(
                'Label'    => _('Дополнительное образование, сертификаты, лицензии'),
                'Required' => false,
            )
        );

        $this->addElement(
            $this->getDefaultSelectElementName(),
            'academic_degree',
            array(
                'Label'    => _('Наличие ученой степени/звания'),
                'Required' => false,
                'MultiOptions' => HM_At_Profile_ProfileModel::getAcademicDegreeVariants()
            )
        );

        $this->addElement(
            $this->getDefaultTextElementName(),
            'experience',
            array(
                'Label'      => _('Опыт работы в данной позиции'),
                'Required'   => false
            )
        );

        $this->addElement(
            $this->getDefaultTextAreaElementName(),
            'comments',
            array(
                'Label'      => _('Другое'),
                'Required'   => false
            )
        );
        
        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'age_min',
                'age_max',
                'gender',
                'education',
                'specialities',
                'universities',
                'additional_education',
                'academic_degree',
                'experience',
                'comments'                        
        ),
            'personal'
//            array('legend' => _('Формальные требования'))
        );

        // #24559
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
        $this->addElement($this->getDefaultSelectElementName(), 'trips', array(
                'Label'    => _('Необходимость командировок'),
                'Required' => false,
                'MultiOptions' => HM_At_Profile_ProfileModel::getTripsVariants(),
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'trips_duration',
            array(
                'Label'      => _('Длительность командировок, дн.'),
                'Required'   => false,
                'class'      => 'brief'
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'mobility', array(
                'Label'    => _('Статус мобильности'),
                'Required' => false,
                'MultiOptions' => HM_At_Profile_ProfileModel::getMobilityVariants(),
            )
        );        

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'age_min',
                'age_max',
                'gender',
                'education',
                'specialities',
                'universities',
                'additional_education',
                'academic_degree',
                'experience',
                'comments'                        
        ),
            'personal'
//            array('legend' => _('Формальные требования'))
        );

        $this->addDisplayGroup(
            array(
                'trips',
                'trips_duration',
                'mobility',
        ),
            'misc',
            array('legend' => _('Дополнительные требования'))
        );
        }
        // end of #24559
        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        }

        parent::init(); // required!
    }
}