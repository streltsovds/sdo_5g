<?php
class HM_Form_UploadTemplatesReadOnly extends HM_Form
{

    public function init()
    {
        $courseLaborSafety = Zend_Registry::get('serviceContainer')->getService('Subject')->find(
            HM_Subject_SubjectModel::BUILTIN_COURSE_LABOR_SAFETY
        )->current();

        $courseFireSafety = Zend_Registry::get('serviceContainer')->getService('Subject')->find(
            HM_Subject_SubjectModel::BUILTIN_COURSE_FIRE_SAFETY
        )->current();

        $courseElectroSafety = Zend_Registry::get('serviceContainer')->getService('Subject')->find(
            HM_Subject_SubjectModel::BUILTIN_COURSE_ELECTRO_SAFETY
        )->current();

        $courseIndustrialSafety = Zend_Registry::get('serviceContainer')->getService('Subject')->find(
            HM_Subject_SubjectModel::BUILTIN_COURSE_INDUSTRIAL_SAFETY
        )->current();

        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('UploadTemplatesReadOnly');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false
        ));

        $this->addElement($this->getDefaultFileElementName(), 'labor_safety', array(
            'Label' => 'Курс "'.$courseLaborSafety->name.'"',
            'Description' => 'Загрузка шаблона печатной формы для курса "'.$courseLaborSafety->name.'"',
            'Destination' => realpath(Zend_Registry::get('config')->path->templates->print_forms),
            'file_upload_limit' => 1,
            'file_types' => '*.docx',
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/form_labor_safety_protocol.docx',
            'validators' => array(
                array('Count', false, 3),
                array('Extension', false, 'docx')
            )
        ));

        $this->addElement($this->getDefaultFileElementName(), 'fire_safety', array(
            'Label' => 'Курс "'.$courseFireSafety->name.'"',
            'Description' => 'Загрузка шаблона печатной формы для курса "'.$courseFireSafety->name.'"',
            'Destination' => realpath(Zend_Registry::get('config')->path->templates->print_forms),
            'file_upload_limit' => 1,
            'file_types' => '*.docx',
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/form_fire_safety_protocol.docx',
            'validators' => array(
                array('Count', false, 3),
                array('Extension', false, 'docx')
            )
        ));

        $this->addElement($this->getDefaultFileElementName(), 'electro_safety', array(
            'Label' => 'Курс "'.$courseElectroSafety->name.'"',
            'Description' => 'Загрузка шаблона печатной формы для курса "'.$courseElectroSafety->name.'"',
            'Destination' => realpath(Zend_Registry::get('config')->path->templates->print_forms),
            'file_upload_limit' => 1,
            'file_types' => '*.docx',
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/form_electro_safety_protocol.docx',
            'validators' => array(
                array('Count', false, 3),
                array('Extension', false, 'docx')
            )
        ));

        $this->addElement($this->getDefaultFileElementName(), 'industrial_safety', array(
            'Label' => 'Курс "'.$courseIndustrialSafety->name.'"',
            'Description' => 'Загрузка шаблона печатной формы для курса "'.$courseIndustrialSafety->name.'"',
            'Destination' => realpath(Zend_Registry::get('config')->path->templates->print_forms),
            'file_upload_limit' => 1,
            'file_types' => '*.docx',
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/form_industrial_safety_protocol.docx',
            'validators' => array(
                array('Count', false, 3),
                array('Extension', false, 'docx')
            )
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'labor_safety',
            'fire_safety',
            'electro_safety',
            'industrial_safety',
        ),
            'uploadTemplates',
            array('legend' => _('Шаблоны курсов ОТ'))
        );

        parent::init(); // required!
    }
}