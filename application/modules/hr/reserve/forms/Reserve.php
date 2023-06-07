<?php
class HM_Form_Reserve extends HM_Form {

    public function init()
    {
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index', 'reserve_id'=> null))
            )
        );

        $this->addElement($this->getDefaultTagsElementName(), 'user_id', array(
            'required' => true,
            'Label' => _('Пользователь'),
            'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
            'json_url' => '/user/ajax/users-list',
            'newel' => false,
            'maxitems' => 1
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('user_id', array(
//                'required' => true,
//                'Label' => _('Пользователь'),
//                'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
//                'json_url' => '/user/ajax/users-list',
//                'newel' => false,
//                'maxitems' => 1
//            )
//        ));

        $cycles = array(0 => _('Выберите период')) + $this->getService('Cycle')->fetchAll(array('type = ?' => "reserve"))->getList('cycle_id', 'name');

        $this->addElement($this->getDefaultSelectElementName(), 'cycle_id', array(
            'Label' => _('Период'),
            'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Filters' => array('Int'),
            'Required' => true,
            'MultiOptions' => $cycles
        ));

        $reservePositions = $this->getService('HrReservePosition')->fetchAll(null, 'name')->getList('reserve_position_id', 'name');
        $reservePositions = array(0 => _('Выберите должность КР')) + $reservePositions;
        $this->addElement($this->getDefaultSelectElementName(), 'reserve_position_id', array(
            'Label' => _('Должность КР'),
            'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Filters' => array('Int'),
            'Required' => true,
            'MultiOptions' => $reservePositions
        ));

        $this->addDisplayGroup(array(
            'user_id',
            'reserve_position_id',
            'cycle_id',
            'cancelUrl',
            'submit'
        ),
            "fieldset_reserve",
            array('legend' => 'Общие данные')
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}