<?php
class HM_Form_Indicator extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('indicator');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('module' => 'criterion', 'controller' => 'indicator', 'action' => 'index'))
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
        
        $this->addElement('hidden',
            'indicator_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );        
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextElementName(), 'name_questionnaire', array(
                'Label' => _('Название (для анкеты)'),
                'Required' => false,
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );

        $competenceUseIndicatorsDescriptions = $this->getService('Option')->getOption('competenceUseIndicatorsDescriptions') || $this->getService('Option')->getOption('competenceUseIndicatorsDescriptions', HM_Option_OptionModel::MODIFIER_RECRUIT);
        $this->addElement($this->getDefaultTextAreaElementName(), 'description_positive', array(
            'Label' => _('Позитивное проявление'),
            'Description' => !$competenceUseIndicatorsDescriptions ? _('Использование проявлений индикаторов отключено в настройках методики оценки') : _('Соответствует максимальному баллу по шкале оценивания 360 град.'),
            'Disabled' => null, //!$competenceUseIndicatorsDescriptions ? true : null, // теперь всегда разрешен, даже если отключено в настройках
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultTextAreaElementName(), 'description_negative', array(
            'Label' => _('Негативное проявление'),
            'Description' => !$competenceUseIndicatorsDescriptions ? _('Использование проявлений индикаторов отключено в настройках методики оценки') : _('Соответствует минимальному баллу по шкале оценивания 360 град.'),
            'Disabled' => null, //!$competenceUseIndicatorsDescriptions ? true : null, // теперь всегда разрешен, даже если отключено в настройках
            'Required' => false,
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $this->addElement($this->getDefaultCheckboxElementName(), 'doubt', array(
            'Label' => _('Респондент может не оценивать данный индикатор'),
            'Description' => _('Если установлена данная опция, система позволяет не оценивать данный индикатор.'),
        ));

//        $this->addElement($this->getDefaultCheckboxElementName(), 'reverse', array(
//            'Label' => _('Реверсивный индикатор'),
//            'Description' => !$this->getService('Option')->getOption('competenceUseIndicatorsReversive') ? _('Использование реверсивных индикаторов отключено в настройках методики оценки') : '',
//            'Disabled' => null, // !$this->getService('Option')->getOption('competenceUseIndicatorsReversive') ? true : null,// теперь всегда разрешен, даже если отключено в настройках
//            'Required' => false,
//            'Value' => 0,
//        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'name_questionnaire',
            'doubt',
//            'reverse',
        ),
            'indicator',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(array(
            'description_negative',
            'description_positive',
        ),
            'description',
            array('legend' => _('Проявления индикатора'))
        );

        if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceUseIndicatorsScaleValues')) {

            $scaleValueDescriptions = array();
            $scaleValueDescriptionsQuestionnaire = array();
            $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId'); // шкала рег.оценки; в подборе не используется описание уровней
            // @todo: надо отсортировать по 'ScaleValue.value'; в MSSQL не работает 3-й параметр
//            $scale = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', Zend_Registry::get('serviceContainer')->getService('Scale')->quoteInto('self.scale_id = ?', $scaleId))->current();

            $scaleValuesService = Zend_Registry::get('serviceContainer')->getService('ScaleValue');
            $scaleValues = $scaleValuesService->fetchAll($scaleValuesService->quoteInto("scale_id = ?", $scaleId), array("value"));

            if (count($scaleValues)) {
                foreach ($scaleValues as $value) {
                    if ($value->value == HM_Scale_Value_ValueModel::VALUE_NA) continue;
                    $this->addElement($this->getDefaultTextAreaElementName(), $scaleValueDescriptions[] = 'scale_value_' . $value->value_id, array(
                        'Label' => $value->text,
                        'Required' => false,
                    ));
                    $this->addElement($this->getDefaultTextAreaElementName(), $scaleValueDescriptionsQuestionnaire[] = 'scale_value_questionnaire_' . $value->value_id, array(
                        'Label' => $value->text,
                        'Required' => false,
                    ));
                }
            }

            if (count($scaleValueDescriptions)) {
                $this->addDisplayGroup(
                    $scaleValueDescriptions,
                    'descriptions',
                    array('legend' => _('Описание уровней развития (для отчёта)'))
                );
            }

            if (count($scaleValueDescriptionsQuestionnaire)) {
                $this->addDisplayGroup(
                    $scaleValueDescriptionsQuestionnaire,
                    'descriptions_questionnaire',
                    array('legend' => _('Описание уровней развития (для анкеты)'))
                );
            }
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}