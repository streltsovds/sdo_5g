<?php
class HM_Form_ResourceTypeUrl extends HM_Form_ResourceType
{
	public function init()
	{
        parent::init();

        $this->addElement($this->getDefaultTextElementName(), 'url', array(
            'Label' => _('Ссылка'),
            'placeholder' => 'http://',
            'Description' => _('Сюда можно вставить ссылку (URL) на другую веб-страницу, если это позволено (многие сайты блокируют подобный способ встраивания)'),
            'Required' => false,
            'class' => 'wide',
        ));

        $this->addDisplayGroup(
            ['url'],
            'default',
            array('legend' => '')
        );

        $this->addSubmitBlock();
	}
}
