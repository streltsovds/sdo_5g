<?php
class HM_Form_ReservePosition extends HM_Form {

    public function init() {
        $reservePositionId = $this->getRequest()->getParam('reserve_position_id');

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName(_('Создать должность КР'));

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Description' => _('Именно это название отображается в "Витрине кадрового резерва" для потенциальных кандидатов.'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide')
        );


        $this->addElement($this->getDefaultWysiwygElementName(), 'formation_source', array(
            'Label' => _('Источник формирования'),
            'Required' => false,
            'Description' => _('Эту информацию видят потенциальные кандидаты при подаче заявки на участие в программе кадрового резерва.'),
            'Validators' => array(
                array('StringLength', 4000, 1)
            ),
            'Filters' => array('HtmlSanitizeRich'),
            //'toolbar' => 'hmToolbarMidi',
            'rows' => 3,
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'requirements', array(
            'Label' => _('Требования к кандидатам'),
            'Description' => _('Эту информацию видят потенциальные кандидаты при подаче заявки на участие в программе кадрового резерва.'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 1)
            ),
            'Filters' => array('HtmlSanitizeRich'),
            //'toolbar' => 'hmToolbarMidi',
            'rows' => 3,
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
                'Label' => _('Комментарий'),
                'Description' => _('Это поле доступно только менеджеру по персоналу.'),
                'Required' => false,
                'Validators' => array(
                    array('StringLength',
                        false,
                        array('min' => 1, 'max' => 255)
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide')
        );

        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array('baseUrl' => '', 'module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 0))

        );

        if ($reservePositionId) {
            $reservePosition = $this->getService('HrReservePosition')->getOne(
                $this->getService('HrReservePosition')->find($reservePositionId)
            );

            if ($collection = $this->getService('Orgstructure')->find($reservePosition->position_id)) {
                $department = $collection->current();
                $positionIdJQueryParams['selected'][] = [
                    "id" => $department->soid,
                    "value" => htmlspecialchars($department->name),
                    "leaf" => !(isset($department->descendants) && count($department->descendants))
                ];
                $positionIdJQueryParams['ownerId'] = $department->owner_soid;
                $positionIdJQueryParams['ignoreDefaultSelectedValue'] = true;
            }
        }

        $this->addElement($this->getDefaultTreeSelectElementName(), 'position_id', array(
            'Label' => _('Должность в оргструктуре'),
            'Required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'params' => $positionIdJQueryParams,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'in_slider', array(
            'Label' => _('Отображать в виджете "Витрина кадрового резерва"'),
            'Value' => 0,
        ));

        $this->addElement($this->getDefaultFileElementName(), 'icon', array(
            'Label' => _('Загрузить иконку из файла'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для использования в вижете "Витрина кадрового резерва" рекомендуемый размер изображения: 220px x 110px. Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'subject' => null,
        ));

        if ($reservePositionId != 0) {
            /** @var HM_Hr_Reserve_Position_PositionModel $position */
            $position = $this->getService('HrReservePosition')->find($reservePositionId)->current();
            $icon = $position->getUserIcon();
        }

        $this->addElement('serverFile', 'server_icon', array(
            'Label' => _('Выбрать иконку из файлов на сервере'),
            'Value' => $icon,
            'preview' => $icon,
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'app_gather_end_date', array(
            'Label' => _('Дата завершения сбора заявок'),
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                    false,
                    array('min' => 10, 'max' => 50, 'messages' => _('Неверный формат даты'))
                ),

            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        ));

        $this->addElement($this->getDefaultTagsElementName(), 'custom_respondents', array(
            'required' => true,
            'Label' => _('Кураторы сессии кадрового резерва'),
            'json_url' => $this->getView()->url(array('baseUrl' => '', 'module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
            'newel' => false,
            'maxitems' => 10
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('custom_respondents', array(
//            'required' => true,
//            'Label' => _('Кураторы сессии кадрового резерва'),
//            'json_url' => $this->getView()->url(array('baseUrl' => '', 'module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
//            'newel' => false,
//            'maxitems' => 10
//        )));

        $this->addElement($this->getDefaultTagsElementName(), 'recruiters', array(
            'required' => true,
            'Label' => _('Ответственные менеджеры по персоналу'),
            'json_url' => $this->getView()->url(array('baseUrl' => 'recruit', 'module' => 'recruiter', 'controller' => 'ajax', 'action' => 'recruiters-list'), null, true),
            'newel' => false,
            'maxitems' => 10
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('recruiters', array(
//            'required' => true,
//            'Label' => _('Ответственные менеджеры по персоналу'),
//            'json_url' => $this->getView()->url(array('baseUrl' => 'recruit', 'module' => 'recruiter', 'controller' => 'ajax', 'action' => 'recruiters-list'), null, true),
//            'newel' => false,
//            'maxitems' => 10
//        )));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'position_id',
            'requirements',
            'formation_source',
            'description',
        ),
            'main',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(array(
            'in_slider',
            'icon',
            'server_icon',
            'app_gather_end_date'
        ),
            'applications',
            array('legend' => _('Заявки на включение в резерв'))
        );

        $this->addDisplayGroup(array(
            'recruiters',
            'custom_respondents',
        ),
            'respondentsGroup',
            array('legend' => _("Ответственные лица"))
        );
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}