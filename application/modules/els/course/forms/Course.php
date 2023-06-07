<?php
/**
 * Форма для создания и редактирования курсов
 *
 */
class HM_Form_Course extends HM_Form
{
    public $status;

    public function init()
    {

        $modelName = Zend_Registry::get('serviceContainer')->getService('Course')->getMapper()->getModelClass();
        $model = new $modelName(null);

        $front = Zend_Controller_Front::getInstance();
        $req = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('course');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('course/list/' . $req->getParam('status'))
        ));

        $this->addElement('hidden', 'cid', array(

            'Required' => true,
            'Validators' => array(

                'Int'),
            'Filters' => array(

                'Int')));

        $this->addElement($this->getDefaultTextElementName(), 'name', array(

            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(

                array(

                    'StringLength',
                    255,
                    1)),
            'Filters' => array(

                'StripTags')));

        $this->addElement($this->getDefaultSelectElementName(), 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'description' => _('Ресурсы со статусом "Ограниченное использование" могут быть использованы в учебных курсах тьюторами; ресурсы со статусом "Не опубликован" и "Архивный" доступны для просмотра и редактирования только менеджерам Базы знаний и разработчикам ресурсов.'),
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => HM_Course_CourseModel::getStatuses()
                )
            );

        $this->addElement($this->getDefaultTextAreaElementName(), 'describe', array('Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(),
            'Filters' =>
                array('StripTags')
            )
        );

        $providers = array(_('Нет'));
        $collections = Zend_Registry::get('serviceContainer')->getService('Provider')->fetchAll(null, 'title');
        if (count($collections)) {
            $providers = $collections->getList('id', 'title', _('Нет'));
        }

        $this->addElement($this->getDefaultSelectElementName(), 'provider',
            array(
                'Label' => _('Поставщик'),
                'Required' => false,
                'Validators' => array(),
                'Filters' => array('StripTags'),
                'MultiOptions' => $providers
            )
        );


        $this->addElement($this->getDefaultDatePickerElementName(), 'planDate', array(

            'Label' => _('Плановая дата окончания разработки'),
            'Required' => false,
            'Validators' => array(),
            'Filters' => array(),
            'id' => "planDate",
            'value' => date("d.m.Y")));

        $subjects = array();
        $this->addElement($this->getDefaultTextElementName(), 'hours', array(
            'Label'    => _('Продолжительность обучения (в часах)'),
            'Required' => false,
            'jQueryParams' => array(
                'min' => 0,
                'max' => 100
            ),
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )));

         /*$this->addElement($this->getDefaultCheckboxElementName(), 'has_tree', array(
             'Label' => _('Не показывать меню учебного модуля (он имеет собственную встроенную навигацию)'),
             'Value' => 0
             //'MultiOptions' => array('has' => 'Курс использует собственную встроенную навигацию'),
         ));*/

        $this->addElement($this->getDefaultCheckboxElementName(), 'new_window', array(
            'Label' => _('Принудительно открывать модуль в новом окне'),
            'Value' => 0
            //'MultiOptions' => array('has' => 'Курс использует собственную встроенную навигацию'),
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'emulate', array(
            'Label' => _('Эмулировать режим совместимости с версией Internet Explorer'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'MultiOptions' => HM_Course_CourseModel::getEmulateModes()
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'emulate_scorm', array(
            'Label' => _('Эмулировать интерфейс SCORM'),
            'Value' => 0,
            'description' => _('Только для модулей с произвольным форматом'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'extra_navigation', array(
            'Label' => _('Использовать дополнительно навигацию "вперёд/назад" и сервис "закладки"'),
            'Value' => 0
        ));

        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Метки'),
				'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
                'json_url' => $this->getView()->url(array('module' => 'course', 'controller' => 'index', 'action' => 'tags')),
                'value' => '',
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
//                'Label' => _('Метки'),
//				'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
//                'json_url' => $this->getView()->url(array('module' => 'course', 'controller' => 'index', 'action' => 'tags')),
//                'value' => '',
//            )
//        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'cid',
            'name',
            'status',
            'describe',
            'hours',
            /*'has_tree',*/
            'tags',
            'submit_and_redirect'
            ), 'groupCourse1', array(

                'legend' => _('Общие свойства')
            )
        );

        $this->addDisplayGroup(array(
                'new_window',
                'emulate',
                'emulate_scorm',
                'extra_navigation'
            ), 'groupCourse0', array(
                'legend' => _('Представление модуля')
            )
        );

        $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_COURSE, $this->getParam('CID', 0));

        if (is_array($classifierElements) && count($classifierElements) ) {
            $this->addDisplayGroup(
                $classifierElements,
                'groupCourse2',
                array(
                    'legend' => _('Классификация')
                )
            );
        }

        $this->addDisplayGroup(array(
                'provider',
                'planDate',
            ), 'groupCourse3', array(
                'legend' => _('Разработка и поставка')
            )
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к редактированию карточки учебного модуля'),
                    'url' => $this->getView()->url([
                        'module' => 'course',
                        'controller' => 'list',
                        'action' => 'edit-course'
                    ]),
                ],
            ]
        ]);


        parent::init(); // required!
    }


}