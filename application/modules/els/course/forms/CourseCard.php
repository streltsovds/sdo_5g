<?php
class HM_Form_CourseCard extends HM_Form
{
    public function init()
    {
        $courseId = $this->getParam('course_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement('hidden', 'CID', array('Value' => $courseId));

        $this->addElement($this->getDefaultTextElementName(), 'Title', array(
            'Label' => _('Название'),
            'Description' => _('Название учебного модуля, отображаемое на странице "Все материалы".'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'Status', array(
                'label' => _('Статус ресурса БЗ'),
                'description' => _('Ресурсы со статусом "Ограниченное использование" могут быть использованы в учебных курсах тьюторами; ресурсы со статусом "Не опубликован" и "Архивный" доступны для просмотра и редактирования только менеджерам Базы знаний и разработчикам ресурсов.'),
                'required' => true,
                'filters' => array(array('int')),
                'multiOptions' => HM_Course_CourseModel::getStatuses()
            )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'Description', array('Label' => _('Краткое описание'),
                'Required' => false,
                'Validators' => array(),
                'Filters' =>
                    array('StripTags')
            )
        );

        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Метки'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'course', 'controller' => 'index', 'action' => 'tags')),
            'value' => '',
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'new_window', array(
            'Label' => _('Принудительно открывать модуль в новом окне'),
            'Value' => 0
            //'MultiOptions' => array('has' => 'Курс использует собственную встроенную навигацию'),
        ));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url($courseId ? array('action' => 'index', 'course_id' => $courseId) : array('action' => 'index'))
        ));

        $this->addDisplayGroup(
            array(
                'CID',
                'Title',
                'Description',
                'Status',
                'new_window',
                'tags'
            ),
            'courseGroup',
            array('legend' => '')
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

        $this->addDisplayGroup(
            array(
                'submit',
                'cancel'
            ),
            'submitGroup',
            array('legend' => '')
        );

        parent::init(); // required!
    }

}
