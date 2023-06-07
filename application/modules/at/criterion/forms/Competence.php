<?php
class HM_Form_Competence extends HM_Form {

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
        ));

        $competenceUseClusters = $this->getService('Option')->getOption('competenceUseClusters') || $this->getService('Option')->getOption('competenceUseClusters', HM_Option_OptionModel::MODIFIER_RECRUIT); 
        $clusters = $this->getService('AtCriterionCluster')->fetchAll()->getList('cluster_id', 'name', _('Выберите кластер'));
        $this->addElement($this->getDefaultSelectElementName(), 'cluster_id', array(
            'Label' => _('Кластер'),
            'Description' => !$competenceUseClusters ? _('Использование кластеров отключено в настройках методики оценки') : '',
            'Disabled' => !$competenceUseClusters ? true : null,
            'validators' => !$competenceUseClusters ? array() : array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $clusters,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_CRITERION_CLUSTER
        ));


        $this->addElement('hidden', 'type', array('Value' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE));        
        
//        $categories = Zend_Registry::get('serviceContainer')->getService('AtCategory')->fetchAll(null, 'name')->getList('category_id', 'name');
//        $this->addElement($this->getDefaultSelectElementName(), 'category_id', array(
//            'Label' => _('Категория должности'),
//            'Description' => _('Если компетенция привязана к категории должности, возможность её включения в программы оценки будет ограничена только профилями, созданными на основе данной категории'), // сам понял что сказал?
//            'Required' => false,
//            'Validators' => array(
//                'Int'
//            ),
//            'Filters' => array(
//                'Int'
//            ),
//            'multiOptions' => array(-1 => _('Не задано')) + $categories,
//        ));

        $scaleValueDescriptions = array();
        $scaleId = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId'); // шкала рег.оценки; в подборе не используется описание уровней
        // @todo: надо отсортировать по 'ScaleValue.value'; в MSSQL не работает 3-й параметр

//        $scale = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', Zend_Registry::get('serviceContainer')->getService('Scale')->quoteInto('self.scale_id = ?', $scaleId))->current();
        $scaleValueService = Zend_Registry::get('serviceContainer')->getService('ScaleValue');
        $scaleValues = $scaleValueService->fetchAll(
            $scaleValueService->quoteInto("scale_id = ?", $scaleId),
            array("value", "value_id")
        );

        if (count($scaleValues)) {
            foreach ($scaleValues as $value) {
                if ($value->value == HM_Scale_Value_ValueModel::VALUE_NA) continue;
                $this->addElement($this->getDefaultTextAreaElementName(), $scaleValueDescriptions[] = 'scale_value_' . $value->value_id, array(
                    'Label' => $value->text,
                    'Required' => false,
                ));
            }
        }
        

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'description',
            'cluster_id',
            'type',
//            'category_id',
            'doubt'
        ),
            'criteria',
            array('legend' => _('Компетенция'))
        );

        if (count($scaleValueDescriptions)) {
            $this->addDisplayGroup(
                $scaleValueDescriptions,
                'descriptions',
                array('legend' => _('Описание уровней развития'))
            );
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    public function getElementDecorators($alias, $first = 'ViewHelper'){
        if ($alias == 'cluster_id') {
            return array (
                array($first),
                array('RedErrors'),
                array('AddOption'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt')),
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }
}