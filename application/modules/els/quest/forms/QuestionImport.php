<?php
class HM_Form_QuestionImport extends HM_Form
{
	public function init()
	{
        $questId = $this->getRequest()->getParam('quest_id', 0);

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
                'Label' => _('Файл'),
                'Description' => _('Вопросы в тесте будут созданы автоматически на основании загруженного файла'),
                'tooltip' => _('Можно загрузить файлы формата xlsx, txt'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => true,
                'Filters' => array('StripTags'),
//                'file_size_limit' => 10485760,
                'file_upload_limit' => 1,
                'delete_button' => true,
                'file_sample' => [
                    'Пример теста.txt' => Zend_Registry::get('config')->url->base . 'samples/test_questions.txt',
                    'Пример теста.xlsx' => Zend_Registry::get('config')->url->base . 'samples/test_questions.xlsx'
                ],
                'allow_conversion' => false,
            )
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'quest',
                'controller' => 'question',
                'action' => 'list',
                'quest_id' => $questId,
            ))
        ));

        $this->addDisplayGroup(
            [
                'file',
                'submit',
                'cancel',
            ],
            'default',
            array('legend' => '')
        );

        parent::init();
	}
}
