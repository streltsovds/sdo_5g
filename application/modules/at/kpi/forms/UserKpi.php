<?php
class HM_Form_UserKpi extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('user_kpi');

        $userKpiId = $this->getParam('user_kpi_id', 0);
        $userKpi = $this->getService('AtKpiUser')->getOne($this->getService('AtKpiUser')->find($userKpiId));

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
            'user_id',
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

        if (!$userKpiId) {
            $this->addElement($this->getDefaultTagsElementName(), 'user_id', array(
                'required' => true,
                'Label' => _('Пользователь'),
                'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
                // @todo: $this->getView()->url() не работает
                'json_url' => '/user/ajax/users-list',
                'newel' => false,
                'maxitems' => 1
            ));
//
//            $this->addElement(new HM_Form_Element_FcbkComplete('user_id', array(
//                    'required' => true,
//                    'Label' => _('пользователь'),
//    				'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
//    				// @todo: $this->getView()->url() не работает
//                    'json_url' => '/user/ajax/users-list',
//                    'newel' => false,
//                    'maxitems' => 1
//                )
//            ));
        } else {
            $this->addElement('hidden',
                'user_id',
                array(
                    'Value' => $userKpi->user_id,
                )
            );            
        }
                
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Показатель эффективности'),
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
        
        $cycles = $this->getService('Cycle')->fetchAll(array(
            'end_date > ?' => HM_Date::now()->toString('yyyy-MM-dd'),
            'newcomer_id IS NULL' => ''
            ), 'begin_date')->getList('cycle_id', 'name', _('Выберите период оценки'));
        $cycle = $this->getService('Cycle')->getCurrent();
        $this->addElement($this->getDefaultSelectElementName(), 'cycle_id', array(
            'Label' => _('Период оценки'),
            'required' => true,
            'validators' => array(
                'int',
            ),
            'filters' => array('int'),
            'value' => $cycle ? $cycle->cycle_id : null,
            'multiOptions' => $cycles,
        ));        
        
        $kpiUseClusters = $this->getService('Option')->getOption('kpiUseClusters') || $this->getService('Option')->getOption('kpiUseClusters', HM_Option_OptionModel::MODIFIER_RECRUIT);
        $clusters = $this->getService('AtKpiCluster')->fetchAll()->getList('kpi_cluster_id', 'name', _('Выберите кластер'));
        $this->addElement($this->getDefaultSelectElementName(), 'kpi_cluster_id', array(
            'Label' => _('Кластер'),
            'Required' => $kpiUseClusters,
            'Description' => !$kpiUseClusters ? _('Использование кластеров отключено в настройках методики оценки') : '',
            'disabled' => (!$kpiUseClusters) ? true : null,
            'validators' => (!$kpiUseClusters) ? array() : array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $clusters,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_KPI_CLUSTER
        ));
        
        $units = $this->getService('AtKpiUnit')->fetchAll(array('name != ?' => ''))->getList('kpi_unit_id', 'name', _('Выберите единицы измерения'));
        $this->addElement($this->getDefaultSelectElementName(), 'kpi_unit_id', array(
            'Label' => _('Единицы измерения'),
            'filters' => array('int'),
            'multiOptions' => $units,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_KPI_UNIT
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
        
        $this->addElement($this->getDefaultTextElementName(), 'value_fact', array(
            'Label' => _('Фактическое значение'),
            'Required' => false,
            'Description' => _('Как правило, на момент создания показателя эффективности, его фактическое значение не известно. Оно может быть введено в систему самим пользователем в виджете "Мои показатели эффективности" либо руководителем в процессе заполнения анкеты оценки выполнения задач.'),
            'Filters' => array('StripTags'),
            'class' => 'wide',
            'Validators' => array(
                array('float', true, array('locale' => 'en_US')),
            ),
        ));
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'user_id',
            'name',
            'cycle_id',
            'kpi_cluster_id',
            'kpi_unit_id',
        ),
            'kpi',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(array(
            'weight',
            'value_plan',
            'value_fact',
        ),
            'kpi2',
            array('legend' => _('Показатели для пользователя'))
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
