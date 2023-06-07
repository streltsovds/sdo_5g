<?php
class HM_Form_ResourceTypeExtended extends HM_Form_ResourceType
{
	public function init()
	{
        parent::init();

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
                'Label' => _('Файл'),
                'Description' => _('Сюда можно загрузить: *файлы любого формата, допустимого к использованию в web;'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Filters' => array('StripTags'),
//                'file_size_limit' => 10485760,
                'file_upload_limit' => 1,
                'delete_button' => true,
            )
        );

        $this->addDisplayGroup(
            ['file'],
            'default',
            array('legend' => '')
        );

        $this->addSubmitBlock();
	}
}
