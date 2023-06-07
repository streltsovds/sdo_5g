<?php
class HM_Form_AtOptions extends HM_Form
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

//        $this->addElement('RadioGroup', 'competenceUseRandom', array(
//            'Label' => _('Использование случайной выборки экспертов'),
//            'Description' => _('Если установлена данная опция, система позволяет выбирать определенное число респондентов среди коллег и подчиненных случайным образом.'),
//            'MultiOptions' => array(
//                0 => 'Не использовать случайную выборку',
//                1 => 'Использовать случайную выборку',
//            ),
//            'separator' => '',
//            'form' => $this,
//            'dependences' => array(
//                0 => array(),
//                1 => array(
//                    'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
//                    'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
//                ),
//            )
//        ));

        $this->addElement($this->getDefaultTextElementName(), 'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN, array(
            'Label' => _('Количество случайных респондентов из числа подчиненных'),
            'Description' => _('Если установлена данная опция, система позволяет выбирать определенное число респондентов среди подчиненных случайным образом. Если значение пустое, случайный режим отключен'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS, array(
            'Label' => _('Количество случайных респондентов из числа коллег'),
            'Description' => _('Если установлена данная опция, система позволяет выбирать определенное число респондентов среди коллег случайным образом. Если значение пустое, случайный режим отключен'),
        ));

        

        $this->addElement($this->getDefaultWysiwygElementName(), 'sessionComment', array(
            'Label' => _('Комментарий к оценочной сессии (по умолчанию)'),
            'Required' => false,
            'class' => 'wide',
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'competenceComment', array(
            'Label' => _('Комментарий к анкете'),
            'Required' => false,
            'class' => 'wide',
        ));

/*        $this->addElement($this->getDefaultWysiwygElementName(), 'competenceReportComment', array(
            'Label' => _('Комментарий к разделу "Оценка по компетенциям" в индивидуальном отчете'),
            'Required' => false,
            'class' => 'wide'
        ));
*/

        $this->addElement($this->getDefaultTextElementName(), 'competenceEmployedBeforeDays', array(
            'Label' => _('Не включать в оценку работников, находящимся в должности менее N дней'),
            'Description' => _('Настройка относится одновременно к участникам и респондентам оценочной сессии.'),
            'Required' => false,
        ));

/*        $this->addElement($this->getDefaultCheckboxElementName(), 'competenceDisableStop', array(
            'Label' => _('Запретить завершение сессии оценки'),
      	     'Description' => _('Если установлена данная опция, система блокирует запросы менеджеров и специалистов по оценке на завершение оценочных сессий.'),
            'Value' => 1,
        ));



        $this->addDisplayGroup(array(
            'competenceEmployedBeforeDays',
//            'competenceDisableStop',
        ),
            'general',
            array('legend' => 'Общие свойства')
        );
*/

        $this->addDisplayGroup(array(
            'competenceUseIndicators',
            'competenceUseScaleValues',
            'competenceUseIndicatorsDescriptions',
            'competenceUseIndicatorsReversive',
            'competenceUseIndicatorsScaleValues',
            'competenceScaleId',
            'competenceUseClusters',
//            'competenceUseRandom',
            'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
            'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
            'competenceEmployedBeforeDays',
            'sessionComment',
            'competenceComment',
//            'competenceReportComment',
        ),
            'competence',
            array('legend' => HM_At_Evaluation_Method_CompetenceModel::getMethodName())
        );



        
        $this->addElement($this->getDefaultCheckboxElementName(), 'kpiUseClusters', array(
            'Label' => _('Использовать кластеры показателей эффективности'),
        	'Description' => _('Если установлена данная опция, система позволяет объединять показатели эффективности в кластеры.'),
            'Value' => 1,
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
            'class' => 'wide'
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'kpiReportComment', array(
            'Label' => _('Комментарий к разделу "Оценка KPI" в индивидуальном отчете'),
            'Required' => false,
            'class' => 'wide'
        ));

        $this->addDisplayGroup(array(
            'kpiUseCriteria',
            'kpiScaleId',
            'kpiUseClusters',
            'kpiComment',
            'kpiReportComment',
        ),
            'kpi',
            array('legend' => HM_At_Evaluation_Method_KpiModel::getMethodName())
        );

        $this->addElement($this->getDefaultWysiwygElementName(), 'ratingComment', array(
            'Label' => _('Комментарий к анкете'),
            'Required' => false,
            'class' => 'wide'
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'ratingReportComment', array(
            'Label' => _('Комментарий к разделу "Парные сравнения" в индивидуальном отчете'),
            'Required' => false,
            'class' => 'wide'
        ));

        $this->addDisplayGroup(array(
            'ratingComment',
            'ratingReportComment',
        ),
            'rating',
            array('legend' => HM_At_Evaluation_Method_RatingModel::getMethodName())
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')));

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_AT_OPTIONS);
        $this->getService('EventDispatcher')->filter($event, $this);
        
        parent::init(); // required!
    }
}