<?php
class HM_Form_UploadFile extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('upload');

        $this->addElement('File', 'file', array(
                'Label'      => _('Файл сертификата'),
                'Required'   => false,
                'Filters'    => array('StripTags'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'file_size_limit' => 10485760,
                'file_types' => '*.pdf',
                'file_upload_limit' => 1
            )
        );

        $this->getElement('file')->addValidator('Extension', true, array('pdf', 'jpg', 'png', 'bmp'));

        $this->addDisplayGroup(
            array(
                'file'
            ),
            'uploadFileGroup',
            array('legend' => _('Загрузить сертификат'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}
}