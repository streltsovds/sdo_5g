<?php
/*
 * 5G
 *
 */
class HM_Form_LessonMaterial extends HM_Form
{
    const CREATE_TYPE_AUTODETECT = 'auto';
    const CREATE_TYPE_MATERIAL = 'material';
    const CREATE_TYPE_EMPTY = 'empty';

	public function init()
	{
        $subjectId = $this->getParam('subject_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement($this->getDefaultTabsElementName(), 'tabs', [
            'tabs' => [
                [
                    'title' => _('Создать материал'),
                    'description' => _('На этой вкладке можно создать новый материал'),
                    'groups' => ['create-tab'],
                ],
                [
                    'title' => _('Выбрать из материалов курса'),
                    'description' => _('На этой вкладке можно выбрать материал, ранее созданный в данном курсе (полный список на странице "Исходные материалы курса")'),
                    'groups' => ['materials-tab'],
                ],
                [
                    'title' => _('Выбрать из Базы знаний'),
                    'description' => _('На этой вкладке можно создать выбрать материал, ранее созданный в Базе знаний (полный список на странице "База знаний")'),
                    'groups' => ['kbase-tab'],
                ]
            ],
            'form' => $this,
        ]);

        $this->addElement('hidden', 'subject_id', array(
            'Required' => false,
        ));

        $this->addElement('hidden', 'statuses', array(
            'value' => 'all', // @todo: или не все?
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Description' => _('Название будет присвоено и создаванному материалу, и занятию в плане'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'has_proctoring', array(
            'Label' => _('Режим прокторинга'),
            'Description' => _('Режим прокторинга позволяет визуально контролировать процесс прохождения занятия слушателем'),
            'Required' => false,
            'Filters' => array('Int'),
        ));

        $this->addElement('RadioGroup', 'create_type', array(
            'form' => $this,
            'Label' => '',
            'MultiOptions' => [
                self::CREATE_TYPE_AUTODETECT => _('Загрузить из файла'),
                self::CREATE_TYPE_MATERIAL => _('Создать в редакторе'),
                self::CREATE_TYPE_EMPTY => _('Без материала'),
            ],
            'Descriptions' => [
                self::CREATE_TYPE_AUTODETECT => _('В этом случае материал будет создан автоматически на основании загруженного файла.'),
                self::CREATE_TYPE_MATERIAL => _('В этом случае будет создан пустой материал выбранного типа и произойдёт перенаправление в соответствующий редактор.'),
                self::CREATE_TYPE_EMPTY => _('В этом случае занятие будет создано без материала.'),
            ],
            'dependences' => [
                self::CREATE_TYPE_AUTODETECT => array('file'),
                self::CREATE_TYPE_MATERIAL => array('material_type'),
                self::CREATE_TYPE_EMPTY => array(),
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
                'Description' => _('При импорте Теста в формате .txt и .xlsx отключите галочку "Сконвертировать в PDF", иначе будет создан Инфоресурс. Примеры:'),
                'file_sample' => [
                    //'пример_HTML.zip;' => Zend_Registry::get('config')->url->base . 'samples/test_questions.txt',
                    //'пример_SCORM.zip' => Zend_Registry::get('config')->url->base . 'samples/test_questions.xlsx',
                    'пример_тест.xlsx' => Zend_Registry::get('config')->url->base . 'samples/test_questions.xlsx',
                    'пример_тест.txt' => Zend_Registry::get('config')->url->base . 'samples/test_questions.txt'
                ],
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Filters' => array('StripTags'),
//                'file_size_limit' => 10485760,
                'file_upload_limit' => 1,
                'delete_button' => true,
//                'allow_conversion' => false,
            )
        );

//        $this->addElement($this->getDefaultTextAreaElementName(), 'code', array(
//            'Label' => _('Код для вставки или URL'),
//            'Description' => _('Сюда можно вставить: <li>специальный HTML-код для вставки с внешнего ресурса (например, с YouTube);<li>ссылку на внешний или внутренний ресурс (если он позволяет открывать себя в iframe); '),
//            'Required' => false,
//            'class' => 'wide',
//        ));

        $materials = HM_Material_MaterialModel::getMaterialTypes();
        // у нас нет конструктора УМ
        unset($materials[HM_Event_EventModel::TYPE_COURSE]);

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_LESSON_TYPES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $materials);

        $this->addElement($this->getDefaultSelectElementName(), 'material_type', array(
//            'Description' => _('Будет создан пустой материал выбранного типа; в дальнейшем его можно отредактировать через действие "Редактировать материал"'),
            'Required' => true,
            'Label' => _('Тип материала'),
            'multiOptions' => $event->getReturnValue(),
            'class' => 'wide',
        ));

        /****** Выбрать из материалов курса ******/

        $materials = $this->getService('Material')->getSubjectMaterials($subjectId);
        $this->addElement($this->getDefaultMaterialListElementName(), 'subject_material_id_type', array(
            'Required' => false,
            'multiOptions' => $materials,
            'class' => 'wide',
        ));

        $this->addDisplayGroup(
            ['subject_material_id_type'],
            'materials-tab',
            array('legend' => _(''))
        );

        /****** Выбрать из БЗ ******/

        $classifiersGroups = $this->getService('Classifier')->getKnowledgeBaseClassifiers();
        $classifiersResults = [];

        if (is_array($classifiersGroups) && count($classifiersGroups)) {
            foreach ($classifiersGroups as $classifierGroupName => $classifiers) {

                $classifiersResults[$classifierGroupName]['title'] = $classifiers['title'];
                $resultItemsBag = [];

                foreach($classifiers['items'] as $classifierKey => $classifier) {
                    $resultItemsBag[$classifier->classifier_id] = $classifier->name;
                }

                $classifiersResults[$classifierGroupName]['items'] = $resultItemsBag;
            }
        }


        $this->addElement($this->searchMaterialElementName(), 'kb_material_id_type',
            array(
                'search_field' => [
                    'label' => _('Поиск по названию'),
                    'Required' => false,
                ],
                'classifiers' => [
                    'Required' => false,
                    'Label' => '',
                    'MultiOptions' => $classifiersResults,
                ],
                'type' => [
                    'label' => _('Тип материала'),
                    'multiOptions' => array(
                        HM_Kbase_KbaseModel::TYPE_RESOURCE => _('Информационный ресурс'),
                        HM_Kbase_KbaseModel::TYPE_COURSE => _('Учебный модуль'),
                        HM_Kbase_KbaseModel::TYPE_TEST => _('Тест'),
                        HM_Kbase_KbaseModel::TYPE_POLL => _('Опрос'),
                        HM_Kbase_KbaseModel::TYPE_TASK => _('Задание'),
                    ),
                    'value' => HM_Kbase_KbaseModel::TYPE_RESOURCE
                ],
                'url' => $this->getView()->url(array('module' => 'kbase', 'controller' => 'search', 'action' => 'index', 'subject_id' => null))
            ));

        $this->addDisplayGroup(
            ['kb_material_id_type'],
            'kbase-tab',
            array('legend' => _(''))
        );
        /**************/

        $this->addDisplayGroup(
            array(
                'has_proctoring'
            ),
            'proctoring',
            array('legend' => '')
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit',[
            'label' => _('Сохранить'),
        ]);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к настройкам занятия'),
                    'url' => $this->getView()->url([
                        'module' => 'subject',
                        'controller' => 'lesson',
                        'action' => 'edit',
                        'subject_id' => $subjectId,
                    ]),
                ],
                [
                    'label' => _('к назначению участников'),
                    'url' => $this->getView()->url([
                        'module' => 'subject',
                        'controller' => 'lesson',
                        'action' => 'edit-assign',
                        'subject_id' => $subjectId,
                    ]),
                ],
            ]
        ]);

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'edit',
                'subject_id' => $subjectId,
            ))
        ));

        parent::init(); // required!
	}

}
