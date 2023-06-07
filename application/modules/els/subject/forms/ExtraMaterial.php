<?php
class HM_Form_ExtraMaterial extends HM_Form
{
    const CREATE_TYPE_AUTODETECT = 'auto';
    const CREATE_TYPE_MATERIAL = 'material';

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
            ],
            'Descriptions' => [
                self::CREATE_TYPE_AUTODETECT => _('В этом случае материал будет создан автоматически на основании загруженного файла.'),
                self::CREATE_TYPE_MATERIAL => _('В этом случае будет создан пустой материал выбранного типа и произойдёт перенаправление в соответствующий редактор.'),
            ],
            'form' => $this,
            'dependences' => [
                self::CREATE_TYPE_AUTODETECT => array('file'),
                self::CREATE_TYPE_MATERIAL => array(),
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
                'Description' => _('Будет автоматически создан дополнительный материал на основе загруженного файла;<br>кроме того, Система распознаёт файлы вида: <ul><li>HTML-сайт (.zip);</ul>'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => true,
                'Filters' => array('StripTags'),
                'file_upload_limit' => 1,
                'delete_button' => true,
            )
        );

        /****** Выбрать из материалов курса ******/

        $materials = $this->getService('Material')->getSubjectMaterials($subjectId, [HM_Event_EventModel::TYPE_RESOURCE]);
        $this->addElement($this->getDefaultMaterialListElementName(), 'subject_material_id_type', array(
            'Description' => _('Занятие будет создано на основе выбранного материала курса.'),
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

        $multiOptions = array_intersect_key(HM_Event_EventModel::getTypes(), HM_Kbase_KbaseModel::getKbaseAndEventTypesMap());
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getControllerName() == 'extra') {
            $multiOptions = [HM_Kbase_KbaseModel::TYPE_RESOURCE => _('Инфоресурс')];
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
                // @todo: добавить другие типы: УМ, тесты и т.п.
                'type' => [
                    'label' => _('Тип материала'),
                    // Ключ из Kbase, описание из Event
                    'multiOptions' => $multiOptions,
                    'value' => HM_Kbase_KbaseModel::TYPE_RESOURCE
                ],
                'url' => $this->getView()->url(array(
                    'module' => 'kbase',
                    'controller' => 'search',
                    'action' => 'index',
                    'subject_id' => null,
                    'types' => HM_Kbase_KbaseModel::TYPE_RESOURCE
                ))
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

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

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
