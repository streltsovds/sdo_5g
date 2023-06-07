<?php
class HM_Form_Resource extends HM_Form
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
            'Required' => false,
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
                self::CREATE_TYPE_MATERIAL => _('Создать в редакторе'),
                self::CREATE_TYPE_CARD => _('Только карточка'),
            ],
            'Descriptions' => [
                self::CREATE_TYPE_AUTODETECT => _('В этом случае инфоресурс будет создан автоматически на основе загруженного файла.'),
                self::CREATE_TYPE_MATERIAL => _('В этом случае будет создан пустой инфоресурс и произойдёт перенаправление в редактор инфоресурсов.'),
                self::CREATE_TYPE_CARD => _('В этом случае будет создана только карточка инфоресурса, без содержимого.'),
            ],
            'form' => $this,
            'dependences' => [
                self::CREATE_TYPE_AUTODETECT => array('file'),
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
                'Description' => _('Будет автоматически создан инфоресурс на основе загруженного файла;<br>кроме того, Система распознаёт файлы вида: <ul><li>HTML-сайт (.zip);</ul>'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Filters' => array('StripTags'),
//                'file_size_limit' => 10485760,
                'file_upload_limit' => 1,
                'delete_button' => true,
            )
        );

//        $this->addElement($this->getDefaultTextAreaElementName(), 'code', array(
//            'Label' => _('Код для вставки или URL'),
//            'Description' => _('Сюда можно вставить: <li>специальный HTML-код для вставки с внешнего ресурса (например, с YouTube);<li>ссылку на внешний или внутренний ресурс (если он позволяет открывать себя в iframe); '),
//            'Required' => false,
//            'class' => 'wide',
//        ));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit',[
            'label' => _('Сохранить'),
        ]);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к карточке материала'),
                    'url' => $this->getView()->url([
                        'module' => 'kbase',
                        'controller' => 'resource',
                        'action' => 'edit-card',
                    ]),
                ],
            ]
        ]);

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'kbase',
                'controller' => 'resources',
                'action' => 'index'
            ))
        ));

        parent::init(); // required!
	}
}
