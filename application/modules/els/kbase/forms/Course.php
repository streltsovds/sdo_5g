<?php
class HM_Form_Course extends HM_Form
{
    const CREATE_TYPE_AUTODETECT = 'auto';
    const CREATE_TYPE_MATERIAL = 'material';
    const CREATE_TYPE_CARD = 'card';

	public function init()
	{
        $subjectId = $this->getParam('subject_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('RadioGroup', 'create_type', array(
            'Label' => '',
            'MultiOptions' => [
                self::CREATE_TYPE_AUTODETECT => _('Загрузить из файла'),
                self::CREATE_TYPE_CARD => _('Только карточка'),
// здесь будет новый eAuthor.online
//                self::CREATE_TYPE_MATERIAL => _('Создать в конструкторе'),
            ],
            'Descriptions' => [
                self::CREATE_TYPE_AUTODETECT => _('В этом случае учебный модуль будет создан автоматически на основе загруженного пакета.'),
                self::CREATE_TYPE_CARD => _('В этом случае будет создана только карточка учебного модуля, без содержимого.'),
            ],
            'form' => $this,
            'dependences' => [
                self::CREATE_TYPE_AUTODETECT => array(
                    'file',
                ),
            ],
            'dependencies_inline' => true, // @todo
        ));

        $this->addDisplayGroup(
            array(
                'title',
                'create_type',
            ),
            'create-tab',
            array('legend' => '')
        );

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
                'Label' => _('Файл'),
                'Description' => _('Сюда можно загрузить учебные модули в формате: *SCORM, *TinCan;'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => true,
                'Filters' => array('StripTags'),
//                'file_size_limit' => 10485760,
                'file_types' => '*.zip',
                'file_upload_limit' => 1,
                'delete_button' => true,
            )
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit',[
            'label' => _('Сохранить'),
        ]);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к карточке учебного модуля'),
                    'url' => $this->getView()->url([
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'edit-card',
                    ]),
                ],
            ]
        ]);

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'kbase',
                'controller' => 'courses',
            ))
        ));

        parent::init(); // required!
	}
}
