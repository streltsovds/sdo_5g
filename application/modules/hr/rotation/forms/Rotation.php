<?php
class HM_Form_Rotation extends HM_Form {

    public function init()
    {
        $rotationId = $this->getRequest()->getParam('rotation_id');
        $orgId = $this->getRequest()->getParam('org_id');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index', 'rotation_id'=> null))
            )
        );
        $this->addElement($this->getDefaultTagsElementName(), 'user_id', array(
            'required' => true,
            'Label' => _('Пользователь'),
            'Description' => _('Пользователь, проходящий процедуру ротации. Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
            'json_url' => '/user/ajax/users-list',
            'newel' => false,
            'maxitems' => 1
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('user_id', array(
//                'required' => true,
//                'Label' => _('Пользователь'),
//                'Description' => _('Пользователь, проходящий процедуру ротаци. Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
//                'json_url' => '/user/ajax/users-list',
//                'newel' => false,
//                'maxitems' => 1
//            )
//        ));

        $this->addElement($this->getDefaultDatePickerElementName(), "begin_date", array(
                'Label' => _('Дата начала'),
                'Required' => true,
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

        $this->addElement($this->getDefaultDatePickerElementName(), "end_date", array(
                'Label' => _('Дата завершения'),
                'Required' => true,
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

        $positionIdJQueryParams = array(
            'remoteUrl' => $this->getView()->url(array('baseUrl' => '', 'module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 0))

        );

        if ($rotationId) {
            $rotation = $this->getService('HrRotation')->getOne(
                $this->getService('HrRotation')->find($rotationId)
            );

            if ($collection = $this->getService('Orgstructure')->find($rotation->position_id)) {
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

        if (!$orgId) {
            $this->addElement($this->getDefaultTreeSelectElementName(), 'position_id', array(
                'Label' => _('Должность'),
                'Description' => _('Должность, на которую пользователь перемещается на период ротации.'),
                'Required' => true,
                'validators' => array(
                    'int',
                    array('GreaterThan', false, array(-1))
                ),
                'filters' => array('int'),
                'params' => $positionIdJQueryParams,
            ));
        } else {
            $this->addElement('hidden', 'position_id', array(
                'Value' => $orgId
            ));
        }

        $this->addDisplayGroup(array(
            'user_id',
            'position_id',
            'begin_date',
            'end_date',
            'cancelUrl',
            'submit'
        ),
            "fieldset_rotation",
            array('legend' => 'Общие данные')
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}