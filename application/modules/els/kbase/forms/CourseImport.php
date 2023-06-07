<?php
class HM_Form_CourseImport extends HM_Form
{
    public function init()
    {
        $description = _('Сюда можно загрузить учебные модули в формате: *SCORM, *TinCan;');
        if ($this->getParam('edition', 0)) {
            $description = _('Учебный модуль будет создан автоматически, на основе загруженного пакета');
        }
        $this->addElement($this->getDefaultFileElementName(), 'file', array(
                'Label' => _('Файл'),
                'Description' => $description,
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => true,
                'Filters' => array('StripTags'),
//                'file_size_limit' => 10485760,
                'file_types' => '*.zip',
                'file_upload_limit' => 1,
                'delete_button' => true,
            )
        );

        $this->addDisplayGroup(
            ['file'],
            'default',
            array('legend' => '')
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к редактированию карточки учебного модуля'),
                    'url' => $this->getView()->url([
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'edit-card',
                        'resource_id' => $this->_course->CID,
                        'idType' => null,
                        'edition' => null
                    ]),
                ],
            ]
        ]);

        if ($this->getView()->course->subject_id) {
            if ($this->getView()->idType) {
                $backUrl = $this->getView()->url([
                    'module' => 'subject',
                    'controller' => 'materials',
                    'action' => 'index',
                    'subject_id' => $this->getView()->course->subject_id,
                    'idType' => null,
                    'course_id' => null
                ]);
            } else {
                $backUrl = $this->getView()->url([
                    'module' => 'subject',
                    'controller' => 'lessons',
                    'action' => 'edit',
                    'subject_id' => $this->getView()->course->subject_id,
                    'idType' => null,
                    'lesson_id' => null,
                    'course_id' => null,
                ]);
            }
        } else {
            $backUrl = $this->getView()->url([
                'module' => 'kbase',
                'controller' => 'courses',
                'action' => 'index',
                'edition' => null,
                'course_id' => null,
                'gridmod' => null
            ]);
        }

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $backUrl,
        ));

        parent::init();
    }
}
