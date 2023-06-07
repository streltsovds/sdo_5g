<?php
class HM_Form_Personal extends HM_Form {

    public function init()
    {
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
        
        $quests = array(0 => '');
        if (count($collection = $this->getService('Quest')->fetchAll(array(
            'type = ?' => HM_Quest_QuestModel::TYPE_PSYCHO,
            'status = ?' => HM_Quest_QuestModel::STATUS_RESTRICTED
        ), 'name'))) {
            $quests = $collection->getList('quest_id', 'name', _('Выберите психологический опрос'));
        }
        $this->addElement($this->getDefaultSelectElementName(), 'quest_id', array(
            'Label' => _('Оценивается при помощи'),
            'required' => false,
            'validators' => array(
                'int',
                //array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $quests,
        ));              

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'quest_id',
            'description',
        ),
            'criteria',
            array('legend' => _('Личностная характеристика'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}