<?php
class HM_Form_Kpi extends HM_Form {

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
            'kpi_id',
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
        
        $kpiUseClusters = $kpiUseClusters || $this->getService('Option')->getOption('kpiUseClusters');
        $clusters = $this->getService('AtKpiCluster')->fetchAll()->getList('kpi_cluster_id', 'name', _('Выберите кластер'));
        $this->addElement($this->getDefaultSelectElementName(), 'kpi_cluster_id', array(
            'Label' => _('Кластер'),
            'Description' => !$kpiUseClusters ? _('Использование кластеров отключено в настройках методики оценки') : '',
            'disabled' => !$kpiUseClusters ? true : null,
            'validators' => !$kpiUseClusters ? array() : array(
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
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'kpi_cluster_id',
            'kpi_unit',
        ),
            'kpi',
            array('legend' => _('Общие свойства'))
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