<?php
class HM_Form_Projects extends HM_Form {

    public function init() {
        $model = new HM_Project_ProjectModel(null);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('projects');

        if (!($projectId = $this->getParam('project_id', 0))) {
            $projectId = $this->getParam('projid', 0);
        }

        /*$front      = Zend_Controller_Front::getInstance();
        $request    = $front->getRequest();
        $action     = $request->getActionName();

        if ( $action == 'edit' ) {
            $this->setAttrib('onSubmit', 'if (confirm("'._('При изменении времени обучения автоматически меняются все даты занятий, которые выходят за дату окончания курса. Продолжить?').'")) return true; return false;');
        }*/

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module'     => 'project',
                        'controller' => 'list',
                        'action'     => 'index',
                        'base'       => $this->getParam('base', 0)
                    ),
                    NULL,
                    TRUE)
            )
        );

        $this->addElement('hidden',
            'projid',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $this->addElement('hidden',
            'base',
            array(
                'Required'   => true,
                'Validators' => array('Int'),
                'Filters'    => array('Int'),
                'Value'      => $this->getParam('base', 0)
            )
        );

        $this->addElement('hidden',
            'base_id',
            array(
                'Required'   => true,
                'Validators' => array('Int'),
                'Filters'    => array('Int'),
                'Value'      => $projectId
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'shortname', array(
            'Label' => _('Краткое название'),
			'Description' => _('Краткое название необходимо для &laquo;хлебных крошек&raquo; и планов занятий'),
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 24)
                )
            ),
            'Filters' => array('StripTags'),
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'code', array(
            'Label' => _('Код'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags')
        )
        );

        $this->addElement('hidden', 'type', array(
            'value' => HM_Project_ProjectModel::TYPE_DEFAULT,
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'class' => 'wide',
        ));

//        $this->addElement($this->getDefaultCheckboxElementName(), 'is_public', array(
//                'Label' => _('Публичный конкурс'),
//                'Description' => _('Данная опция позволяет всем пользователям видеть содержимое конкурса, создавать комментарии'),
//                'Required' => false,
//                'Validators' => array(
//                    array('StringLength',
//                        false,
//                        array('min' => 0, 'max' => 1)
//                    )
//                ),
//                'Filters' => array('StripTags')
//            )
//        );

        $this->addElement('RadioGroup', 'period', array(
            'Label' => '',
            'Value' => HM_Project_ProjectModel::PERIOD_FREE,
            //'Required' => true,
            'MultiOptions' => HM_Project_ProjectModel::getPeriodTypes(),
            'form' => $this,
            'dependences' => array(
                                 HM_Project_ProjectModel::PERIOD_FREE => array(),
                                 HM_Project_ProjectModel::PERIOD_DATES => array('begin', 'end', 'period_restriction_type'),
                             )
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'begin', array(
            'Label' => _('Дата начала'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'end', array(
            'Label' => _('Дата окончания'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                ),
                array(
                    'DateGreaterThanFormValue',
                    false,
                    array('name' => 'begin')
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'period_restriction_type', array(
                'Label' => _('Тип ограничения'),
                'Required' => false,
                'Description' => nl2br(_('При строгом ограничении времени конкурса участники могут входить в конкурс строго в указанный диапазон дат. В остальное время доступ блокируется. 
При нестрогом ограничении даты начала и окончания конкурса носят рекомендательный характер. Участники могут входить в конкурс до даты начала и после даты окончания. 
В случае подтверждения менеджером даты начала и окончания конкурса также носят рекомендательный характер. Факт начала конкурса, включая рассылку уведомлений и предоставление доступа участникам, подтверждается менеджером вручную.')),
                'multiOptions' => HM_Project_ProjectModel::getPeriodRestrictionTypes(),
            )
        );

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
            'Label' => _('Иконка'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'project' => null,
        )
        );

        $photo = $this->getElement('icon');
        $photo->addDecorator('SubjectImage')
               ->addValidator('FilesSize', true, array(
                       'max' => '10MB'
                   )
               )
                ->addValidator('Extension', true, 'jpg,png,gif,jpeg')
                ->setMaxFileSize(10485760);

        $this->addDisplayGroup(array(
            'cancelUrl',
            'projid',
            'name',
            'shortname',
            'code',
            'icon',
            'description',
//            'is_public'
        ),
            'projectProjects1',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(
            array(
                'period',
                'begin',
                'end',
                'period_restriction_type',
            ),
            'projectPeriodGroup',
            array('legend' => _('Ограничение времени конкурса'))
        );


        $this->addElement($this->getDefaultFileElementName(), 'protocol', array(
                'Label' => _('Сводный итоговый протокол'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Required' => false,
                'Filters' => array('StripTags'),
                'file_size_limit' => 104857600,
                'file_types' => '*.xls;*.xlsx;*.doc;*.docx;*.jpg;*.png;*.gif;*.jpeg;*.pdf',
                'file_upload_limit' => 1,
                'project' => null,
            )
        );

        $this->addDisplayGroup(
            array(
                'protocol',
            ),
            'projectResultsGroup',
            array('legend' => _('Результаты конкурса'))
        );

//         $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $projectId);
//         $this->addClassifierDisplayGroup($classifierElements);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        if ($alias == 'icon') {
            $decorators = parent::getElementDecorators($alias, 'SubjectImage');
            array_unshift($decorators, 'ViewHelper');
            return $decorators;
        }
        return parent::getElementDecorators($alias, $first);
    }

}