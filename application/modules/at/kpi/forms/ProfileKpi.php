<?php
class HM_Form_ProfileKpi extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('profile_kpi');
        $readOnly = $this->getParam('kpi_id', null);

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );
        
        $this->addElement('hidden',
            'profile_kpi_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );        
        
        $this->addElement('hidden',
            'kpi_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );        
        
        $this->addElement('hidden',
            'profile_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );        
        
        $this->addElement('hidden',
            'is_typical',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int'),
                'Value' => HM_At_Kpi_KpiModel::TYPICAL,
            )
        );        
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => !$readOnly,
            'disabled' => $readOnly,
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
        
        $kpiUseClusters = $this->getService('Option')->getOption('kpiUseClusters') || $this->getService('Option')->getOption('kpiUseClusters', HM_Option_OptionModel::MODIFIER_RECRUIT);
        $clusters = $this->getService('AtKpiCluster')->fetchAll()->getList('kpi_cluster_id', 'name', _('Выберите кластер'));
        $this->addElement($this->getDefaultSelectElementName(), 'kpi_cluster_id', array(
            'Label' => _('Кластер'),
            'Required' => ($kpiUseClusters && !$readOnly),
            'Description' => !$kpiUseClusters ? _('Использование кластеров отключено в настройках методики оценки') : '',
            'disabled' => (!$kpiUseClusters || $readOnly) ? true : null,
            'validators' => (!$kpiUseClusters || $readOnly) ? array() : array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $clusters,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_KPI_CLUSTER
        ));

        if ($kpiId = $this->getRequest()->getParam('kpi_id')) {
            $unit = $this->getService('AtKpiUnit')->getUnit($kpiId);
        }
        $this->addElement($this->getDefaultTagsElementName(), 'kpi_unit', array(
            'Label' => _('Единицы измерения'),
            'Description' => _('После ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'kpi', 'controller' => 'unit', 'action' => 'units')),
            'value' => $unit,
            'Filters' => array(),
            'limit' => 1
        ));
        
        $this->addElement($this->getDefaultTextElementName(), 'weight', array(
            'Label' => _('Вес'),
            'Required' => false,
            'Validators' => array(
                array('float', true, array('locale' => 'en_US')),
                array('greaterThan', false, array('min' => 0)),
                array('lessThan', false, array('max' => 1.00001)),
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        // @todo: добавить валидатор на int || float
        $this->addElement($this->getDefaultTextElementName(), 'value_plan', array(
            'Label' => _('Плановое значение'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'class' => 'wide',
            'Validators' => array(
                array('float', true, array('locale' => 'en_US')),
            ),
        ));        
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'kpi_cluster_id',
            'kpi_unit',
        ),
            'kpi',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(array(
            'weight',
            'value_plan',
        ),
            'kpi2',
            array('legend' => _('Показатели для профиля'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
    
    public function getElementDecorators($alias, $first = 'ViewHelper'){
        if (in_array($alias, array('kpi_cluster_id', 'kpi_unit_id'))) {
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