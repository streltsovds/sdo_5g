<?php
class HM_Form_RecruitOptions extends HM_Form
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('AtOptions')
            ->setAttrib('class', 'all-fieldsets-collapsed');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'competenceUseClusters', array(
            'Label' => _('Использовать кластеры компетенций'),
        	'Description' => _('Если установлена данная опция, система позволяет объединять компетенции в кластеры; при этом оценочные анкеты разбиваются на несколько страниц, на одной странице выводятся компетенции, принадлежащие к одному кластеру.'),
            'Value' => 1,
        ));

        $builtInTypes = HM_Scale_ScaleModel::getBuiltInTypes();
        $scales = $this->getService('Scale')->fetchAll(array('scale_id NOT IN (?)' => $builtInTypes, 'mode = ?' => HM_Scale_ScaleModel::MODE_COMPETENCE))->getList('scale_id', 'name', _('Выберите шкалу'));
        $this->addElement($this->getDefaultSelectElementName(), 'competenceScaleId', array(
            'Label' => _('Шкала оценки компетенций/индикаторов'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $scales,
        ));

        $this->addElement('RadioGroup', 'competenceUseIndicators', array(
            'Label' => _('Использование индикаторов'),
            'Description' => _('Если установлена данная опция, система позволяет создавать индикаторы компетенций; при этом оценочные анкеты содержат как компетенции, так и их индикаторы, по которым непосредственно осуществляется оценка.'),
            'MultiOptions' => array(
                0 => 'Не использовать индикаторы компетенций',
                1 => 'Использовать индикаторы компетенций',
            ),
            'separator' => '',
            'form' => $this,
            'dependences' => array(
                0 => array('competenceUseScaleValues'),
                1 => array('competenceUseIndicatorsDescriptions', 'competenceUseIndicatorsReversive', 'competenceUseIndicatorsScaleValues'),
            )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'competenceUseScaleValues', array(
            'Label' => _('Использовать описания уровней развития компетенций'),
        	'Description' => _('Если установлена данная опция, система позволяет задать описание для каждого уровня развития компетенции.'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'competenceUseIndicatorsDescriptions', array(
            'Label' => _('Использовать позитивные и негативные проявления индикаторов'),
        	'Description' => _('Если установлена данная опция, система позволяет по каждому индикатору отдельно указывать его негативные и позитивные проявления; в оценочных анкетах выводятся не только названия индикаторов, но и их проявления.'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'competenceUseIndicatorsReversive', array(
            'Label' => _('Использовать реверсивные индикаторы'),
        	'Description' => _('Если установлена данная опция, система позволяет создавать реверсивные индикаторы; при этом оценочные анкеты в явном виде не указывают, какие из проявлений является негативными, а какое - позитивными.'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'competenceUseIndicatorsScaleValues', array(
            'Label' => _('Использовать описания уровней развития индикаторов'),
            'Description' => _('Если установлена данная опция, система позволяет задать описание для каждого уровня развития индикаторов.'),
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'competenceComment', array(
            'Label' => _('Комментарий к анкете'),
            'Required' => false,
            'class' => 'wide',
        ));


        $this->addDisplayGroup(array(
            'competenceUseIndicators',
            'competenceUseScaleValues',
            'competenceUseIndicatorsDescriptions',
            'competenceUseIndicatorsReversive',
            'competenceUseIndicatorsScaleValues',
            'competenceScaleId',
            'competenceUseClusters',
            'competenceComment',
//            'competenceReportComment',
        ),
            'competence',
            array('legend' => HM_At_Evaluation_Method_CompetenceModel::getMethodName())
        );


        /* KPIs */
        
        $this->addElement('hidden', 'kpiUseClusters', array(
            'Value' => 0,
        ));

        $this->addElement('RadioGroup', 'kpiUseCriteria', array(
            'Label' => _('Оценка способа достижения показателей'),
            'Description' => _('Если установлена данная опция, система позволяет одновременно с оценкой показателей оценить способ их достижения.'),
            'MultiOptions' => array(
                0 => 'Не использовать оценку способов достижения',
                1 => 'Использовать оценку способов достижения',
            ),
            'form' => $this,
            'dependences' => array(
                0 => array(),
                1 => array('kpiScaleId'),
            )
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'kpiScaleId', array(
            'Label' => _('Шкала оценки способов достижения'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $scales,
        ));



        $this->addElement($this->getDefaultWysiwygElementName(), 'kpiComment', array(
            'Label' => _('Комментарий к анкете'),
            'Required' => false,
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'kpiReportComment', array(
            'Label' => _('Комментарий к разделу "Оценка целей" в индивидуальном отчете'),
            'Required' => false,
            'class' => 'wide',
        ));

        $this->addDisplayGroup(array(
            'kpiUseCriteria',
            'kpiScaleId',
            'kpiUseClusters',
            'kpiComment',
            'kpiReportComment',
        ),
            'kpi',
            array('legend' => _('Оценка целей'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_AT_OPTIONS);
        $this->getService('EventDispatcher')->filter($event, $this);
        
        parent::init(); // required!
    }
}