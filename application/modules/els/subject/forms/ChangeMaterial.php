<?php
/*
 * 5G
 *
 */
class HM_Form_ChangeMaterial extends HM_Form
{
    const CREATE_TYPE_AUTODETECT = 'auto';
    const CREATE_TYPE_MATERIAL = 'material';
    const CREATE_TYPE_EMPTY = 'empty';

	public function init()
	{
        $subjectId = $this->getParam('subject_id', 0);
        $lessonId = $this->getParam('lesson_id', 0);
        $lessonCollection = $this->getService('Lesson')->find($lessonId);
        if (count($lessonCollection)) {
            $lesson = $lessonCollection->current();
        }

        $tabs = $lesson->typeID == HM_Event_EventModel::TYPE_COURSE ? [] : [
            [
                'title' => _('Создать материал'),
                'description' => _('На этой вкладке можно создать новый материал'),
                'groups' => ['create-tab'],
            ]
        ];

        $tabs = array_merge($tabs, [
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
        ]);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement($this->getDefaultTabsElementName(), 'tabs', [
            'tabs' => $tabs,
            'form' => $this,
        ]);

        $this->addElement('hidden', 'subject_id', array(
            'Required' => false,
        ));

        $this->addElement('hidden', 'statuses', array(
            'value' => 'all', // @todo: или не все?
        ));

        if (count($tabs) > 2) {
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

            $this->addElement('RadioGroup', 'create_type', array(
                'form' => $this,
                'Label' => '',
                'MultiOptions' => [
                    self::CREATE_TYPE_MATERIAL => _('Создать в редакторе'),
                ],
                'Descriptions' => [
                    self::CREATE_TYPE_MATERIAL => _('В этом случае будет создан пустой материал выбранного типа и произойдёт перенаправление в соответствующий редактор.'),
                ],
                'dependences' => [
                    self::CREATE_TYPE_MATERIAL => array('material_type'),
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

            $materialTypeOptions = [
//                'Description' => _('Будет создан пустой материал выбранного типа; в дальнейшем его можно отредактировать через действие "Редактировать материал"'),
                'Required' => false,
                'Label' => _('Тип материала'),
                'multiOptions' => $event->getReturnValue(),
                'class' => 'wide'
            ];
            if ($lesson) {
                $materialTypeOptions['value'] = $lesson->typeID;
            }
            $this->addElement($this->getDefaultSelectElementName(), 'material_type', $materialTypeOptions);
        }

        /****** Выбрать из материалов курса ******/

        $allowedTypes = null;
        if ($lesson) {
            $allowedTypes = [$lesson->typeID];
        }
        $materials = $this->getService('Material')->getSubjectMaterials($subjectId, $allowedTypes);
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
                'url' => $this->getView()->url(array('module' => 'kbase', 'controller' => 'search', 'action' => 'index', 'subject_id' => null, 'types' => $lesson->typeID))
            ));

        $this->addDisplayGroup(
            ['kb_material_id_type'],
            'kbase-tab',
            array('legend' => _(''))
        );
        /**************/


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
