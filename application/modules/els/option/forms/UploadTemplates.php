<?php
class HM_Form_UploadTemplates extends HM_Form
{

    public function init()
    {
        $this->addElement($this->getDefaultFileElementName(), 'study_journal', array(
            'Label' => _('Журнал посещаемости и успеваемости по курсу'),
            'Description' => _('Загрузка шаблона печатной формы'),
            'Destination' => realpath(Zend_Registry::get('config')->path->templates->print_forms),
            'file_upload_limit' => 1,
            'file_types' => '*.docx',
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/form_study_journal.docx',
            'validators' => array(
                array('Count', false, 3),
                array('Extension', false, 'docx')
            )
        ));

        $this->addElement($this->getDefaultFileElementName(), 'study_protocol', array(
            'Label' => _('Итоговый протокол обучения по курсу'),
            'Description' => _('Загрузка шаблона печатной формы'),
            'Destination' => realpath(Zend_Registry::get('config')->path->templates->print_forms),
            'file_upload_limit' => 1,
            'file_types' => '*.docx',
            'file_sample' => Zend_Registry::get('config')->url->base . 'samples/form_study_protocol.docx',
            'validators' => array(
                array('Count', false, 3),
                array('Extension', false, 'docx')
            )
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'study_journal',
            'study_protocol',
        ),
            'uploadTemplates',
            array('legend' => _('Шаблоны документов'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        parent::init(); // required!
    }
}