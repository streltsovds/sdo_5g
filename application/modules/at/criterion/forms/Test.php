<?php
class HM_Form_Test extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('criteria');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $this->addElement('hidden',
            'criterion_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
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
        
        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
                'Label' => _('Описание'),
                'Required' => false,
                'Filters' => array('StripTags'),
        ));        
        
        $collection = $this->getService('Quest')->fetchAll(array(
            'status = ?' => HM_Quest_QuestModel::STATUS_RESTRICTED,        
            'type = ?' => HM_Quest_QuestModel::TYPE_TEST,
        	'profile_id IS NULL' => 0        
        ));

        $tests = $collection->getList('quest_id', 'name', _('Выберите тест'));
        $this->addElement($this->getDefaultSelectElementName(), 'quest_id', array(
            'Label' => _('Оценивается при помощи теста'),
            'required' => false,
            'validators' => array(
                'int',
                //array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $tests,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'required', array(
            'Label' => _('Обязательная квалификация'),
            'required' => false,
            'value' => false
        ));

        $this->addElement($this->getDefaultTextElementName(), 'validity', array(
            'Label' => _('Срок действия, в месяцах'),
            'required' => false,
            'Filter' => array('StripTags'),
            'Validators' => array('int'),
            'class' => 'brief2'
        ));
        
        $this->addElement('hidden', 'employee_type', array('value' => HM_At_Criterion_Test_TestModel::EMPLOYEE_TYPE_EMPLOYEE));
//        $this->addElement($this->getDefaultSelectElementName(), 'employee_type', array(
//            'Label' => _('Тип квалификации'),
//            'required' => false,
//            'validators' => array(
//                'int',
//            ),
//            'filters' => array('int'),
//            'value' => HM_At_Criterion_Test_TestModel::EMPLOYEE_TYPE_EMPLOYEE,
//            'multiOptions' => HM_At_Criterion_Test_TestModel::getEmploeeTypes(),
//        ));
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'quest_id',
            'description',
            'required',
            'validity',
            'employee_type'
        ),
            'criteria',
            array('legend' => _('Квалификация'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}