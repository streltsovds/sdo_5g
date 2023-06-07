<?php
class HM_Form_Kpi extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('user_kpi');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );
        
        $this->addElement('hidden',
            'user_kpi_id',
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
                'Value' => HM_At_Kpi_KpiModel::NOT_TYPICAL,
            )
        );       

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Постановка задачи'),
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
        
//        $this->addElement($this->getDefaultSelectElementName(), 'cycle_id', array(
//            'Label' => _('Период оценки'),
//            'disabled' => true,
//            'validators' => array(
//                'int',
//            ),
//            'filters' => array('int'),
//            'multiOptions' => array(_('Сессия адаптации')),
//        ));        
//        
//        $kpiUseClusters = $this->getService('Option')->getOption('kpiUseClusters', HM_Option_OptionModel::MODIFIER_RECRUIT);
//        $clusters = $this->getService('AtKpiCluster')->fetchAll()->getList('kpi_cluster_id', 'name', _('Выберите кластер'));
//        $this->addElement($this->getDefaultSelectElementName(), 'kpi_cluster_id', array(
//            'Label' => _('Кластер'),
//            'Required' => $kpiUseClusters,
//            'Description' => !$kpiUseClusters ? _('Использование кластеров отключено в настройках методики оценки') : '',
//            'disabled' => !$kpiUseClusters ? true : null,
//            'validators' => !$kpiUseClusters ? array() : array(
//                'int',
//                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
//            ),
//            'filters' => array('int'),
//            'multiOptions' => $clusters,
//            'OptionType' => HM_Form_Decorator_AddOption::TYPE_KPI_CLUSTER
//        ));
        
        $units = $this->getService('AtKpiUnit')->fetchAll(array('name != ?' => ''))->getList('kpi_unit_id', 'name', _('Выберите единицы измерения'));
        $this->addElement($this->getDefaultSelectElementName(), 'kpi_unit_id', array(
            'Label' => _('Единицы измерения'),
            'filters' => array('int'),
            'multiOptions' => $units,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_KPI_UNIT
        ));
        
        $this->addElement('RadioGroup', 'value_type',
            array(
                'Label' => _(''),
                'Required' => false,
                'multiOptions' => HM_At_Kpi_User_UserModel::getValueTypes(),
                'Filters' => array('StripTags'),
                'form' => $this,
                'dependences' => array(
                    HM_At_Kpi_User_UserModel::TYPE_QUALITATIVE  => array(),
                    HM_At_Kpi_User_UserModel::TYPE_QUANTITATIVE => array('value_plan','kpi_unit_id')
                )
            )
        );
        
//        $this->addElement($this->getDefaultTextElementName(), 'weight', array(
//            'Label' => _('Вес'),
//            'Required' => true,
//            'Validators' => array(
//                array('float', true, array('locale' => 'en_US')),
//                array('greaterThan', false, array('min' => 0)),
//                array('lessThan', false, array('max' => 1.00001)),
//            ),
//            'Filters' => array('StripTags'),
//            'class' => 'wide'
//        )
//        );

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
        
        $this->addElement($this->getDefaultDatePickerElementName(), 'begin_date', array(
            'Label' => _('Начало'),
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
        ));
        
        $this->addElement($this->getDefaultDatePickerElementName(), 'end_date', array(
            'Label' => _('Плановое завершение'),
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
        ));
        
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'begin_date',
            'end_date',
        ),
            'main',
            array('legend' => _('Общие свойства'))
        );
        
        $this->addDisplayGroup(array(
            'value_type',
            'value_plan',
            'kpi_unit_id',
        ),
            'kpi',
            array('legend' => _('Тип задачи'))
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