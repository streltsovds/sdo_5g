<?php
class HM_Form_Reservist extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('vacancies');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $services = array(
            '',
            _('Из числа пользователей Компании'),
            _('Интернет-сервис'),
            _('Другое'),
        );

        $this->addElement($this->getDefaultSelectElementName(), 'source',
            array(
                'Label' => _('Источник'),
                'Required' => false,
                'Filters' => array(
                    'Int'
                ),
                'multiOptions' => $services,
            )
        );



        $this->addElement($this->getDefaultTextElementName(), 'fio', array(
            'Label' => _('Фамилия Имя Отчество'),
            'required' => true,
        ));


        $this->addElement($this->getDefaultFileElementName(), 'resume',
            array(
                'Label' => _('Резюме'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Description' => _('Для загрузки следует использовать документы следующих типов: doc, pdf, docx. Максимальный размер загружаемого файла - 20 мегабайтов.'),
                'file_size_limit' => 20971520,
                'file_types' => '*.doc;*.docx;*.pdf',
                'file_upload_limit' => 1,
            )
        );

/*        $photo = $this->getElement('resume');
        $photo->addValidator('FilesSize', true, array(
                       'max' => '10MB'
                   )
               )
                ->addValidator('Extension', true, 'doc,docx,pdf')
                ->setMaxFileSize(10485760);*/



        $this->addDisplayGroup(array(
            'cancelUrl',
            'fio',
            'source',
            'resume'
        ),
            'vacancies',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'icon') {
            $decorators = parent::getElementDecorators($alias, 'SubjectImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }



}